<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand; // <-- THÊM DÒNG NÀY

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ của khách hàng.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Lấy 4 danh mục đang hoạt động
        // Code mới: Lấy tất cả danh mục, sắp xếp theo mới nhất
        $categories = Category::where('status', 'active')->latest()->get();

        // Lấy 8 sản phẩm mới nhất
        $newProducts = Product::where('status', 'active')->latest()->take(8)->get();

        // Lấy 8 sản phẩm ngẫu nhiên làm sản phẩm nổi bật
        $featuredProducts = Product::where('status', 'active')->inRandomOrder()->take(8)->get();

        // --- THÊM MỚI: LẤY 4 THƯƠNG HIỆU NGẪU NHIÊN ---
        $brands = Brand::where('status', 'active')->inRandomOrder()->take(6)->get();
        // ------------------------------------------

        // Truyền tất cả dữ liệu qua view
        return view('customer.home', compact('categories', 'newProducts', 'featuredProducts', 'brands'));
    }

    /**
     * Hiển thị trang Blog.
     *
     * @return \Illuminate\View\View
     */
    public function blog()
    {
        return view('customer.blog');
    }

    /**
     * Hiển thị trang Liên hệ.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('customer.contact');
    }
}
