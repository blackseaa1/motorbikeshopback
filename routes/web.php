<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controller Imports
| Sắp xếp theo thứ tự alphabet để dễ quản lý.
|--------------------------------------------------------------------------
*/

// --- ADMIN CONTROLLERS ---
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\PendingAuthorizationController;
use App\Http\Controllers\Admin\Auth\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\Content\BlogController;
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

// --- CUSTOMER CONTROLLERS ---
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ShopController as CustomerShopController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\BlogController as CustomerPublicBlogController;

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

// =========================================================================
// == CUSTOMER FACING ROUTES (FRONT-END) ==
// =========================================================================

// --- Các route công khai ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// ... blog, contact, products ...
Route::get('/products', [CustomerShopController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [CustomerShopController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [CustomerShopController::class, 'index'])->name('categories.show');

// --- Trang Blog công khai ---
Route::get('/blog', [CustomerPublicBlogController::class, 'index'])->name('blog');
Route::get('/blog/{blogPost}', [CustomerPublicBlogController::class, 'show'])->name('blog.show');

// --- Trang Liên hệ ---
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// --- Các route xác thực (Đăng nhập, Đăng ký) ---
Route::controller(AuthController::class)->group(function () {
    Route::middleware(['guest:customer', 'guest:admin'])->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login');
        Route::get('register', 'showRegisterForm')->name('register');
        Route::post('register', 'register');
    });
});

/**
 * ===============================================================
 * == CÁC ROUTE CHỈ DÀNH CHO KHÁCH HÀNG ĐÃ ĐĂNG NHẬP ==
 * ===============================================================
 * Middleware `EnsureCustomerPasswordIsChanged` đã được đăng ký toàn cục
 * nên nó sẽ tự động kiểm tra. Ở đây chúng ta chỉ cần middleware `auth:customer`.
 */
Route::middleware(['auth:customer'])->group(function () {

    // Route trang tài khoản
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'profile'])->name('profile');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::patch('/update-profile', [AccountController::class, 'updateProfile'])->name('updateProfile');
        Route::put('/update-password', [AccountController::class, 'updatePassword'])->name('updatePassword');
        Route::post('/update-avatar', [AccountController::class, 'updateAvatar'])->name('updateAvatar');
    });

    // Route gửi đánh giá
    Route::post('/products/{product}/reviews', [CustomerReviewController::class, 'store'])->name('reviews.store');

    // Các route bắt buộc đổi mật khẩu và đăng xuất
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/force-password-change', [AuthController::class, 'showForcePasswordChangeForm'])->name('customer.password.force_change');
    Route::post('/force-password-change', [AuthController::class, 'handleForcePasswordChange'])->name('customer.password.handle_force_change');
});

// =========================================================================
// == ADMIN ROUTES ==
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {

    // --- Admin Authentication ---
    Route::middleware('auth:admin')->group(function () {
        Route::controller(AdminLoginController::class)->group(function () {
            Route::get('/force-password-change', 'showForcePasswordChangeForm')->name('auth.showForcePasswordChangeForm');
            Route::post('/force-password-change', 'forcePasswordChange')->name('auth.forcePasswordChange');
            Route::post('/logout', 'logout')->name('logout');
        });
        Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])->name('pending_authorization');
    });

    // Áp dụng middleware kép để chặn Customer đã đăng nhập
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
            // Products
            Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}', 'show')->name('show')->withTrashed();
                Route::post('/{product}', 'update')->name('update')->withTrashed();
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

        // --- Module: Content Management ---
        Route::prefix('content')->name('content.')->group(function () {
            // Blogs
            Route::controller(BlogController::class)->prefix('blogs')->name('blogs.')->group(function () {
                Route::get('/', 'index')->name('index')->middleware('can:viewAny,App\Models\BlogPost');
                Route::post('/', 'store')->name('store')->middleware('can:create,App\Models\BlogPost');
                Route::get('/{blog}', 'show')->name('show')->withTrashed()->middleware('can:view,blog');
                Route::post('/{blog}', 'update')->name('update')->withTrashed()->middleware('can:update,blog');
                Route::delete('/{blog}', 'destroy')->name('destroy')->middleware('can:delete,blog');
                Route::post('/{blog}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed()->middleware('can:toggleStatus,blog');
                Route::post('/{blog}/restore', 'restore')->name('restore')->withTrashed()->middleware('can:restore,blog');
                Route::delete('/{blog}/force-delete', 'forceDelete')->name('forceDelete')->withTrashed()->middleware('can:forceDelete,blog');
            });

            // Reviews
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
    // --- CUSTOMER API ---
    Route::prefix('customer')->name('customer.')->group(function () {
        // API để lấy danh sách sản phẩm (có lọc, phân trang)
        Route::get('/products', [CustomerShopController::class, 'getProductsApi'])->name('products.index');

        // API cho giỏ hàng
        Route::controller(CustomerCartController::class)->prefix('cart')->name('cart.')->group(function () {
            Route::post('/add', 'add')->name('add');
            Route::get('/info', 'getCartInfo')->name('info');
            // Thêm các route khác: update, remove, clear...
        });
    });
});
