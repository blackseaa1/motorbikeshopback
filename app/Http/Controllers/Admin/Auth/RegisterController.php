<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Hiển thị form đăng ký admin.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // Chuyển hướng nếu admin đã đăng nhập
        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.register'); // Sẽ tạo view này
    }

    /**
     * Xử lý yêu cầu đăng ký admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'], // email phải là duy nhất trong bảng admins
            'phone' => ['nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:20'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed' // Yêu cầu trường password_confirmation
            ],
            // 'role' => ['required', 'string', Rule::in(['editor', 'manager'])], // Ví dụ nếu có trường role
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => null, // Quan trọng: Gán role là NULL
            'status' => Admin::STATUS_ACTIVE, // Mặc định là active, hoặc Admin::STATUS_SUSPENDED nếu cần duyệt
            // 'img' => 'path/to/default_avatar.png', // Ảnh đại diện mặc định
        ]);

        event(new Registered($admin)); // Bắn sự kiện Registered

        // Không tự động đăng nhập sau khi đăng ký, yêu cầu đăng nhập lại
        // Nếu muốn tự động đăng nhập: Auth::guard('admin')->login($admin);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đăng ký thành công! Vui lòng đăng nhập.',
                'redirect_url' => route('admin.auth.login')
            ], 201);
        }

        return redirect()->route('admin.auth.login')->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
    }
}
