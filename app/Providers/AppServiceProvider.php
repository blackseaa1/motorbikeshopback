<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // Thêm dòng này
use App\Models\Category; // Thêm dòng này

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
        Paginator::useBootstrapFive();

        // Chia sẻ biến $categories với tất cả các view
        try {
            $sharedCategories = Category::where('is_active', true)->select('id', 'name', 'slug')->get();
            View::share('sharedCategories', $sharedCategories);
        } catch (\Exception $e) {
            // Xử lý trường hợp không có kết nối DB khi chạy migrate/composer install
            View::share('sharedCategories', collect());
        }
    }
}
