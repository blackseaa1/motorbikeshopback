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
    // --- AUTH LOGIC ---
    Route::middleware('auth:admin')->group(function () {
        Route::get('/force-password-change', [AdminLoginController::class, 'showForcePasswordChangeForm'])->name('auth.showForcePasswordChangeForm');
        Route::post('/force-password-change', [AdminLoginController::class, 'forcePasswordChange'])->name('auth.forcePasswordChange');
        Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])->name('pending_authorization');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    });

    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('auth.login');
        Route::post('login', [AdminLoginController::class, 'login'])->name('auth.login.perform');
        Route::get('register', [AdminRegisterController::class, 'showRegistrationForm'])->name('auth.register');
        Route::post('register', [AdminRegisterController::class, 'register'])->name('auth.register.perform');
    });

    // --- MAIN ADMIN PANEL ---
    Route::middleware(['auth:admin', 'password.changed', 'admin.hasrole'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- Sales Management ---
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

            Route::resource('promotions', PromotionController::class)->except(['create', 'show', 'edit'])->names('promotions');
            Route::post('promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggleStatus');
        });

        // --- Product Management ---
        Route::prefix('product-management')->name('productManagement.')->group(function () {





            Route::resource('products', ProductController::class)->except(['create', 'edit']);

            Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show'])->names('categories');
            Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');

            Route::resource('brands', BrandController::class)->except(['create', 'edit', 'show'])->names('brands');
            Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggleStatus');

            Route::get('vehicle-management', [VehicleManagementController::class, 'index'])->name('vehicle.index');
            Route::resource('vehicle-brands', VehicleBrandController::class)->except(['index', 'create', 'edit', 'show'])->names('vehicleBrands');
            Route::post('vehicle-brands/{vehicle_brand}/toggle-status', [VehicleBrandController::class, 'toggleStatus'])->name('vehicleBrands.toggleStatus');
            Route::resource('vehicle-models', VehicleModelController::class)->except(['index', 'create', 'edit', 'show'])->names('vehicleModels');
            Route::post('vehicle-models/{vehicle_model}/toggle-status', [VehicleModelController::class, 'toggleStatus'])->name('vehicleModels.toggleStatus');

            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        });

        // --- Content Management ---
        Route::prefix('content')->name('content.')->group(function () {
            Route::resource('posts', PostController::class)->except(['show']);
            Route::resource('reviews', ReviewController::class)->only(['index', 'destroy']);
            Route::post('reviews/{review}/update-status', [ReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
        });

        // --- Reports & Statistics ---
        Route::get('reports', [ReportsController::class, 'index'])->name('reports');

        // --- User Management ---
        Route::prefix('user-management')->name('userManagement.')->group(function () {
            Route::resource('staff', StaffAccountController::class);
            Route::post('staff/{staff}/toggle-status', [StaffAccountController::class, 'toggleStatus'])->name('staff.toggleStatus');
            Route::post('staff/{staff}/reset-password', [StaffAccountController::class, 'resetPassword'])->name('staff.resetPassword');

            Route::resource('customers', CustomerAccountController::class)->except(['create', 'edit', 'show'])->names('customers');
            Route::post('customers/{customer}/toggle-status', [CustomerAccountController::class, 'toggleStatus'])->name('customers.toggleStatus');
            Route::post('customers/{customer}/reset-password', [CustomerAccountController::class, 'resetPassword'])->name('customers.resetPassword');
            Route::post('customers/{customer}/restore', [CustomerAccountController::class, 'restore'])->name('customers.restore')->withTrashed();
            Route::delete('customers/{customer}/force-delete', [CustomerAccountController::class, 'forceDelete'])->name('customers.forceDelete')->withTrashed();
        });

        Route::prefix('system')->name('system.')->group(function () {
            /**
             * SỬA ĐỔI: Thêm ->names('deliveryServices') để tên route được tạo ra là
             * 'admin.system.deliveryServices.index' (camelCase) thay vì
             * 'admin.system.delivery-services.index' (kebab-case).
             * Điều này sẽ khắc phục lỗi "Route not defined" của bạn.
             */
            Route::resource('delivery-services', DeliveryServiceController::class)
                ->except(['create', 'edit', 'show'])
                ->names('deliveryServices'); // <--- SỬA ĐỔI QUAN TRỌNG

            // Đặt tên cho route toggle-status cho nhất quán
            Route::post('delivery-services/{delivery_service}/toggle-status', [DeliveryServiceController::class, 'toggleStatus'])
                ->name('deliveryServices.toggleStatus');

            Route::prefix('geography')->name('geography.')->group(function () {
                Route::get('/', [GeographyController::class, 'index'])->name('index');
                Route::post('/import', [GeographyController::class, 'import'])->name('import');
                Route::resource('provinces', ProvinceController::class)->only(['store', 'update', 'destroy']);
                Route::resource('districts', DistrictController::class)->only(['store', 'update', 'destroy']);
                Route::resource('wards', WardController::class)->only(['store', 'update', 'destroy']);
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

// --- API Routes ---
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('provinces.districts');
    Route::get('/notifications/unread-count', function () {
        return response()->json(['count' => rand(0, 5)]);
    })->middleware('auth:admin')->name('notifications.unreadCount');
});
