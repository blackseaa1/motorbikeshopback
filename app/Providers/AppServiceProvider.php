<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // Đảm bảo đã import
use App\Models\Category;             // Đảm bảo đã import

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
        // Sử dụng Bootstrap 5 cho phân trang
        Paginator::useBootstrapFive();

        // SỬA ĐỔI: Dùng View::share để chia sẻ biến $sharedCategories cho TOÀN BỘ các view.
        // Đây là cách khắc phục lỗi "Undefined variable $sharedCategories".
        try {
            // Lấy các danh mục đang hoạt động và chỉ chọn các cột cần thiết (id, name).
            $sharedCategories = Category::where('is_active', true)
                ->select('id', 'name') // Bỏ 'slug' để thống nhất với việc không dùng slug nữa
                ->orderBy('name', 'asc')
                ->get();

            View::share('sharedCategories', $sharedCategories);
        } catch (\Exception $e) {
            // Xử lý trường hợp không thể kết nối database (ví dụ khi chạy lệnh artisan)
            // Chia sẻ một collection rỗng để tránh lỗi.
            View::share('sharedCategories', collect());
        }
    }
}
