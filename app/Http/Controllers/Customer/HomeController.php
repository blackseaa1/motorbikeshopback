<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product; // <-- Thêm dòng này để sử dụng Product Model
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ của khách hàng.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Lấy 6 danh mục đang ở trạng thái hoạt động, sắp xếp theo thứ tự mới nhất
        $categories = Category::where('status', Category::STATUS_ACTIVE)
            ->latest()
            ->take(6)
            ->get();

        // (PHẦN BỔ SUNG)
        // Lấy 8 sản phẩm đang bán làm sản phẩm nổi bật
        // Bạn có thể thay đổi logic ở đây, ví dụ: lấy sản phẩm được xem nhiều nhất, bán chạy nhất, v.v.
        $featuredProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->inRandomOrder() // Lấy ngẫu nhiên để trang chủ luôn mới mẻ
            ->take(8)
            ->get();

        // Trả về view 'customer.home' và truyền cả 2 biến: categories và featuredProducts
        return view('customer.home', compact('categories', 'featuredProducts'));
    }
    public function blog()
    {
        // Sau này bạn có thể lấy các bài blog từ DB và truyền vào view
        return view('customer.blog');
    }

    // Thêm phương thức mới cho trang Liên hệ
    public function contact()
    {
        return view('customer.contact');
    }
}
