<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Admin; // Thêm import model Admin
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use App\Support\CartManager;

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
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Support\CartManager  $cartManager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request, CartManager $cartManager)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Thử đăng nhập với guard 'customer'
        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Gộp giỏ hàng từ session vào database
            $cartManager->mergeSessionCartToDatabase();

            /** @var \App\Models\Customer $customer */
            $customer = Auth::guard('customer')->user();

            // Bắt đầu defensive programming: Kiểm tra null cho $customer
            // Mặc dù về lý thuyết không nên null nếu attempt() thành công, nhưng để an toàn.
            if (!$customer) {
                Auth::guard('customer')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Đã xảy ra lỗi không xác định trong quá trình đăng nhập. Vui lòng thử lại.',
                ]);
            }

            // KIỂM TRA STATUS KHÁCH HÀNG (Nếu tài khoản bị khóa)
            if ($customer->status !== Customer::STATUS_ACTIVE) {
                Auth::guard('customer')->logout(); // Đăng xuất khách hàng
                $request->session()->invalidate();
                $request->session()->regenerateToken(); // Đảm bảo token mới sau khi logout
                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn đã bị tạm khóa. Vui lòng liên hệ hỗ trợ.',
                ]);
            }

            // KIỂM TRA BẮT BUỘC ĐỔI MẬT KHẨU
            if ($customer->password_change_required) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Bạn cần phải tạo mật khẩu mới để tiếp tục.',
                        'redirect_url' => route('customer.password.force_change')
                    ]);
                }
                return redirect()->route('customer.password.force_change');
            }

            // Đăng nhập thành công, chuyển hướng đến trang mong muốn hoặc trang chủ
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Đăng nhập thành công!',
                    'redirect_url' => $request->session()->pull('url.intended', route('home'))
                ]);
            }
            return redirect()->intended(route('home'));
        }

        // Nếu đăng nhập khách hàng thất bại, kiểm tra xem có phải là tài khoản quản trị viên không
        // (Nếu email tồn tại trong bảng admins)
        if (Admin::where('email', $credentials['email'])->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Đây là tài khoản quản trị viên. Vui lòng đăng nhập tại trang quản trị.',
                    'redirect_url' => route('admin.auth.login')
                ], 403); // Sử dụng mã lỗi 403 Forbidden
            }
            // Nếu không phải AJAX, chuyển hướng trực tiếp
            return redirect()->route('admin.auth.login')->withErrors([
                'email' => 'Đây là tài khoản quản trị viên. Vui lòng đăng nhập tại trang quản trị.'
            ]);
        }

        // Thông tin đăng nhập không chính xác hoặc tài khoản bị khóa (chung chung nếu không phải admin)
        throw ValidationException::withMessages([
            'email' => 'Thông tin đăng nhập không chính xác hoặc tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
        ]);
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handleForcePasswordChange(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        // Nếu không có khách hàng hoặc không có yêu cầu đổi mật khẩu, chuyển hướng về trang chủ.
        if (!$customer || !$customer->password_change_required) {
            return redirect()->route('home');
        }

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
