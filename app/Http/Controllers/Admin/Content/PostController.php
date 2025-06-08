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
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Hiển thị danh sách các bài viết.
     */
    public function index()
    {
        // Eager load 'author' để tránh N+1 query
        $posts = BlogPost::with('author')->latest()->paginate(15);
        return view('admin.content.posts', compact('posts')); // Bạn cần tạo view này
    }

    /**
     * Lưu một bài viết mới.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:blog_posts,title',
            'content' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => ['required', Rule::in(['published', 'draft', 'archived'])],
        ]);

        DB::beginTransaction();
        try {
            // Xác định tác giả là admin đang đăng nhập
            $author = Auth::guard('admin')->user();

            $postData = $validatedData;
            $postData['author_id'] = $author->id;
            $postData['author_type'] = Admin::class; // Quan trọng cho quan hệ đa hình

            if ($request->hasFile('image_url')) {
                $postData['image_url'] = $request->file('image_url')->store('blog_thumbnails', 'public');
            }

            BlogPost::create($postData);

            DB::commit();

            return redirect()->route('admin.content.posts.index')->with('success', 'Tạo bài viết thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi tạo bài viết: " . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra, không thể tạo bài viết.')->withInput();
        }
    }

    /**
     * Hiển thị form để chỉnh sửa bài viết.
     */
    public function edit(BlogPost $post)
    {
        // Trả về view chỉnh sửa với dữ liệu của bài viết
        return view('admin.content.posts_edit', compact('post')); // Bạn cần tạo view này
    }

    /**
     * Cập nhật một bài viết đã có.
     */
    public function update(Request $request, BlogPost $post)
    {
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('blog_posts')->ignore($post->id)],
            'content' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => ['required', Rule::in(['published', 'draft', 'archived'])],
        ]);

        DB::beginTransaction();
        try {
            $postData = $validatedData;

            if ($request->hasFile('image_url')) {
                // Xóa ảnh cũ nếu có
                if ($post->image_url && Storage::disk('public')->exists($post->image_url)) {
                    Storage::disk('public')->delete($post->image_url);
                }
                $postData['image_url'] = $request->file('image_url')->store('blog_thumbnails', 'public');
            }

            $post->update($postData);

            DB::commit();

            return redirect()->route('admin.content.posts.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật bài viết (ID: {$post->id}): " . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra, không thể cập nhật.')->withInput();
        }
    }

    /**
     * Xóa một bài viết.
     */
    public function destroy(BlogPost $post)
    {
        try {
            // Xóa ảnh của bài viết khỏi storage
            if ($post->image_url && Storage::disk('public')->exists($post->image_url)) {
                Storage::disk('public')->delete($post->image_url);
            }
            $post->delete();

            return redirect()->route('admin.content.posts.index')->with('success', 'Xóa bài viết thành công!');
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa bài viết (ID: {$post->id}): " . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra, không thể xóa bài viết.');
        }
    }
}
