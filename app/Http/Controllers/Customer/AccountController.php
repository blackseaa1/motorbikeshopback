<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Hiển thị trang hồ sơ.
     */
    public function profile()
    {
        // View này đã có sẵn
        return view('customer.account.profile');
    }

    // Các hàm orders() và addresses() bạn có thể tự phát triển sau
    public function orders()
    { /* ... */
    }
    public function addresses()
    { /* ... */
    }

    /**
     * Cập nhật thông tin hồ sơ của khách hàng.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:20'],
        ]);

        $customer->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Cập nhật thông tin thành công!']);
        }

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Cập nhật mật khẩu của khách hàng.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($customer) {
                if (!Hash::check($value, $customer->password)) {
                    $fail('Mật khẩu hiện tại không chính xác.');
                }
            }],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Đổi mật khẩu thành công!']);
        }

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
