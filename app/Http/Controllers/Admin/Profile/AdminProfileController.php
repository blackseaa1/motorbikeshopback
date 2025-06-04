<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;


class AdminProfileController extends Controller
{
    public function showProfileForm()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile.index', compact('admin'));
    }

    public function updateInfo(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $errorBag = 'updateInfo'; // Để chỉ định error bag

        $request->validateWithBag($errorBag, [ // Sử dụng validateWithBag
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->save();

        return redirect()->route('admin.profile.show')
            ->with('success', 'Thông tin cá nhân đã được cập nhật.')
            ->with('active_tab_hash', '#settings'); // Hoặc $request->input('active_tab', '#settings')
    }

    public function updateAvatar(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $errorBag = 'updateAvatar';

        $request->validateWithBag($errorBag, [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);


        if ($request->hasFile('avatar')) {
            if ($admin->img && Storage::disk('public')->exists(str_replace('/storage/', '', $admin->img)) && !str_contains($admin->img, 'placehold.co')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $admin->img));
            }
            $path = $request->file('avatar')->store('admin_avatars', 'public');
            $admin->img = '/storage/' . $path;
            $admin->save();

            return redirect()->route('admin.profile.show')
                ->with('success', 'Ảnh đại diện đã được cập nhật.')
                ->with('active_tab_hash', session('active_tab_hash', '#settings')); // Giữ lại tab trước đó nếu có, hoặc tab settings
        }

        return redirect()->route('admin.profile.show')
            ->with('error', 'Không có tệp ảnh nào được tải lên.')
            ->with('active_tab_hash', session('active_tab_hash', '#settings'));
    }

    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $errorBag = 'changePassword';

        $request->validateWithBag($errorBag, [
            'current_password' => ['required', function ($attribute, $value, $fail) use ($admin) {
                if (!Hash::check($value, $admin->password)) {
                    $fail('Mật khẩu hiện tại không chính xác.');
                }
            }],
            'new_password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return redirect()->route('admin.profile.show')
            ->with('success', 'Mật khẩu đã được thay đổi.')
            ->with('active_tab_hash', '#changePassword'); // Hoặc $request->input('active_tab', '#changePassword')
    }
}
