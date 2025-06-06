<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Lấy thông tin người dùng admin đang đăng nhập
        $admin = Auth::guard('admin')->user();

        // Kiểm tra xem người dùng có tồn tại, có cờ bắt buộc đổi mật khẩu hay không
        // và quan trọng nhất: trang họ đang truy cập KHÔNG PHẢI là trang đổi mật khẩu
        // để tránh vòng lặp chuyển hướng vô tận (infinite redirect loop).
        if ($admin && $admin->password_change_required && !$request->routeIs('admin.auth.showForcePasswordChangeForm')) {

            // Đồng thời cho phép truy cập route đăng xuất
            if ($request->routeIs('admin.logout')) {
                return $next($request);
            }

            // Nếu các điều kiện trên thỏa mãn, chuyển hướng họ về trang đổi mật khẩu
            return redirect()->route('admin.auth.showForcePasswordChangeForm');
        }

        // Nếu không, cho phép request tiếp tục như bình thường
        return $next($request);
    }
}
