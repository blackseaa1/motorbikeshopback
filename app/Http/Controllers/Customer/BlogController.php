<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Hiển thị trang danh sách các bài viết, có xử lý tìm kiếm.
     */
    public function index(Request $request)
    {
        $query = BlogPost::query()
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->with('author');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $posts = $query->latest()->paginate(5)->withQueryString();

        $recentPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
            ->latest()
            ->take(5)
            ->get();

        // SỬA LẠI ĐƯỜNG DẪN VIEW
        return view('customer.blog.index', compact('posts', 'recentPosts'));
    }

    /**
     * Hiển thị chi tiết một bài viết.
     */
    public function show(BlogPost $blogPost)
    {
        $isAuthor = auth('customer')->check() && auth('customer')->id() === $blogPost->author_id;

        if ($blogPost->status !== BlogPost::STATUS_PUBLISHED && !$isAuthor) {
            abort(404);
        }

        $blogPost->load('author');

        $recentPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
            ->where('id', '!=', $blogPost->id)
            ->latest()
            ->take(5)
            ->get();

        // SỬA LẠI ĐƯỜNG DẪN VIEW
        return view('customer.blog.show', compact('blogPost', 'recentPosts'));
    }
}
