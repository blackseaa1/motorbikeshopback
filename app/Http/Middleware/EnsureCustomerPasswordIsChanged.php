<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerPasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('customer')->check()) {

            /** @var \App\Models\Customer $customer */
            $customer = Auth::guard('customer')->user();

            if ($customer && $customer->password_change_required) {

                $allowedRoutes = [
                    'customer.password.force_change',
                    'customer.password.handle_force_change',
                    'logout'
                ];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    // === THÊM MỚI TẠI ĐÂY ===
                    // Gửi một thông báo cảnh báo đến người dùng.
                    $message = 'Để đảm bảo an toàn cho tài khoản, bạn phải tạo một mật khẩu mới để tiếp tục sử dụng dịch vụ.';

                    // Chuyển hướng về trang đổi mật khẩu kèm theo thông báo
                    return redirect()->route('customer.password.force_change')->with('warning', $message);
                    // =======================
                }
            }
        }

        return $next($request);
    }
}
