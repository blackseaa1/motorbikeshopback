<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Customer;
use App\Models\DeliveryService;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách các đơn hàng với bộ lọc.
     */
    public function index(Request $request): View
    {
        // Giữ nguyên logic của phương thức index
        $query = Order::with(['customer', 'deliveryService'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('guest_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $allProductsForJs = Product::where('status', 'active')
            ->with('images')
            ->get();

        $initialStatuses = $this->getInitialOrderStatuses();
        $customers = Customer::where('status', 'active')->get(['id', 'name', 'email']);
        $deliveryServices = DeliveryService::where('status', true)->get();

        $promotions = Promotion::where('status', Promotion::STATUS_MANUAL_ACTIVE)
            ->where(function ($q) {
                $q->where('end_date', '>', now())
                    ->orWhereNull('end_date');
            })
            ->select('id', 'code', DB::raw('description as name'))
            ->get();

        $provinces = Province::orderBy('name', 'asc')->get();

        return view('admin.sales.order.orders', [
            'orders' => $orders,
            'orderStatuses' => Order::STATUSES,
            'allProductsForJs' => $allProductsForJs,
            'initialStatuses' => $initialStatuses,
            'customers' => $customers,
            'deliveryServices' => $deliveryServices,
            'promotions' => $promotions,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Lấy và trả về chi tiết của một đơn hàng dưới dạng JSON.
     * Dùng cho các yêu cầu AJAX từ front-end để hiển thị modal xem/sửa.
     * === SỬA LỖI & HOÀN THIỆN ===
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        try {
            // Tải các relationship cần thiết để hiển thị đầy đủ thông tin
            $order->load([
                'customer.addresses.province',
                'customer.addresses.district',
                'customer.addresses.ward',
                'province',
                'district',
                'ward',
                'deliveryService',
                'promotion',
                'items.product.images', // Tải sản phẩm và hình ảnh của sản phẩm
                'createdByAdmin'
            ]);

            // Lấy danh sách khuyến mãi hợp lệ để hiển thị trong dropdown
            $promotions = Promotion::where('status', Promotion::STATUS_MANUAL_ACTIVE)
                ->where(function ($q) {
                    $q->where('end_date', '>', now())->orWhereNull('end_date');
                })
                ->get(['id', 'code', 'description']);

            // Trả về dữ liệu thành công dưới dạng JSON
            return response()->json([
                'success' => true,
                'order' => $order,
                // Trả về cả danh sách địa chỉ của khách hàng nếu có
                'addresses' => $order->customer ? $order->customer->addresses()->with(['province', 'district', 'ward'])->get() : [],
                // Trả về danh sách khuyến mãi
                'promotions' => $promotions,
            ]);
        } catch (\Exception $e) {
            // Ghi lại log lỗi để debug
            Log::error("Lỗi khi lấy chi tiết đơn hàng (ID: {$order->id}): " . $e->getMessage());

            // Trả về thông báo lỗi
            return response()->json([
                'success' => false,
                'message' => 'Không thể tìm thấy hoặc có lỗi khi tải chi tiết đơn hàng.'
            ], 404);
        }
    }


    /**
     * Lưu một đơn hàng mới được tạo.
     * Xử lý cả yêu cầu AJAX và form truyền thống.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validation được điều chỉnh để phù hợp với logic của file gốc
        $validatedData = $request->validate([
            'customer_type' => ['required', Rule::in(['existing', 'guest'])],
            'customer_id' => ['nullable', 'exists:customers,id', 'required_if:customer_type,existing'],
            'guest_name' => ['nullable', 'string', 'max:255', 'required_if:customer_type,guest'],
            'guest_email' => ['nullable', 'email', 'max:255', 'required_if:customer_type,guest'],
            'guest_phone' => ['nullable', 'string', 'max:20', 'required_if:customer_type,guest'],
            'shipping_address_line' => ['nullable', 'string', 'max:255', 'required_if:customer_type,guest'],
            'province_id' => ['nullable', 'exists:provinces,id', 'required_if:customer_type,guest'],
            'district_id' => ['nullable', 'exists:districts,id', 'required_if:customer_type,guest'],
            'ward_id' => ['nullable', 'exists:wards,id', 'required_if:customer_type,guest'],
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'payment_method' => ['required', Rule::in(['cod', 'vnpay'])],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys($this->getInitialOrderStatuses()))],
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Logic tạo đơn hàng được lấy từ file gốc của bạn và điều chỉnh
            $order = new Order();
            $order->fill($request->except(['products', 'customer_type']));
            $order->created_by_admin_id = Auth::guard('admin')->id();
            $order->order_date = now();

            if ($request->customer_type === 'existing' && $request->customer_id) {
                $customer = Customer::find($request->customer_id);
                $defaultAddress = $customer->addresses()->where('is_default', true)->first();
                if ($defaultAddress) {
                    $order->customer_id = $customer->id;
                    $order->guest_name = $customer->name; // Sử dụng tên khách hàng cho đơn hàng
                    $order->guest_phone = $customer->phone;
                    $order->guest_email = $customer->email;
                    $order->shipping_address_line = $defaultAddress->address_line;
                    $order->province_id = $defaultAddress->province_id;
                    $order->district_id = $defaultAddress->district_id;
                    $order->ward_id = $defaultAddress->ward_id;
                }
            }

            $subTotal = 0;
            // Kiểm tra và tính toán tổng tiền
            foreach ($validatedData['products'] as $productData) {
                $product = Product::find($productData['product_id']);
                if ($product->stock_quantity < $productData['quantity']) {
                    throw ValidationException::withMessages(['products' => "Sản phẩm '{$product->name}' không đủ số lượng trong kho."]);
                }
                $subTotal += $product->price * $productData['quantity'];
            }
            $order->sub_total = $subTotal;

            $shippingFee = DeliveryService::find($validatedData['delivery_service_id'])->shipping_fee ?? 0;
            $order->shipping_fee = $shippingFee;

            $discount = 0;
            $promotion = null;
            if ($request->promotion_id) {
                $promotion = Promotion::find($request->promotion_id);
                if ($promotion && method_exists($promotion, 'isEffective') && $promotion->isEffective()) {
                    // Logic tính discount (ví dụ: theo phần trăm)
                    $discount = ($subTotal * $promotion->discount_percentage) / 100;
                }
            }
            $order->discount = $discount;
            $order->total_amount = $subTotal + $shippingFee - $discount;

            $order->save();

            // Lưu các mục trong đơn hàng (order_items)
            foreach ($validatedData['products'] as $productData) {
                $product = Product::find($productData['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                ]);

                // Trừ tồn kho nếu trạng thái là 'Đã duyệt'
                if ($order->status === Order::STATUS_APPROVED) {
                    $product->decrement('stock_quantity', $productData['quantity']);
                }
            }

            // Tăng số lần sử dụng promotion nếu có và trạng thái là 'Đã duyệt'
            if ($promotion && $order->status === Order::STATUS_APPROVED) {
                $promotion->increment('uses_count');
            }

            DB::commit();

            // NẾU LÀ YÊU CẦU AJAX, TRẢ VỀ JSON
            if ($request->expectsJson()) {
                // Tải lại các quan hệ cần thiết để hiển thị trên table
                $order->load('customer', 'deliveryService');
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo đơn hàng thành công!',
                    'order' => $order
                ]);
            }

            // Nếu là form thường, chuyển hướng
            return redirect()->route('admin.sales.orders.index')->with('success', 'Tạo đơn hàng thành công!');
        } catch (ValidationException $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra. Không thể tạo đơn hàng.'
                ], 500);
            }

            return back()->with('error', 'Đã có lỗi xảy ra. Không thể tạo đơn hàng.')->withInput();
        }
    }

    /**
     * Cập nhật một đơn hàng đã có trong CSDL.
     * Xử lý cả yêu cầu AJAX và form truyền thống.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Order $order)
    {
        // Validation được lấy từ file gốc của bạn
        $validatedData = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'guest_email' => ['required', 'email', 'max:255'],
            'shipping_address_line' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'exists:provinces,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'payment_method' => ['required', Rule::in(['cod', 'vnpay'])],
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.order_item_id' => ['nullable', 'exists:order_items,id'],
        ]);

        DB::beginTransaction();
        try {
            // Logic cập nhật được lấy và tinh chỉnh từ file gốc
            $oldStatus = $order->status;

            // Xử lý hoàn/trừ tồn kho cho các sản phẩm
            $this->handleStockOnUpdate($order, $validatedData['items']);

            // Cập nhật thông tin cơ bản của đơn hàng
            $order->fill($request->only([
                'guest_name',
                'guest_phone',
                'guest_email',
                'shipping_address_line',
                'province_id',
                'district_id',
                'ward_id',
                'payment_method',
                'delivery_service_id',
                'promotion_id',
                'status',
                'notes'
            ]));

            // Xóa các item cũ không còn trong request
            $currentItemIds = collect($validatedData['items'])->pluck('order_item_id')->filter();
            $order->items()->whereNotIn('id', $currentItemIds)->delete();

            // Cập nhật hoặc tạo mới các items
            foreach ($validatedData['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                OrderItem::updateOrCreate(
                    ['id' => $itemData['order_item_id'] ?? null, 'order_id' => $order->id],
                    ['product_id' => $product->id, 'quantity' => $itemData['quantity'], 'price' => $product->price]
                );
            }

            // Tải lại các items để tính toán lại tổng tiền
            $order->load('items');
            $subTotal = $order->items->sum(fn($item) => $item->price * $item->quantity);
            $shippingFee = DeliveryService::find($order->delivery_service_id)->shipping_fee ?? 0;
            $discount = 0;
            if ($order->promotion_id) {
                // Thêm logic tính discount nếu cần
            }
            $order->sub_total = $subTotal;
            $order->shipping_fee = $shippingFee;
            $order->discount = $discount;
            $order->total_amount = $subTotal + $shippingFee - $discount;

            $order->save();

            // Xử lý logic khi trạng thái thay đổi (ví dụ từ chờ sang đã duyệt)
            $this->handleStatusChangeLogic($order, $oldStatus, $order->status);

            DB::commit();

            // Tải lại các quan hệ để trả về dữ liệu mới nhất
            $order->load('customer', 'deliveryService', 'items.product');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật đơn hàng thành công!',
                    'order' => $order
                ]);
            }

            return redirect()->route('admin.sales.orders.index')->with('success', 'Cập nhật đơn hàng thành công!');
        } catch (ValidationException $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật đơn hàng (ID: {$order->id}): " . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra. Không thể cập nhật đơn hàng.'
                ], 500);
            }

            return back()->with('error', 'Đã có lỗi xảy ra khi cập nhật.')->withInput();
        }
    }


    /**
     * Phương thức xóa đơn hàng.
     */
    public function destroy(Order $order): JsonResponse|RedirectResponse
    {
        // Giữ nguyên logic của phương thức destroy
        if (!$order->isCancellable() && $order->status !== Order::STATUS_CANCELLED) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa đơn hàng ở một số trạng thái nhất định.'], 403);
        }

        DB::beginTransaction();
        try {
            if ($order->wasApproved()) { // wasApproved() là một phương thức giả định, bạn cần định nghĩa nó trong Model
                foreach ($order->items as $item) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
                if ($order->promotion) {
                    $order->promotion->decrement('uses_count');
                }
            }

            $order->items()->delete();
            $order->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đơn hàng đã được xóa thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa đơn hàng.'], 500);
        }
    }

    /**
     * Cập nhật trạng thái của một đơn hàng.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        // Giữ nguyên logic của phương thức updateStatus
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true, 'message' => 'Trạng thái không thay đổi.', 'order' => $order]);
        }

        DB::beginTransaction();
        try {
            $order->status = $newStatus;
            $order->save();

            $this->handleStatusChangeLogic($order, $oldStatus, $newStatus);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'order' => $order->refresh()->load('customer', 'deliveryService'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật trạng thái đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra.'], 500);
        }
    }

    /**
     * Helper để lấy các trạng thái đơn hàng ban đầu mà admin có thể thiết lập.
     */
    private function getInitialOrderStatuses(): array
    {
        return [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_APPROVED => 'Đã duyệt',
        ];
    }

    /**
     * Xử lý logic trừ/hoàn kho và khuyến mãi khi trạng thái đơn hàng thay đổi.
     */
    private function handleStatusChangeLogic(Order $order, string $oldStatus, string $newStatus): void
    {
        $wasApproved = in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]);
        $isApproved = in_array($newStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]);

        // Trường hợp 1: Chuyển sang trạng thái duyệt/giao hàng (trừ kho)
        if (!$wasApproved && $isApproved) {
            foreach ($order->items as $item) {
                $item->product->decrement('stock_quantity', $item->quantity);
            }
            if ($order->promotion_id && $order->promotion) {
                $order->promotion->increment('uses_count');
            }
        }
        // Trường hợp 2: Chuyển từ trạng thái đã duyệt sang hủy/trả hàng (hoàn kho)
        elseif ($wasApproved && !$isApproved) {
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
            if ($order->promotion_id && $order->promotion && $order->promotion->uses_count > 0) {
                $order->promotion->decrement('uses_count');
            }
        }
    }

    /**
     * Xử lý tồn kho khi cập nhật các sản phẩm trong đơn hàng.
     */
    private function handleStockOnUpdate(Order $order, array $newItemsData): void
    {
        // Chỉ xử lý tồn kho nếu đơn hàng ở trạng thái đã duyệt
        if (!in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
            return;
        }

        $originalItems = $order->items->keyBy('id');
        $newItems = collect($newItemsData)->keyBy('order_item_id');

        // Hoàn trả tồn kho cho các sản phẩm bị xóa
        foreach ($originalItems as $originalItem) {
            if (!$newItems->has($originalItem->id)) {
                $originalItem->product->increment('stock_quantity', $originalItem->quantity);
            }
        }

        // Cập nhật tồn kho cho các sản phẩm được giữ lại hoặc thêm mới
        foreach ($newItems as $newItemData) {
            $product = Product::find($newItemData['product_id']);
            $originalItem = $originalItems->get($newItemData['order_item_id']);
            $newQuantity = $newItemData['quantity'];
            $originalQuantity = $originalItem ? $originalItem->quantity : 0;

            $quantityDiff = $newQuantity - $originalQuantity;

            if ($quantityDiff > 0) { // Số lượng tăng -> trừ kho
                if ($product->stock_quantity < $quantityDiff) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm '{$product->name}' không đủ số lượng trong kho."]);
                }
                $product->decrement('stock_quantity', $quantityDiff);
            } elseif ($quantityDiff < 0) { // Số lượng giảm -> hoàn kho
                $product->increment('stock_quantity', abs($quantityDiff));
            }
        }
    }
}
