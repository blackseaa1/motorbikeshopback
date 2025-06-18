<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        try {
            // Lấy danh mục đang hoạt động để chia sẻ với tất cả view (cho header)
            $sharedCategories = Category::where('status', 'active')
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();

            View::share('sharedCategories', $sharedCategories);
        } catch (\Exception $e) {
            Log::error('Could not share categories with views: ' . $e->getMessage());
            View::share('sharedCategories', collect());
        }
    }
}
