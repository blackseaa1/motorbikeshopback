<?php

namespace App\Providers;

use Illuminate\Support\Facades\View; // <-- Thêm dòng này
use Illuminate\Support\ServiceProvider;
use App\Models\Category; // <-- Thêm dòng này

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
        // SỬA ĐỔI: Chia sẻ dữ liệu danh mục với một view cụ thể (header)
        View::composer('customer.layouts.partials._header', function ($view) {
            $navbarCategories = Category::where('status', Category::STATUS_ACTIVE)
                ->orderBy('name', 'asc')
                ->get();
            $view->with('navbarCategories', $navbarCategories);
        });
    }
}
