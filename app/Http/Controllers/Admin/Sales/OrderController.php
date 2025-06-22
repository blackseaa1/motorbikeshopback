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
use App\Models\OrderItem; // Đảm bảo đã import
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse; // Giữ lại nếu có các phần khác vẫn dùng
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách các đơn hàng với bộ lọc.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'deliveryService', 'province', 'district', 'ward', 'promotion'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('guest_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%")
                            ->orWhere('phone', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $orders = $query->paginate(10);

        $orderStatuses = Order::STATUSES;

        $customers = Customer::select('id', 'name', 'email', 'phone')->orderBy('name')->get();
        // Lấy tất cả sản phẩm đang active để thêm vào đơn hàng
        $products = Product::active()->orderBy('name')->get();
        $deliveryServices = DeliveryService::where('status', 'active')->get(['id', 'name', 'shipping_fee']);
        $promotions = Promotion::where('status', 'active')->get(['id', 'code', 'description', 'discount_percentage']);
        $provinces = Province::orderBy('name')->get(['id', 'name']);
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
     * Lấy danh sách quận/huyện theo tỉnh/thành.
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $provinceId = $request->input('province_id');
        $districts = District::where('province_id', $provinceId)->get(['id', 'name']);
        return response()->json($districts);
    }

    /**
     * Lấy danh sách phường/xã theo quận/huyện.
     */
    public function getWards(Request $request): JsonResponse
    {
        $districtId = $request->input('district_id');
        $wards = Ward::where('district_id', $districtId)->get(['id', 'name']);
        return response()->json($wards);
    }

    /**
     * Lấy thông tin chi tiết sản phẩm. (Có thể không cần dùng trực tiếp nếu đã load tất cả products vào JS)
     */
    public function getProductDetails(Request $request): JsonResponse
    {
        $product = Product::select('id', 'name', 'price', 'stock_quantity')
            ->find($request->product_id);

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        return response()->json($product);
    }

    /**
     * Lấy địa chỉ khách hàng.
     */
    public function getCustomerAddresses(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['error' => 'Khách hàng không tồn tại.'], 404);
        }

        $addresses = $customer->addresses()->with(['province', 'district', 'ward'])->get();

        return response()->json($addresses);
    }


    /**
     * Lưu đơn hàng mới được tạo bởi Admin.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'customer_type' => ['required', Rule::in(['existing', 'guest'])],
                'customer_id' => ['nullable', 'required_if:customer_type,existing', 'exists:customers,id'],
                'shipping_address_id' => ['nullable', 'required_if:customer_type,existing', 'exists:customer_addresses,id'],
                'guest_name' => ['nullable', 'required_if:customer_type,guest', 'string', 'max:255'],
                'guest_email' => ['nullable', 'required_if:customer_type,guest', 'email', 'max:255'],
                'guest_phone' => ['nullable', 'required_if:customer_type,guest', 'string', 'max:100'],
                'province_id' => ['nullable', 'required_if:customer_type,guest', 'exists:provinces,id'],
                'district_id' => ['nullable', 'required_if:customer_type,guest', 'exists:districts,id'],
                'ward_id' => ['nullable', 'required_if:customer_type,guest', 'exists:wards,id'],
                'shipping_address_line' => ['nullable', 'required_if:customer_type,guest', 'string', 'max:255'],
                'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
                'delivery_service_id' => ['required', 'exists:delivery_services,id'],
                'promotion_id' => ['nullable', 'exists:promotions,id'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'exists:products,id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.price' => ['nullable', 'numeric', 'min:0'], // Added for safety, will be recalculated
            ], [
                'customer_id.required_if' => 'Vui lòng chọn khách hàng có sẵn nếu loại khách hàng là \'Khách hàng có sẵn\'.',
                'customer_id.exists' => 'Khách hàng không tồn tại.',
                'shipping_address_id.required_if' => 'Vui lòng chọn địa chỉ giao hàng nếu loại khách hàng là \'Khách hàng có sẵn\'.',
                'shipping_address_id.exists' => 'Địa chỉ giao hàng không tồn tại.',
                'guest_name.required_if' => 'Tên khách vãng lai là bắt buộc.',
                'guest_email.required_if' => 'Email khách vãng lai là bắt buộc.',
                'guest_phone.required_if' => 'Số điện thoại khách vãng lai là bắt buộc.',
                'province_id.required_if' => 'Tỉnh/Thành phố là bắt buộc nếu là khách vãng lai.',
                'district_id.required_if' => 'Quận/Huyện là bắt buộc nếu là khách vãng lai.',
                'ward_id.required_if' => 'Phường/Xã là bắt buộc nếu là khách vãng lai.',
                'shipping_address_line.required_if' => 'Địa chỉ chi tiết là bắt buộc nếu là khách vãng lai.',
                'payment_method.required' => 'Phương thức thanh toán là bắt buộc.',
                'delivery_service_id.required' => 'Dịch vụ vận chuyển là bắt buộc.',
                'delivery_service_id.exists' => 'Dịch vụ vận chuyển không tồn tại.',
                'promotion_id.exists' => 'Mã khuyến mãi không tồn tại.',
                'items.required' => 'Đơn hàng phải có ít nhất một sản phẩm.',
                'items.array' => 'Dữ liệu sản phẩm không hợp lệ.',
                'items.*.product_id.required' => 'Mã sản phẩm là bắt buộc.',
                'items.*.product_id.exists' => 'Sản phẩm không tồn tại.',
                'items.*.quantity.required' => 'Số lượng sản phẩm là bắt buộc.',
                'items.*.quantity.integer' => 'Số lượng phải là số nguyên.',
                'items.*.quantity.min' => 'Số lượng sản phẩm phải ít nhất là :min.',
                'status.required' => 'Trạng thái đơn hàng là bắt buộc.',
                'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            ]);

            DB::beginTransaction();

            $customer = null;
            $shippingAddressInfo = [];
            if ($validatedData['customer_type'] === 'existing') {
                $customer = Customer::find($validatedData['customer_id']);
                $address = CustomerAddress::with(['province', 'district', 'ward'])
                    ->find($validatedData['shipping_address_id']);
                if (!$customer || !$address || $address->customer_id !== $customer->id) {
                    // Cần ném ValidationException với định dạng errors để JS bắt được
                    throw ValidationException::withMessages(['shipping_address_id' => 'Địa chỉ không thuộc về khách hàng đã chọn.']);
                }
                $shippingAddressInfo = [
                    'name' => $address->full_name,
                    'email' => $customer->email,
                    'phone' => $address->phone,
                    'province_id' => $address->province_id,
                    'district_id' => $address->district_id,
                    'ward_id' => $address->ward_id,
                    'full_address_line' => $address->address_line,
                ];
            } else { // guest
                $shippingAddressInfo = [
                    'name' => $validatedData['guest_name'],
                    'email' => $validatedData['guest_email'],
                    'phone' => $validatedData['guest_phone'],
                    'province_id' => $validatedData['province_id'],
                    'district_id' => $validatedData['district_id'],
                    'ward_id' => $validatedData['ward_id'],
                    'full_address_line' => $validatedData['shipping_address_line'],
                ];
            }

            $subtotal = 0;
            $orderItemsData = [];
            $productIds = collect($validatedData['items'])->pluck('product_id')->toArray();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($validatedData['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages(['items' => 'Một hoặc nhiều sản phẩm không tồn tại.']);
                }
                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm {$product->name} chỉ còn {$product->stock_quantity} trong kho."]);
                }
                $itemPrice = $product->price; // Luôn lấy giá từ database để tránh bị sửa đổi từ frontend
                $subtotal += ($itemPrice * $item['quantity']);
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $itemPrice,
                ];
            }

            $promotion = null;
            $discountAmount = 0;
            if ($validatedData['promotion_id']) {
                $promotion = Promotion::find($validatedData['promotion_id']);
                if ($promotion && $promotion->status === 'active' && now()->between($promotion->start_date, $promotion->end_date)) {
                    $discountAmount = ($subtotal * $promotion->discount_percentage) / 100;
                    $discountAmount = min($discountAmount, $subtotal);
                } else {
                    throw ValidationException::withMessages(['promotion_id' => 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.']);
                }
            }

            $deliveryService = DeliveryService::find($validatedData['delivery_service_id']);
            if (!$deliveryService) {
                throw ValidationException::withMessages(['delivery_service_id' => 'Dịch vụ vận chuyển không hợp lệ.']);
            }
            $shippingFee = $deliveryService->shipping_fee;

            $totalPrice = ($subtotal + $shippingFee - $discountAmount);
            if ($totalPrice < 0) $totalPrice = 0;

            $order = Order::create([
                'customer_id' => $customer ? $customer->id : null,
                'guest_name' => $customer ? null : $shippingAddressInfo['name'],
                'guest_email' => $customer ? null : $shippingAddressInfo['email'],
                'guest_phone' => $customer ? null : $shippingAddressInfo['phone'],
                'promotion_id' => $promotion ? $promotion->id : null,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'total_price' => $totalPrice,
                'status' => $validatedData['status'],
                'province_id' => $shippingAddressInfo['province_id'],
                'district_id' => $shippingAddressInfo['district_id'],
                'ward_id' => $shippingAddressInfo['ward_id'],
                'shipping_address_line' => $shippingAddressInfo['full_address_line'],
                'payment_method' => $validatedData['payment_method'],
                'delivery_service_id' => $validatedData['delivery_service_id'],
                'notes' => $validatedData['notes'] ?? null,
                'created_by_admin_id' => Auth::guard('admin')->id(),
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
                Product::where('id', $itemData['product_id'])->decrement('stock_quantity', $itemData['quantity']);
            }

            if ($promotion) {
                $promotion->increment('uses_count');
            }

            DB::commit();

            return response()->json(['message' => 'Đơn hàng đã được tạo thành công!', 'order_id' => $order->id], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi nhập liệu!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi tạo đơn hàng từ Admin: " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Cập nhật thông tin đơn hàng và trạng thái.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
                'delivery_service_id' => ['required', 'exists:delivery_services,id'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ], [
                'status.required' => 'Trạng thái đơn hàng là bắt buộc.',
                'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
                'delivery_service_id.required' => 'Dịch vụ vận chuyển là bắt buộc.',
                'delivery_service_id.exists' => 'Dịch vụ vận chuyển không tồn tại.',
            ]);

            DB::beginTransaction();

            $oldStatus = $order->status;
            $newStatus = $validatedData['status'];

            $order->status = $newStatus;
            $order->delivery_service_id = $validatedData['delivery_service_id'];
            $order->notes = $validatedData['notes'] ?? null;

            $deliveryService = DeliveryService::find($validatedData['delivery_service_id']);
            if ($deliveryService) {
                $order->shipping_fee = $deliveryService->shipping_fee;
                // Recalculate total_price based on potentially new shipping_fee
                // We assume subtotal and discount_amount are fixed for update
                $order->total_price = ($order->subtotal + $order->shipping_fee - $order->discount_amount);
                if ($order->total_price < 0) $order->total_price = 0;
            }

            $this->handleOrderStatusTransition($order, $oldStatus, $newStatus);

            $order->save();

            DB::commit();

            return response()->json(['message' => 'Cập nhật đơn hàng thành công!', 'order' => $order->fresh()], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi nhập liệu!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật đơn hàng. Vui lòng thử lại.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xử lý chuyển đổi trạng thái đơn hàng (ví dụ: duyệt đơn hàng, hủy đơn hàng).
     * Cập nhật số lượng sản phẩm trong kho và số lượt sử dụng khuyến mãi.
     */
    protected function handleOrderStatusTransition(Order $order, string $oldStatus, string $newStatus): void
    {
        // Khi hủy đơn hàng (từ bất kỳ trạng thái nào sang cancelled)
        if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
            // Hoàn lại số lượng sản phẩm vào kho
            $order->load('items.product');
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }
            // Hoàn lại lượt sử dụng khuyến mãi nếu có
            if ($order->promotion_id) {
                $promotion = Promotion::find($order->promotion_id);
                if ($promotion && $promotion->uses_count > 0) {
                    $promotion->decrement('uses_count');
                }
            }
        }
        // Thêm logic khác nếu cần, ví dụ: giảm tồn kho khi chuyển từ pending/processing sang approved
        // if ($newStatus === Order::STATUS_APPROVED && !in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
        //     // Logic này thường được xử lý lúc tạo đơn hàng.
        //     // Nếu bạn muốn xử lý giảm tồn kho tại đây (ví dụ: nếu đơn hàng được tạo thủ công và chưa giảm tồn kho),
        //     // hãy cẩn thận để tránh giảm trùng lặp.
        // }
    }

    /**
     * Hiển thị chi tiết một đơn hàng.
     */
    public function show(Order $order): JsonResponse
    {
        // MODIFIED: Thay 'admin' bằng 'createdByAdmin'
        $order->load(['customer', 'promotion', 'deliveryService', 'province', 'district', 'ward', 'items.product', 'createdByAdmin']);
        // Trả về order dưới dạng array để accessors và relationships đã load được chuyển thành JSON
        return response()->json([
            'order' => $order->toArray(),
            'customer_name' => $order->customer_name,
            'shipping_address_full' => $order->full_shipping_address,
            'created_by_admin_name' => $order->createdByAdmin ? $order->createdByAdmin->name : 'Khách hàng',
        ]);
    }

    /**
     * Xóa một đơn hàng.
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        try {
            $request->validate([
                'admin_password_delete_order' => ['required', 'string'],
            ], [
                'admin_password_delete_order.required' => 'Vui lòng nhập mật khẩu của bạn để xác nhận xóa.',
            ]);

            if (!Hash::check($request->admin_password_delete_order, Auth::guard('admin')->user()->password)) {
                throw ValidationException::withMessages(['admin_password_delete_order' => 'Mật khẩu không đúng.']);
            }

            DB::beginTransaction();
            // Hoàn lại số lượng sản phẩm vào kho và promotion uses_count khi xóa đơn hàng
            // Logic này chỉ thực hiện nếu đơn hàng KHÔNG phải đã bị hủy trước đó
            if ($order->status !== Order::STATUS_CANCELLED) {
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

            $order->delete();
            DB::commit();
            return response()->json(['message' => 'Đơn hàng đã được xóa thành công.'], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi xác thực!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa đơn hàng.', 'error' => $e->getMessage()], 500);
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
