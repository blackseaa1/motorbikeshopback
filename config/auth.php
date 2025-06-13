<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Tùy chọn này xác định "guard" xác thực mặc định và "broker" đặt lại
    | mật khẩu cho ứng dụng của bạn. 'web' là lựa chọn mặc định
    | cho các ứng dụng web thông thường.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Tại đây, bạn có thể định nghĩa mọi guard xác thực cho ứng dụng.
    | Chúng ta sẽ định nghĩa 'web' cho Customer và 'admin' cho Admin.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users', // <<< 'web' guard sẽ sử dụng provider tên là 'users'
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins', // <<< 'admin' guard sẽ sử dụng provider tên là 'admins'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Tất cả các guard xác thực đều có một user provider. Provider định nghĩa
    | cách người dùng được lấy ra từ cơ sở dữ liệu.
    | Đây là nơi chúng ta giải quyết lỗi "User not found".
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class, // <<< Quan trọng: provider 'users' trỏ đến model Customer
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class, // <<< Quan trọng: provider 'admins' trỏ đến model Admin
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho chức năng "Quên mật khẩu". Chúng ta cũng định nghĩa
    | hai broker riêng biệt cho Customer và Admin.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens', // Có thể dùng chung bảng token
            'expire' => 15, // Thời gian hết hạn cho admin có thể ngắn hơn để tăng bảo mật
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Thời gian (giây) mà người dùng cần nhập lại mật khẩu để xác nhận
    | thực hiện một hành động nhạy cảm.
    |
    */

    'password_timeout' => 10800, // 3 giờ

];
