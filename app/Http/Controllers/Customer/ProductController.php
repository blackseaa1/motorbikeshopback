<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Hiển thị trang liệt kê tất cả sản phẩm với bộ lọc.
     * Xử lý cho route('products.index')
     */
    public function index(Request $request)
    {
        // TODO: Thêm logic lọc sản phẩm nâng cao dựa trên $request

        $products = Product::where('status', 'active')->paginate(12);

        // Lấy dữ liệu cho bộ lọc (nếu cần)
        $categories = Category::where('status', 'active')->get();
        $brands = Brand::where('status', true)->get();

        // Trả về view cho trang lọc sản phẩm
        return view('customer.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Hiển thị trang chi tiết sản phẩm.
     * Sử dụng Route Model Binding, Laravel tự tìm Product từ ID.
     * Xử lý cho route('products.show')
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        // Kiểm tra sản phẩm có active không, nếu không thì báo lỗi 404
        if ($product->status !== 'active') {
            abort(404);
        }

        // Tải các thông tin liên quan để tối ưu truy vấn
        $product->load('category', 'brand', 'images', 'vehicleModels.vehicleBrand');

        return view('customer.products.show', compact('product'));
    }
}
