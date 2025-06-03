<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View; // Import View nếu bạn trả về view

class DashboardController extends Controller
{
    /**
     * Hiển thị trang dashboard của admin.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View // Hoặc chỉ public function index() nếu không dùng type hinting
    {
        // Tại đây bạn có thể truyền dữ liệu sang view nếu cần
        // $data = [
        //     'totalOrders' => 150,
        //     'totalRevenue' => 50000000,
        // ];
        // return view('admin.dashboard', $data);

        // Hoặc đơn giản là trả về view
        return view('admin.dashboard'); // Đảm bảo bạn có file view tại resources/views/admin/dashboard.blade.php
    }

    // Các phương thức khác của controller có thể được thêm vào đây
}
