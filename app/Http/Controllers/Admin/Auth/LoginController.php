<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Admin; // Quan trọng: Đảm bảo import Model Admin
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    /**
     * Hiển thị trang đăng nhập của admin.
     *
     * @return \Illuminate\View\View
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

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            /** @var \App\Models\Admin $admin */
            $admin = Auth::guard('admin')->user();

            // KIỂM TRA STATUS
            if ($admin->status !== Admin::STATUS_ACTIVE) {
                // START: MODIFICATION
                // Chỉ cần logout để đảm bảo người dùng không ở trạng thái đăng nhập.
                // Không hủy session để tránh lỗi CSRF token cho lần đăng nhập sau trên cùng một trang.
                Auth::guard('admin')->logout();
                // $request->session()->invalidate(); // Bỏ dòng này
                // $request->session()->regenerateToken(); // Bỏ dòng này
                // END: MODIFICATION

                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn đã bị tạm khóa. Vui lòng liên hệ quản trị viên.',
                ]);
            }

            $request->session()->regenerate();

            // KIỂM TRA BẮT BUỘC ĐỔI MẬT KHẨU
            if ($admin->password_change_required) {
                // Nếu là yêu cầu AJAX, trả về JSON chứa URL chuyển hướng
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Yêu cầu đổi mật khẩu.',
                        'redirect_url' => route('admin.auth.showForcePasswordChangeForm'),
                    ]);
                }
                // Nếu không, thực hiện redirect bình thường
                return redirect()->route('admin.auth.showForcePasswordChangeForm');
            }

            // KIỂM TRA ROLE (NẾU KHÔNG BỊ BUỘC ĐỔI MẬT KHẨU)
            if ($admin->role === null) {
                // Nếu là yêu cầu AJAX, trả về JSON chứa URL chuyển hướng
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Tài khoản đang chờ xét duyệt.',
                        'redirect_url' => route('admin.pending_authorization'),
                    ]);
                }
                // Nếu không, thực hiện redirect bình thường
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

        throw ValidationException::withMessages([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    /**
     * Hiển thị form bắt buộc đổi mật khẩu.
     */
    public function showForcePasswordChangeForm()
    {
        return view('admin.auth.force-password-change');
    }

    /**
     * Xử lý việc đổi mật khẩu bắt buộc.
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
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.auth.login');
    }
}
