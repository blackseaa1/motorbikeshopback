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

        // Trong phương thức getLowStockProducts()
        $lowStockProducts = Product::select('id', 'name', 'stock_quantity', 'price', 'category_id', 'brand_id')
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
            'limit' => 'integer|min:1|max:100',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $limit = $request->input('limit', 10);

        // ## Step 1: Get the best-selling product data (IDs and sales figures).
        // Note: We remove the 'image'/'thumbnail' column from here to avoid errors
        // and to keep the aggregation query simple and correct.
        $salesData = Product::join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select(
                'products.id',
                'products.name',
                'categories.name as category_name',
                'brands.name as brand_name',
                DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue_generated')
            )
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereIn('orders.status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->groupBy('products.id', 'products.name', 'categories.name', 'brands.name')
            ->orderByDesc('total_quantity_sold')
            ->limit($limit)
            ->get();

        // ## Step 2: Get all the unique product IDs from the sales data.
        $productIds = $salesData->pluck('id');

        // ## Step 3: Fetch the full Product models for those IDs in a single query.
        // Eager load any relationships needed for the 'thumbnail_url' accessor to work.
        // This avoids the N+1 query problem.
        $products = Product::with('images') // Assuming 'images' is the relationship name
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id'); // Key by ID for easy lookup

        // ## Step 4: Combine the sales data with the product model data (which has the thumbnail).
        $formattedData = $salesData->map(function ($sale) use ($products) {
            // Find the full product model from the collection we fetched.
            $product = $products->get($sale->id);

            return [
                'id' => $sale->id,
                'name' => $sale->name,
                'category' => $sale->category_name ?? 'N/A',
                'brand' => $sale->brand_name ?? 'N/A',
                'total_quantity_sold' => (int)$sale->total_quantity_sold,
                'total_revenue_generated' => \Illuminate\Support\Number::currency($sale->total_revenue_generated, 'VND', 'vi'),
                // Use the accessor from the full Product model, with a fallback.
                'thumbnail_url' => $product ? $product->thumbnail_url : 'https://placehold.co/50x50/grey/white?text=No+Img',
            ];
        });

        return response()->json($formattedData);
    }
}
