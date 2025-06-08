<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

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
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_RETURNED,
        ];

        return view('admin.sales.orders', compact('orders', 'orderStatuses')); // Bạn cần tạo view này
    }

    /**
     * Hiển thị chi tiết một đơn hàng.
     */
    public function show(Order $order)
    {
        // Eager load tất cả các quan hệ cần thiết để hiển thị chi tiết
        $order->load([
            'items.product',
            'customer',
            'promotion',
            'province',
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
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
            ])],
        ]);

        try {
            $order->status = $validated['status'];
            $order->save();

            // Logic bổ sung (ví dụ: gửi email cho khách hàng, cập nhật kho hàng...) có thể thêm ở đây

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'order' => $order->refresh(), // Trả về order đã cập nhật với các accessor
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật trạng thái đơn hàng (ID: {$order->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra.'], 500);
        }
    }
}
