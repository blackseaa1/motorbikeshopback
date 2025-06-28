<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin; // Import Model Admin
use App\Models\Customer; // Import Model Customer

class CheckUserStatus
{
    /**
     * Xử lý một yêu cầu đến.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Kiểm tra xem người dùng có đang đăng nhập với guard này không
        if (Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();

            // Kiểm tra trạng thái của người dùng
            // Đảm bảo rằng model Admin và Customer có hằng số STATUS_ACTIVE hoặc một thuộc tính 'status' để kiểm tra.
            // Cập nhật logic này dựa trên cách bạn định nghĩa trạng thái trong model của mình.
            $isLocked = false;
            if ($guard === 'admin' && $user instanceof Admin) {
                if ($user->status !== Admin::STATUS_ACTIVE) {
                    $isLocked = true;
                }
            } elseif ($guard === 'customer' && $user instanceof Customer) {
                if ($user->status !== Customer::STATUS_ACTIVE) {
                    $isLocked = true;
                }
            }

            if ($isLocked) {
                Auth::guard($guard)->logout(); // Đăng xuất người dùng
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Chuyển hướng đến trang đăng nhập phù hợp với thông báo
                if ($guard === 'admin') {
                    return redirect()->route('admin.auth.login')->withErrors(['email' => 'Tài khoản quản trị viên của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.']);
                } else {
                    return redirect()->route('customer.auth.login')->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
                }
            }
        }

        return $next($request);
    }
}
