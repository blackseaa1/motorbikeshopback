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

    }
}
