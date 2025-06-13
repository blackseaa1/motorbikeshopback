<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        // api: __DIR__ . '/../routes/api.php', // Thêm dòng này nếu bạn có file api.php
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Cấu hình chuyển hướng cho người dùng chưa đăng nhập (guest).
         * Khi middleware 'auth' hoặc 'auth:guard' thất bại, người dùng sẽ được đưa đến đây.
         */
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return null;
            }
            // Nếu request thuộc nhóm route 'admin', chuyển hướng đến trang đăng nhập admin.
            // Ngược lại, chuyển đến trang đăng nhập mặc định (của khách hàng).
            return $request->routeIs('admin.*') ? route('admin.auth.login') : route('login');
        });

        /**
         * Đăng ký bí danh (alias) cho các middleware.
         * Đây là trung tâm đăng ký tất cả các middleware tùy chỉnh của bạn.
         */
        $middleware->alias([
            // Các alias đã có của bạn
            'admin.hasrole' => \App\Http\Middleware\CheckAdminHasRole::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            // 'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class, // Thêm alias 'verified' nếu bạn cần

            // =================================================================
            // ALIAS CHO "BỨC TƯỜNG LỬA" GIỮA CUSTOMER VÀ ADMIN
            // =================================================================
            // Chặn Customer đã đăng nhập vào trang login/register của Admin
            'guest.customer' => \App\Http\Middleware\RedirectIfCustomer::class,

            // Chặn Admin đã đăng nhập vào trang login/register của Customer
            'guest.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Tùy chỉnh cách xử lý các ngoại lệ (exception).
         * Đoạn code này là một "chốt chặn cuối cùng" để xử lý lỗi xác thực.
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            // Lấy guard gây ra lỗi xác thực
            $guard = data_get($e->guards(), 0);

            // Chuyển hướng đến đúng trang login dựa trên guard
            switch ($guard) {
                case 'admin':
                    return redirect()->guest(route('admin.auth.login'));
                default:
                    return redirect()->guest(route('login'));
            }
        });
    })->create();
