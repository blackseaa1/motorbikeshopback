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
use Carbon\Carbon; // Import Carbon for date comparisons

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng với bộ lọc và phân trang.
     * Đồng thời, tải sẵn tất cả dữ liệu cần thiết cho các modal.
     */
    public function index(Request $request): View
    {
        // Start with eager loading necessary relationships to avoid N+1 query problem
        $query = Order::with(['customer', 'deliveryService', 'paymentMethod']);

        // Apply status filter if provided and not 'all'
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply search term filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . $searchTerm . '%') // Search by order ID
                    ->orWhere('guest_name', 'like', '%' . $searchTerm . '%') // Search by guest name
                    ->orWhere('guest_email', 'like', '%' . $searchTerm . '%') // Search by guest email
                    ->orWhere('guest_phone', 'like', '%' . $searchTerm . '%') // Search by guest phone
                    ->orWhereHas('customer', function ($subQuery) use ($searchTerm) { // Search by registered customer info
                        $subQuery->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('email', 'like', '%' . $searchTerm . '%')
                            ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Sorting logic based on 'sort_by' parameter
        if ($request->filled('sort_by')) {
            if ($request->sort_by == 'oldest') {
                $query->oldest(); // Sort by created_at in ascending order
            } elseif ($request->sort_by == 'priority') {
                // Define the custom order of statuses for priority sorting.
                // Orders will appear in this specific sequence, with 'pending' first.
                $statusOrder = [
                    Order::STATUS_PENDING,    // 'Pending' status is now the highest priority (chờ xử lý)
                    Order::STATUS_PROCESSING, // 'Processing' status
                    Order::STATUS_APPROVED,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_DELIVERED,
                    Order::STATUS_COMPLETED,
                    Order::STATUS_RETURNED,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_FAILED,
                ];

                // Use orderByRaw with FIELD to sort by the custom order of statuses
                $query->orderByRaw(DB::raw("FIELD(status, '" . implode("','", $statusOrder) . "')"));
                // Then, sort by the creation date in descending order (newest first)
                $query->latest();
            } else {
                // Default to 'latest' if 'sort_by' is not 'oldest' or 'priority'
                $query->latest(); // Sort by created_at in descending order
            }
        } else {
            // Default sorting if no 'sort_by' parameter is provided in the request.
            // We'll default to 'latest' (newest orders first).
            $query->latest();
        }

        // Paginate the results, 10 orders per page
        $orders = $query->paginate(10);

        // Load all necessary data for the modals (create, update, view)
        $orderStatuses = Order::STATUSES; // Get all defined order statuses
        $customers = Customer::all(); // All customers for selection
        $provinces = Province::all(); // All provinces for address selection
        $deliveryServices = DeliveryService::all(); // All delivery services
        $promotions = Promotion::all(); // All promotions
        $paymentMethods = PaymentMethod::all(); // All payment methods

        // Return the view with the paginated orders and other necessary data
        return view('admin.sales.order.orders', compact(
            'orders',
            'orderStatuses',
            'customers',
            'provinces',
            'deliveryServices',
            'promotions',
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
            // Tính toán tổng giá trị đơn hàng
            $calculation = $this->calculateOrderTotals($validated['items'], $validated['delivery_service_id'], $validated['promotion_id'] ?? null);

            // *** FIX: Thêm lớp kiểm tra cuối cùng để đảm bảo tính nhất quán của khuyến mãi ***
            if (isset($validated['promotion_id']) && $validated['promotion_id'] !== null) {
                // Nếu ID khuyến mãi từ form không khớp với ID được xác thực cuối cùng (có thể là null)
                if ($validated['promotion_id'] != $calculation['promotion_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã khuyến mãi đã chọn không còn hợp lệ hoặc đã hết lượt sử dụng. Vui lòng xóa và thử lại.'
                    ], 409); // 409 Conflict
                }
            }

            $order = new Order();
            // Gán thông tin khách hàng và địa chỉ
            $this->assignCustomerAndAddress($order, $validated);

            $order->fill([
                'payment_method_id'   => $validated['payment_method_id'],
                'status'              => $validated['status'],
                'notes'               => $validated['notes'],
                'delivery_service_id' => $calculation['delivery_service_id'],
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
        // *** FIX: Tải thêm quan hệ createdByAdmin để hiển thị tên người tạo ***
        $order->load(['customer', 'deliveryService', 'province', 'district', 'ward', 'promotion', 'items.product', 'paymentMethod', 'createdByAdmin']);
        return response()->json($order);
    }

    /**
     * Cập nhật thông tin đơn hàng (chủ yếu là trạng thái).
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $finalStatuses = [
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_FAILED,
            Order::STATUS_RETURNED
        ];

        if (in_array($order->status, $finalStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái của đơn hàng đã hoàn thành hoặc đã bị hủy.'
            ], 403);
        }

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

        // *** LOGIC NGHIỆP VỤ: Kiểm tra quy trình chuyển đổi trạng thái ***
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Không thể chuyển trạng thái từ '{$order->status_text}' sang '{$this->getStatusText($newStatus)}'."
            ], 422); // Unprocessable Entity
        }


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
        if ($order->status === Order::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa đơn hàng đã hoàn thành.'
            ], 403);
        }

        if (config('admin.deletion_password') && $request->input('password') !== config('admin.deletion_password')) {
            return response()->json(['success' => false, 'errors' => ['password' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        DB::beginTransaction();
        try {
            if ($order->status === Order::STATUS_APPROVED) {
                $this->handleStockAndPromotionOnStatusChange($order, Order::STATUS_APPROVED, Order::STATUS_CANCELLED);
            }

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
     */
    public function getProductsForOrderCreation(): JsonResponse
    {
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->with('firstImage')
            ->orderBy('name')
            ->get();

        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'thumbnail_url' => $product->thumbnail_url,
            ];
        });

        return response()->json($productsData);
    }

    /**
     * API: Tính toán tóm tắt đơn hàng.
     */
    public function calculateOrderSummaryApi(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'items'                 => ['nullable', 'array'],
                'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
                'items.*.quantity'      => ['required', 'integer', 'min:1'],
                'delivery_service_id'   => ['nullable', 'integer', 'exists:delivery_services,id'],
                'promotion_code'        => ['nullable', 'string', 'max:50'],
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();
            $items = $validated['items'] ?? [];
            $deliveryServiceId = $validated['delivery_service_id'] ?? null;
            $promotionCode = $validated['promotion_code'] ?? null;

            $subtotal = 0;
            $productIds = array_column($items, 'product_id');
            $products = !empty($productIds) ? Product::find($productIds)->keyBy('id') : collect();

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                if ($product) {
                    $subtotal += $product->price * $item['quantity'];
                }
            }

            $shippingFee = 0;

            // Tính toán discount từ promotion code
            $promoResult = $this->validateAndCalculatePromotion($promotionCode, $subtotal);

            $grandTotal = $subtotal + $shippingFee - $promoResult['discount_amount'];
            $grandTotal = max(0, $grandTotal);

            return response()->json([
                'success' => true,
                'summary' => [
                    'subtotal'        => $subtotal,
                    'shipping_fee'    => $shippingFee,
                    'discount_amount' => $promoResult['discount_amount'],
                    'total_price'     => $grandTotal,
                    'promotion_id'    => $promoResult['promotion_id'],
                    'promotion_info'  => $promoResult['promotion'],
                    'promo_error'     => $promoResult['error'],
                ]
            ]);
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
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'delivery_service_id'   => ['required', 'integer', 'exists:delivery_services,id'],
            'promotion_id'          => ['nullable', 'integer', 'exists:promotions,id'],
            'status'                => ['required', Rule::in(array_keys(Order::STATUSES))],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'payment_method_id'     => ['required', 'integer', Rule::exists('payment_methods', 'id')->where(fn($query) => $query->whereIn('code', ['cod', 'bank_transfer']))],
            'guest_name'          => ['required', 'string', 'max:255'],
            'guest_phone'         => ['required', 'string', 'max:20'],
            'guest_email'         => ['nullable', 'email', 'max:255'],
            'guest_province_id'   => ['required', 'integer', 'exists:provinces,id'],
            'guest_district_id'   => ['required', 'integer', 'exists:districts,id'],
            'guest_ward_id'       => ['required', 'integer', 'exists:wards,id'],
            'guest_address_line'  => ['required', 'string', 'max:255'],
        ];

        if ($request->input('customer_type') === 'existing') {
            $rules['customer_id'] = ['required', 'integer', 'exists:customers,id'];
        } else {
            $rules['customer_id'] = ['nullable'];
        }

        return Validator::make($request->all(), $rules);
    }

    private function assignCustomerAndAddress(Order &$order, array $validatedData): void
    {
        $order->customer_id = ($validatedData['customer_type'] === 'existing') ? $validatedData['customer_id'] : null;
        $order->guest_name = $validatedData['guest_name'];
        $order->guest_phone = $validatedData['guest_phone'];
        $order->guest_email = $validatedData['guest_email'] ?? null;
        $order->province_id = $validatedData['guest_province_id'];
        $order->district_id = $validatedData['guest_district_id'];
        $order->ward_id = $validatedData['guest_ward_id'];
        $order->shipping_address_line = $validatedData['guest_address_line'];
    }

    /**
     * *** FIX: Hợp nhất logic kiểm tra và tính toán khuyến mãi ***
     *
     * @param integer|null $promotionId ID của khuyến mãi
     * @param float $subtotal Tổng phụ của đơn hàng
     * @return array
     */
    private function calculateOrderTotals(array $items, ?int $deliveryServiceId, ?int $promotionId): array
    {
        $subtotal = 0;
        $productIds = array_column($items, 'product_id');
        $products = !empty($productIds) ? Product::find($productIds)->keyBy('id') : collect();

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if ($product) {
                $subtotal += $product->price * $item['quantity'];
            }
        }

        $shippingFee = 0;
        $discountAmount = 0;
        $validPromotionId = null;

        if ($promotionId) {
            $promotion = Promotion::find($promotionId);
            // Kiểm tra lại tất cả các điều kiện tại thời điểm tạo đơn hàng
            if (
                $promotion &&
                $promotion->isManuallyActive() &&
                $promotion->isCurrentlyActive() &&
                $promotion->hasUsesLeft() &&
                $promotion->meetsMinOrderAmount($subtotal)
            ) {
                $validPromotionId = $promotion->id;
                $discountAmount = $promotion->calculateDiscount($subtotal);
            }
        }

        $grandTotal = max(0, $subtotal + $shippingFee - $discountAmount);

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'promotion_id' => $validPromotionId,
            'delivery_service_id' => $deliveryServiceId,
        ];
    }

    /**
     * *** MỚI: Phương thức tập trung để xác thực và tính toán khuyến mãi ***
     *
     * @param string|null $promotionCode
     * @param float $subtotal
     * @return array
     */
    private function validateAndCalculatePromotion(?string $promotionCode, float $subtotal): array
    {
        $result = [
            'error' => null,
            'discount_amount' => 0,
            'promotion_id' => null,
            'promotion' => null,
        ];

        if (!$promotionCode) {
            return $result;
        }

        $promotion = Promotion::where('code', strtoupper(trim($promotionCode)))->first();

        if (!$promotion) {
            $result['error'] = 'Mã giảm giá không tồn tại.';
        } elseif (!$promotion->isManuallyActive()) {
            $result['error'] = 'Mã giảm giá đã bị vô hiệu hóa.';
        } elseif (!$promotion->isCurrentlyActive()) {
            $result['error'] = $promotion->start_date && Carbon::now()->lt($promotion->start_date)
                ? 'Mã giảm giá chưa bắt đầu.'
                : 'Mã giảm giá đã hết hạn.';
        } elseif (!$promotion->hasUsesLeft()) {
            $result['error'] = 'Mã giảm giá đã hết lượt sử dụng.';
        } elseif (!$promotion->meetsMinOrderAmount($subtotal)) {
            $requiredAmount = number_format($promotion->min_order_amount, 0, ',', '.') . ' ₫';
            $result['error'] = "Đơn hàng tối thiểu {$requiredAmount} để dùng mã này.";
        }

        if (!$result['error']) {
            $result['discount_amount'] = min($promotion->calculateDiscount($subtotal), $subtotal);
            $result['promotion_id'] = $promotion->id;
            $result['promotion'] = $promotion;
        }

        return $result;
    }


    private function syncOrderItems(Order $order, array $items): void
    {
        $productIds = array_column($items, 'product_id');
        $products = Product::find($productIds)->keyBy('id');

        $order->items()->delete();

        foreach ($items as $itemData) {
            $product = $products->get($itemData['product_id']);
            if ($product) {
                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("Sản phẩm '{$product->name}' không đủ số lượng tồn kho.");
                }
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
        // *** FIX: Sử dụng fresh() để đảm bảo dữ liệu là mới nhất từ DB ***
        $freshOrder = $order->fresh(['items.product', 'promotion']);
        if (!$freshOrder) {
            // Biện pháp an toàn nếu đơn hàng đã bị xóa bởi một tiến trình khác
            return;
        }

        // Khi trạng thái chuyển thành Đã duyệt (Approved) lần đầu tiên
        if ($newStatus === Order::STATUS_APPROVED && $oldStatus !== Order::STATUS_APPROVED) {
            foreach ($freshOrder->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }
            // Chỉ tăng lượt sử dụng nếu đơn hàng có khuyến mãi
            if ($freshOrder->promotion) {
                $freshOrder->promotion->increment('uses_count');
            }
        }
        // Khi một đơn hàng Đã duyệt bị hủy hoặc trả lại
        elseif ($oldStatus === Order::STATUS_APPROVED && in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])) {
            foreach ($freshOrder->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }
            // Chỉ giảm lượt sử dụng nếu có khuyến mãi và đã được sử dụng
            if ($freshOrder->promotion && $freshOrder->promotion->uses_count > 0) {
                $freshOrder->promotion->decrement('uses_count');
            }
        }
    }

    /**
     * *** MỚI: Kiểm tra quy trình chuyển đổi trạng thái hợp lệ ***
     *
     * @param string $from Trạng thái hiện tại
     * @param string $to Trạng thái mới
     * @return boolean
     */
    private function isValidStatusTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true; // Không thay đổi thì luôn hợp lệ
        }

        // Định nghĩa các luồng chuyển đổi hợp lệ
        $allowedTransitions = [
            Order::STATUS_PENDING    => [Order::STATUS_PROCESSING, Order::STATUS_APPROVED, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_APPROVED, Order::STATUS_CANCELLED],
            Order::STATUS_APPROVED   => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
            Order::STATUS_SHIPPED    => [Order::STATUS_DELIVERED, Order::STATUS_RETURNED],
            Order::STATUS_DELIVERED  => [Order::STATUS_COMPLETED, Order::STATUS_RETURNED],
            // Các trạng thái cuối cùng không thể chuyển đi đâu khác
            Order::STATUS_COMPLETED  => [],
            Order::STATUS_CANCELLED  => [],
            Order::STATUS_RETURNED   => [],
            Order::STATUS_FAILED     => [],
        ];

        return in_array($to, $allowedTransitions[$from] ?? []);
    }

    /**
     * *** MỚI: Helper để lấy text của trạng thái cho thông báo lỗi ***
     */
    private function getStatusText(string $statusKey): string
    {
        return Order::STATUSES[$statusKey] ?? ucfirst($statusKey);
    }
}
