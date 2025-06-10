<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controller Imports
| Sắp xếp theo thứ tự alphabet để dễ quản lý.
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\PendingAuthorizationController;
use App\Http\Controllers\Admin\Auth\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Admin\Content\ReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductManagement\BrandController;
use App\Http\Controllers\Admin\ProductManagement\CategoryController;
use App\Http\Controllers\Admin\ProductManagement\InventoryController;
use App\Http\Controllers\Admin\ProductManagement\ProductController;
use App\Http\Controllers\Admin\ProductManagement\VehicleBrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleManagementController;
use App\Http\Controllers\Admin\ProductManagement\VehicleModelController;
use App\Http\Controllers\Admin\Profile\AdminProfileController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\Sales\OrderController;
use App\Http\Controllers\Admin\Sales\PromotionController;
use App\Http\Controllers\Admin\System\DeliveryServiceController;
use App\Http\Controllers\Admin\System\DistrictController;
use App\Http\Controllers\Admin\System\GeographyController;
use App\Http\Controllers\Admin\System\ProvinceController;
use App\Http\Controllers\Admin\System\WardController;
use App\Http\Controllers\Admin\UserManagement\CustomerAccountController;
use App\Http\Controllers\Admin\UserManagement\StaffAccountController;
use App\Http\Controllers\Api\GeographyApiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Quan trọng: Mỗi khi bạn thay đổi file này, hãy chạy lệnh sau trong
| terminal để hệ thống cập nhật:
| php artisan route:clear
|
*/

// --- Chuyển hướng cho URL gốc (/) ---
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        $adminUser = Auth::guard('admin')->user();
        if ($adminUser->role === null) {
            return redirect()->route('admin.pending_authorization');
        }
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.auth.login');
});

// =========================================================================
// == NHÓM ROUTE CHO ADMIN ==
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 1. Nhóm Route Xác thực (Authentication)
    |--------------------------------------------------------------------------
    */
    Route::controller(AdminLoginController::class)->group(function () {
        Route::middleware('auth:admin')->group(function () {
            Route::get('/force-password-change', 'showForcePasswordChangeForm')->name('auth.showForcePasswordChangeForm');
            Route::post('/force-password-change', 'forcePasswordChange')->name('auth.forcePasswordChange');
            Route::post('/logout', 'logout')->name('logout');
        });
        Route::middleware('guest:admin')->group(function () {
            Route::get('login', 'showLoginForm')->name('auth.login');
            Route::post('login', 'login')->name('auth.login.perform');
        });
    });

    Route::controller(AdminRegisterController::class)->middleware('guest:admin')->group(function () {
        Route::get('register', 'showRegistrationForm')->name('auth.register');
        Route::post('register', 'register')->name('auth.register.perform');
    });

    Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])
        ->middleware('auth:admin')->name('pending_authorization');

    /*
    |--------------------------------------------------------------------------
    | 2. Nhóm Route cho Trang Quản Trị Chính
    | Yêu cầu: Đã đăng nhập, đã đổi mật khẩu lần đầu, đã được phân quyền.
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:admin', 'password.changed', 'admin.hasrole'])->group(function () {

        // --- Dashboard & Trang cá nhân ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::controller(AdminProfileController::class)->prefix('profile')->name('profile.')->group(function () {
            Route::get('/', 'showProfileForm')->name('show');
            Route::post('/update-info', 'updateInfo')->name('updateInfo');
            Route::post('/change-password', 'changePassword')->name('changePassword');
            Route::post('/update-avatar', 'updateAvatar')->name('updateAvatar');
        });

        // --- Module: Quản lý Bán hàng ---
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{order}', 'show')->name('show');
                Route::post('/{order}/update-status', 'updateStatus')->name('updateStatus');
            });
            Route::resource('promotions', PromotionController::class)->except(['create', 'edit']);
            Route::post('promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggleStatus');
        });

        // --- Module: Quản lý Sản phẩm ---
        Route::prefix('product-management')->name('productManagement.')->group(function () {
            // Products
            Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}', 'show')->name('show')->withTrashed();
                Route::post('/{product}', 'update')->name('update')->withTrashed(); // Dùng POST để xử lý form, Laravel tự nhận PUT/PATCH
                Route::delete('/{product}', 'destroy')->name('destroy');
                Route::post('/{product}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed();
                Route::post('/{product}/restore', 'restore')->name('restore')->withTrashed();
                Route::delete('/{product}/force-delete', 'forceDelete')->name('forceDelete')->withTrashed();
            });

            // Categories, Brands, Vehicle
            Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
            Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');
            Route::resource('brands', BrandController::class)->except(['create', 'edit', 'show']);
            Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggleStatus');
            Route::get('vehicles', [VehicleManagementController::class, 'index'])->name('vehicle.index');
            Route::resource('vehicle-brands', VehicleBrandController::class)->except(['index', 'create', 'edit', 'show'])->names('vehicleBrands');
            Route::post('vehicle-brands/{vehicle_brand}/toggle-status', [VehicleBrandController::class, 'toggleStatus'])->name('vehicleBrands.toggleStatus');
            Route::resource('vehicle-models', VehicleModelController::class)->except(['index', 'create', 'edit', 'show'])->names('vehicleModels');
            Route::post('vehicle-models/{vehicle_model}/toggle-status', [VehicleModelController::class, 'toggleStatus'])->name('vehicleModels.toggleStatus');
            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        });

        // --- Module: Quản lý Nội dung ---
        Route::prefix('content')->name('content.')->group(function () {
            Route::resource('posts', PostController::class)->except(['show']);
            Route::controller(ReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::delete('/{review}', 'destroy')->name('destroy');
                Route::post('/{review}/update-status', 'updateStatus')->name('updateStatus');
            });
        });

        // --- Module: Quản lý Người dùng ---
        Route::prefix('user-management')->name('userManagement.')->group(function () {
            Route::resource('staff', StaffAccountController::class);
            Route::controller(StaffAccountController::class)->prefix('staff')->name('staff.')->group(function () {
                Route::post('/{staff}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed();
                Route::post('/{staff}/reset-password', 'resetPassword')->name('resetPassword')->withTrashed();
            });
            Route::resource('customers', CustomerAccountController::class)->except(['create', 'edit', 'show']);
            Route::controller(CustomerAccountController::class)->prefix('customers')->name('customers.')->group(function () {
                Route::post('/{customer}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed();
                Route::post('/{customer}/reset-password', 'resetPassword')->name('resetPassword')->withTrashed();
                Route::post('/{customer}/restore', 'restore')->name('restore')->withTrashed();
                Route::delete('/{customer}/force-delete', 'forceDelete')->name('forceDelete')->withTrashed();
            });
        });

        // --- Module: Cấu hình Hệ thống & Báo cáo ---
        Route::prefix('system')->name('system.')->group(function () {
            Route::resource('delivery-services', DeliveryServiceController::class)->except(['create', 'edit', 'show'])->names('deliveryServices');
            Route::post('delivery-services/{delivery_service}/toggle-status', [DeliveryServiceController::class, 'toggleStatus'])->name('deliveryServices.toggleStatus');

            // Nhóm tất cả route địa lý vào chung một group để có tên route đúng
            Route::prefix('geography')->name('geography.')->group(function () {
                Route::get('/', [GeographyController::class, 'index'])->name('index');
                Route::post('/import', [GeographyController::class, 'import'])->name('import');
                // Tên route sẽ là: admin.system.geography.provinces.store
                Route::resource('provinces', ProvinceController::class)->only(['store', 'update', 'destroy']);
                Route::resource('districts', DistrictController::class)->only(['store', 'update', 'destroy']);
                Route::resource('wards', WardController::class)->only(['store', 'update', 'destroy']);
            });

            Route::get('settings', fn() => view('admin.system.settings'))->name('settings');
        });

        // Thống kê & Báo cáo
        Route::get('reports', [ReportsController::class, 'index'])->name('reports');
    });
});


// =========================================================================
// == API ROUTES (Dùng cho Javascript trong Web App) ==
// =========================================================================
Route::prefix('api')->name('api.')->group(function () {
    // API lấy danh sách quận/huyện theo tỉnh/thành
    Route::get('/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('provinces.districts');

    // API lấy số thông báo chưa đọc
    Route::get('/notifications/unread-count', function () {
        return response()->json([
            // 'count' => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->unreadNotifications()->count() : 0
        ]);
    })->middleware('auth:admin')->name('notifications.unreadCount');
});
