<?php

use Illuminate\Support\Facades\Route;
// Import the new Admin Login Controller
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController; // Alias to avoid naming conflict if you have other LoginControllers
// Controller cho Dashboard
use App\Http\Controllers\Admin\DashboardController;


// Controller cho Sales
use App\Http\Controllers\Admin\Sales\OrderController;
use App\Http\Controllers\Admin\Sales\PromotionController;

// Controller cho productManagement
use App\Http\Controllers\Admin\ProductManagement\CategoryController;
use App\Http\Controllers\Admin\ProductManagement\ProductController;
use App\Http\Controllers\Admin\ProductManagement\BrandController;
use App\Http\Controllers\Admin\ProductManagement\VehicleController;
use App\Http\Controllers\Admin\ProductManagement\InventoryController;
// Controller cho Content
use App\Http\Controllers\Admin\Content\PostController;
use App\Http\Controllers\Admin\Content\ReviewController;
// Controller cho Reports
use App\Http\Controllers\Admin\ReportsController;
// Controller cho User Management
use App\Http\Controllers\Admin\UserManagement\AdminController;


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
    return view('admin/login');
});

// MODIFIED: Admin Login Route - Now uses AdminLoginController
// The GET route displays the login form
Route::get('admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
// ADDED: The POST route handles the login attempt (form submission)
Route::post('admin/login', [AdminLoginController::class, 'login']);


// =========================================================================
// --- CÁC ROUTE CỦA TRANG ADMIN ---
// =========================================================================
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () { // ADDED: middleware('auth:admin') to protect admin routes

    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // ADDED: Admin Logout Route
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');


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

        // Route::get('categories', function () {
        //     return view('admin.productManagement.categories');
        // })->name('categories');
        Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);

        // Route::get('brands', function () {
        //     return view('admin.productManagement.brands');
        // })->name('brands');
        Route::resource('brands', BrandController::class)->except(['create', 'edit', 'show']);

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
// THÊM VÀO CUỐI FILE routes/web.php

Route::get('/api/notifications/unread-count', function () {
    // GHI CHÚ: Trong một ứng dụng thực tế, bạn sẽ truy vấn cơ sở dữ liệu ở đây.
    // Ví dụ: $count = App\Models\Notification::where('read_at', null)->count();

    // Để phục vụ hướng dẫn, chúng ta sẽ giả lập bằng một con số ngẫu nhiên.
    $simulatedCount = rand(1, 15);

    // Trả về dữ liệu dưới dạng JSON
    return response()->json(['count' => $simulatedCount]);
})->middleware('auth:admin'); // Quan trọng: Chỉ admin đã đăng nhập mới được truy cập