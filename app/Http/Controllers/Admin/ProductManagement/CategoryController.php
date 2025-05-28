<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
// Bỏ "use Illuminate\Support\Facades\Storage;" vì không còn dùng

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('admin.productManagement.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        // 1. Validate dữ liệu (đã xóa 'logo_url')
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        // 2. Tạo danh mục mới (đã xóa 'logo_url')
        Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Tạo danh mục thành công!');
    }

    public function update(Request $request, Category $category)
    {
        // 1. Validate dữ liệu (đã xóa 'logo_url')
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        // 2. Cập nhật danh mục (đã xóa 'logo_url')
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(Category $category)
    {
        // (Bạn có thể thêm logic kiểm tra xem danh mục có sản phẩm nào không trước khi xóa)
        $category->delete();
        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Xóa danh mục thành công!');
    }
}
