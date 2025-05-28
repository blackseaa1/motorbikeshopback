<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // THÊM DÒNG NÀY VÀO ĐÂY
        // Chỉ cho Laravel biết nơi chuyển hướng khách chưa đăng nhập
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        // Bạn có thể có các cấu hình middleware khác ở đây
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Đoạn code xử lý AuthenticationException này vẫn hữu ích,
        // đặc biệt nếu bạn muốn xử lý các guard khác nhau hoặc JSON response.
        // Nó sẽ được ưu tiên nếu được gọi.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            Log::info("HANDLER_REACHED: AuthenticationException for guard: " . (data_get($e->guards(), 0) ?: 'UNKNOWN_GUARD'));

            if ($request->expectsJson()) {
                Log::info("HANDLER_JSON_RESPONSE: Sending JSON unauthenticated response.");
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $guard = data_get($e->guards(), 0);
            Log::info("HANDLER_GUARD_CHECK: Guard is '{$guard}'");

            // Logic này vẫn đúng để xử lý cụ thể cho guard 'admin'
            if ($guard === 'admin') {
                Log::info("HANDLER_REDIRECTING: Redirecting to admin.login for admin guard.");
                return redirect()->guest(route('admin.login'));
            }

            // Trường hợp này sẽ ít khi xảy ra nếu redirectGuestsTo đã được cấu hình
            Log::info("HANDLER_REDIRECTING: Redirecting to admin.login for other/unknown guard (fallback).");
            return redirect()->guest(route('admin.login'));
        });

        // Bỏ comment hoặc xóa phần log cho OTHER_EXCEPTION nếu bạn muốn,
        // vì bây giờ chúng ta đã xử lý RouteNotFoundException cho 'login' rồi.
        // $exceptions->render(function (Throwable $e, Request $request) {
        //     if (!($e instanceof AuthenticationException)) {
        //          Log::error("HANDLER_OTHER_EXCEPTION: Type: " . get_class($e) . " Message: " . $e->getMessage(), ['exception' => $e]);
        //     }
        // });
    })->create();