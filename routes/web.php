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
use App\Http\Controllers\Admin\System\PaymentMethodController;
use App\Http\Controllers\Admin\System\DeliveryServiceController;
use App\Http\Controllers\Admin\System\DistrictController;
use App\Http\Controllers\Admin\System\GeographyController;
use App\Http\Controllers\Admin\System\ProvinceController;
use App\Http\Controllers\Admin\System\WardController;
use App\Http\Controllers\Admin\UserManagement\CustomerAccountController;
use App\Http\Controllers\Admin\UserManagement\StaffAccountController;
use App\Http\Controllers\Api\GeographyApiController;

// --- CUSTOMER CONTROLLERS ---
use App\Http\Controllers\Customer\SearchController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\BlogController as CustomerPublicBlogController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\ShopController as CustomerShopController;
use App\Http\Controllers\PaymentController;

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
// == CUSTOMER FACING ROUTES (FRONT-END)
// =========================================================================
Route::middleware(['web'])->group(function () {
    // --- CÁC TRANG CÔNG KHAI & CHUNG ---
    Route::get('/search', [SearchController::class, 'index'])->name('global.search');
    Route::get('/search/autocomplete', [SearchController::class, 'autocompleteSearch'])->name('customer.search.autocomplete');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact.index');
    Route::post('/contact', [HomeController::class, 'submitContactForm'])->name('contact.submit');

    Route::controller(CustomerShopController::class)->group(function () {
        Route::get('/products', 'index')->name('products.index');
        Route::get('/products/{product}', 'show')->name('products.show');
        Route::get('/categories/{category}', 'index')->name('categories.show');
    });

    Route::controller(CustomerPublicBlogController::class)->prefix('blog')->name('blog.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{blogPost}', 'show')->name('show');
    });


    /*
|--------------------------------------------------------------------------
| Guest Order Routes
|--------------------------------------------------------------------------
*/
    // Tuyến đường cho khách vãng lai xem đơn hàng
    Route::match(['get', 'post'], '/guest/order-lookup', [CheckoutController::class, 'lookupGuestOrder'])->name('guest.order.lookup');
    Route::get('/guest/orders/{order}', [CheckoutController::class, 'showGuestOrder'])->name('guest.order.show');
    Route::post('/guest/orders/{order}/cancel', [CheckoutController::class, 'cancelOrder'])->name('guest.order.cancel');

    /*
|--------------------------------------------------------------------------
| Payment Gateway Routes
|--------------------------------------------------------------------------
*/
    // Các tuyến đường liên quan đến cổng thanh toán
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/momo/initiate/{order_id}', [PaymentController::class, 'initiateMomoPayment'])->name('momo.initiate');
        Route::get('/momo/callback', [PaymentController::class, 'handleMomoCallback'])->name('momo.callback');
        Route::post('/momo/ipn', [PaymentController::class, 'handleMomoIpn'])->name('momo.ipn');

        // THÊM ROUTES CHO VNPAY
        Route::get('/vnpay/initiate/{order_id}', [PaymentController::class, 'initiateVnpayPayment'])->name('vnpay.initiate');
        Route::get('/vnpay/callback', [PaymentController::class, 'handleVnpayCallback'])->name('vnpay.callback'); // GET method cho callback
        Route::post('/vnpay/ipn', [PaymentController::class, 'handleVnpayIpn'])->name('vnpay.ipn'); // POST method cho IPN

        Route::get('/bank-transfer/details/{order_id}', [PaymentController::class, 'showBankTransferDetails'])->name('bank_transfer.details');
    });


    // --- XÁC THỰC KHÁCH HÀNG (ĐĂNG NHẬP, ĐĂNG KÝ, ĐĂNG XUẤT) ---
    Route::controller(AuthController::class)->group(function () {
        Route::middleware('guest:customer')->group(function () {
            Route::get('login', 'showLoginForm')->name('login');
            Route::post('login', 'login');
            Route::get('register', 'showRegisterForm')->name('register');
            Route::post('register', 'register');
        });
        Route::post('logout', 'logout')->name('logout')->middleware('auth:customer');
    });

    // --- GIỎ HÀNG & THANH TOÁN ---
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');

    // --- API NỘI BỘ CHO GIỎ HÀNG (Sử dụng session, csrf...) ---
    Route::controller(CartController::class)->prefix('api/cart')->name('api.cart.')->group(function () {
        Route::get('/', 'getCartData')->name('data');
        Route::post('/add', 'add')->name('add');
        Route::post('/update', 'update')->name('update');
        Route::post('/remove', 'remove')->name('remove');
        Route::post('/remove-multiple', [CartController::class, 'removeMultiple']);
        Route::post('/update-summary', 'updateSummary')->name('updateSummary');
    });


    // --- CÁC ROUTE YÊU CẦU KHÁCH HÀNG ĐĂNG NHẬP ---
    // *** UPDATED: Thêm middleware 'check.user.status:customer'
    Route::middleware(['auth:customer', 'check.user.status:customer'])->group(function () {
        // --- Quản lý tài khoản ---
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/', [AccountController::class, 'showAccountInfo'])->name('profile');
            Route::post('/update-profile', [AccountController::class, 'updateInfo'])->name('updateProfile');
            Route::post('/update-password', [AccountController::class, 'updatePassword'])->name('updatePassword');
            Route::post('/update-avatar', [AccountController::class, 'updateAvatar'])->name('updateAvatar');

            // Đơn hàng
            Route::get('/orders', [AccountController::class, 'showOrdersIndex'])->name('orders.index'); // ĐÃ SỬA
            Route::get('/orders/{order}', [AccountController::class, 'showOrdersShow'])->name('orders.show');
            Route::post('/orders/{order}/cancel', [CheckoutController::class, 'cancelOrder'])->name('orders.cancel');

            // Sổ địa chỉ
            Route::resource('addresses', AddressController::class)->except(['show']);
            Route::post('addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.setDefault');
        });

        // --- Gửi đánh giá sản phẩm ---
        Route::post('/products/{product}/reviews', [CustomerReviewController::class, 'store'])->name('reviews.store');

        // --- Bắt buộc đổi mật khẩu ---
        Route::get('/force-password-change', [AuthController::class, 'showForcePasswordChangeForm'])->name('customer.password.force_change');
        Route::post('/force-password-change', [AuthController::class, 'handleForcePasswordChange'])->name('customer.password.handle_force_change');
    });
});


// =========================================================================
// == ADMIN ROUTES
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {

    // --- XÁC THỰC ADMIN ---
    // Route cho admin đã đăng nhập (logout, đổi mật khẩu bắt buộc)
    // *** UPDATED: Thêm middleware 'check.user.status:admin'
    Route::middleware(['auth:admin', 'check.user.status:admin'])->group(function () {
        Route::controller(AdminLoginController::class)->group(function () {
            Route::get('/force-password-change', 'showForcePasswordChangeForm')->name('auth.showForcePasswordChangeForm');
            Route::post('/force-password-change', 'forcePasswordChange')->name('auth.forcePasswordChange');
            Route::post('/logout', 'logout')->name('logout');
        });
        Route::get('/pending-authorization', [PendingAuthorizationController::class, 'show'])->name('pending_authorization');
    });

    // Route cho khách (chưa đăng nhập admin)
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

    // --- BẢNG ĐIỀU KHIỂN CHÍNH CỦA ADMIN ---
    // Yêu cầu: Đã đăng nhập, đã đổi mật khẩu lần đầu, có vai trò hợp lệ
    // *** UPDATED: Thêm middleware 'check.user.status:admin'
    Route::middleware(['auth:admin', 'check.user.status:admin', 'password.changed', 'admin.hasrole'])->group(function () {
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/revenue-data', [DashboardController::class, 'getRevenueChartData'])->name('dashboard.revenueData');

        // --- Module: Quản lý Nội dung (Content Management) ---
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
                // Đã sửa: Sử dụng customer_id và product_id làm tham số định tuyến
                Route::post('/{customer_id}/{product_id}/update-status', 'updateStatus')->name('updateStatus');
                // Đã sửa: Sử dụng customer_id và product_id làm tham số định tuyến
                Route::delete('/{customer_id}/{product_id}', 'destroy')->name('destroy');
            });
        });

        // --- Module: Quản lý Sản phẩm (Product Management) ---
        Route::prefix('product-management')->name('productManagement.')->group(function () {
            // Products
            Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
                // Các hành động hàng loạt (phải đặt trước route resource hoặc các route có tham số động)
                Route::post('bulk-destroy', 'bulkDestroy')->name('bulkDestroy'); // Xóa mềm hàng loạt
                Route::post('bulk-toggle-status', 'bulkToggleStatus')->name('bulkToggleStatus'); // Thay đổi trạng thái hàng loạt
                Route::post('bulk-restore', 'bulkRestore')->name('bulkRestore'); // Khôi phục hàng loạt
                Route::post('bulk-force-delete', 'bulkForceDelete')->name('bulkForceDelete'); // Xóa vĩnh viễn hàng loạt

                // Các route đơn lẻ hoặc API
                Route::get('/', 'index')->name('index'); // Danh sách sản phẩm (có thể kèm filter/search)
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/details', 'details')->name('details')->withTrashed(); // API cho modal xem/sửa, đổi tên thành details
                Route::put('/{product}', 'update')->name('update')->withTrashed(); // Cập nhật sản phẩm
                Route::delete('/{product}', 'destroy')->name('destroy')->withTrashed(); // Xóa mềm sản phẩm
                Route::post('/{product}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed(); // Bật/tắt trạng thái sản phẩm
                Route::post('/{product}/restore', 'restore')->name('restore')->withTrashed(); // Khôi phục sản phẩm
                Route::delete('/{product}/force-delete', 'forceDelete')->name('forceDelete')->withTrashed(); // Xóa vĩnh viễn sản phẩm
                Route::prefix('api')->name('api.')->group(function () {
                    // API để lấy thông tin chi tiết sản phẩm cho modal "Chi Tiết Sản Phẩm"
                    Route::get('products/{product}/details', [ProductController::class, 'details'])->name('products.details'); //

                    // API để cập nhật số lượng tồn kho sản phẩm
                    Route::put('products/{product}/update-stock', [ProductController::class, 'updateStockQuantity'])->name('products.update-stock'); //
                });
            });

            // Categories, Brands, Vehicle
            Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}', 'show')->name('show'); // For view/edit modal data fetching
                Route::put('/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
                Route::post('/{category}/toggle-status', 'toggleStatus')->name('toggleStatus');
                Route::post('/bulk-destroy', 'bulkDestroy')->name('bulkDestroy'); // NEW bulk destroy
                Route::post('/bulk-toggle-status', 'bulkToggleStatus')->name('bulkToggleStatus'); // NEW bulk toggle status
            });
            // Brands
            Route::controller(BrandController::class)->prefix('brands')->name('brands.')->group(function () {
                // **ĐẶT CÁC CUSTOM ROUTES (NON-RESOURCE-MODEL-BOUND) LÊN TRƯỚC**
                // Các route này không nhận tham số ID động từ URL segment
                Route::post('bulk-destroy', 'bulkDestroy')->name('bulkDestroy');
                Route::post('bulk-toggle-status', 'bulkToggleStatus')->name('bulkToggleStatus');

                // Standard CRUD routes
                Route::get('/', 'index')->name('index'); // brands.index
                Route::post('/', 'store')->name('store'); // brands.store

                // Các route có tham số model {brand}
                // (Laravel sẽ chỉ cố gắng match {brand} nếu các route trên không khớp)
                Route::get('{brand}', 'show')->name('show'); // brands.show
                Route::put('{brand}', 'update')->name('update'); // brands.update
                Route::delete('{brand}', 'destroy')->name('destroy'); // brands.destroy
                Route::post('{brand}/toggle-status', 'toggleStatus')->name('toggleStatus'); // brands.toggleStatus
            });
            // Vehicle Management
            Route::get('vehicles', [VehicleManagementController::class, 'index'])->name('vehicle.index');

            // Vehicle Brands (sub-module of vehicle management)
            Route::controller(VehicleBrandController::class)->prefix('vehicle-brands')->name('vehicleBrands.')->group(function () {
                // Bulk actions MUST come before singular resource routes
                Route::post('bulk-destroy', 'bulkDestroy')->name('bulkDestroy');
                Route::post('bulk-toggle-status', 'bulkToggleStatus')->name('bulkToggleStatus');
                // Standard CRUD routes (excluding index as it's part of VehicleManagementController@index)
                Route::post('/', 'store')->name('store');
                Route::get('{vehicleBrand}', 'show')->name('show'); // For view/edit modal data fetching
                Route::put('{vehicleBrand}', 'update')->name('update');
                Route::delete('{vehicleBrand}', 'destroy')->name('destroy');
                Route::post('{vehicleBrand}/toggle-status', 'toggleStatus')->name('toggleStatus');
            });

            // Vehicle Models (sub-module of vehicle management)
            Route::controller(VehicleModelController::class)->prefix('vehicle-models')->name('vehicleModels.')->group(function () {
                // Bulk actions MUST come before singular resource routes
                Route::post('bulk-destroy', 'bulkDestroy')->name('bulkDestroy');
                Route::post('bulk-toggle-status', 'bulkToggleStatus')->name('bulkToggleStatus');
                // Standard CRUD routes (excluding index as it's part of VehicleManagementController@index)
                Route::post('/', 'store')->name('store');
                Route::get('{vehicleModel}', 'show')->name('show'); // For view/edit modal data fetching
                Route::put('{vehicleModel}', 'update')->name('update');
                Route::delete('{vehicleModel}', 'destroy')->name('destroy');
                Route::post('{vehicleModel}/toggle-status', 'toggleStatus')->name('toggleStatus');
            });
            Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');

            // API routes for Inventory page
            Route::prefix('api/products')->name('api.products.')->group(function () {
                Route::get('/{product}/details', [ProductController::class, 'details'])->name('details'); // Renamed from getProductDetailsApi
                Route::put('/{product}/update-stock', [ProductController::class, 'updateStockQuantity'])->name('updateStock');
            });
        });

        // --- Module: Quản lý Hồ sơ cá nhân (Profile Management) ---
        Route::controller(AdminProfileController::class)->prefix('profile')->name('profile.')->group(function () {
            Route::get('/', 'showProfileForm')->name('show');
            Route::post('/update-info', 'updateInfo')->name('updateInfo');
            Route::post('/change-password', 'changePassword')->name('changePassword');
            Route::post('/update-avatar', 'updateAvatar')->name('updateAvatar');
        });

        Route::get('reports', [ReportsController::class, 'index'])->name('reports');

        // Các route API cho báo cáo
        Route::prefix('reports/api')->name('reports.api.')->group(function () {
            Route::get('/daily-revenue', [ReportsController::class, 'getDailyRevenue'])->name('dailyRevenue');
            Route::get('/monthly-revenue', [ReportsController::class, 'getMonthlyRevenue'])->name('monthlyRevenue');
            Route::get('/low-stock-products', [ReportsController::class, 'getLowStockProducts'])->name('lowStockProducts');
            Route::get('/best-selling-products', [ReportsController::class, 'getBestSellingProducts'])->name('bestSellingProducts');
            Route::get('/product-details/{id}', [ReportsController::class, 'getProductDetails'])->name('product_details');
            Route::get('/orders-by-date', [ReportsController::class, 'getOrdersByDate'])->name('ordersByDate');
            Route::get('/orders-by-month', [ReportsController::class, 'getOrdersByMonth'])->name('ordersByMonth');

            // NEW: Route to get top selling product for a period
            Route::get('/top-selling-product-for-period', [ReportsController::class, 'getTopSellingProductForPeriod'])->name('topSellingProductForPeriod');
        });
        // Sales Management
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::post('orders/calculate-summary', [OrderController::class, 'calculateOrderSummaryApi'])->name('orders.calculate-summary.api'); // <--- Đảm bảo route này nằm ở đây
            // == ROUTES CHO ĐƠN HÀNG (ORDERS) ==
            // Các route tùy chỉnh cho OrderController (ví dụ: AJAX để lấy dữ liệu)
            // Cần đặt các route này TRƯỚC Route::resource nếu có khả năng trùng lặp URL
            Route::resource('orders', OrderController::class)->except(['create', 'edit']);

            Route::get('orders/get-districts', [OrderController::class, 'getDistricts'])->name('orders.getDistricts');
            Route::get('orders/get-wards', [OrderController::class, 'getWards'])->name('orders.getWards');
            Route::get('orders/get-product-details', [OrderController::class, 'getProductDetails'])->name('orders.getProductDetails');
            Route::get('orders/get-customer-addresses', [OrderController::class, 'getCustomerAddresses'])->name('orders.getCustomerAddresses');
            // THIS IS THE ROUTE TO MOVE - place it here or similar logical spot


            // == ROUTES CHO KHUYẾN MÃI (PROMOTIONS) ==
            // == ROUTES CHO KHUYẾN MÃI (PROMOTIONS) ==
            Route::resource('promotions', PromotionController::class)->except(['create', 'edit']);
            Route::post('promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggleStatus');

            Route::post('promotions/bulk-destroy', [PromotionController::class, 'bulkDestroy'])->name('promotions.bulkDestroy');
            Route::post('promotions/bulk-toggle-status', [PromotionController::class, 'bulkToggleStatus'])->name('promotions.bulkToggleStatus');
        });

        // --- Module: Cấu hình Hệ thống (System Configuration) ---
        Route::prefix('system')->name('system.')->group(function () {
            Route::post('payment-methods/{paymentMethod}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('paymentMethods.toggleStatus');
            Route::resource('payment-methods', PaymentMethodController::class)->names('paymentMethods')->except(['show']);
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

        // --- Module: Quản lý Người dùng (User Management) ---
        Route::prefix('user-management')->name('userManagement.')->group(function () {
            // Staff Accounts
            Route::resource('staff', StaffAccountController::class);
            Route::controller(StaffAccountController::class)->prefix('staff')->name('staff.')->group(function () {
                Route::post('/{staff}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed();
                Route::post('/{staff}/reset-password', 'resetPassword')->name('resetPassword')->withTrashed();
            });
            // Customer Accounts
            Route::resource('customers', CustomerAccountController::class)->except(['create', 'edit', 'show']);
            Route::controller(CustomerAccountController::class)->prefix('customers')->name('customers.')->group(function () {
                Route::post('/{customer}/toggle-status', 'toggleStatus')->name('toggleStatus')->withTrashed();
                Route::post('/{customer}/reset-password', 'resetPassword')->name('resetPassword')->withTrashed();
                Route::post('/{customer}/restore', 'restore')->name('restore')->withTrashed();
                Route::delete('/{customer}/force-delete', 'forceDelete')->name('forceDelete')->withTrashed();
                Route::get('/{customer}/addresses-api', 'getAddressesApi')->name('addresses.api');
            });
        });
    });
    // --- BẮT ĐẦU: THÊM CÁC API NỘI BỘ CHO ADMIN ---
    // Các API này có thể được truy cập từ bất kỳ đâu trong Admin Panel
    Route::prefix('api')->name('api.')->group(function () {
        // API tìm kiếm sản phẩm để thêm vào đơn hàng
        Route::get('/products', [ProductController::class, 'searchProductsApi'])->name('products.search.api');
        // API lấy danh sách địa chỉ của một khách hàng
        Route::get('/customers/{customer}/addresses', [CustomerAccountController::class, 'getAddressesApi'])->name('customers.addresses.api');
        // Đảm bảo dòng này đã tồn tại và sử dụng 'post'

        // Note: The product details and update stock APIs are now under 'admin.productManagement.api.products.'
        // So, the routes below are redundant if they are also defined under 'admin.productManagement.api.products.'
        // If you intend for them to be globally accessible under 'admin.api.', keep them here.
        // Otherwise, remove them from here to avoid route conflicts or ambiguity.
        // Route::get('/products/{product}/details', [ProductController::class, 'getProductDetailsApi'])->name('products.details.api');
        // Route::put('/products/{product}/update-stock', [ProductController::class, 'updateStockQuantity'])->name('products.updateStockQuantity');
    });
    // --- KẾT THÚC: THÊM CÁC API NỘI BỘ CHO ADMIN ---
});


// =========================================================================
// == GENERAL API ROUTES
// =========================================================================
Route::prefix('api')->name('api.')->group(function () {
    // --- GEOGRAPHY API ---
    Route::get('/provinces/{province}/districts', [GeographyApiController::class, 'getDistrictsByProvince'])->name('provinces.districts');
    Route::get('/districts/{district}/wards', [GeographyApiController::class, 'getWardsByDistrict'])->name('districts.wards');
    Route::get('/products/all-for-order', [OrderController::class, 'getProductsForOrderCreation'])->name('api.orders.products-for-creation')->middleware('auth:admin');
    // Note: The product details and update stock APIs are now under 'admin.productManagement.api.products.'
    // So, the routes below are redundant if they are also defined under 'admin.productManagement.api.products.'
    // If you intend for them to be globally accessible under 'api.', keep them here.
    // Otherwise, remove them from here to avoid route conflicts or ambiguity.
    // Route::get('/products/{product}/details', [ProductController::class, 'getProductDetailsApi'])->name('products.details.api');
    // Route::put('/products/{product}/update-stock', [ProductController::class, 'updateStockQuantity'])->name('products.updateStockQuantity');
    // --- CUSTOMER API ---
    Route::prefix('customer')->name('customer.')->group(function () {
        // API để lấy danh sách sản phẩm (có lọc, phân trang) cho front-end
        Route::get('/products', [CustomerShopController::class, 'getProductsApi'])->name('products.index');
    });
});

use App\Mail\OrderConfirmation;
use App\Models\Order;

// Route::get('/test-email', function () {
//     // Lấy một đơn hàng bất kỳ để test, hoặc thay 94 bằng ID đơn hàng bạn muốn test
//     $order = Order::find(94);

//     if (!$order) {
//         return "Không tìm thấy đơn hàng với ID 94.";
//     }

//     // Tải các mối quan hệ cần thiết giống như trong Controller
//     $order->load(['customer', 'province', 'district', 'ward', 'items.product', 'paymentMethod']);

//     // **DÒNG QUAN TRỌNG NHẤT**
//     // Render nội dung của email và hiển thị nó ra màn hình
//     return $mailable->render();
// });