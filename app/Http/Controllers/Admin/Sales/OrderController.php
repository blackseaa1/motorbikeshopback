<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\PaymentMethod; // Import PaymentMethod model
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng với bộ lọc và phân trang.
     * Đồng thời, tải sẵn tất cả dữ liệu cần thiết cho các modal.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'deliveryService', 'paymentMethod'])->latest(); // Eager load paymentMethod

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', fn($subQ) => $subQ->where('name', 'like', "%{$searchTerm}%"))
                    ->orWhere('shipping_name', 'like', "%{$searchTerm}%") // Tìm kiếm theo guest_name
                    ->orWhere('shipping_email', 'like', "%{$searchTerm}%") // Tìm kiếm theo guest_email
                    ->orWhere('shipping_phone', 'like', "%{$searchTerm}%"); // Tìm kiếm theo guest_phone
            });
        }

        // Sử dụng pagination.blade.php để phân trang
        $orders = $query->paginate(config('admin.pagination.per_page', 10))->withQueryString();

        // Tải sẵn dữ liệu cho các modal
        $customers = Customer::where('status', Customer::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name', 'email']);
        $provinces = Province::orderBy('name')->get(['id', 'name']);
        $deliveryServices = DeliveryService::where('status', DeliveryService::STATUS_ACTIVE)->get(['id', 'name', 'shipping_fee']);
        // Chỉ lấy phương thức thanh toán COD và Bank Transfer
        $paymentMethods = PaymentMethod::whereIn('code', ['cod', 'bank_transfer'])->where('status', 'active')->get();
        $promotions = Promotion::where('status', Promotion::STATUS_MANUAL_ACTIVE)
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>', now()))
            ->get();
        $orderStatuses = Order::STATUSES;

        return view('admin.sales.order.orders', compact(
            'orders',
            'customers',
            'provinces',
            'deliveryServices',
            'promotions',
            'orderStatuses',
            'paymentMethods'
        ));
    }

    /**
     * Lưu một đơn hàng mới được tạo từ admin panel.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateOrderRequest($request);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            // Logic xử lý địa chỉ mới cho khách hàng có sẵn
            if ($validated['shipping_address_option'] === 'new' && $validated['customer_type'] === 'existing') {
                $newAddress = CustomerAddress::create([
                    'customer_id'    => $validated['customer_id'],
                    'full_name'      => $validated['new_shipping_name'],
                    'phone'          => $validated['new_shipping_phone'],
                    'province_id'    => $validated['new_province_id'],
                    'district_id'    => $validated['new_district_id'],
                    'ward_id'        => $validated['new_ward_id'],
                    'address_line'   => $validated['new_address_line'],
                    'is_default'     => false,
                ]);
                // Gán ID địa chỉ mới để lưu vào đơn hàng
                $validated['shipping_address_id'] = $newAddress->id;
            }

            // Tính toán tổng giá trị đơn hàng
            $calculation = $this->calculateOrderTotals($validated['items'], $validated['delivery_service_id'], $validated['promotion_id'] ?? null);

            $order = new Order();
            // Gán thông tin khách hàng và địa chỉ
            $this->assignCustomerAndAddress($order, $validated);

            $order->fill([
                'payment_method_id'   => $validated['payment_method_id'], // Sử dụng payment_method_id từ request
                'status'              => $validated['status'],
                'notes'               => $validated['notes'],
                'delivery_service_id' => $validated['delivery_service_id'],
                'promotion_id'        => $calculation['promotion_id'],
                'subtotal'            => $calculation['subtotal'],
                'shipping_fee'        => $calculation['shipping_fee'],
                'discount_amount'     => $calculation['discount_amount'],
                'total_price'         => $calculation['grand_total'],
                'created_by_admin_id' => Auth::id(),
            ]);
            $order->save();

            $this->syncOrderItems($order, $validated['items']);

            if ($order->status === Order::STATUS_APPROVED) {
                $this->handleStockAndPromotionOnStatusChange($order, null, Order::STATUS_APPROVED);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tạo đơn hàng thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi tạo đơn hàng từ admin: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Đã có lỗi nghiêm trọng xảy ra: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Trả về chi tiết đơn hàng dưới dạng JSON cho modal view/update.
     */
    public function show(Order $order): JsonResponse
    {
        // Thêm eager loading cho paymentMethod
        $order->load(['customer', 'deliveryService', 'province', 'district', 'ward', 'promotion', 'items.product', 'paymentMethod']);
        return response()->json($order);
    }

    /**
     * Cập nhật thông tin đơn hàng (chủ yếu là trạng thái).
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus && $order->notes === $validated['notes']) {
            return response()->json(['success' => true, 'message' => 'Không có gì thay đổi.']);
        }

        DB::beginTransaction();
        try {
            $order->status = $newStatus;
            $order->notes = $validated['notes'];
            $order->save();

            $this->handleStockAndPromotionOnStatusChange($order, $oldStatus, $newStatus);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật đơn hàng thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật đơn hàng #{$order->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra khi cập nhật.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn một đơn hàng.
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        if (config('admin.deletion_password') && $request->input('password') !== config('admin.deletion_password')) {
            return response()->json(['success' => false, 'errors' => ['password' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        DB::beginTransaction();
        try {
            // Hoàn trả số lượng sản phẩm và lượt sử dụng khuyến mãi nếu đơn hàng đã được duyệt
            if ($order->status === Order::STATUS_APPROVED) {
                $this->handleStockAndPromotionOnStatusChange($order, Order::STATUS_APPROVED, Order::STATUS_CANCELLED);
            }

            // Xóa tất cả các mục đơn hàng (order items) liên quan
            $order->items()->delete();
            $order->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => "Đã xóa vĩnh viễn đơn hàng #{$order->id}."]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa đơn hàng #{$order->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa đơn hàng. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * API: Lấy danh sách sản phẩm để sử dụng trong modal tạo đơn hàng.
     * Trả về dữ liệu JSON tinh gọn để JavaScript xử lý.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsForOrderCreation(): JsonResponse
    {
        // Lấy sản phẩm đang hoạt động, kèm hình ảnh đầu tiên.
        // Giả định Model Product có accessor `thumbnail_url` để lấy URL ảnh nhỏ.
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->with('firstImage')
            ->orderBy('name')
            ->get();

        // Chuyển đổi collection sang một array với các thuộc tính cần thiết cho JS
        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'thumbnail_url' => $product->thumbnail_url, // Sử dụng accessor từ Model
            ];
        });

        return response()->json($productsData);
    }

    /**
     * API: Tính toán tóm tắt đơn hàng (subtotal, shipping, discount, total)
     * dựa trên dữ liệu gửi từ frontend (items, delivery_service, promotion).
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateOrderSummaryApi(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'items'                 => ['nullable', 'array'], // Cho phép rỗng nếu chưa có sản phẩm
                'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
                'items.*.quantity'      => ['required', 'integer', 'min:1'],
                'delivery_service_id'   => ['nullable', 'integer', 'exists:delivery_services,id'], // Có thể rỗng ban đầu
                'promotion_id'          => ['nullable', 'integer', 'exists:promotions,id'],
            ]);

            // Nếu có lỗi validation, trả về 422
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $items = $validated['items'] ?? [];
            $deliveryServiceId = $validated['delivery_service_id'] ?? null;
            $promotionId = $validated['promotion_id'] ?? null;

            // Sử dụng lại hàm tính toán tổng đơn hàng đã có
            $calculation = $this->calculateOrderTotals($items, $deliveryServiceId, $promotionId);

            // Trả về dữ liệu JSON đã tính toán
            return response()->json([
                'success' => true,
                'summary' => [
                    'subtotal'        => $calculation['subtotal'],
                    'shipping_fee'    => $calculation['shipping_fee'],
                    'discount_amount' => $calculation['discount_amount'],
                    'total_price'     => $calculation['grand_total'],
                    'promotion_id'    => $calculation['promotion_id'], // Có thể trả về ID khuyến mãi hợp lệ
                ]
            ]);
        } catch (ValidationException $e) {
            // Xử lý các lỗi validation cụ thể để trả về
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Lỗi API tính toán tóm tắt đơn hàng: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tính toán tóm tắt đơn hàng.'], 500);
        }
    }

    // =========================================================================
    // == PRIVATE HELPER METHODS
    // =========================================================================

    private function validateOrderRequest(Request $request)
    {
        $rules = [
            'customer_type'         => ['required', Rule::in(['existing', 'guest'])],
            'shipping_address_option' => ['required', Rule::in(['existing', 'new'])],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'delivery_service_id'   => ['required', 'integer', 'exists:delivery_services,id'],
            'promotion_id'          => ['nullable', 'integer', 'exists:promotions,id'],
            'status'                => ['required', Rule::in(array_keys(Order::STATUSES))],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'payment_method_id'     => ['required', 'integer', Rule::exists('payment_methods', 'id')->where(function ($query) {
                // Chỉ cho phép COD và Bank Transfer
                $query->whereIn('code', ['cod', 'bank_transfer']);
            })],
        ];

        $customerType = $request->input('customer_type');
        $addressOption = $request->input('shipping_address_option');

        if ($customerType === 'existing') {
            $rules['customer_id'] = ['required', 'integer', 'exists:customers,id'];

            if ($addressOption === 'existing') {
                $rules['shipping_address_id'] = ['required', 'integer', 'exists:customer_addresses,id'];
            } else { // 'new' address for existing customer
                $rules['new_shipping_name']  = ['required', 'string', 'max:255'];
                $rules['new_shipping_phone'] = ['required', 'string', 'max:20'];
                $rules['new_province_id']    = ['required', 'integer', 'exists:provinces,id'];
                $rules['new_district_id']    = ['required', 'integer', 'exists:districts,id'];
                $rules['new_ward_id']        = ['required', 'integer', 'exists:wards,id'];
                $rules['new_address_line']   = ['required', 'string', 'max:255'];
            }
        } else { // 'guest'
            $rules['guest_name']          = ['required', 'string', 'max:255'];
            $rules['guest_phone']         = ['required', 'string', 'max:20'];
            $rules['guest_email']         = ['nullable', 'email', 'max:255'];
            $rules['guest_province_id']   = ['required', 'integer', 'exists:provinces,id'];
            $rules['guest_district_id']   = ['required', 'integer', 'exists:districts,id'];
            $rules['guest_ward_id']       = ['required', 'integer', 'exists:wards,id'];
            $rules['guest_address_line']  = ['required', 'string', 'max:255'];
        }

        return Validator::make($request->all(), $rules);
    }

    private function assignCustomerAndAddress(Order &$order, array $validatedData): void
    {
        if ($validatedData['customer_type'] === 'existing') {
            // Lấy thông tin địa chỉ mà khách hàng đã chọn từ sổ địa chỉ
            $address = CustomerAddress::with('customer')->findOrFail($validatedData['shipping_address_id']);

            $order->customer_id = $validatedData['customer_id'];

            // Sao chép thông tin vào các cột tương ứng của đơn hàng
            $order->shipping_name = $address->full_name; // Sử dụng shipping_name
            $order->shipping_phone = $address->phone;   // Sử dụng shipping_phone
            $order->shipping_email = $address->customer->email; // Sử dụng shipping_email
            $order->province_id = $address->province_id;
            $order->district_id = $address->district_id;
            $order->ward_id = $address->ward_id;

            // Lưu địa chỉ chi tiết vào cột mới
            $order->shipping_address_line = $address->address_line;
        } else { // Xử lý cho khách vãng lai
            $order->customer_id = null;

            // Gán thông tin từ form của khách vãng lai
            $order->shipping_name = $validatedData['guest_name']; // Sử dụng shipping_name
            $order->shipping_phone = $validatedData['guest_phone']; // Sử dụng shipping_phone
            $order->shipping_email = $validatedData['guest_email']; // Sử dụng shipping_email
            $order->province_id = $validatedData['guest_province_id'];
            $order->district_id = $validatedData['guest_district_id'];
            $order->ward_id = $validatedData['guest_ward_id'];

            // Lưu địa chỉ chi tiết vào cột mới
            $order->shipping_address_line = $validatedData['guest_address_line'];
        }
    }

    private function calculateOrderTotals(array $items, ?int $deliveryServiceId, ?int $promotionId): array
    {
        $subtotal = 0;
        $productIds = array_column($items, 'product_id');
        // Chỉ tìm sản phẩm nếu có productIds
        $products = !empty($productIds) ? Product::find($productIds)->keyBy('id') : collect();


        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if ($product) {
                $subtotal += $product->price * $item['quantity'];
            }
        }

        $shippingFee = 0;
        if ($deliveryServiceId) {
            $deliveryService = DeliveryService::find($deliveryServiceId); // Dùng find thay vì findOrFail để tránh exception nếu ID không tồn tại
            if ($deliveryService) {
                $shippingFee = $deliveryService->shipping_fee;
            }
        }

        $discountAmount = 0;
        $validPromotionId = null;
        if ($promotionId) {
            $promotion = Promotion::find($promotionId);
            if ($promotion && $promotion->isEffective()) {
                $discountAmount = $promotion->calculateDiscount($subtotal);
                $validPromotionId = $promotion->id;
            }
        }

        $grandTotal = $subtotal + $shippingFee - $discountAmount;

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'grand_total' => max(0, $grandTotal), // Đảm bảo tổng cộng không âm
            'promotion_id' => $validPromotionId,
        ];
    }

    private function syncOrderItems(Order $order, array $items): void
    {
        $productIds = array_column($items, 'product_id');
        $products = Product::find($productIds)->keyBy('id');

        // Xóa tất cả các mục đơn hàng (order items) cũ trước khi thêm mới
        $order->items()->delete();

        // Lặp qua và tạo các mục đơn hàng mới
        foreach ($items as $itemData) {
            $product = $products->get($itemData['product_id']);
            if ($product) {
                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("Sản phẩm '{$product->name}' không đủ số lượng tồn kho.");
                }
                // Sử dụng create() để tạo OrderItem mới liên kết với Order
                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity'   => $itemData['quantity'],
                    'price'      => $product->price,
                ]);
            }
        }
    }

    private function handleStockAndPromotionOnStatusChange(Order $order, ?string $oldStatus, string $newStatus): void
    {
        $order->load('items.product', 'promotion');

        // Khi trạng thái chuyển sang Đã duyệt (Approved)
        if ($oldStatus !== Order::STATUS_APPROVED && $newStatus === Order::STATUS_APPROVED) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }
            $order->promotion?->increment('uses_count'); // Tăng lượt sử dụng khuyến mãi
        }
        // Khi trạng thái chuyển từ Đã duyệt sang Đã hủy hoặc Đã trả hàng
        elseif ($oldStatus === Order::STATUS_APPROVED && ($newStatus === Order::STATUS_CANCELLED || $newStatus === Order::STATUS_RETURNED)) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }
            // Giảm lượt sử dụng khuyến mãi nếu có và lớn hơn 0
            if ($order->promotion && $order->promotion->uses_count > 0) {
                $order->promotion->decrement('uses_count');
            }
        }
    }
}
