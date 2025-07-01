<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display the reports index page.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Get daily revenue report for a given month and year.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyRevenue(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (Carbon::now()->year + 5), // Adjust max year as needed
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

        $dailyRevenue = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as total_revenue')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $formattedData = $dailyRevenue->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('d/m/Y'),
                'total_revenue' => $item->total_revenue,
                'formatted_revenue' => \Illuminate\Support\Number::currency($item->total_revenue, 'VND', 'vi')
            ];
        });

        return response()->json($formattedData);
    }

    /**
     * Get monthly revenue report for a given year.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyRevenue(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . (Carbon::now()->year + 5),
        ]);

        $year = $request->input('year');

        $monthlyRevenue = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_price) as total_revenue')
        )
            ->whereYear('created_at', $year)
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        $formattedData = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenue = $monthlyRevenue->where('month', $i)->first();
            $totalRevenue = $revenue ? $revenue->total_revenue : 0;
            $formattedData[] = [
                'month' => 'Tháng ' . $i,
                'total_revenue' => $totalRevenue,
                'formatted_revenue' => \Illuminate\Support\Number::currency($totalRevenue, 'VND', 'vi')
            ];
        }

        return response()->json($formattedData);
    }

    /**
     * Get products that are low in stock.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLowStockProducts(Request $request)
    {
        $threshold = $request->input('threshold', 20); // Default threshold

        $lowStockProducts = Product::select('id', 'name', 'stock_quantity', 'price')
            ->with('category', 'brand') // Eager load relationships if needed for display
            ->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')
            ->get();

        $formattedData = $lowStockProducts->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'stock_quantity' => $product->stock_quantity,
                'price' => $product->formatted_price,
                'category' => $product->category->name ?? 'N/A',
                'brand' => $product->brand->name ?? 'N/A',
                'thumbnail_url' => $product->thumbnail_url,
            ];
        });

        return response()->json($formattedData);
    }

    /**
     * Get best-selling products within a specified date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBestSellingProducts(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'integer|min:1|max:100', // Limit for top products
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $limit = $request->input('limit', 10);

        $bestSellingProducts = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue_generated')
            )
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderByDesc('total_quantity_sold')
            ->limit($limit)
            ->get();

        $formattedData = $bestSellingProducts->map(function ($product) {
            // Re-fetch product to get accessors like thumbnail_url, or adjust query to include it
            $fullProduct = Product::find($product->id);
            return [
                'id' => $product->id,
                'name' => $product->name,
                'total_quantity_sold' => $product->total_quantity_sold,
                'total_revenue_generated' => \Illuminate\Support\Number::currency($product->total_revenue_generated, 'VND', 'vi'),
                'thumbnail_url' => $fullProduct ? $fullProduct->thumbnail_url : 'https://placehold.co/400x400/EFEFEF/AAAAAA&text=Product', // Use accessor
            ];
        });

        return response()->json($formattedData);
    }
}
