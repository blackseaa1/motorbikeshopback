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
     * Hiển thị chi tiết một sản phẩm.
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->inRandomOrder()
            ->limit(4)
            ->get();
        return view('customer.product.show', compact('product', 'relatedProducts'));
    }

    /**
     * Lấy và hiển thị các sản phẩm theo danh mục.
     */
    public function getProductsByCategory($slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $products = Product::where('category_id', $category->id)
            ->where('status', 'published')
            ->paginate(9);
        $brands = Brand::where('is_active', true)->orderBy('name', 'asc')->get();
        $vehicleBrands = VehicleBrand::where('is_active', true)->orderBy('name', 'asc')->get();

        // *** THAY ĐỔI QUAN TRỌNG Ở ĐÂY ***
        // Trả về view 'customer.categories.show' thay vì 'index'
        return view('customer.categories.show', compact(
            'category',
            'products',
            'brands',
            'vehicleBrands'
        ));
    }
}
