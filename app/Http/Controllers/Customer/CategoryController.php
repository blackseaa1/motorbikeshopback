<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\VehicleBrand; // <-- Thêm dòng này
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
        // Truy vấn các brands
        $brands = Brand::where('status', Brand::STATUS_ACTIVE)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        // Truy vấn thêm các vehicleBrands
        $vehicleBrands = VehicleBrand::where('status', VehicleBrand::STATUS_ACTIVE)
            ->withCount('vehicleModels')
            ->orderBy('name')
            ->get(); // <-- Thêm đoạn này

        // Truyền tất cả các biến cần thiết cho view
        return view('customer.categories.index', compact('categories', 'brands', 'vehicleBrands')); // <-- Cập nhật lại dòng này
    }
}
