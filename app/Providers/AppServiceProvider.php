<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Illuminate\Pagination\Paginator; // Thêm dòng này để sử dụng Paginator

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    // public function boot()
    // {
    //     Paginator::useBootstrapFive(); // Thêm dòng này để sử dụng style Bootstrap 5 cho phân trang
    //     // Hoặc Paginator::useBootstrapFour(); nếu bạn dùng Bootstrap 4
    //     // Hoặc Paginator::useBootstrap(); nếu bạn dùng Bootstrap 3
    // }
}
