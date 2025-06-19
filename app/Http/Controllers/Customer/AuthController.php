<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập.
     * Tự động chuyển hướng nếu người dùng đã đăng nhập.
     */
    public function showLoginForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('home');
        }
        return view('customer.auth.login');
    }

    /**
     * Xử lý yêu cầu đăng nhập.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Bước 1: Tìm khách hàng bằng email
        $customer = Customer::where('email', $request->email)->first();

        // Bước 2: Kiểm tra trạng thái tài khoản (phải active mới được đăng nhập)
        if ($customer && $customer->status !== Customer::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
            ]);
        }

        // Bước 3: Kiểm tra thông tin đăng nhập (email hoặc mật khẩu)
        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ]);
        }

        // Bước 4: Đăng nhập thành công
        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        // KIỂM TRA NẾU BỊ ADMIN YÊU CẦU ĐỔI MẬT KHẨU
        if ($customer->password_change_required) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bạn cần phải tạo mật khẩu mới để tiếp tục.',
                    'redirect_url' => route('customer.password.force_change')
                ]);
            }
            return redirect()->route('customer.password.force_change');
        }

        // Nếu không, chuyển hướng đến trang mong muốn hoặc trang chủ
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đăng nhập thành công!',
                'redirect_url' => $request->session()->pull('url.intended', route('home'))
            ]);
        }
        return redirect()->intended(route('home'));
    }

    /**
     * Hiển thị form đăng ký.
     * Tự động chuyển hướng nếu người dùng đã đăng nhập.
     */
    public function showRegisterForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('home');
        }
        return view('customer.auth.register');
    }

    /**
     * Xử lý yêu cầu đăng ký.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // Tạo khách hàng mới với trạng thái mặc định là 'active'
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // Bắn sự kiện Registered
        event(new Registered($customer));

        // Tự động đăng nhập cho khách hàng sau khi đăng ký thành công
        Auth::guard('customer')->login($customer);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đăng ký thành công!',
                'redirect_url' => route('home')
            ]);
        }
        return redirect()->route('home');
    }

    /**
     * Đăng xuất tài khoản khách hàng.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * ==========================================================
     * CÁC HÀM XỬ LÝ VIỆC BẮT BUỘC ĐỔI MẬT KHẨU
     * ==========================================================
     */

    /**
     * Hiển thị form bắt buộc đổi mật khẩu.
     */
    public function showForcePasswordChangeForm()
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        // Chỉ cho phép truy cập nếu có cờ yêu cầu đổi mật khẩu
        if (!$customer || !$customer->password_change_required) {
            return redirect()->route('home');
        }

        return view('customer.auth.force-password-change');
    }

    /**
     * Xử lý việc đổi mật khẩu bắt buộc.
     */
    public function handleForcePasswordChange(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // Cập nhật mật khẩu và tắt cờ yêu cầu
        $customer->password = Hash::make($request->password);
        $customer->password_change_required = false;
        $customer->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tạo mật khẩu mới thành công! Bạn sẽ được chuyển đến trang chủ.',
                'redirect_url' => route('home')
            ]);
        }
        return redirect()->route('home')->with('success', 'Tạo mật khẩu mới thành công!');
    }
}
