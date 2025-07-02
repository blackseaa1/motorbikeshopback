<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // Import Category model [new]
use App\Models\Brand;    // Import Brand model [new]
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

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
        // Lấy các tham số tìm kiếm và lọc từ request
        $search = $request->query('search');        // [new]
        $category_id = $request->query('category_id'); // [new]
        $brand_id = $request->query('brand_id');    // [new]

        $query = Product::with('category', 'brand', 'firstImage') // Eager load relationships for display
            ->where('stock_quantity', '<', 10) //
            ->where('stock_quantity', '>', 0) // Ensure stock is not zero or negative
            ->orderBy('stock_quantity', 'asc'); // Order by lowest stock first

        // Áp dụng các bộ lọc nếu có
        if ($search) { // [new]
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($category_id) { // [new]
            $query->where('category_id', $category_id);
        }
        if ($brand_id) { // [new]
            $query->where('brand_id', $brand_id);
        }

        $lowStockProducts = $query->paginate(10)->withQueryString(); // Paginate the results for better performance

        // Nếu request là AJAX, trả về JSON
        if ($request->expectsJson()) {
            // Truyền toàn bộ collection $lowStockProducts vào partial view
            // để partial view tự lặp và render các hàng.
            $tableRowsHtml = view('admin.productManagement.inventory.partials._inventory_table_rows', [
                'lowStockProducts' => $lowStockProducts // Truyền đúng biến mà partial mong đợi
            ])->render();

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $lowStockProducts->links('admin.vendor.pagination')->render(),
            ]);
        }

        // Nếu không phải AJAX, trả về view đầy đủ
        // Lấy danh sách danh mục và thương hiệu để điền vào dropdown lọc trên frontend
        $categories = Category::where('status', 'active')->orderBy('name')->get(); // [new]
        $brands = Brand::where('status', 'active')->orderBy('name')->get();       // [new]

        // Để giải quyết việc truyền categories, brands cho modal update_product được include trong inventory.blade.php
        // Nếu bạn muốn hiển thị VehicleBrands trong modal đó, cũng phải truyền vào.
        $vehicleBrands = \App\Models\VehicleBrand::with(['vehicleModels' => fn($q) => $q->where('status', 'active')->orderBy('name')])
            ->where('status', 'active')->orderBy('name')->get(); //

        return view('admin.productManagement.inventory.inventory', compact('lowStockProducts', 'categories', 'brands', 'vehicleBrands')); //
    }
}
