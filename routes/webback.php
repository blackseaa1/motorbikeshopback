<?php

use Illuminate\Support\Facades\Route;

// Thay đổi namespace của Controller cho phù hợp
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Sales\OrderController;
use App\Http\Controllers\Admin\Sales\PromotionController;
use App\Http\Controllers\Admin\ProductManagement\ProductController;
use App\Http\Controllers\Admin\ProductManagement\CategoryController;
use App\Http\Controllers\Admin\ProductManagement\BrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleController;
use App\Http\Controllers\Admin\ProductManagement\InventoryController;
use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Admin\Content\ReviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserManagement\AdminAccountController;
use App\Http\Controllers\Admin\UserManagement\CustomerAccountController;
use App\Http\Controllers\Admin\System\DeliveryServiceController;
use App\Http\Controllers\Admin\System\GeographyController;
use App\Http\Controllers\Admin\System\SettingController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- CÁC ROUTE CÔNG KHAI ---
Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');


// =========================================================================
// --- CÁC ROUTE CỦA TRANG ADMIN ---
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Quản lý Bán Hàng ---
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::resource('orders', OrderController::class);
        Route::resource('promotions', PromotionController::class);
    });

    // --- Quản lý Sản phẩm ---
    Route::prefix('productManagement')->name('productManagement.')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('vehicle', VehicleController::class); // Đổi tên route resource cho nhất quán
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
    });

    // --- Quản lý Nội dung ---
    Route::prefix('content')->name('content.')->group(function () {
        Route::resource('posts', PostController::class);
        Route::resource('reviews', ReviewController::class);
    });

    // --- Thống kê & Báo cáo ---
    Route::get('reports', [ReportController::class, 'index'])->name('reports');

    // --- Quản lý Người dùng (ĐÃ SỬA LẠI) ---
    Route::prefix('userManagement')->name('userManagement.')->group(function () {
        Route::resource('admins', AdminAccountController::class);
        Route::resource('customers', CustomerAccountController::class);
    });

    // --- Cấu hình Hệ thống ---
    Route::prefix('system')->name('system.')->group(function () {
        Route::resource('delivery', DeliveryServiceController::class);
        Route::resource('geography', GeographyController::class);
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
