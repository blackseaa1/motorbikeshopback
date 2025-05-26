<?php

use Illuminate\Support\Facades\Route;

// Controller cho Địa lý
use App\Http\Controllers\Admin\System\GeographyController;
use App\Http\Controllers\Admin\System\ProvinceController;
use App\Http\Controllers\Admin\System\DistrictController;
use App\Http\Controllers\Admin\System\WardController;
use App\Http\Controllers\Api\GeographyApiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- CÁC ROUTE CÔNG KHAI ---
Route::get('/', function () {
    return view('welcome');
});

Route::get('admin/login', function () {
    return view('admin.login');
})->name('admin.login');

// =========================================================================
// --- CÁC ROUTE CỦA TRANG ADMIN ---
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // --- Quản lý Bán Hàng ---
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('orders', function () {
            return view('admin.sales.orders');
        })->name('orders');
        Route::get('promotions', function () {
            return view('admin.sales.promotions');
        })->name('promotions');
    });

    // --- Quản lý Sản phẩm ---
    Route::prefix('productManagement')->name('productManagement.')->group(function () {
        Route::get('products', function () {
            return view('admin.productManagement.products');
        })->name('products');
        Route::get('categories', function () {
            return view('admin.productManagement.categories');
        })->name('categories');
        Route::get('brands', function () {
            return view('admin.productManagement.brands');
        })->name('brands');
        Route::get('vehicle', function () {
            return view('admin.productManagement.vehicle');
        })->name('vehicle');
        Route::get('inventory', function () {
            return view('admin.productManagement.inventory');
        })->name('inventory');
    });

    // --- Quản lý Nội dung ---
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('posts', function () {
            return view('admin.content.posts');
        })->name('posts');
        Route::get('reviews', function () {
            return view('admin.content.reviews');
        })->name('reviews');
    });

    // --- Thống kê & Báo cáo ---
    Route::get('reports', function () {
        return view('admin.reports');
    })->name('reports');

    // --- Quản lý Người dùng ---
    Route::prefix('userManagement')->name('userManagement.')->group(function () {
        Route::get('admins', function () {
            return view('admin.userManagement.admins');
        })->name('admins');
        Route::get('customers', function () {
            return view('admin.userManagement.customers');
        })->name('customers');
    });

    // --- Cấu hình Hệ thống ---
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('delivery', function () {
            return view('admin.system.delivery');
        })->name('delivery');

        // --- Quản lý Địa lý ---
        Route::prefix('geography')->name('geography.')->group(function () {
            Route::get('/', [GeographyController::class, 'index'])->name('index');
            Route::post('/import', [GeographyController::class, 'import'])->name('import');

            // CRUD cho Tỉnh/Thành phố
            Route::resource('provinces', ProvinceController::class)->except(['create', 'edit', 'show']);

            // CRUD cho Quận/Huyện
            Route::resource('districts', DistrictController::class)->except(['create', 'edit', 'show']);

            // CRUD cho Phường/Xã
            Route::resource('wards', WardController::class)->except(['create', 'edit', 'show']);
        });

        Route::get('settings', function () {
            return view('admin.system.settings');
        })->name('settings');
    });
});
// --- THÊM ROUTE API VÀO ĐÂY ---
Route::get('/api/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('api.provinces.districts');
