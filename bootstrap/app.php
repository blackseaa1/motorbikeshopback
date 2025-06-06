<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // Make sure Request is imported
use Illuminate\Auth\AuthenticationException; // Make sure AuthenticationException is imported
// use Illuminate\Support\Facades\Log; // Uncomment if you need detailed logging

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cấu hình chuyển hướng cho khách (guest) chưa đăng nhập của guard 'admin'
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return null;
            }
            return route('admin.auth.login');
        });

        // Đăng ký bí danh (alias) cho middleware tùy chỉnh
        $middleware->alias([
            'admin.hasrole' => \App\Http\Middleware\CheckAdminHasRole::class,

            // =================================================================
            // THÊM DÒNG NÀY: Đăng ký middleware kiểm tra đổi mật khẩu
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
            // =================================================================
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý khi người dùng chưa được xác thực
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
