<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Category; // Đảm bảo model Category đã được import
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule để validate status
use Illuminate\Support\Facades\Config;

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
    public function destroy(Request $request, Category $category) // Thêm Request $request
    {
        $request->validate([
            'deletion_password' => 'required|string',
        ]);

        // Lấy mật khẩu từ config (đã đọc từ .env)
        $requiredPassword = config::get('admin.deletion_password');
        // Hoặc trực tiếp: $requiredPassword = env('ADMIN_DELETION_PASSWORD');

        // Kiểm tra mật khẩu
        // Lưu ý: Mật khẩu này được lưu dạng plain text trong .env và config.
        // Nếu bạn muốn một lớp bảo mật cao hơn (ví dụ: hash mật khẩu này khi lưu trữ),
        // bạn sẽ cần một cơ chế để hash nó ban đầu và dùng Hash::check() ở đây.
        // Tuy nhiên, với một mật khẩu admin chung cho hành động xóa, plain text từ .env thường chấp nhận được.
        if (!$requiredPassword || $request->input('deletion_password') !== $requiredPassword) {
            // Nếu request là AJAX (thường là vậy với modal)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu xác nhận không đúng.',
                    'errors' => [ // Trả về lỗi cụ thể cho trường mật khẩu
                        'deletion_password' => ['Mật khẩu xác nhận không đúng.']
                    ]
                ], 422); // 422 Unprocessable Entity
            }
            // Fallback nếu không phải AJAX
            return redirect()->back()
                ->withErrors(['deletion_password_modal' => 'Mật khẩu xác nhận không đúng.'])
                ->withInput(); // Giữ lại input nếu có, dù ở đây không có nhiều input
        }

        // Cân nhắc kiểm tra xem danh mục có sản phẩm nào không trước khi xóa
        // Ví dụ: if ($category->products()->count() > 0) { ... Freturn error ... }

        $category->delete(); //

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công!',
                'redirect_url' => route('admin.productManagement.categories.index') // URL để JS redirect sau khi xóa
            ]);
        }

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
