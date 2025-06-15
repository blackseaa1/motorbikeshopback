<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Sử dụng withCount('products') để lấy số lượng sản phẩm hiệu quả
        $categories = Category::where('status', Category::STATUS_ACTIVE)
            ->withCount('products') // Đếm số sản phẩm trong mỗi category
            ->orderBy('name')
            ->paginate(9);

        return view('customer.categories.index', compact('categories'));
    }
}
