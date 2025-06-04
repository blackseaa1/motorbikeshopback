<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Import Auth facade

// Import các Controller cho Admin
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\Auth\PendingAuthorizationController; // Controller cho trang chờ phân quyền
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Profile\AdminProfileController; // <<< THÊM CONTROLLER HỒ SƠ ADMIN

// Import các controller khác (giữ nguyên từ file của bạn, đảm bảo namespace chính xác)
use App\Http\Controllers\Admin\Sales\OrderController;
use App\Http\Controllers\Admin\Sales\PromotionController;
use App\Http\Controllers\Admin\ProductManagement\CategoryController;
use App\Http\Controllers\Admin\ProductManagement\ProductController;
use App\Http\Controllers\Admin\ProductManagement\BrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleManagementController; // Controller mới cho trang hợp nhất
use App\Http\Controllers\Admin\ProductManagement\VehicleBrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleModelController; // Giả sử bạn đã đổi tên VehicleController trong file VehicleModel.php
use App\Http\Controllers\Admin\ProductManagement\InventoryController;
use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Admin\Content\ReviewController;
use App\Http\Controllers\Admin\ReportsController; // Đảm bảo tên file là ReportsController.php
use App\Http\Controllers\Admin\UserManagement\AdminController as UserManagementAdminController; // Đổi tên alias để tránh xung đột
use App\Http\Controllers\Admin\System\GeographyController;
use App\Http\Controllers\Admin\System\ProvinceController; // Sửa namespace nếu cần
use App\Http\Controllers\Admin\System\DistrictController; // Sửa namespace nếu cần
use App\Http\Controllers\Admin\System\WardController;    // Sửa namespace nếu cần
use App\Http\Controllers\Api\GeographyApiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- ROUTE GỐC / ---
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        $adminUser = Auth::guard('admin')->user();
        if ($adminUser->role === null) {
            return redirect()->route('admin.pending_authorization');
        }
        return redirect()->route('admin.dashboard');
    }
    // Nếu không phải admin hoặc chưa đăng nhập, chuyển đến trang login của admin
    return redirect()->route('admin.auth.login');
});

// --- CÁC ROUTE CHO GUEST ADMIN (LOGIN, REGISTER) ---
// Áp dụng middleware 'guest:admin' để người đã đăng nhập admin không vào lại được
Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('admin.auth.login');
    Route::post('login', [AdminLoginController::class, 'login']); // Action login

    Route::get('register', [AdminRegisterController::class, 'showRegistrationForm'])->name('admin.auth.register');
    Route::post('register', [AdminRegisterController::class, 'register']); // Action register
});


// =========================================================================
// --- CÁC ROUTE CỦA TRANG ADMIN (YÊU CẦU ĐĂNG NHẬP ADMIN) ---
// =========================================================================
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {

    // Trang chờ phân quyền - Route này phải được định nghĩa TRƯỚC group dùng middleware 'admin.hasrole'
    // và không bị chặn bởi 'admin.hasrole'
    Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])->name('pending_authorization');

    // Admin Logout Route
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    // Các route cần admin đã được phân quyền (có role khác null)
    // Áp dụng middleware 'admin.hasrole' cho nhóm này
    Route::middleware(['admin.hasrole'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- Quản lý Bán Hàng ---
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders');
            Route::get('promotions', [PromotionController::class, 'index'])->name('promotions');
        });

        // --- Quản lý Sản phẩm ---
        Route::prefix('productManagement')->name('productManagement.')->group(function () {
            Route::get('products', [ProductController::class, 'index'])->name('products');

            Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
            Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');

            Route::resource('brands', BrandController::class)->except(['create', 'edit', 'show']);
            Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggleStatus');

            // TRANG QUẢN LÝ XE HỢP NHẤT (HÃNG XE & DÒNG XE)
            Route::get('vehicle-management', [VehicleManagementController::class, 'index'])->name('vehicle.index');

            // --- API/Actions cho HÃNG XE (VEHICLE BRAND) ---
            Route::resource('vehicle-brands', VehicleBrandController::class)
                ->except(['create', 'edit', 'show']) // Các form sẽ là modal
                ->names('vehicleBrands'); // Sẽ tạo ra admin.productManagement.vehicleBrands.store, .update, .destroy, .index (nếu cần riêng)
            Route::post('vehicle-brands/{vehicleBrand}/toggle-status', [VehicleBrandController::class, 'toggleStatus'])
                ->name('vehicleBrands.toggleStatus');

            // --- API/Actions cho DÒNG XE (VEHICLE MODEL) ---
            Route::resource('vehicle-models', VehicleModelController::class)
                ->except(['create', 'edit', 'show'])
                ->names('vehicleModels');
            Route::post('vehicle-models/{vehicleModel}/toggle-status', [VehicleModelController::class, 'toggleStatus'])
                ->name('vehicleModels.toggleStatus');

            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
        });

        // --- Quản lý Nội dung ---
        Route::prefix('content')->name('content.')->group(function () {
            Route::get('posts', [PostController::class, 'index'])->name('posts');
            Route::get('reviews', [ReviewController::class, 'index'])->name('reviews');
        });

        // --- Thống kê & Báo cáo ---
        Route::get('reports', [ReportsController::class, 'index'])->name('reports');

        // --- Quản lý Người dùng ---
        Route::prefix('userManagement')->name('userManagement.')->group(function () {
            Route::get('admins', [UserManagementAdminController::class, 'index'])->name('admins');
            Route::get('customers', function () {
                return view('admin.userManagement.customers'); // Placeholder
            })->name('customers');
        });

        // --- Cấu hình Hệ thống ---
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('delivery', function () {
                return view('admin.system.delivery'); // Placeholder
            })->name('delivery');
            Route::prefix('geography')->name('geography.')->group(function () {
                Route::get('/', [GeographyController::class, 'index'])->name('index');
                Route::post('/import', [GeographyController::class, 'import'])->name('import');
                Route::resource('provinces', ProvinceController::class)->except(['create', 'edit', 'show']);
                Route::resource('districts', DistrictController::class)->except(['create', 'edit', 'show']);
                Route::resource('wards', WardController::class)->except(['create', 'edit', 'show']);
            });
            Route::get('settings', function () {
                return view('admin.system.settings'); // Placeholder
            })->name('settings');
        });

        // --- HỒ SƠ ADMIN ---
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [AdminProfileController::class, 'showProfileForm'])->name('show');
            Route::post('/update-info', [AdminProfileController::class, 'updateInfo'])->name('updateInfo');
            Route::post('/change-password', [AdminProfileController::class, 'changePassword'])->name('changePassword');
            Route::post('/update-avatar', [AdminProfileController::class, 'updateAvatar'])->name('updateAvatar');
        });
    }); // Kết thúc group middleware 'admin.hasrole'
}); // Kết thúc group prefix 'admin' và middleware 'auth:admin'

// =========================================================================
// --- CÁC ROUTE API ---
// =========================================================================
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('provinces.districts');
    Route::get('/notifications/unread-count', function () {
        $simulatedCount = rand(0, 5);
        return response()->json(['count' => $simulatedCount]);
    })->middleware('auth:admin')->name('notifications.unreadCount');
});
