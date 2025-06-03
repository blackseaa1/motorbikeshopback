<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Admin; // Quan trọng: Đảm bảo import Model Admin

class LoginController extends Controller
{
    /**
     * Hiển thị trang đăng nhập của admin.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Nếu admin đã đăng nhập, chuyển hướng thẳng đến dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login'); //
    }

    /**
     * Xử lý yêu cầu đăng nhập của admin, hỗ trợ cả AJAX và request thường.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // 1. Xác thực dữ liệu đầu vào từ form
        $credentials = $request->validate([
            'email' => ['required', 'email'], //
            'password' => ['required'], //
        ]);

        // 2. Cố gắng đăng nhập với guard 'admin' và xử lý "Ghi nhớ tôi"
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            // Đăng nhập thành công bước đầu (email/password khớp)

            /** @var \App\Models\Admin $admin */
            $admin = Auth::guard('admin')->user(); // Lấy thông tin admin vừa được xác thực

            // === KIỂM TRA STATUS ===
            // Sử dụng hằng số Admin::STATUS_ACTIVE (giả định là 'active') từ Model Admin
            if ($admin->status !== Admin::STATUS_ACTIVE) {
                // Nếu tài khoản không hoạt động, đăng xuất ngay lập tức
                Auth::guard('admin')->logout(); //

                // Ném lỗi ValidationException.
                throw ValidationException::withMessages([ //
                    'email' => 'Tài khoản của bạn không hoạt động hoặc đã bị khóa.',
                    // Tùy chọn thông báo tiếng Anh: 'Your account is not active or has been suspended.'
                ]);
            }
            // === KẾT THÚC BƯỚC KIỂM TRA STATUS ===

            // 3. Nếu status là 'active', tiếp tục tạo lại session ID mới
            $request->session()->regenerate();

            // 4. KIỂM TRA NẾU LÀ YÊU CẦU AJAX
            if ($request->expectsJson()) {
                // Trả về response JSON chứa đường dẫn để JavaScript chuyển hướng
                return response()->json([
                    'message' => 'Đăng nhập thành công!',
                    'redirect_url' => route('admin.dashboard'),
                ]);
            }

            // Nếu là request thường, trả về một lệnh chuyển hướng tiêu chuẩn
            return redirect()->intended(route('admin.dashboard'));
        }

        // 5. NẾU ĐĂNG NHẬP THẤT BẠI (email/password không đúng)
        throw ValidationException::withMessages([ //
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    /**
     * Xử lý việc đăng xuất của admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Đăng xuất khỏi guard 'admin'
        Auth::guard('admin')->logout();

        // Hủy bỏ session hiện tại
        $request->session()->invalidate();

        // Tạo lại token mới để chống tấn công CSRF
        $request->session()->regenerateToken();

        // Chuyển hướng về trang đăng nhập
        return redirect()->route('admin.auth.login');
    }
}
