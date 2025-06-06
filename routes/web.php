<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import Controllers
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\Auth\PendingAuthorizationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Profile\AdminProfileController;
use App\Http\Controllers\Admin\Sales\OrderController;
use App\Http\Controllers\Admin\Sales\PromotionController;
use App\Http\Controllers\Admin\ProductManagement\CategoryController;
use App\Http\Controllers\Admin\ProductManagement\ProductController;
use App\Http\Controllers\Admin\ProductManagement\BrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleModelController;
use App\Http\Controllers\Admin\ProductManagement\VehicleBrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleManagementController;
use App\Http\Controllers\Admin\ProductManagement\InventoryController;
use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Admin\Content\ReviewController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\UserManagement\StaffAccountController;
use App\Http\Controllers\Admin\UserManagement\CustomerAccountController;
use App\Http\Controllers\Admin\System\GeographyController;
use App\Http\Controllers\Admin\System\ProvinceController;
use App\Http\Controllers\Admin\System\DistrictController;
use App\Http\Controllers\Admin\System\WardController;
use App\Http\Controllers\Admin\System\DeliveryServiceController;
use App\Http\Controllers\Api\GeographyApiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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

Route::prefix('admin')->name('admin.')->group(function () {
    // Routes for forcing password change (nằm ngoài nhóm bảo vệ chính)
    Route::get('/force-password-change', [AdminLoginController::class, 'showForcePasswordChangeForm'])->middleware('auth:admin')->name('auth.showForcePasswordChangeForm');
    Route::post('/force-password-change', [AdminLoginController::class, 'forcePasswordChange'])->middleware('auth:admin')->name('auth.forcePasswordChange');

    // Routes for guests (unauthenticated)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('auth.login');
        Route::post('login', [AdminLoginController::class, 'login']);
        Route::get('register', [AdminRegisterController::class, 'showRegistrationForm'])->name('auth.register');
        Route::post('register', [AdminRegisterController::class, 'register']);
    });

    Route::middleware(['auth:admin', 'password.changed'])->group(function () {
        Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])->name('pending_authorization');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

        // Routes requiring authentication and role (được kế thừa bảo vệ từ nhóm cha)
        Route::middleware(['admin.hasrole'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // --- Sales Management ---
            Route::prefix('sales')->name('sales.')->group(function () {
                Route::get('orders', [OrderController::class, 'index'])->name('orders');
                Route::resource('promotions', PromotionController::class)->except(['create', 'show', 'edit']);
                Route::post('promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggleStatus');
            });

            // --- Product Management ---
            Route::prefix('productManagement')->name('productManagement.')->group(function () {
                Route::get('products', [ProductController::class, 'index'])->name('products');
                Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
                Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');
                Route::resource('brands', BrandController::class)->except(['create', 'edit', 'show'])->names('brands');
                Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggleStatus');
                Route::get('vehicle-management', [VehicleManagementController::class, 'index'])->name('vehicle.index');
                Route::post('vehicle-brands/store', [VehicleBrandController::class, 'store'])->name('vehicleBrands.store');
                Route::put('vehicle-brands/{vehicleBrand}', [VehicleBrandController::class, 'update'])->name('vehicleBrands.update');
                Route::delete('vehicle-brands/{vehicleBrand}', [VehicleBrandController::class, 'destroy'])->name('vehicleBrands.destroy');
                Route::post('vehicle-brands/{vehicleBrand}/toggle-status', [VehicleBrandController::class, 'toggleStatus'])->name('vehicleBrands.toggleStatus');
                Route::post('vehicle-models/store', [VehicleModelController::class, 'store'])->name('vehicleModels.store');
                Route::put('vehicle-models/{vehicleModel}', [VehicleModelController::class, 'update'])->name('vehicleModels.update');
                Route::delete('vehicle-models/{vehicleModel}', [VehicleModelController::class, 'destroy'])->name('vehicleModels.destroy');
                Route::post('vehicle-models/{vehicleModel}/toggle-status', [VehicleModelController::class, 'toggleStatus'])->name('vehicleModels.toggleStatus');
                Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
            });

            // --- Content Management ---
            Route::prefix('content')->name('content.')->group(function () {
                Route::get('posts', [PostController::class, 'index'])->name('posts');
                Route::get('reviews', [ReviewController::class, 'index'])->name('reviews');
            });

            // --- Reports & Statistics ---
            Route::get('reports', [ReportsController::class, 'index'])->name('reports');

            // --- User Management ---
            Route::prefix('userManagement')->name('userManagement.')->group(function () {
                // Staff Routes (giữ nguyên)
                Route::resource('staff', StaffAccountController::class);
                Route::post('staff/{staff}/toggle-status', [StaffAccountController::class, 'toggleStatus'])->name('staff.toggleStatus');
                Route::post('staff/{staff}/reset-password', [StaffAccountController::class, 'resetPassword'])->name('staff.resetPassword');

                // === CUSTOMER ROUTES ĐÃ ĐƯỢC SỬA ĐÚNG ===
                Route::get('customers', [CustomerAccountController::class, 'index'])->name('customers.index');
                Route::post('customers', [CustomerAccountController::class, 'store'])->name('customers.store');

                // Đổi {id} thành {customer} để Route Model Binding hoạt động
                Route::put('customers/{customer}', [CustomerAccountController::class, 'update'])->name('customers.update');
                Route::delete('customers/{customer}', [CustomerAccountController::class, 'destroy'])->name('customers.destroy');
                Route::post('customers/{customer}/toggle-status', [CustomerAccountController::class, 'toggleStatus'])->name('customers.toggleStatus');
                Route::post('customers/{customer}/reset-password', [CustomerAccountController::class, 'resetPassword'])->name('customers.resetPassword');

                // Thêm withTrashed() để binding hoạt động với các model trong thùng rác
                Route::post('customers/{customer}/restore', [CustomerAccountController::class, 'restore'])->name('customers.restore')->withTrashed();
                Route::delete('customers/{customer}/force-delete', [CustomerAccountController::class, 'forceDelete'])->name('customers.forceDelete')->withTrashed();
            });

            // --- System Configuration ---
            Route::prefix('system')->name('system.')->group(function () {
                Route::get('delivery-services', [DeliveryServiceController::class, 'index'])->name('deliveryServices.index');
                Route::post('delivery-services/store', [DeliveryServiceController::class, 'store'])->name('deliveryServices.store');
                Route::put('delivery-services/{deliveryService}', [DeliveryServiceController::class, 'update'])->name('deliveryServices.update');
                Route::delete('delivery-services/{deliveryService}', [DeliveryServiceController::class, 'destroy'])->name('deliveryServices.destroy');
                Route::post('delivery-services/{deliveryService}/toggle-status', [DeliveryServiceController::class, 'toggleStatus'])->name('deliveryServices.toggleStatus');
                Route::prefix('geography')->name('geography.')->group(function () {
                    Route::get('/', [GeographyController::class, 'index'])->name('index');
                    Route::post('/import', [GeographyController::class, 'import'])->name('import');
                    Route::resource('provinces', ProvinceController::class)->except(['create', 'edit', 'show']);
                    Route::resource('districts', DistrictController::class)->except(['create', 'edit', 'show']);
                    Route::resource('wards', WardController::class)->except(['create', 'edit', 'show']);
                });
                Route::get('settings', function () {
                    return view('admin.system.settings');
                })->name('settings');
            });

            // --- Admin Profile ---
            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('/', [AdminProfileController::class, 'showProfileForm'])->name('show');
                Route::post('/update-info', [AdminProfileController::class, 'updateInfo'])->name('updateInfo');
                Route::post('/change-password', [AdminProfileController::class, 'changePassword'])->name('changePassword');
                Route::post('/update-avatar', [AdminProfileController::class, 'updateAvatar'])->name('updateAvatar');
            });
        });
    });
});

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('provinces.districts');
    Route::get('/notifications/unread-count', function () {
        $simulatedCount = rand(0, 5);
        return response()->json(['count' => $simulatedCount]);
    })->middleware('auth:admin')->name('notifications.unreadCount');
});
