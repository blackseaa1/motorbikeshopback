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
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Thêm import Hash
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
        $query = Order::with(['customer', 'deliveryService', 'province', 'district', 'ward', 'promotion'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('guest_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('customer', function ($cq) use ($searchTerm) {
                        $cq->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%")
                            ->orWhere('phone', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $orders = $query->paginate(10);

        // Chuẩn bị dữ liệu cho JS (sản phẩm, khuyến mãi, khách hàng)
        $allProductsForJs = Product::active()->get(['id', 'name', 'price', 'stock_quantity']);
        $promotions = Promotion::all(); // Lấy tất cả promotions để hiển thị trong select
        $customers = Customer::all(['id', 'name', 'email', 'phone']); // Lấy thông tin khách hàng

        // Lấy danh sách Provinces, Districts, Wards để đổ vào dropdown
        $provinces = Province::orderBy('name')->get(['id', 'name']);
        $deliveryServices = DeliveryService::where('status', DeliveryService::STATUS_ACTIVE)->get(['id', 'name', 'shipping_fee']);

        // Xác định các trạng thái đơn hàng để lọc
        $orderStatuses = Order::STATUSES;

        return view('admin.sales.order.orders', compact(
            'orders',
            'orderStatuses',
            'allProductsForJs',
            'promotions',
            'customers',
            'provinces',
            'deliveryServices'
        ));
    }
    public function show(Order $order): JsonResponse // Đảm bảo dòng này tồn tại và đúng
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
                'items.product.images',
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
                'addresses' => $order->customer ? $order->customer->addresses()->with(['province', 'district', 'ward'])->get() : [],
                'promotions' => $promotions,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Lỗi khi lấy chi tiết đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể tìm thấy hoặc có lỗi khi tải chi tiết đơn hàng.'
            ], 404);
        }
    }

    /**
     * Lưu đơn hàng mới vào database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate($this->createValidationRules(), [], $this->validationMessages());

        DB::beginTransaction();
        try {
            $customer = null;
            $shippingAddressLine = null;
            $provinceId = null;
            $districtId = null;
            $wardId = null;
            $guestName = $validatedData['guest_name'];
            $guestEmail = $validatedData['guest_email'];
            $guestPhone = $validatedData['guest_phone'];

            // Determine if it's a registered customer or guest
            if ($validatedData['customer_id']) {
                $customer = Customer::find($validatedData['customer_id']);
                if (!$customer) {
                    throw ValidationException::withMessages(['customer_id' => 'Khách hàng không tồn tại.']);
                }
                // If customer is selected, guest info might be ignored or used as fallback
                $guestName = $customer->name;
                $guestEmail = $customer->email;
                $guestPhone = $customer->phone;

                // Get default address or first address if no default
                $customerAddress = $customer->defaultAddress ?? $customer->addresses->first();
                if ($customerAddress) {
                    $shippingAddressLine = $customerAddress->address_line;
                    $provinceId = $customerAddress->province_id;
                    $districtId = $customerAddress->district_id;
                    $wardId = $customerAddress->ward_id;
                }
            } else {
                // Guest customer
                $shippingAddressLine = $validatedData['shipping_address_line'];
                $provinceId = $validatedData['province_id'];
                $districtId = $validatedData['district_id'];
                $wardId = $validatedData['ward_id'];
            }

            // Calculate total_price based on items, shipping, and promotion
            $promotion = null;
            if ($validatedData['promotion_id']) {
                $promotion = Promotion::find($validatedData['promotion_id']);
                if ($promotion && $promotion->effective_status_key !== Promotion::STATUS_EFFECTIVE_ACTIVE) {
                    throw ValidationException::withMessages(['promotion_id' => 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.']);
                }
            }

            $deliveryService = DeliveryService::find($validatedData['delivery_service_id']);
            if (!$deliveryService || $deliveryService->status !== DeliveryService::STATUS_ACTIVE) {
                throw ValidationException::withMessages(['delivery_service_id' => 'Dịch vụ vận chuyển không hợp lệ hoặc không hoạt động.']);
            }

            $order = new Order();
            $order->customer_id = $customer ? $customer->id : null;
            $order->guest_name = $guestName;
            $order->guest_email = $guestEmail;
            $order->guest_phone = $guestPhone;
            $order->promotion_id = $promotion ? $promotion->id : null;
            $order->status = $validatedData['status'];
            $order->province_id = $provinceId;
            $order->district_id = $districtId;
            $order->ward_id = $wardId;
            $order->shipping_address_line = $shippingAddressLine;
            $order->payment_method = $validatedData['payment_method'];
            $order->delivery_service_id = $deliveryService->id;
            $order->notes = $validatedData['notes'] ?? null;
            $order->created_by_admin_id = Auth::guard('admin')->id(); // Admin hiện tại tạo đơn hàng

            // Save order first to get ID for order items
            $order->save();

            $subtotal = 0;
            foreach ($validatedData['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product || $product->status !== Product::STATUS_ACTIVE) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm '{$product->name}' không tồn tại hoặc không còn bán."]);
                }
                if ($product->stock_quantity < $itemData['quantity']) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm '{$product->name}' không đủ số lượng tồn kho."]);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $product->price, // Price at the time of order
                ]);
                $subtotal += $product->price * $itemData['quantity'];
            }

            // Reload order items to calculate totals
            $order->load('items');
            $order->total_price = $order->grand_total; // Save grand total as total_price

            // Handle stock and promotion usage based on initial status
            if (in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                $this->decreaseStockAndUsePromotion($order);
            }

            $order->save(); // Save again to update total_price and any other calculated fields

            DB::commit();
            return redirect()->route('admin.sales.orders.index')->with('success', 'Đơn hàng đã được tạo thành công!');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation Error creating order: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput()->with('form_identifier', 'create_order_form');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Không thể tạo đơn hàng. Vui lòng thử lại.')->withInput()->with('form_identifier', 'create_order_form');
        }
    }


    /**
     * Cập nhật đơn hàng trong database.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $validatedData = $request->validate($this->updateValidationRules($order), [], $this->validationMessages());

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $oldPromotionId = $order->promotion_id;

            $customer = null;
            $shippingAddressLine = $validatedData['shipping_address_line']; // For guest
            $provinceId = $validatedData['province_id']; // For guest
            $districtId = $validatedData['district_id']; // For guest
            $wardId = $validatedData['ward_id']; // For guest

            // If customer_id is provided, use their info and potentially override guest info
            if ($validatedData['customer_id']) {
                $customer = Customer::find($validatedData['customer_id']);
                if (!$customer) {
                    throw ValidationException::withMessages(['customer_id' => 'Khách hàng không tồn tại.']);
                }
                // Use customer's name, email, phone if selected
                $order->guest_name = $customer->name;
                $order->guest_email = $customer->email;
                $order->guest_phone = $customer->phone;

                // For existing addresses, you might need a different input for shipping_address_id
                // For simplicity here, if customer_id is set, we assume you might be updating the address directly
                // or the address is part of the Customer model and not an explicit CustomerAddress.
                // If you use CustomerAddress, you'd need to fetch and set based on $validatedData['shipping_address_id']
                if (isset($validatedData['shipping_address_id']) && $validatedData['shipping_address_id']) {
                    $customerAddress = CustomerAddress::with(['province', 'district', 'ward'])->find($validatedData['shipping_address_id']);
                    if ($customerAddress && $customerAddress->customer_id === $customer->id) {
                        $shippingAddressLine = $customerAddress->address_line;
                        $provinceId = $customerAddress->province_id;
                        $districtId = $customerAddress->district_id;
                        $wardId = $customerAddress->ward_id;
                    } else {
                        throw ValidationException::withMessages(['shipping_address_id' => 'Địa chỉ giao hàng không hợp lệ cho khách hàng này.']);
                    }
                }
            } else {
                // It's a guest order, use provided guest info
                $order->guest_name = $validatedData['guest_name'];
                $order->guest_email = $validatedData['guest_email'];
                $order->guest_phone = $validatedData['guest_phone'];
            }

            $order->customer_id = $customer ? $customer->id : null;
            $order->promotion_id = $validatedData['promotion_id'] ?? null;
            $order->status = $validatedData['status'];
            $order->province_id = $provinceId;
            $order->district_id = $districtId;
            $order->ward_id = $wardId;
            $order->shipping_address_line = $shippingAddressLine;
            $order->payment_method = $validatedData['payment_method'];
            $order->delivery_service_id = $validatedData['delivery_service_id'];
            $order->notes = $validatedData['notes'] ?? null;


            // Handle removed items: increment stock for removed items if order was approved
            $existingItemIds = $order->items()->pluck('id')->toArray();
            $updatedItemIds = collect($validatedData['items'])->pluck('order_item_id')->filter()->toArray();
            $removedItemIds = array_diff($existingItemIds, $updatedItemIds);

            foreach ($removedItemIds as $removedItemId) {
                $removedOrderItem = OrderItem::find($removedItemId);
                if ($removedOrderItem && in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                    $removedOrderItem->product->increment('stock_quantity', $removedOrderItem->quantity);
                }
                $removedOrderItem->delete();
            }

            // Update or create order items
            foreach ($validatedData['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product || $product->status !== Product::STATUS_ACTIVE) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm '{$product->name}' không tồn tại hoặc không còn bán."]);
                }

                $orderItem = null;
                if (isset($itemData['order_item_id']) && $itemData['order_item_id']) {
                    $orderItem = OrderItem::find($itemData['order_item_id']);
                }

                $oldQuantity = $orderItem ? $orderItem->quantity : 0;
                $newQuantity = $itemData['quantity'];

                if ($orderItem) {
                    // Update existing item
                    if ($orderItem->product_id !== $product->id) {
                        // Product changed for existing order item
                        if (in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                            // Return old product's stock
                            $orderItem->product->increment('stock_quantity', $oldQuantity);
                        }
                        // Check new product's stock
                        if ($product->stock_quantity < $newQuantity) {
                            throw ValidationException::withMessages(['items' => "Sản phẩm mới '{$product->name}' không đủ số lượng tồn kho."]);
                        }
                        // Deduct new product's stock
                        if (in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                            $product->decrement('stock_quantity', $newQuantity);
                        }
                        $orderItem->product_id = $product->id;
                        $orderItem->quantity = $newQuantity;
                        $orderItem->price = $product->price;
                    } elseif ($newQuantity !== $oldQuantity) {
                        // Quantity changed for existing product
                        $quantityDiff = $newQuantity - $oldQuantity;
                        if ($quantityDiff > 0) { // Increased quantity
                            if ($product->stock_quantity < $quantityDiff) {
                                throw ValidationException::withMessages(['items' => "Sản phẩm '{$product->name}' không đủ số lượng tồn kho."]);
                            }
                            if (in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                                $product->decrement('stock_quantity', $quantityDiff);
                            }
                        } elseif ($quantityDiff < 0) { // Decreased quantity
                            if (in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                                $product->increment('stock_quantity', abs($quantityDiff));
                            }
                        }
                        $orderItem->quantity = $newQuantity;
                        $orderItem->price = $product->price;
                    }
                    $orderItem->save();
                } else {
                    // Create new item
                    if ($product->stock_quantity < $newQuantity) {
                        throw ValidationException::withMessages(['items' => "Sản phẩm '{$product->name}' không đủ số lượng tồn kho."]);
                    }
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $newQuantity,
                        'price' => $product->price,
                    ]);
                    if (in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                        $product->decrement('stock_quantity', $newQuantity);
                    }
                }
            }

            // Handle promotion usage change
            if ($oldPromotionId && $oldPromotionId !== $order->promotion_id) {
                // If old promo existed and is different from new one, decrement its uses_count if order was approved
                $oldPromotion = Promotion::find($oldPromotionId);
                if ($oldPromotion && in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                    $oldPromotion->decrement('uses_count');
                }
            }
            if ($order->promotion_id && $oldPromotionId !== $order->promotion_id) {
                // If a new promo is applied and order is approved, increment its uses_count
                $newPromotion = Promotion::find($order->promotion_id);
                if ($newPromotion && in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                    $newPromotion->increment('uses_count');
                }
            }

            // Handle status change for stock and promotion (if not already handled by item changes)
            if ($order->status !== $oldStatus) {
                if (
                    in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]) &&
                    !in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
                ) {
                    // Status changed TO approved/shipped/delivered/completed: decrease stock, increment promo use
                    $this->decreaseStockAndUsePromotion($order, true);
                } elseif (
                    !in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]) &&
                    in_array($oldStatus, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
                ) {
                    // Status changed FROM approved/shipped/delivered/completed: increase stock, decrement promo use
                    $this->increaseStockAndUnusePromotion($order);
                }
            }

            // Recalculate total price
            $order->load('items'); // Reload items to ensure correct subtotal calculation
            $order->total_price = $order->grand_total; // Update total_price in DB

            $order->save();

            DB::commit();
            return redirect()->route('admin.sales.orders.index')->with('success', 'Đơn hàng đã được cập nhật thành công!');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation Error updating order: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput()->with('form_identifier', 'update_order_form');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật đơn hàng: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Không thể cập nhật đơn hàng. Vui lòng thử lại.')->withInput()->with('form_identifier', 'update_order_form');
        }
    }


    /**
     * Xóa đơn hàng khỏi database.
     */
    public function destroy(Request $request, Order $order): RedirectResponse
    {
        // Check for admin deletion password if configured
        if (config('admin.deletion_password')) {
            $request->validate([
                'admin_password_delete_order' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!Hash::check($value, Auth::guard('admin')->user()->password)) {
                            $fail('Mật khẩu xác nhận không đúng.');
                        }
                    },
                ],
            ], [
                'admin_password_delete_order.required' => 'Vui lòng nhập mật khẩu xác nhận.',
                'admin_password_delete_order.min' => 'Mật khẩu phải có ít nhất :min ký tự.', // Add if you have min rule
            ], [], 'delete_order_form'); // Pass form_identifier for errors
        }

        DB::beginTransaction();
        try {
            // If the order was approved or shipped, return stock and unuse promotion
            if (in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                $this->increaseStockAndUnusePromotion($order);
            }

            $order->delete(); // This will also cascade delete order items if foreign keys are set up with cascadeOnDelete

            DB::commit();
            return redirect()->route('admin.sales.orders.index')->with('success', 'Đơn hàng đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa đơn hàng: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Không thể xóa đơn hàng. Vui lòng thử lại.');
        }
    }

    /**
     * Duyệt đơn hàng (chuyển trạng thái sang "approved").
     * Đảm bảo giảm tồn kho và sử dụng khuyến mãi.
     */
    public function approve(Order $order): JsonResponse
    {
        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng không thể duyệt ở trạng thái hiện tại.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order->status = Order::STATUS_APPROVED;
            $order->save();

            $this->decreaseStockAndUsePromotion($order);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được duyệt thành công!',
                'order' => $order->fresh(['customer', 'deliveryService', 'province', 'district', 'ward', 'promotion', 'items.product'])
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation Error approving order: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi duyệt đơn hàng: ' . $e->getMessage(), ['order_id' => $order->id, 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể duyệt đơn hàng. Vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Helper function to decrease product stock and increment promotion uses_count.
     * This is called when an order moves into an "approved" or "completed" state.
     */
    private function decreaseStockAndUsePromotion(Order $order, bool $isStatusChange = false): void
    {
        // Only proceed if the order is now in an inventory-affecting status
        if (!in_array($order->status, [Order::STATUS_APPROVED, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
            return;
        }

        $order->load('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product->stock_quantity < $item->quantity) {
                throw ValidationException::withMessages(['stock' => "Sản phẩm '{$product->name}' không đủ số lượng tồn kho. Vui lòng kiểm tra lại."]);
            }
            $product->decrement('stock_quantity', $item->quantity);
        }

        if ($order->promotion) {
            if ($order->promotion->uses_count >= $order->promotion->max_uses) {
                throw ValidationException::withMessages(['promotion' => 'Mã khuyến mãi đã đạt số lượt sử dụng tối đa.']);
            }
            $order->promotion->increment('uses_count');
        }
    }

    /**
     * Helper function to increase product stock and decrement promotion uses_count.
     * This is called when an order is cancelled, returned, failed, or deleted after being approved.
     */
    private function increaseStockAndUnusePromotion(Order $order): void
    {
        $order->load('items.product');

        foreach ($order->items as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }

        if ($order->promotion) {
            $order->promotion->decrement('uses_count');
        }
    }

    /**
     * Validation rules for creating an order.
     */
    private function createValidationRules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'guest_name' => 'required_without:customer_id|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'required_without:customer_id|string|max:100',
            'promotion_id' => 'nullable|exists:promotions,id',
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'province_id' => 'required_without:customer_id|exists:provinces,id',
            'district_id' => 'required_without:customer_id|exists:districts,id',
            'ward_id' => 'required_without:customer_id|exists:wards,id',
            'shipping_address_line' => 'required_without:customer_id|string|max:255',
            'payment_method' => 'required|string|max:100',
            'delivery_service_id' => 'required|exists:delivery_services,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // 'items.*.price' => 'required|numeric|min:0', // Price will be fetched from product
        ];
    }

    /**
     * Validation rules for updating an order.
     */
    private function updateValidationRules(Order $order): array
    {
        // These rules are similar to creation, but with some adjustments for existing data
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'shipping_address_id' => 'nullable|exists:customer_addresses,id', // New for using existing addresses
            'guest_name' => 'required_without:customer_id|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'required_without:customer_id|string|max:100',
            'promotion_id' => 'nullable|exists:promotions,id',
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'province_id' => 'required_without:customer_id|exists:provinces,id',
            'district_id' => 'required_without:customer_id|exists:districts,id',
            'ward_id' => 'required_without:customer_id|exists:wards,id',
            'shipping_address_line' => 'required_without:customer_id|string|max:255',
            'payment_method' => 'required|string|max:100',
            'delivery_service_id' => 'required|exists:delivery_services,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'nullable|exists:order_items,id', // For updating existing items
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Custom validation messages.
     */
    private function validationMessages(): array
    {
        return [
            'customer_id.exists' => 'Khách hàng được chọn không tồn tại.',
            'guest_name.required_without' => 'Tên khách hàng là bắt buộc nếu không chọn khách hàng có sẵn.',
            'guest_phone.required_without' => 'Số điện thoại là bắt buộc nếu không chọn khách hàng có sẵn.',
            'guest_email.email' => 'Email không đúng định dạng.',
            'promotion_id.exists' => 'Mã khuyến mãi không hợp lệ.',
            'status.required' => 'Trạng thái đơn hàng là bắt buộc.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'province_id.required_without' => 'Tỉnh/Thành phố là bắt buộc nếu không chọn khách hàng có sẵn.',
            'province_id.exists' => 'Tỉnh/Thành phố không tồn tại.',
            'district_id.required_without' => 'Quận/Huyện là bắt buộc nếu không chọn khách hàng có sẵn.',
            'district_id.exists' => 'Quận/Huyện không tồn tại.',
            'ward_id.required_without' => 'Phường/Xã là bắt buộc nếu không chọn khách hàng có sẵn.',
            'ward_id.exists' => 'Phường/Xã không tồn tại.',
            'shipping_address_line.required_without' => 'Địa chỉ chi tiết là bắt buộc nếu không chọn khách hàng có sẵn.',
            'payment_method.required' => 'Phương thức thanh toán là bắt buộc.',
            'delivery_service_id.required' => 'Dịch vụ vận chuyển là bắt buộc.',
            'delivery_service_id.exists' => 'Dịch vụ vận chuyển không tồn tại.',
            'items.required' => 'Đơn hàng phải có ít nhất một sản phẩm.',
            'items.array' => 'Dữ liệu sản phẩm không hợp lệ.',
            'items.*.product_id.required' => 'Mã sản phẩm là bắt buộc.',
            'items.*.product_id.exists' => 'Sản phẩm không tồn tại.',
            'items.*.quantity.required' => 'Số lượng sản phẩm là bắt buộc.',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải ít nhất là :min.',
            'admin_password_delete_order.required' => 'Vui lòng nhập mật khẩu xác nhận.',
            'admin_password_delete_order.incorrect' => 'Mật khẩu xác nhận không đúng.',
        ];
    }
}
