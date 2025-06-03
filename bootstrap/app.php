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
        // Đảm bảo route 'admin.auth.login' đã được định nghĩa trong web.php
        $middleware->redirectGuestsTo(function (Request $request) {
            // Kiểm tra xem request có mong muốn JSON không, nếu có thì không redirect
            if ($request->expectsJson()) {
                return null;
            }
            // Kiểm tra xem có phải là request cho guard 'admin' không (nếu cần thiết)
            // Đối với nhiều guards, bạn có thể cần logic phức tạp hơn ở đây
            // hoặc xử lý trong AuthenticationException handler.
            // Tuy nhiên, với redirectGuestsTo, nó thường áp dụng cho guard mặc định
            // hoặc guard được chỉ định khi middleware 'auth' được gọi mà không có guard cụ thể.
            // Nếu bạn chỉ dùng guard 'admin' cho phần admin, thì đây là đủ.
            return route('admin.auth.login');
        });

        // Đăng ký bí danh (alias) cho middleware tùy chỉnh
        $middleware->alias([
            'admin.hasrole' => \App\Http\Middleware\CheckAdminHasRole::class,
            // Thêm các bí danh middleware khác của bạn ở đây nếu cần
            // ví dụ: 'isAdmin' => \App\Http\Middleware\IsAdminMiddleware::class,
        ]);

        // Bạn có thể thêm các cấu hình middleware toàn cục hoặc nhóm ở đây nếu cần
        // Ví dụ:
        // $middleware->append(\App\Http\Middleware\LogResponseTime::class);
        // $middleware->prepend(\App\Http\Middleware\SetLocale::class);

        // Cấu hình cho CSRF token, thường được xử lý tự động bởi Laravel
        // nhưng bạn có thể tùy chỉnh 'except' ở đây nếu cần
        // $middleware->validateCsrfTokens(except: [
        //     'stripe/*',
        //     'http://example.com/foo/bar',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý khi người dùng chưa được xác thực
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            // Xác định guard gây ra lỗi xác thực
            $guard = data_get($e->guards(), 0);

            // Chuyển hướng dựa trên guard
            switch ($guard) {
                case 'admin':
                    return redirect()->guest(route('admin.auth.login'));
                    // Thêm các case khác cho các guard khác nếu có
                    // case 'web':
                    //     return redirect()->guest(route('login'));
                default:
                    // Fallback nếu không xác định được guard cụ thể hoặc cho guard mặc định
                    return redirect()->guest(route('login')); // Hoặc một route login chung
            }
        });

        // Bạn có thể thêm các trình xử lý ngoại lệ tùy chỉnh khác ở đây
        // Ví dụ:
        // $exceptions->report(function (MyCustomException $e) {
        //     // Gửi thông báo lỗi đến một dịch vụ bên ngoài
        // });
    })->create();
