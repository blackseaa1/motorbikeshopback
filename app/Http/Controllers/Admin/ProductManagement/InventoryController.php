<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Product; // Import Product model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; // Import JsonResponse

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory, focusing on low stock products.
     * Hiển thị danh sách tồn kho, tập trung vào các sản phẩm sắp hết hàng.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        // Fetch products with low stock (e.g., quantity less than 10)
        // Lấy các sản phẩm có số lượng tồn kho thấp (ví dụ: số lượng < 10)
        // You can adjust the threshold as needed
        // Bạn có thể điều chỉnh ngưỡng này tùy theo nhu cầu
        $query = Product::with('category', 'brand', 'firstImage') // Eager load relationships for display
            ->where('stock_quantity', '<', 10)
            ->where('stock_quantity', '>', 0) // Ensure stock is not zero or negative
            ->orderBy('stock_quantity', 'asc'); // Order by lowest stock first

        $lowStockProducts = $query->paginate(10)->withQueryString(); // Paginate the results for better performance

        // Nếu request là AJAX, trả về JSON
        if ($request->expectsJson()) {
            $tableRowsHtml = '';
            // Sử dụng loopIndex và startIndex để tính toán STT chính xác cho mỗi hàng
            $startIndex = $lowStockProducts->firstItem() ? ($lowStockProducts->firstItem() - 1) : 0;

            foreach ($lowStockProducts as $index => $product) {
                $tableRowsHtml .= view('admin.productManagement.inventory.partials._inventory_table_rows', [
                    'product' => $product,
                    'loopIndex' => $index,
                    'startIndex' => $startIndex,
                ])->render();
            }

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $lowStockProducts->links('admin.vendor.pagination')->render(),
            ]);
        }

        // Nếu không phải AJAX, trả về view đầy đủ
        return view('admin.productManagement.inventory.inventory', compact('lowStockProducts'));
    }
}
