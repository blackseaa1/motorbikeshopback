<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order; // Import Order model
use App\Models\Product; // Import Product model
use App\Models\Customer; // Import Customer model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; // Import DB facade for raw queries
use Illuminate\Http\JsonResponse; // Import JsonResponse for API methods

class DashboardController extends Controller
{
    /**
     * Hiển thị trang dashboard của admin.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // 1. Tổng Sản Phẩm
        $totalProducts = Product::count();
        $newProductsThisWeek = Product::where('created_at', '>=', now()->subWeek())->count();

        // 2. Đơn Hàng Mới (trong tuần này)
        $newOrdersThisWeek = Order::where('created_at', '>=', now()->subWeek())->count();
        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        // 3. Doanh Thu (Tháng này)
        $monthlyRevenue = Order::where('status', Order::STATUS_COMPLETED)
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->sum('total_price');

        // Doanh thu tháng trước để so sánh
        $lastMonthRevenue = Order::where('status', Order::STATUS_COMPLETED)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->sum('total_price');

        $revenueComparison = 0;
        if ($lastMonthRevenue > 0) {
            $revenueComparison = (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        } elseif ($monthlyRevenue > 0) {
            $revenueComparison = 100; // Tăng 100% nếu tháng trước 0 và tháng này có doanh thu
        }

        // 4. Sản phẩm gần hết hàng (dưới 10 sản phẩm trong kho)
        $lowStockProductsCount = Product::where('stock_quantity', '<', 10)->count();

        // 5. Sản phẩm bán chạy nhất (ví dụ: top 5 trong tháng này)
        $bestSellingProducts = Product::select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity_sold'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereYear('orders.created_at', now()->year)
            ->whereMonth('orders.created_at', now()->month)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity_sold')
            ->limit(5)
            ->get();

        // 6. Các đơn hàng gần đây (ví dụ: 5 đơn hàng mới nhất)
        // Sửa lỗi ở đây: firstImage không tồn tại trên Order.
        // Nếu cần hình ảnh sản phẩm trong đơn hàng, phải eager load thông qua items.product
        $recentOrders = Order::with(['customer', 'items.product.firstImage']) // SỬA LỖI TẠI ĐÂY: Thêm .firstImage vào chuỗi quan hệ
            ->latest()
            ->limit(5)
            ->get();

        // 7. Các sản phẩm mới nhất (ví dụ: 5 sản phẩm mới thêm)
        $latestProducts = Product::with('firstImage')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'newProductsThisWeek',
            'newOrdersThisWeek',
            'pendingOrders',
            'monthlyRevenue',
            'revenueComparison',
            'lowStockProductsCount',
            'bestSellingProducts',
            'recentOrders',
            'latestProducts'
        ));
    }

    /**
     * API: Lấy dữ liệu doanh thu hàng tháng cho biểu đồ.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueChartData(Request $request): JsonResponse
    {
        // Lấy năm hiện tại hoặc từ request
        $year = $request->input('year', date('Y'));

        // Lấy doanh thu theo tháng cho 6 tháng gần nhất
        $months = [];
        $revenueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = $date->month;
            $yearNum = $date->year;

            $months[] = 'Tháng ' . $monthNum; // Label cho biểu đồ

            $monthlyTotal = Order::where('status', Order::STATUS_COMPLETED)
                ->whereYear('updated_at', $yearNum)
                ->whereMonth('updated_at', $monthNum)
                ->sum('total_price');

            // Chuyển đổi sang triệu VNĐ và làm tròn 2 chữ số thập phân
            $revenueData[] = round($monthlyTotal / 1000000, 2);
        }

        return response()->json([
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Doanh thu (Triệu VNĐ)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'data' => $revenueData,
                ]
            ]
        ]);
    }
}
