<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Policies\BlogPostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route; // Thêm dòng này để sử dụng Route facade
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Đăng ký Policy một cách tường minh
        Gate::policy(BlogPost::class, BlogPostPolicy::class);

        // 2. Tùy chỉnh cách Laravel tìm model 'blog'
        // Lệnh này yêu cầu Laravel: "Khi gặp route parameter {blog}, hãy dùng
        // logic này để tìm BlogPost, bao gồm cả những bài đã xóa mềm (withTrashed)".
        // Đây là chìa khóa để middleware 'can' hoạt động đúng.
        Route::bind('blog', function ($value) {
            return BlogPost::withTrashed()->find($value) ?? abort(404);
        });
    }
}
