<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin // This middleware should be applied to customer-facing routes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If an admin is currently logged in, force them to log out from admin
        // if they try to access customer-specific functionalities
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('customer.auth.login')->withErrors([
                'email' => 'Bạn đã đăng nhập với tài khoản quản trị viên. Phiên làm việc quản trị viên của bạn đã bị kết thúc để bạn có thể đăng nhập với tư cách khách hàng.'
            ]);
        }

        return $next($request);
    }
}