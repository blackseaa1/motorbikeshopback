<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product; // Thêm
use App\Models\Promotion; // Thêm
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Thêm

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách các đơn hàng với bộ lọc.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'deliveryService'])->latest();

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo mã đơn hàng hoặc tên khách hàng
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

        // Lấy các hằng số trạng thái từ Model để dùng trong view
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

        return view('admin.sales.order.orders', compact('orders', 'orderStatuses'));
    }

    /**
     * Hiển thị chi tiết một đơn hàng cụ thể.
     */
    public function show(Order $order)
    {
        $order->load([
            'items.product',
            'customer',
            'promotion',
            'province', // Đảm bảo eager load province, district, ward để accessor full_address hoạt động
            'district',
            'ward',
            'deliveryService',
            'createdByAdmin'
        ]);

        return view('admin.sales.order_show', compact('order')); // Bạn cần tạo view này
    }

    /**
     * Cập nhật trạng thái của một đơn hàng.
     */
    public function updateStatus(Request $request, Order $order)
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

        DB::beginTransaction(); // Bắt đầu giao dịch
        try {
            $order->status = $newStatus;
            $order->save();

            // LOGIC MỚI: Trừ tồn kho và tăng lượt sử dụng mã giảm giá khi đơn hàng được DUYỆT
            if ($oldStatus !== Order::STATUS_APPROVED && $newStatus === Order::STATUS_APPROVED) {
                // 1. Trừ số lượng sản phẩm trong kho
                // Tải lại items.product để đảm bảo dữ liệu mới nhất
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock_quantity', $item->quantity);
                    }
                }

                // 2. Tăng số lượt sử dụng của mã giảm giá (nếu có)
                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    // Chỉ tăng nếu promotion còn hiệu lực hoặc chưa đạt max_uses
                    if ($promotion && $promotion->isEffective() && ($promotion->max_uses === null || $promotion->uses_count < $promotion->max_uses)) {
                        $promotion->increment('uses_count');
                    }
                }
            }
            // LOGIC MỚI: Hoàn trả số lượng và giảm lượt sử dụng khi đơn hàng bị HỦY sau khi đã duyệt
            elseif ($oldStatus === Order::STATUS_APPROVED && $newStatus === Order::STATUS_CANCELLED) {
                // 1. Hoàn trả số lượng sản phẩm vào kho
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                // 2. Giảm số lượt sử dụng của mã giảm giá (nếu có)
                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    if ($promotion && $promotion->uses_count > 0) { // Đảm bảo uses_count không âm
                        $promotion->decrement('uses_count');
                    }
                }
            }


            DB::commit(); // Hoàn tất giao dịch

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'order' => $order->refresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu có lỗi
            Log::error("Lỗi khi cập nhật trạng thái đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra.'], 500);
        }
    }
}
