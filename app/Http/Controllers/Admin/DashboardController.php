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
            $revenueComparison = 100; // Nếu tháng trước 0 mà tháng này có doanh thu
        }

        // 4. Sản phẩm Sắp Hết Hàng (ví dụ: stock_quantity < 10)
        $lowStockProductsCount = Product::where('stock_quantity', '<', 10)
            ->where('stock_quantity', '>', 0)
            ->count();

        // 5. Sản Phẩm Bán Chạy Nhất (ví dụ: top 3 sản phẩm theo số lượng bán ra)
        $bestSellingProducts = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                'products.status',
                DB::raw('SUM(order_items.quantity) as total_quantity_sold')
            )
            ->with('firstImage', 'category') // Eager load firstImage and category
            ->where('orders.status', Order::STATUS_COMPLETED) // Chỉ tính các đơn hàng đã hoàn thành
            ->groupBy('products.id', 'products.name', 'products.price', 'products.status')
            ->orderByDesc('total_quantity_sold')
            ->limit(3)
            ->get();

        // 6. Đơn Hàng Gần Đây (top 5 đơn hàng mới nhất)
        $recentOrders = Order::with(['customer', 'paymentMethod'])
            ->latest()
            ->limit(5)
            ->get();

        // 7. Sản Phẩm Mới Thêm (top 3 sản phẩm mới nhất)
        $latestProducts = Product::with('firstImage', 'category')
            ->latest()
            ->limit(3)
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
            'data' => $revenueData
        ]);
    }
}
