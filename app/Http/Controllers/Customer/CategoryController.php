<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product; // Đảm bảo đã import Product
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Hiển thị trang liệt kê tất cả danh mục.
     * Xử lý cho route('categories.index')
     */
    public function index()
    {
        // Lấy danh sách các danh mục, kèm theo số lượng sản phẩm
        $categories = Category::where('status', 'active')
            ->withCount('products')
            ->paginate(12);

        return view('customer.categories.index', compact('categories'));
    }

    /**
     * Hiển thị trang chi tiết một danh mục và các sản phẩm thuộc về nó.
     * Sử dụng Route Model Binding, Laravel tự tìm Category từ ID.
     * Xử lý cho route('categories.show')
     *
     * @param  \App\Models\Category $category
     * @return \Illuminate\View\View
     */
    public function show(Category $category)
    {
        // Lấy các sản phẩm thuộc về danh mục này
        $products = $category->products()->where('status', 'active')->paginate(9);

        return view('customer.categories.show', compact('category', 'products'));
    }
}
