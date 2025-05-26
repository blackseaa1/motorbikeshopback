<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| File route này đã được cập nhật để trỏ đến các view Blade tương ứng
| dựa trên cấu trúc thư mục bạn cung cấp.
|
*/

// --- CÁC ROUTE CÔNG KHAI ---
Route::get('/', function () {
    return view('welcome');
});

// Route này trỏ đến file `resources/views/admin/login.blade.php`
Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');


// =========================================================================
// --- CÁC ROUTE CỦA TRANG ADMIN ---
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Trỏ đến `resources/views/admin/dashboard.blade.php`
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // --- Quản lý Bán Hàng ---
    Route::prefix('sales')->name('sales.')->group(function () {
        // Trỏ đến `resources/views/admin/sales/orders.blade.php`
        Route::get('orders', function() { return view('admin.sales.orders'); })->name('admin.salesorders');
        // Trỏ đến `resources/views/admin/sales/promotions.blade.php`
        Route::get('promotions', function() { return view('admin.sales.promotions'); })->name('admin.sales.promotions');
    });

    // --- Quản lý Sản phẩm ---
    Route::prefix('productManagement')->name('productManagement.')->group(function () {
        // Trỏ đến `resources/views/admin/productManagement/products.blade.php`
        Route::get('products', function() { return view('admin.productManagement.products'); })->name('admin.productManagement.products');
        // Trỏ đến `resources/views/admin/productManagement/categories.blade.php`
        Route::get('categories', function() { return view('admin.productManagement.categories'); })->name('admin.productManagement.categories');
        // Trỏ đến `resources/views/admin/productManagement/brands.blade.php`
        Route::get('brands', function() { return view('admin.productManagement.brands'); })->name('admin.productManagement.brands');
        // Trỏ đến `resources/views/admin/productManagement/vehicle.blade.php`
        Route::get('vehicle', function() { return view('admin.productManagement.vehicle'); })->name('admin.productManagement.vehicle');
        // Trỏ đến `resources/views/admin/productManagement/inventory.blade.php`
        Route::get('inventory', function() { return view('admin.productManagement.inventory'); })->name('admin.productManagement.inventory');
    });

    // --- Quản lý Nội dung (ĐÃ CẬP NHẬT) ---
    Route::prefix('content')->name('content.')->group(function () {
        // Trỏ đến `resources/views/admin/content/posts.blade.php`
        Route::get('posts', function() { return view('admin.content.posts'); })->name('admin.content.posts');
        // Trỏ đến `resources/views/admin/content/reviews.blade.php`
        Route::get('reviews', function() { return view('admin.content.reviews'); })->name('admin.content.reviews');
    });

    // --- Thống kê & Báo cáo ---
    // Trỏ đến `resources/views/admin/reports.blade.php`
    Route::get('reports', function() {
        return view('admin.reports');
    })->name('admin.reports');

    // --- Quản lý Người dùng ---
    Route::prefix('userManagement')->name('userManagement.')->group(function () {
        // Trỏ đến `resources/views/admin/userManagement/admins.blade.php`
        Route::get('admins', function() { return view('admin.userManagement.admins'); })->name('admin.userManagement.admins');
        // Trỏ đến `resources/views/admin/userManagement/customers.blade.php`
        Route::get('customers', function() { return view('admin.userManagement.customers'); })->name('admin.userManagement.customers');
    });

    // --- Cấu hình Hệ thống ---
    Route::prefix('system')->name('system.')->group(function () {
        // Trỏ đến `resources/views/admin/system/delivery.blade.php`
        Route::get('delivery', function() { return view('admin.system.delivery'); })->name('admin.system.delivery');
        // Trỏ đến `resources/views/admin/system/geography.blade.php`
        Route::get('geography', function() { return view('admin.system.geography'); })->name('admin.system.geography');
        // Trỏ đến `resources/views/admin/system/settings.blade.php`
        Route::get('settings', function() { return view('admin.system.settings'); })->name('admin.system.settings');
    });
});