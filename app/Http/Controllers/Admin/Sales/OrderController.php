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
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse; // Thêm import JsonResponse
use Illuminate\Http\RedirectResponse; // Thêm import RedirectResponse
use Illuminate\View\View; // Thêm import View

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách các đơn hàng với bộ lọc.
     */
    public function index(Request $request): View
    {
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

        $orders = $query->paginate(20)->withQueryString();

        $orderStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_RETURNED,
            Order::STATUS_FAILED,
            Order::STATUS_APPROVED,
        ];

        // Dữ liệu cho form tạo đơn hàng (modal)
        $customers = Customer::orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $deliveryServices = DeliveryService::where('status', 'active')->get();
        $promotions = Promotion::where('status', Promotion::STATUS_MANUAL_ACTIVE)->get();
        $provinces = Province::orderBy('name')->get();
        $initialOrderStatuses = $this->getInitialOrderStatuses();

        return view('admin.sales.order.orders', compact(
            'orders',
            'orderStatuses',
            'customers',
            'products',
            'deliveryServices',
            'promotions',
            'provinces',
            'initialOrderStatuses'
        ));
    }

    /**
     * Hiển thị chi tiết một đơn hàng cụ thể.
     * SỬA ĐỔI: Trả về JSON nếu là yêu cầu AJAX.
     */
    public function show(Order $order): View|JsonResponse
    {
        $order->load([
            'items.product.images', // Tải ảnh sản phẩm
            'customer',
            'promotion',
            'province',
            'district',
            'ward',
            'deliveryService',
            'createdByAdmin'
        ]);

        if (request()->expectsJson()) {
            return response()->json($order);
        }

        return view('admin.sales.order.modals.order_show', compact('order'));
    }

    /**
     * Phương thức hiển thị form tạo đơn hàng mới cho admin.
     * SỬA ĐỔI: Phương thức này không còn được gọi trực tiếp qua route nữa
     * vì form đã được chuyển vào modal trên trang index.
     * Vẫn giữ code để tham khảo.
     */
    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $deliveryServices = DeliveryService::where('status', 'active')->get();
        $promotions = Promotion::where('status', Promotion::STATUS_MANUAL_ACTIVE)->get();
        $provinces = Province::orderBy('name')->get();
        $initialOrderStatuses = $this->getInitialOrderStatuses();

        return view('admin.sales.order.create', compact(
            'customers',
            'products',
            'deliveryServices',
            'promotions',
            'provinces',
            'initialOrderStatuses'
        ));
    }

    /**
     * Phương thức lưu đơn hàng mới do admin tạo.
     * SỬA ĐỔI: Trả về JsonResponse.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validate([
            'customer_type' => ['required', Rule::in(['existing', 'guest'])],
            'customer_id' => ['nullable', 'exists:customers,id', 'required_if:customer_type,existing'],
            'guest_name' => ['nullable', 'string', 'max:255', 'required_if:customer_type,guest'],
            'guest_email' => ['nullable', 'email', 'max:255', 'required_if:customer_type,guest'],
            'guest_phone' => ['nullable', 'string', 'max:20', 'required_if:customer_type,guest'],
            'guest_address_line' => ['nullable', 'string', 'max:255', 'required_if:customer_type,guest'],
            'guest_province_id' => ['nullable', 'exists:provinces,id', 'required_if:customer_type,guest'],
            'guest_district_id' => ['nullable', 'exists:districts,id', 'required_if:customer_type,guest'],
            'guest_ward_id' => ['nullable', 'exists:wards,id', 'required_if:customer_type,guest'],
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'payment_method' => ['required', Rule::in(['cod', 'vnpay'])],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys($this->getInitialOrderStatuses()))],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'exists:products,id'],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $customer = null;
            $guestName = null;
            $guestEmail = null;
            $guestPhone = null;
            $provinceId = null;
            $districtId = null;
            $wardId = null;
            $shippingAddressLine = null;

            if ($validatedData['customer_type'] === 'existing') {
                $customer = Customer::find($validatedData['customer_id']);
                $customerAddress = $customer->addresses()->where('is_default', true)->first();
                if (!$customerAddress) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Khách hàng đã chọn chưa có địa chỉ mặc định.'], 422);
                }
                $provinceId = $customerAddress->province_id;
                $districtId = $customerAddress->district_id;
                $wardId = $customerAddress->ward_id;
                $shippingAddressLine = $customerAddress->address_line;
            } else {
                $guestName = $validatedData['guest_name'];
                $guestEmail = $validatedData['guest_email'];
                $guestPhone = $validatedData['guest_phone'];
                $provinceId = $validatedData['guest_province_id'];
                $districtId = $validatedData['guest_district_id'];
                $wardId = $validatedData['guest_ward_id'];
                $shippingAddressLine = $validatedData['guest_address_line'];
            }

            $subtotal = 0;
            $orderItemsData = [];
            foreach ($validatedData['product_ids'] as $index => $productId) {
                $product = Product::find($productId);
                $quantity = $validatedData['quantities'][$index];

                if (!$product) { // Sản phẩm không tồn tại
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Sản phẩm với ID {$productId} không tồn tại."], 422);
                }
                if ($product->stock_quantity < $quantity) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Sản phẩm \"{$product->name}\" không đủ số lượng trong kho. (Còn: {$product->stock_quantity})"], 422);
                }

                $itemPrice = $product->price;
                $subtotal += $itemPrice * $quantity;

                $orderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $itemPrice,
                ];
            }

            $deliveryService = DeliveryService::find($validatedData['delivery_service_id']);
            $shippingFee = $deliveryService->shipping_fee ?? 0;

            $promotion = null;
            $discountAmount = 0;
            if ($validatedData['promotion_id']) {
                $promotion = Promotion::find($validatedData['promotion_id']);
                if ($promotion && $promotion->isEffective()) {
                    $discountAmount = ($subtotal * $promotion->discount_percentage) / 100;
                }
            }

            $totalPrice = $subtotal + $shippingFee - $discountAmount;

            $order = Order::create([
                'customer_id' => $customer?->id,
                'guest_name' => $guestName,
                'guest_email' => $guestEmail,
                'guest_phone' => $guestPhone,
                'province_id' => $provinceId,
                'district_id' => $districtId,
                'ward_id' => $wardId,
                'status' => $validatedData['status'],
                'total_price' => $totalPrice,
                'promotion_id' => $promotion->id ?? null,
                'delivery_service_id' => $validatedData['delivery_service_id'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'] ?? null,
                'created_by_admin_id' => Auth::guard('admin')->id(),
                // 'address_line' => $shippingAddressLine, // Thêm nếu có cột này trong DB
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // LOGIC: Trừ tồn kho và tăng lượt sử dụng mã giảm giá NẾU đơn hàng được tạo với trạng thái DUYỆT
            if ($order->status === Order::STATUS_APPROVED) {
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock_quantity', $item->quantity);
                    }
                }

                if ($order->promotion_id) {
                    $promotion->increment('uses_count');
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Đơn hàng mới đã được tạo thành công!', 'order_id' => $order->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi admin tạo đơn hàng: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra trong quá trình tạo đơn hàng. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * SỬA ĐỔI: Phương thức cập nhật đơn hàng.
     */
    public function update(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validate([
            // Có thể cho phép admin cập nhật một số thông tin (ví dụ: ghi chú, dịch vụ vận chuyển)
            // Hoặc chỉ cho phép update status qua updateStatus method riêng.
            // Để đơn giản, tôi chỉ cho phép update notes và delivery_service_id qua đây.
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // 'status' => ['nullable', Rule::in(array_keys($this->getInitialOrderStatuses()))], // Không cho phép update status qua đây để tránh trùng lặp
        ]);

        DB::beginTransaction();
        try {
            $order->delivery_service_id = $validatedData['delivery_service_id'];
            $order->notes = $validatedData['notes'];
            // Nếu cần cập nhật lại tổng tiền khi đổi dịch vụ vận chuyển, cần logic phức tạp hơn
            // $order->total_price = $order->subtotal + ($order->deliveryService->shipping_fee ?? 0) - $order->discount_amount;
            $order->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật đơn hàng thành công!', 'order' => $order->refresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi admin cập nhật đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng.'], 500);
        }
    }

    /**
     * SỬA ĐỔI: Phương thức xóa đơn hàng.
     */
    public function destroy(Order $order): JsonResponse|RedirectResponse
    {
        // Bạn có thể thêm logic kiểm tra quyền ở đây (ví dụ: chỉ admin có quyền xóa)
        // Hoặc chỉ cho phép xóa đơn hàng ở trạng thái nhất định (ví dụ: đã hủy, pending)
        if ($order->status !== Order::STATUS_PENDING && $order->status !== Order::STATUS_CANCELLED) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa đơn hàng đang chờ xử lý hoặc đã hủy.'], 403);
        }

        DB::beginTransaction();
        try {
            // Hoàn trả số lượng sản phẩm và lượt sử dụng khuyến mãi nếu đơn hàng đã duyệt và bị xóa
            if ($order->status === Order::STATUS_APPROVED) {
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    if ($promotion && $promotion->uses_count > 0) {
                        $promotion->decrement('uses_count');
                    }
                }
            }

            $order->items()->delete(); // Xóa các mục đơn hàng trước
            $order->delete(); // Sau đó xóa đơn hàng

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đơn hàng đã được xóa thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi admin xóa đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa đơn hàng.'], 500);
        }
    }


    /**
     * Cập nhật trạng thái của một đơn hàng.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                Order::STATUS_FAILED,
                Order::STATUS_APPROVED,
            ])],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        DB::beginTransaction();
        try {
            $order->status = $newStatus;
            $order->save();

            if ($oldStatus !== Order::STATUS_APPROVED && $newStatus === Order::STATUS_APPROVED) {
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock_quantity', $item->quantity);
                    }
                }

                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    if ($promotion && $promotion->isEffective() && ($promotion->max_uses === null || $promotion->uses_count < $promotion->max_uses)) {
                        $promotion->increment('uses_count');
                    }
                }
            } elseif ($oldStatus === Order::STATUS_APPROVED && $newStatus === Order::STATUS_CANCELLED) {
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    if ($promotion && $promotion->uses_count > 0) {
                        $promotion->decrement('uses_count');
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'order' => $order->refresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật trạng thái đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra.'], 500);
        }
    }

    /**
     * Helper để lấy các trạng thái đơn hàng ban đầu mà admin có thể thiết lập.
     * @return array
     */
    private function getInitialOrderStatuses(): array
    {
        return [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_APPROVED => 'Đã duyệt',
        ];
    }
}
