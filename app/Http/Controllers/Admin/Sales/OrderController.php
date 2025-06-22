<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryService;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng với bộ lọc và phân trang.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'deliveryService', 'province', 'district', 'ward'])->latest();

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo ID đơn hàng, tên khách hàng hoặc tên khách mời
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('guest_name', 'like', "%{$searchTerm}%")
                    ->orWhere('guest_email', 'like', "%{$searchTerm}%")
                    ->orWhere('guest_phone', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%")
                            ->orWhere('phone', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Phân trang với 20 bản ghi mỗi trang
        $orders = $query->paginate(20)->withQueryString();

        // Các trạng thái đơn hàng
        $orderStatuses = Order::STATUSES;

        // Dữ liệu cho form tạo đơn hàng (modal)
        $customers = Customer::where('status', Customer::STATUS_ACTIVE)->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $deliveryServices = DeliveryService::where('status', DeliveryService::STATUS_ACTIVE)->get();
        $promotions = Promotion::where('status', Promotion::STATUS_MANUAL_ACTIVE)->get();
        $provinces = Province::orderBy('name')->get();
        $initialOrderStatuses = [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_APPROVED => 'Đã duyệt',
        ];

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
     * Hiển thị chi tiết một đơn hàng.
     */
    public function show(Order $order): View|JsonResponse
    {
        $order->load([
            'items.product.images',
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

        return view('admin.sales.order.modals.view_order_modal', compact('order'));
    }

    /**
     * Lưu đơn hàng mới do admin tạo.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'customer_type' => ['required', Rule::in(['existing', 'guest'])],
            'customer_id' => ['nullable', 'exists:customers,id', 'required_if:customer_type,existing'],
            'guest_name' => ['nullable', 'string', 'max:255', 'required_if:customer_type,guest'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:20', 'required_if:customer_type,guest'],
            'guest_address_line' => ['nullable', 'string', 'max:255', 'required_if:customer_type,guest'],
            'guest_province_id' => ['nullable', 'exists:provinces,id', 'required_if:customer_type,guest'],
            'guest_district_id' => ['nullable', 'exists:districts,id', 'required_if:customer_type,guest'],
            'guest_ward_id' => ['nullable', 'exists:wards,id', 'required_if:customer_type,guest'],
            'shipping_address_id' => ['nullable', 'exists:customer_addresses,id'],
            'new_full_name' => ['nullable', 'string', 'max:255', 'required_if:customer_type,existing,shipping_address_id,null'],
            'new_phone' => ['nullable', 'string', 'max:20', 'required_if:customer_type,existing,shipping_address_id,null'],
            'new_province_id' => ['nullable', 'exists:provinces,id', 'required_if:customer_type,existing,shipping_address_id,null'],
            'new_district_id' => ['nullable', 'exists:districts,id', 'required_if:customer_type,existing,shipping_address_id,null'],
            'new_ward_id' => ['nullable', 'exists:wards,id', 'required_if:customer_type,existing,shipping_address_id,null'],
            'new_address_line' => ['nullable', 'string', 'max:255', 'required_if:customer_type,existing,shipping_address_id,null'],
            'set_default_address' => ['nullable', 'boolean'],
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'payment_method' => ['required', Rule::in(['cod', 'vnpay'])],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_APPROVED])],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'exists:products,id'],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            // Xử lý thông tin khách hàng
            $customer = null;
            $guestName = null;
            $guestEmail = null;
            $guestPhone = null;
            $provinceId = null;
            $districtId = null;
            $wardId = null;
            $shippingAddressLine = null;

            if ($validatedData['customer_type'] === 'existing') {
                $customer = Customer::findOrFail($validatedData['customer_id']);
                if ($validatedData['shipping_address_id']) {
                    $customerAddress = CustomerAddress::findOrFail($validatedData['shipping_address_id']);
                    if ($customerAddress->customer_id !== $customer->id) {
                        return response()->json(['success' => false, 'message' => 'Địa chỉ không thuộc khách hàng này.'], 422);
                    }
                    $provinceId = $customerAddress->province_id;
                    $districtId = $customerAddress->district_id;
                    $wardId = $customerAddress->ward_id;
                    $shippingAddressLine = $customerAddress->address_line;
                } else {
                    // Tạo địa chỉ mới cho khách hàng
                    $customerAddress = CustomerAddress::create([
                        'customer_id' => $customer->id,
                        'full_name' => $validatedData['new_full_name'],
                        'phone' => $validatedData['new_phone'],
                        'province_id' => $validatedData['new_province_id'],
                        'district_id' => $validatedData['new_district_id'],
                        'ward_id' => $validatedData['new_ward_id'],
                        'address_line' => $validatedData['new_address_line'],
                        'is_default' => $validatedData['set_default_address'] ?? false,
                    ]);
                    if ($customerAddress->is_default) {
                        CustomerAddress::where('customer_id', $customer->id)
                            ->where('id', '!=', $customerAddress->id)
                            ->update(['is_default' => false]);
                    }
                    $provinceId = $customerAddress->province_id;
                    $districtId = $customerAddress->district_id;
                    $wardId = $customerAddress->ward_id;
                    $shippingAddressLine = $customerAddress->address_line;
                }
            } else {
                $guestName = $validatedData['guest_name'];
                $guestEmail = $validatedData['guest_email'];
                $guestPhone = $validatedData['guest_phone'];
                $provinceId = $validatedData['guest_province_id'];
                $districtId = $validatedData['guest_district_id'];
                $wardId = $validatedData['guest_ward_id'];
                $shippingAddressLine = $validatedData['guest_address_line'];
            }

            // Tính toán giá trị đơn hàng
            $subtotal = 0;
            $orderItemsData = [];
            foreach ($validatedData['product_ids'] as $index => $productId) {
                $product = Product::findOrFail($productId);
                $quantity = $validatedData['quantities'][$index];

                if ($product->stock_quantity < $quantity) {
                    return response()->json(['success' => false, 'message' => "Sản phẩm \"{$product->name}\" không đủ số lượng trong kho (Còn: {$product->stock_quantity})."], 422);
                }

                $itemPrice = $product->price;
                $subtotal += $itemPrice * $quantity;

                $orderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $itemPrice,
                ];
            }

            // Phí vận chuyển
            $deliveryService = DeliveryService::findOrFail($validatedData['delivery_service_id']);
            $shippingFee = $deliveryService->shipping_fee ?? 0;

            // Áp dụng khuyến mãi
            $promotion = null;
            $discountAmount = 0;
            if ($validatedData['promotion_id']) {
                $promotion = Promotion::findOrFail($validatedData['promotion_id']);
                if ($promotion->isEffective()) {
                    $discountAmount = ($subtotal * $promotion->discount_percentage) / 100;
                } else {
                    return response()->json(['success' => false, 'message' => 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.'], 422);
                }
            }

            $totalPrice = $subtotal + $shippingFee - $discountAmount;

            // Tạo đơn hàng
            $order = Order::create([
                'customer_id' => $customer?->id,
                'guest_name' => $guestName,
                'guest_email' => $guestEmail,
                'guest_phone' => $guestPhone,
                'shipping_address_line' => $shippingAddressLine,
                'province_id' => $provinceId,
                'district_id' => $districtId,
                'ward_id' => $wardId,
                'status' => $validatedData['status'],
                'total_price' => $totalPrice,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'promotion_id' => $promotion?->id,
                'delivery_service_id' => $validatedData['delivery_service_id'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'],
                'created_by_admin_id' => Auth::guard('admin')->id(),
            ]);

            // Tạo các mục đơn hàng
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Cập nhật tồn kho và khuyến mãi nếu đơn hàng được duyệt
            if ($order->status === Order::STATUS_APPROVED) {
                foreach ($order->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->decrement('stock_quantity', $item->quantity);
                }
                if ($promotion) {
                    $promotion->increment('uses_count');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công!',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Cập nhật thông tin đơn hàng.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validatedData = $request->validate([
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật dịch vụ vận chuyển và ghi chú
            $deliveryService = DeliveryService::findOrFail($validatedData['delivery_service_id']);
            $shippingFee = $deliveryService->shipping_fee ?? 0;

            // Cập nhật tổng giá trị đơn hàng
            $order->delivery_service_id = $validatedData['delivery_service_id'];
            $order->shipping_fee = $shippingFee;
            $order->total_price = $order->subtotal + $shippingFee - $order->discount_amount;
            $order->notes = $validatedData['notes'];
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đơn hàng thành công!',
                'order' => $order->refresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật đơn hàng (ID: {$order->id}): " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Xóa đơn hàng.
     */
    public function destroy(Order $order): JsonResponse
    {
        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CANCELLED])) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa đơn hàng ở trạng thái chờ xử lý hoặc đã hủy.'], 403);
        }

        DB::beginTransaction();
        try {
            // Hoàn tồn kho và giảm lượt sử dụng khuyến mãi nếu đơn hàng đã duyệt
            if ($order->status === Order::STATUS_APPROVED) {
                foreach ($order->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->increment('stock_quantity', $item->quantity);
                }
                if ($order->promotion_id) {
                    $promotion = Promotion::findOrFail($order->promotion_id);
                    if ($promotion->uses_count > 0) {
                        $promotion->decrement('uses_count');
                    }
                }
            }

            // Xóa các mục đơn hàng và đơn hàng
            $order->items()->delete();
            $order->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Xóa đơn hàng thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa đơn hàng (ID: {$order->id}): " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validatedData = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validatedData['status'];

        DB::beginTransaction();
        try {
            $order->status = $newStatus;
            $order->save();

            // Cập nhật tồn kho và khuyến mãi
            if ($oldStatus !== Order::STATUS_APPROVED && $newStatus === Order::STATUS_APPROVED) {
                foreach ($order->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    if ($product->stock_quantity < $item->quantity) {
                        throw new \Exception("Sản phẩm \"{$product->name}\" không đủ số lượng trong kho.");
                    }
                    $product->decrement('stock_quantity', $item->quantity);
                }
                if ($order->promotion_id) {
                    $promotion = Promotion::findOrFail($order->promotion_id);
                    if ($promotion->isEffective()) {
                        $promotion->increment('uses_count');
                    }
                }
            } elseif ($oldStatus === Order::STATUS_APPROVED && $newStatus === Order::STATUS_CANCELLED) {
                foreach ($order->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->increment('stock_quantity', $item->quantity);
                }
                if ($order->promotion_id) {
                    $promotion = Promotion::findOrFail($order->promotion_id);
                    if ($promotion->uses_count > 0) {
                        $promotion->decrement('uses_count');
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'order' => $order->refresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật trạng thái đơn hàng (ID: {$order->id}): " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'], 500);
        }
    }
}
