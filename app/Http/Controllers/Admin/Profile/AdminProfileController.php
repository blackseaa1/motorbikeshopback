<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Admin; // Quan trọng: Import model Admin

class AdminProfileController extends Controller
{
    public function showProfileForm()
    {
        /** @var Admin $admin */ // Thêm PHPDoc cho $admin nếu bạn sử dụng nó ở đây nhiều
        $admin = Auth::guard('admin')->user();
        return view('admin.profile.index', compact('admin'));
    }

    public function updateInfo(Request $request)
    {
        /** @var Admin $admin */ // Thêm PHPDoc để Intelephense hiểu rõ kiểu của $admin
        $admin = Auth::guard('admin')->user();
        $errorBag = 'updateInfo';

        $validatedData = $request->validateWithBag($errorBag, [
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

        $admin->name = $validatedData['name'];
        $admin->email = $validatedData['email'];
        $admin->phone = $validatedData['phone'];
        $admin->save(); // Intelephense sẽ không còn báo lỗi ở đây

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thông tin cá nhân đã được cập nhật.',
                'updated_admin_name' => $admin->name,
            ]);
        }

        return redirect()->route('admin.profile.show')
            ->with('success_message', 'Thông tin cá nhân đã được cập nhật.')
            ->with('active_tab_hash', '#settings');
    }

    public function updateAvatar(Request $request)
    {
        /** @var Admin $admin */ // Thêm PHPDoc
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
            $admin->save(); // Intelephense sẽ không còn báo lỗi

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ảnh đại diện đã được cập nhật.',
                    'avatar_url' => asset($admin->img)
                ]);
            }

            return redirect()->route('admin.profile.show')
                ->with('success_message', 'Ảnh đại diện đã được cập nhật.')
                ->with('active_tab_hash', '#settings');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có tệp ảnh nào được tải lên hoặc có lỗi xảy ra.'
            ], 400);
        }

        return redirect()->route('admin.profile.show')
            ->with('error_message', 'Không có tệp ảnh nào được tải lên hoặc có lỗi xảy ra.')
            ->with('active_tab_hash', '#settings');
    }

    public function changePassword(Request $request)
    {
        /** @var Admin $admin */ // Thêm PHPDoc
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
        $admin->save(); // Intelephense sẽ không còn báo lỗi

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu đã được thay đổi thành công.'
            ]);
        }

        return redirect()->route('admin.profile.show')
            ->with('success_message', 'Mật khẩu đã được thay đổi thành công.')
            ->with('active_tab_hash', '#changePassword');
    }
}
