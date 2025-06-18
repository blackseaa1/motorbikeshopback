<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\VehicleBrand;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * SỬA ĐỔI: Hiển thị chi tiết sản phẩm bằng Route Model Binding
     * Laravel sẽ tự động tìm Product model có id trùng với tham số {product} trong URL.
     */
    public function show(Product $product)
    {
        // Kiểm tra xem sản phẩm có đang được bán không (tuỳ vào logic status của bạn)
        if ($product->status !== 'published') {
            abort(404, 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.');
        }

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('customer.product.show', compact('product', 'relatedProducts'));
    }

    /**
     * SỬA ĐỔI: Lấy và hiển thị các sản phẩm theo danh mục bằng Route Model Binding.
     * Laravel sẽ tự động tìm Category model có id trùng với tham số {category} trong URL.
     * Đổi tên phương thức `productsByCategory` thành `getProductsByCategory` để dễ hiểu hơn.
     */
    public function getProductsByCategory(Category $category)
    {
        // Kiểm tra xem danh mục có hoạt động không
        if (!$category->is_active) {
            abort(404, 'Danh mục không tồn tại.');
        }

        $products = Product::where('category_id', $category->id)
            ->where('status', 'published')
            ->paginate(9);

        // Các bộ lọc khác có thể giữ nguyên nếu cần
        $brands = Brand::where('is_active', true)->orderBy('name', 'asc')->get();
        $vehicleBrands = VehicleBrand::where('is_active', true)->orderBy('name', 'asc')->get();

        // Giả sử bạn có một view là 'customer.categories.show' để hiển thị sản phẩm của danh mục
        return view('customer.categories.show', compact(
            'category',
            'products',
            'brands',
            'vehicleBrands'
        ));
    }

    /**
     * Hiển thị trang liệt kê tất cả sản phẩm với bộ lọc.
     */
    public function index()
    {
        // Logic cho trang /products, ví dụ:
        $products = Product::where('status', 'published')->latest()->paginate(12);
        return view('customer.products.index', compact('products'));
    }
}
