<?php

use Illuminate\Support\Facades\Route;

// --- ADMIN CONTROLLERS ---
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\PendingAuthorizationController;
use App\Http\Controllers\Admin\Auth\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\Content\BlogController;
use App\Http\Controllers\Admin\Content\ReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductManagement\BrandController;
use App\Http\Controllers\Admin\ProductManagement\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductManagement\InventoryController;
use App\Http\Controllers\Admin\ProductManagement\ProductController as AdminProductController;
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

// --- CUSTOMER CONTROLLERS ---
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\CategoryController as CustomerCategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Mỗi khi bạn thay đổi file này, hãy chạy lệnh: php artisan route:clear
*/

// =========================================================================
// == CUSTOMER FACING ROUTES (FRONT-END) ==
// =========================================================================

Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Authentication routes ---
Route::controller(AuthController::class)->group(function () {
    Route::middleware(['guest', 'guest.admin'])->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login');
        Route::get('register', 'showRegisterForm')->name('register');
        Route::post('register', 'register');
    });
    Route::post('logout', 'logout')->name('logout')->middleware('auth');
});

// --- Trang sản phẩm và danh mục ---
Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [CustomerProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CustomerCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CustomerCategoryController::class, 'show'])->name('categories.show');


// --- Các trang tĩnh khác ---
Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// --- Route cho tài khoản người dùng ---
Route::prefix('account')->name('account.')->middleware('auth')->group(function () {
    Route::get('/', [AccountController::class, 'profile'])->name('profile');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
    Route::patch('/update-profile', [AccountController::class, 'updateProfile'])->name('updateProfile');
    Route::put('/update-password', [AccountController::class, 'updatePassword'])->name('updatePassword');
});


// =========================================================================
// == ADMIN ROUTES ==
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {
    // ... (Toàn bộ các route admin giữ nguyên không thay đổi) ...
    // --- Admin Authentication ---
    Route::middleware('auth:admin')->group(function () {
        Route::controller(AdminLoginController::class)->group(function () {
            Route::get('/force-password-change', 'showForcePasswordChangeForm')->name('auth.showForcePasswordChangeForm');
            Route::post('/force-password-change', 'forcePasswordChange')->name('auth.forcePasswordChange');
            Route::post('/logout', 'logout')->name('logout');
        });
        Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])->name('pending_authorization');
    });

    Route::middleware(['guest:admin', 'guest.customer'])->group(function () {
        Route::controller(AdminLoginController::class)->group(function () {
            Route::get('login', 'showLoginForm')->name('auth.login');
            Route::post('login', 'login')->name('auth.login.perform');
        });
        Route::controller(AdminRegisterController::class)->group(function () {
            Route::get('register', 'showRegistrationForm')->name('auth.register');
            Route::post('register', 'register')->name('auth.register.perform');
        });
    });

    // --- Main Admin Panel Routes ---
    Route::middleware(['auth:admin', 'password.changed', 'admin.hasrole'])->group(function () {
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- Profile Management ---
        Route::controller(AdminProfileController::class)->prefix('profile')->name('profile.')->group(function () {
            Route::get('/', 'showProfileForm')->name('show');
            Route::post('/update-info', 'updateInfo')->name('updateInfo');
            Route::post('/change-password', 'changePassword')->name('changePassword');
            Route::post('/update-avatar', 'updateAvatar')->name('updateAvatar');
        });

        // --- Module: Sales Management ---
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{order}', 'show')->name('show');
                Route::post('/{order}/update-status', 'updateStatus')->name('updateStatus');
            });
            Route::resource('promotions', PromotionController::class)->except(['create', 'edit']);
            Route::post('promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggleStatus');
        });

        // --- Module: Product Management ---
        Route::prefix('product-management')->name('productManagement.')->group(function () {
            Route::resource('products', AdminProductController::class)->except(['create', 'edit']);
            Route::post('products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggleStatus')->withTrashed();
            Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])->name('products.restore')->withTrashed();
            Route::delete('products/{product}/force-delete', [AdminProductController::class, 'forceDelete'])->name('products.forceDelete')->withTrashed();

            Route::resource('categories', AdminCategoryController::class)->except(['create', 'edit', 'show']);
            Route::post('categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');
            Route::resource('brands', BrandController::class)->except(['create', 'edit', 'show']);
            Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggleStatus');
            Route::get('vehicles', [VehicleManagementController::class, 'index'])->name('vehicle.index');
            Route::resource('vehicle-brands', VehicleBrandController::class)->except(['index', 'create', 'edit', 'show'])->names('vehicleBrands');
            Route::post('vehicle-brands/{vehicle_brand}/toggle-status', [VehicleBrandController::class, 'toggleStatus'])->name('vehicleBrands.toggleStatus');
            Route::resource('vehicle-models', VehicleModelController::class)->except(['index', 'create', 'edit', 'show'])->names('vehicleModels');
            Route::post('vehicle-models/{vehicle_model}/toggle-status', [VehicleModelController::class, 'toggleStatus'])->name('vehicleModels.toggleStatus');
            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        });

        // --- Module: Content Management ---
        Route::prefix('content')->name('content.')->group(function () {
            Route::resource('blogs', BlogController::class)->withTrashed();
            Route::post('blogs/{blog}/toggle-status', [BlogController::class, 'toggleStatus'])->name('blogs.toggleStatus')->withTrashed();
            Route::post('blogs/{blog}/restore', [BlogController::class, 'restore'])->name('blogs.restore')->withTrashed();
            Route::delete('blogs/{blog}/force-delete', [BlogController::class, 'forceDelete'])->name('blogs.forceDelete')->withTrashed();

            Route::controller(ReviewController::class)->prefix('reviews')->name('reviews.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::delete('/{review}', 'destroy')->name('destroy');
                Route::post('/{review}/update-status', 'updateStatus')->name('updateStatus');
            });
        });

        // --- Module: User Management ---
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

        // --- Module: System Configuration & Reports ---
        Route::prefix('system')->name('system.')->group(function () {
            Route::resource('delivery-services', DeliveryServiceController::class)->except(['create', 'edit', 'show'])->names('deliveryServices');
            Route::post('delivery-services/{delivery_service}/toggle-status', [DeliveryServiceController::class, 'toggleStatus'])->name('deliveryServices.toggleStatus');

            // Geography routes
            Route::prefix('geography')->name('geography.')->group(function () {
                Route::get('/', [GeographyController::class, 'index'])->name('index');
                Route::post('/import', [GeographyController::class, 'import'])->name('import');
                Route::resource('provinces', ProvinceController::class)->only(['store', 'update', 'destroy']);
                Route::resource('districts', DistrictController::class)->only(['store', 'update', 'destroy']);
                Route::resource('wards', WardController::class)->only(['store', 'update', 'destroy']);
            });

            Route::get('settings', fn() => view('admin.system.settings'))->name('settings');
        });

        // Reports
        Route::get('reports', [ReportsController::class, 'index'])->name('reports');
    });
});

// =========================================================================
// == API ROUTES ==
// =========================================================================
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('provinces.districts');

    // API lấy số thông báo chưa đọc
    Route::get('/notifications/unread-count', function () {
        // return response()->json([
        // 'count' => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->unreadNotifications()->count() : 0
        // ]);
    })->middleware('auth:admin')->name('notifications.unreadCount');
});
