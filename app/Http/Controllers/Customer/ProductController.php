<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // Thêm dòng này

class ProductController extends Controller
{
    /**
     * Hiển thị trang danh sách tất cả sản phẩm.
     */
    public function index()
    {
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->latest()
            ->paginate(12);

        return view('customer.products.index', compact('products'));
    }

    /**
     * Hiển thị trang chi tiết một sản phẩm.
     */
    public function show($slug)
    {
        // Ưu tiên tìm sản phẩm theo slug, nếu không có thì tìm bằng ID.
        // Tải sẵn các quan hệ 'images', 'brand', 'category' để tối ưu.
        // firstOrFail() sẽ tự động trả về 404 nếu không tìm thấy sản phẩm.
        $product = Product::with(['images', 'brand', 'category', 'reviews.customer'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        // (Tùy chọn) Lấy các sản phẩm liên quan (cùng danh mục)
        $relatedProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id) // Loại trừ sản phẩm hiện tại
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('customer.products.show', compact('product', 'relatedProducts'));
    }

    /**
     * (PHƯƠNG THỨC MỚI)
     * Hiển thị các sản phẩm thuộc một danh mục cụ thể.
     */
    public function productsByCategory($slug)
    {
        // Tìm danh mục theo slug hoặc ID. Nếu không tìm thấy, sẽ trả về lỗi 404.
        $category = Category::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();

        // Lấy tất cả sản phẩm đang hoạt động thuộc danh mục này, có phân trang
        $products = Product::where('category_id', $category->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->with('brand') // Tải sẵn thông tin thương hiệu để tối ưu hóa query
            ->latest()
            ->paginate(12); // Hiển thị 12 sản phẩm trên mỗi trang

        // Trả về view và truyền dữ liệu danh mục và sản phẩm đã lọc
        return view('customer.products.products_by_category', compact('category', 'products'));
    }
}
