<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Cấu hình này xác định guard và password broker mặc định.
    | 'web' được đặt làm guard mặc định cho trang khách hàng.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'customers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Định nghĩa các "cổng" xác thực cho ứng dụng.
    | - 'web' và 'customer' dùng cho Khách hàng.
    | - 'admin' dùng cho Quản trị viên.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Provider định nghĩa cách Laravel lấy dữ liệu người dùng từ database.
    | - 'customers' trỏ tới model Customer.
    | - 'admins' trỏ tới model Admin.
    |
    */

    'providers' => [
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho chức năng "Quên mật khẩu" cho từng loại người dùng.
    |
    */

    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Thời gian (giây) mà session xác nhận mật khẩu sẽ tồn tại.
    |
    */

    'password_timeout' => 10800,

];
