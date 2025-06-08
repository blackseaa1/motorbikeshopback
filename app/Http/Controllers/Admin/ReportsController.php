<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        // Viết logic để hiển thị trang báo cáo ở đây
        return view('admin.reports.index'); // Ví dụ
    }
}
