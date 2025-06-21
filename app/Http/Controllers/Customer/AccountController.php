<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Hiển thị trang hồ sơ chính của người dùng.
     */
    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.account.info', compact('customer'));
    }

    /**
     * Hiển thị lịch sử đơn hàng của người dùng.
     */
    public function orders()
    {
        $customer = Auth::guard('customer')->user();
        $orders = Order::where('customer_id', $customer->id)->latest()->paginate(10);
        return view('customer.account.orders_index', compact('customer', 'orders'));
    }

    /**
     * Hiển thị chi tiết một đơn hàng.
     */ public function showOrder(Order $order)
    {
        // Đảm bảo đơn hàng thuộc về người dùng đang đăng nhập
        if ($order->customer_id !== Auth::guard('customer')->id()) {
            abort(403); // Hoặc chuyển hướng với lỗi
        }

        // Tải các quan hệ cần thiết cho việc tính toán accessor và hiển thị
        $order->load(['items.product.images', 'deliveryService', 'promotion', 'province', 'district', 'ward']);

        return view('customer.account.orders_show', compact('order'));
    }

    /**
     * Hiển thị địa chỉ của người dùng (tạm thời ẩn).
     */
    public function addresses()
    {
        return redirect()->route('account.profile');
    }

    /**
     * Cập nhật thông tin hồ sơ của khách hàng.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:20'],
        ]);

        $customer->update($validatedData);

        return response()->json(['message' => 'Cập nhật thông tin thành công!']);
    }

    /**
     * Cập nhật mật khẩu của khách hàng.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($customer) {
                if (!Hash::check($value, $customer->password)) {
                    $fail('Mật khẩu hiện tại không chính xác.');
                }
            }],
            'password' => [
                'required',
                'string',
                'confirmed', // Kiểm tra trùng khớp với password_confirmation
                // Quy tắc độ mạnh mật khẩu
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Đổi mật khẩu thành công!']);
    }

    /**
     * Cập nhật ảnh đại diện của khách hàng.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        // Xóa avatar cũ nếu có
        if ($customer->img && Storage::disk('public')->exists($customer->img)) {
            Storage::disk('public')->delete($customer->img);
        }

        $path = $request->file('avatar')->store('avatars/customers', 'public');

        // Cập nhật vào cột 'img' để khớp với database
        $customer->img = $path;
        $customer->save();

        return response()->json([
            'message' => 'Cập nhật ảnh đại diện thành công!',
            'avatar_url' => Storage::url($path)
        ]);
    }
}
