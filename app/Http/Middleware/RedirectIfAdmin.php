<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xem người dùng có đang đăng nhập với guard 'admin' không.
        if (Auth::guard('admin')->check()) {
            // Nếu có, chuyển hướng họ về trang dashboard của admin
            // kèm theo một thông báo cảnh báo.
            return redirect()->route('home')->with('warning', 'Bạn không thể truy cập mục này vì, bạn đang đăng nhập với vai trò Quản trị viên.');
        }

        // Nếu không, cho phép họ đi tiếp.
        return $next($request);
    }
}