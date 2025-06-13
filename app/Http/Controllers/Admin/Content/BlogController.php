<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    /**
     * Hiển thị danh sách bài viết theo đúng quyền.
     * Super Admin thấy tất cả.
     * Các vai trò khác (bao gồm cả Admin) chỉ thấy bài của mình và các bài đã xuất bản.
     */
    public function index(Request $request)
    {
        // Việc phân quyền được xử lý bởi middleware 'can:viewAny' trong file routes/web.php
        $user = Auth::user();

        $query = BlogPost::with('author');

        // Áp dụng quy tắc phân quyền hiển thị chặt chẽ hơn
        // Nếu người dùng không phải là Super Admin...
        if (!$user->isSuperAdmin()) {
            // ...thì chỉ hiển thị những bài viết thỏa mãn MỘT trong hai điều kiện:
            $query->where(function ($q) use ($user) {
                // 1. Là bài viết của chính họ (bất kể trạng thái).
                $q->where('author_id', $user->id)
                    ->where('author_type', get_class($user)) // Thêm điều kiện này để chắc chắn
                    // 2. HOẶC là bài viết đã được xuất bản của người khác.
                    ->orWhere('status', BlogPost::STATUS_PUBLISHED);
            });
        }
        // Ngược lại, nếu là Super Admin, không áp dụng điều kiện lọc, họ sẽ thấy tất cả.

        // Xử lý bộ lọc xem Thùng rác
        $status = $request->query('status');
        if ($status === 'trashed') {
            $query->onlyTrashed();
        }

        $blogs = $query->latest()->paginate(15);
        return view('admin.content.blog.blogs', compact('blogs', 'status'));
    }

    /**
     * Lưu một bài viết mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        // Phân quyền được xử lý bởi middleware 'can:create'
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:blog_posts,title',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => ['required', Rule::in([BlogPost::STATUS_PUBLISHED, BlogPost::STATUS_DRAFT, BlogPost::STATUS_PENDING])],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $author = Auth::user();
            $postData = $request->except('image');
            $postData['author_id'] = $author->id;
            $postData['author_type'] = get_class($author);

            if ($request->hasFile('image')) {
                $postData['image_url'] = $request->file('image')->store('blog_thumbnails', 'public');
            }

            BlogPost::create($postData);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tạo bài viết mới thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo bài viết: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống.'], 500);
        }
    }

    /**
     * Hiển thị chi tiết một bài viết.
     */
    public function show(BlogPost $blog)
    {
        // Phân quyền được xử lý bởi middleware 'can:view,blog'
        $blog->load('author');
        return response()->json($blog);
    }

    /**
     * Cập nhật một bài viết đã có.
     */
    public function update(Request $request, BlogPost $blog)
    {
        // Phân quyền được xử lý bởi middleware 'can:update,blog'
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255', Rule::unique('blog_posts')->ignore($blog->id)],
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => ['required', Rule::in([BlogPost::STATUS_PUBLISHED, BlogPost::STATUS_DRAFT, BlogPost::STATUS_ARCHIVED, BlogPost::STATUS_PENDING])],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $postData = $request->except(['image', '_method']);
            if ($request->hasFile('image')) {
                if ($blog->image_url) {
                    Storage::disk('public')->delete($blog->image_url);
                }
                $postData['image_url'] = $request->file('image')->store('blog_thumbnails', 'public');
            }
            $blog->update($postData);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật bài viết thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật bài viết (ID: {$blog->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi khi cập nhật bài viết.'], 500);
        }
    }

    /**
     * Chuyển bài viết vào thùng rác (xóa mềm).
     */
    public function destroy(BlogPost $blog)
    {
        // Phân quyền được xử lý bởi middleware 'can:delete,blog'
        try {
            $blog->delete();
            return response()->json(['success' => true, 'message' => "Đã chuyển bài viết '{$blog->title}' vào thùng rác."]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm bài viết (ID: {$blog->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa bài viết này.'], 500);
        }
    }

    /**
     * Khôi phục một bài viết từ thùng rác.
     */
    public function restore(BlogPost $blog)
    {
        // Phân quyền được xử lý bởi middleware 'can:restore,blog'
        try {
            $blog->restore();
            return response()->json(['success' => true, 'message' => "Đã khôi phục bài viết '{$blog->title}'."]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục bài viết (ID: {$blog->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể khôi phục bài viết này.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn một bài viết khỏi cơ sở dữ liệu.
     */
    public function forceDelete(Request $request, BlogPost $blog)
    {
        // Phân quyền được xử lý bởi middleware 'can:forceDelete,blog'
        DB::beginTransaction();
        try {
            if ($blog->image_url) {
                Storage::disk('public')->delete($blog->image_url);
            }
            $blog->forceDelete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đã xóa vĩnh viễn bài viết!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa vĩnh viễn bài viết (ID: {$blog->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa vĩnh viễn bài viết này.'], 500);
        }
    }

    /**
     * Chuyển đổi trạng thái của bài viết (Xuất bản / Ẩn).
     */
    public function toggleStatus(Request $request, BlogPost $blog)
    {
        // Phân quyền được xử lý bởi middleware 'can:toggleStatus,blog'
        try {
            $newStatus = $blog->isPublished()
                ? BlogPost::STATUS_ARCHIVED // Nếu đang xuất bản -> Ẩn
                : BlogPost::STATUS_PUBLISHED; // Nếu đang ở trạng thái khác -> Xuất bản

            $blog->status = $newStatus;
            $blog->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công.',
                'blog' => $blog->refresh()->load('author'),
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi đổi trạng thái bài viết (ID: {$blog->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi khi đổi trạng thái.'], 500);
        }
    }
}
