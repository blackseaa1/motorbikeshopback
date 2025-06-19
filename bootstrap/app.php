<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        /**
         * ===============================================================
         * == THAY ĐỔI QUAN TRỌNG: ÁP DỤNG MIDDLEWARE TOÀN CỤC CHO WEB ==
         * ===============================================================
         * Chèn middleware kiểm tra đổi mật khẩu vào cuối của nhóm 'web'.
         * Nó sẽ chạy trên mọi request web để chặn người dùng chưa đổi mật khẩu.
         */
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureCustomerPasswordIsChanged::class);

        /**
         * Cấu hình chuyển hướng cho người dùng chưa đăng nhập (guest).
         */
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return null;
            }
            return $request->routeIs('admin.*') ? route('admin.auth.login') : route('login');
        });

        /**
         * Đăng ký bí danh (alias) cho các middleware.
         * Vẫn giữ lại alias để có thể dùng trong tương lai nếu cần.
         */
        $middleware->alias([
            'admin.hasrole' => \App\Http\Middleware\CheckAdminHasRole::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            'customer.password.changed' => \App\Http\Middleware\EnsureCustomerPasswordIsChanged::class, // Giữ lại alias
            'guest.customer' => \App\Http\Middleware\RedirectIfCustomer::class,
            'guest.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Tùy chỉnh cách xử lý các ngoại lệ (exception).
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            $guard = data_get($e->guards(), 0);

            switch ($guard) {
                case 'admin':
                    return redirect()->guest(route('admin.auth.login'));
                default:
                    return redirect()->guest(route('login'));
            }
        });
    })->create();
