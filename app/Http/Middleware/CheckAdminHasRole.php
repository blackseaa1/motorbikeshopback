<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminHasRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();

            if (
                $admin->role === null &&
                !$request->routeIs('admin.pending_authorization') &&
                !$request->routeIs('admin.logout')
            ) {
                return redirect()->route('admin.pending_authorization');
            }

            if ($admin->role !== null && $request->routeIs('admin.pending_authorization')) {
                return redirect()->route('admin.dashboard'); // Hoặc trang admin mặc định
            }
        }
        return $next($request);
    }
}
