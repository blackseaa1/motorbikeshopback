<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xem người dùng có đang đăng nhập với guard 'web' (tức là Customer) không.
        if (Auth::guard('web')->check()) {
            // Nếu có, chuyển hướng họ về trang chủ của khách hàng
            // kèm theo một thông báo cảnh báo.
            return redirect()->route('home')->with('warning', 'Bạn đăng nhập dưới dạng khách hàng không thể truy cập nội dung này');
        }

        // Nếu không, cho phép họ đi tiếp.
        return $next($request);
    }
}
