<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Category; // Đảm bảo model Category đã được import
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule để validate status

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách tất cả danh mục.
     */
    public function index()
    {
        $categories = Category::latest()->get(); // Lấy tất cả, sắp xếp mới nhất lên đầu
        return view('admin.productManagement.categories', compact('categories')); //
    }

    /**
     * Lưu một danh mục mới.
     */
    public function store(Request $request)
    {
        $request->validate([ //
            'name' => 'required|string|max:255|unique:categories,name', //
            'description' => 'nullable|string', //
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ]);

        Category::create([ //
            'name' => $request->name, //
            'description' => $request->description, //
            'status' => $request->status,
        ]);

        return redirect()->route('admin.productManagement.categories.index') //
            ->with('success', 'Tạo danh mục thành công!'); //
    }

    /**
     * Cập nhật một danh mục đã có.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([ //
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id, //
            'description' => 'nullable|string', //
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ]);

        $category->update([ //
            'name' => $request->name, //
            'description' => $request->description, //
            'status' => $request->status,
        ]);

        return redirect()->route('admin.productManagement.categories.index') //
            ->with('success', 'Cập nhật danh mục thành công!'); //
    }

    /**
     * Xóa một danh mục.
     */
    public function destroy(Category $category)
    {
        // Cân nhắc kiểm tra xem danh mục có sản phẩm nào không trước khi xóa
        // Ví dụ: if ($category->products()->count() > 0) { ... return error ... }
        $category->delete(); //
        return redirect()->route('admin.productManagement.categories.index') //
            ->with('success', 'Xóa danh mục thành công!'); //
    }

    /**
     * Thay đổi trạng thái (active/inactive) của một danh mục.
     */
    public function toggleStatus(Request $request, Category $category)
    {
        // Không cần validate gì đặc biệt ở đây vì chúng ta chỉ đảo ngược trạng thái hiện tại
        $category->status = ($category->status === Category::STATUS_ACTIVE) ? Category::STATUS_INACTIVE : Category::STATUS_ACTIVE;
        $category->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái danh mục thành công!',
                'new_status' => $category->status,
                'status_text' => $category->isActive() ? 'Hoạt động' : 'Đã ẩn', // Dùng cho UI
                'new_icon_class' => $category->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill',
                'new_button_title' => $category->isActive() ? 'Ẩn danh mục' : 'Hiện danh mục',
            ]);
        }

        // Fallback nếu không phải AJAX, mặc dù route này nên được gọi bằng AJAX
        $message = $category->isActive() ? 'Danh mục đã được hiển thị.' : 'Danh mục đã được ẩn.';
        return redirect()->route('admin.productManagement.categories.index')->with('success', $message);
    }
}
