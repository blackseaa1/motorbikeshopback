<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách tất cả danh mục với phân trang.
     */
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admin.productManagement.category.categories', compact('categories'));
    }

    /**
     * Lưu một danh mục mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ]);

        $category = Category::create($request->all()); // Lưu danh mục được tạo vào biến $category

        // --- BẮT ĐẦU PHẦN THAY ĐỔI ---
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục thành công!',
                'category' => $category, // Trả về đối tượng category vừa tạo nếu cần (có thể dùng để cập nhật UI mà không cần reload)
                'redirect_url' => route('admin.productManagement.categories.index') // Gửi URL để JS có thể chuyển hướng
            ]);
        }
        // --- KẾT THÚC PHẦN THAY ĐỔI ---

        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Tạo danh mục thành công!');
    }

    /**
     * Cập nhật một danh mục đã có trong cơ sở dữ liệu.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ]);

        $category->update($request->all());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công!',
                'category' => $category->refresh(), // Refresh để đảm bảo lấy dữ liệu mới nhất (nếu có thay đổi trong model)
                'redirect_url' => route('admin.productManagement.categories.index')
            ]);
        }

        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    /**
     * Xóa một danh mục khỏi cơ sở dữ liệu.
     * PHIÊN BẢN ĐÃ SỬA LỖI: Đồng bộ hóa hoàn toàn với BrandController.
     */
    public function destroy(Request $request, Category $category)
    {
        $adminDeletionPassword = Config::get('admin.deletion_password');
        if ($adminDeletionPassword) {
            $request->validate([
                'deletion_password' => 'required|string',
            ]);
            if ($request->input('deletion_password') !== $adminDeletionPassword) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mật khẩu xác nhận không đúng.',
                        'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                    ], 422);
                }
                return redirect()->back()->with('error', 'Mật khẩu xác nhận không đúng.');
            }
        }

        try {
            $category->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa danh mục thành công!',
                    'redirect_url' => route('admin.productManagement.categories.index')
                ]);
            }
            return redirect()->route('admin.productManagement.categories.index')
                ->with('success', 'Xóa danh mục thành công!');
        } catch (QueryException $e) {
            $errorMessage = 'Đã xảy ra lỗi. Không thể xóa danh mục.';
            if ($e->getCode() === '23000') {
                $errorMessage = 'Không thể xóa danh mục này vì vẫn còn sản phẩm liên quan.';
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            return redirect()->route('admin.productManagement.categories.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Thay đổi trạng thái (active/inactive) của một danh mục.
     * PHIÊN BẢN ĐÃ SỬA LỖI: Đồng bộ hóa hoàn toàn với BrandController.
     */
    public function toggleStatus(Request $request, Category $category)
    {
        $category->status = ($category->isActive()) ? Category::STATUS_INACTIVE : Category::STATUS_ACTIVE;
        $category->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái danh mục thành công!',
                'new_status' => $category->status,
                'status_text' => $category->isActive() ? 'Hoạt động' : 'Đã ẩn',
                'status_badge_class' => $category->isActive() ? 'bg-success' : 'bg-secondary',
                'new_icon_class' => 'bi-power',
                'new_button_title' => $category->isActive() ? 'Ẩn danh mục này' : 'Hiển thị danh mục này'
            ]);
        }

        $message = $category->isActive() ? 'Danh mục đã được hiển thị.' : 'Danh mục đã được ẩn.';
        return redirect()->route('admin.productManagement.categories.index')->with('success', $message);
    }
}
