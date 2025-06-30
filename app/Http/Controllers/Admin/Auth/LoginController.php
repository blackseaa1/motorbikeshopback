<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    /**
     * Hiển thị trang đăng nhập của admin.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            /** @var \App\Models\Admin $adminUser */
            $adminUser = Auth::guard('admin')->user();

            if ($adminUser) {
                // Ưu tiên kiểm tra đổi mật khẩu trước
                if ($adminUser->password_change_required) {
                    return redirect()->route('admin.auth.showForcePasswordChangeForm');
                }
                // Nếu không cần đổi mật khẩu, kiểm tra vai trò
                if ($adminUser->role === null) {
                    return redirect()->route('admin.pending_authorization');
                }
            }
            // Nếu mọi thứ đều ổn, vào dashboard
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    /**
     * Xử lý yêu cầu đăng nhập của admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Kiểm tra nếu người dùng đã đăng nhập với tài khoản khách hàng
        if (Auth::guard('customer')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bạn đã đăng nhập với tài khoản khách hàng. Vui lòng đăng xuất khỏi tài khoản khách hàng để đăng nhập với tư cách quản trị viên.',
                    'redirect_url' => route('home') // Chuyển hướng về trang chính của khách hàng
                ], 403);
            }
            return redirect()->route('home')->withErrors([
                'email' => 'Bạn đã đăng nhập với tài khoản khách hàng. Vui lòng đăng xuất khỏi tài khoản khách hàng để đăng nhập với tư cách quản trị viên.'
            ]);
        }

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            /** @var \App\Models\Admin $admin */
            $admin = Auth::guard('admin')->user();

            if (!$admin) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Đã xảy ra lỗi không xác định trong quá trình đăng nhập. Vui lòng thử lại.',
                ]);
            }

            // KIỂM TRA STATUS (Nếu tài khoản bị tạm khóa)
            if ($admin->status !== Admin::STATUS_ACTIVE) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn đã bị tạm khóa. Vui lòng liên hệ quản trị viên.',
                ]);
            }

            // NEW LOGIC: Đăng xuất khỏi guard 'customer' nếu có session đang hoạt động
            if (Auth::guard('customer')->check()) {
                Auth::guard('customer')->logout();
                // Có thể thêm invalidate session và regenerate token cho guard customer nếu muốn
                // $request->session()->invalidate();
                // $request->session()->regenerateToken();
            }
            // END NEW LOGIC

            $request->session()->regenerate();

            // KIỂM TRA BẮT BUỘC ĐỔI MẬT KHẨU
            if ($admin->password_change_required) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Yêu cầu đổi mật khẩu.',
                        'redirect_url' => route('admin.auth.showForcePasswordChangeForm'),
                    ]);
                }
                return redirect()->route('admin.auth.showForcePasswordChangeForm');
            }

            // KIỂM TRA ROLE (NẾU KHÔNG BỊ BUỘC ĐỔI MẬT KHẨU)
            if ($admin->role === null) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Tài khoản đang chờ xét duyệt.',
                        'redirect_url' => route('admin.pending_authorization'),
                    ]);
                }
                return redirect()->intended(route('admin.pending_authorization'));
            }

            // VÀO DASHBOARD NẾU TẤT CẢ ĐỀU HỢP LỆ
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Đăng nhập thành công!',
                    'redirect_url' => route('admin.dashboard'),
                ]);
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        // Nếu đăng nhập admin thất bại, kiểm tra xem có phải là tài khoản khách hàng không
        if (Customer::where('email', $credentials['email'])->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Đây là tài khoản khách hàng. Vui lòng đăng nhập tại trang khách hàng.',
                    'redirect_url' => route('customer.auth.login')
                ], 403);
            }
            return redirect()->route('customer.auth.login')->withErrors([
                'email' => 'Đây là tài khoản khách hàng. Vui lòng đăng nhập tại trang khách hàng.'
            ]);
        }

        // Thông tin đăng nhập không chính xác
        throw ValidationException::withMessages([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    /**
     * Hiển thị form bắt buộc đổi mật khẩu.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showForcePasswordChangeForm()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Chỉ cho phép truy cập nếu có cờ yêu cầu đổi mật khẩu
        if (!$admin || !$admin->password_change_required) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.force-password-change');
    }

    /**
     * Xử lý việc đổi mật khẩu bắt buộc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function forcePasswordChange(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                'confirmed'
            ],
        ], [], [
            'current_password' => 'Mật khẩu hiện tại',
            'password' => 'Mật khẩu mới',
        ]);

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Nếu không có admin hoặc không có yêu cầu đổi mật khẩu, chuyển hướng về dashboard.
        if (!$admin || !$admin->password_change_required) {
            return redirect()->route('admin.dashboard');
        }

        if (!Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        // Cập nhật mật khẩu mới và bỏ cờ bắt buộc
        $admin->password = Hash::make($request->password);
        $admin->password_change_required = false;
        $admin->save();

        return redirect()->route('admin.dashboard')->with('success', 'Đổi mật khẩu thành công! Bạn đã có thể tiếp tục sử dụng hệ thống.');
    }

    /**
     * Xử lý việc đăng xuất của admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Nếu người dùng có tồn tại, xóa remember_token
        if ($admin) {
            $admin->setRememberToken(null);
            $admin->save();
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.auth.login');
    }
}
