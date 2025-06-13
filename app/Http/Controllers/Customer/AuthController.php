<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập của khách hàng.
     */
    public function showLoginForm()
    {
        return view('customer.auth.login');
    }

    /**
     * Hiển thị form đăng ký của khách hàng.
     */
    public function showRegisterForm()
    {
        return view('customer.auth.register');
    }

    /**
     * Xử lý yêu cầu đăng nhập.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            /** @var \App\Models\Customer $customer */
            $customer = Auth::guard('web')->user();

            // Kiểm tra trạng thái tài khoản
            if (!$customer->isActive()) { // Sử dụng hàm isActive() từ model Customer
                Auth::guard('web')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
                ]);
            }

            $request->session()->regenerate();

            // Trả về JSON nếu là request AJAX (để JS xử lý)
            if ($request->expectsJson()) {
                return response()->json([
                    'message'      => 'Đăng nhập thành công!',
                    'redirect_url' => route('home') // Chuyển về trang chủ
                ]);
            }

            return redirect()->intended(route('home'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Xử lý yêu cầu đăng ký.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'terms'                 => ['required'], // Validate checkbox điều khoản
        ]);

        $customer = Customer::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => Customer::STATUS_ACTIVE, // Mặc định là active
        ]);

        event(new Registered($customer));

        // Không tự động đăng nhập, chuyển hướng đến trang login và báo thành công
        if ($request->expectsJson()) {
            return response()->json([
                'message'      => 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.',
                'redirect_url' => route('login')
            ], 201);
        }

        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }

    /**
     * Xử lý đăng xuất.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
