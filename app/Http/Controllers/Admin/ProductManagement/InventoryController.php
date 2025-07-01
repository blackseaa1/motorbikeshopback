<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Product; // Import Product model
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory, focusing on low stock products.
     * Hiển thị danh sách tồn kho, tập trung vào các sản phẩm sắp hết hàng.
     */
    public function index(): View
    {
        // Fetch products with low stock (e.g., quantity less than 10)
        // Lấy các sản phẩm có số lượng tồn kho thấp (ví dụ: số lượng < 10)
        // You can adjust the threshold as needed
        // Bạn có thể điều chỉnh ngưỡng này tùy theo nhu cầu
        $lowStockProducts = Product::with('category', 'brand', 'firstImage') // Eager load relationships for display
            ->where('stock_quantity', '<', 10)
            ->where('stock_quantity', '>', 0) // Ensure stock is not zero or negative
            ->orderBy('stock_quantity', 'asc') // Order by lowest stock first
            ->paginate(10); // Paginate the results for better performance

        return view('admin.productManagement.inventory.inventory', compact('lowStockProducts'));
    }
}
