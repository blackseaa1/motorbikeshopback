<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffAccountController extends Controller
{
    protected $viewPath = 'admin.userManagement.staff';
    protected $routeName = 'admin.userManagement.staff';

    private function getAssignableStaffRoles(): array
    {
        return [
            Admin::ROLE_ADMIN => 'Quản trị viên',
            Admin::ROLE_STAFF => 'Nhân viên Hỗ trợ',
            Admin::ROLE_WAREHOUSE_STAFF => 'Nhân viên Kho',
        ];
    }

    public function index()
    {
        $loggedInAdmin = Auth::guard('admin')->user();

        $staffQuery = Admin::where('id', '!=', $loggedInAdmin->id);

        if (!$loggedInAdmin->isSuperAdmin()) {
            $staffQuery->where('role', '!=', Admin::ROLE_SUPER_ADMIN)
                ->whereIn('role', [Admin::ROLE_STAFF, Admin::ROLE_WAREHOUSE_STAFF, null]);
        }


        $staffs = $staffQuery->latest()->paginate(10);
        $availableRoles = $this->getAssignableStaffRoles();
        $routeName = $this->routeName;

        return view($this->viewPath, compact('staffs', 'loggedInAdmin', 'availableRoles', 'routeName'));
    }

    public function store(Request $request)
    {
        $formIdentifier = 'create_staff_form';
        $availableRolesForValidation = $this->getAssignableStaffRoles();

        $validatedData = $request->validateWithBag($formIdentifier, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('admins', 'phone')->ignore($request->staff_id_for_update_modal, 'id')],
            'role' => ['required', 'string', Rule::in(array_keys($availableRolesForValidation))],
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([Admin::STATUS_ACTIVE, Admin::STATUS_SUSPENDED])],
            '_form_identifier' => ['required', 'string', Rule::in([$formIdentifier])]
        ], [], [
            'name' => 'Họ và Tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'role' => 'Vai trò',
            'img' => 'Ảnh đại diện',
            'status' => 'Trạng thái',
        ]);

        try {
            $dataToCreate = $validatedData;
            unset($dataToCreate['_form_identifier']); 
            
            $dataToCreate['password'] = Hash::make('12345');
            $dataToCreate['password_change_required'] = true;

            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('admin_avatars', 'public');
                $dataToCreate['img'] = $path;
            } else {
                unset($dataToCreate['img']);
            }

            Admin::create($dataToCreate);

            // Nếu là request AJAX, trả về JSON. Nếu không, redirect như cũ.
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo tài khoản nhân viên thành công!'
                ]);
            }
            return redirect()->route($this->routeName . '.index')->with('success', 'Tạo tài khoản nhân viên thành công!');

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo Nhân viên Admin: ' . $e->getMessage());

            // Sử dụng hàm helper đã có để xử lý lỗi một cách nhất quán
            return $this->jsonOrRedirectBack(
                $request,
                'error_system',
                'Có lỗi xảy ra khi tạo tài khoản: ' . $e->getMessage()
            );
        }
    }

    public function show(Admin $staff)
    {
        $loggedInAdmin = Auth::guard('admin')->user();
        if (!$loggedInAdmin->isSuperAdmin() && $staff->isSuperAdmin() && $loggedInAdmin->id !== $staff->id) {
            abort(403, 'Bạn không có quyền xem thông tin của Super Admin khác.');
        }
        if (!$loggedInAdmin->isSuperAdmin() && $staff->id !== $loggedInAdmin->id && !in_array($staff->role, [Admin::ROLE_STAFF, Admin::ROLE_WAREHOUSE_STAFF, null])) {
            // Admin thường không được xem chi tiết của Admin khác (trừ vai trò thấp hơn hoặc chính mình)
            // abort(403, 'Bạn không có quyền xem thông tin của quản trị viên này.');
        }

        $routeName = $this->routeName;
        return view('admin.userManagement.staff_show', compact('staff', 'routeName'));
    }

    public function update(Request $request, Admin $staff)
    {
        $loggedInAdmin = Auth::guard('admin')->user();
        if ($staff->isSuperAdmin() && !$loggedInAdmin->isSuperAdmin()) {
            return $this->jsonOrRedirectBack($request, 'error', 'Bạn không có quyền chỉnh sửa tài khoản Super Admin.', $staff->id);
        }
        if (
            !$loggedInAdmin->isSuperAdmin() &&
            $loggedInAdmin->id !== $staff->id &&
            ($staff->role === Admin::ROLE_ADMIN || $staff->role === Admin::ROLE_SUPER_ADMIN)
        ) {
            return $this->jsonOrRedirectBack($request, 'error', 'Bạn không có quyền chỉnh sửa quản trị viên này.', $staff->id);
        }


        $formIdentifier = 'update_staff_form';
        $availableRolesForValidation = $this->getAssignableStaffRoles();

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('admins', 'phone')->ignore($staff->id)],
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'role' => ['required', 'string', Rule::in(array_keys($availableRolesForValidation))],
            'status' => ['required', Rule::in([Admin::STATUS_ACTIVE, Admin::STATUS_SUSPENDED])],
            '_form_identifier' => ['required', 'string', Rule::in([$formIdentifier])],
            'staff_id_for_update_modal' => 'required|integer|exists:admins,id',
        ];

        if ($staff->isSuperAdmin() || ($loggedInAdmin->isSuperAdmin() && $staff->id === $loggedInAdmin->id && $request->role !== Admin::ROLE_SUPER_ADMIN)) {
        } else if (!$loggedInAdmin->isSuperAdmin() && $request->role === Admin::ROLE_SUPER_ADMIN) {
            return $this->jsonOrRedirectBack($request, 'error', 'Bạn không có quyền gán vai trò Super Admin.', $staff->id, ['role' => ['Không thể gán vai trò Super Admin.']]);
        }


        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'];
        }

        $validatedData = $request->validateWithBag($formIdentifier, $rules, [], [
            'name' => 'Họ và Tên',
            'phone' => 'Số điện thoại',
            'role' => 'Vai trò',
            'password' => 'Mật khẩu mới',
            'img' => 'Ảnh đại diện',
            'status' => 'Trạng thái',
        ]);

        if ((int)$request->input('staff_id_for_update_modal') !== $staff->id) {
            return $this->jsonOrRedirectBack($request, 'error_validation', 'Lỗi xác thực ID nhân viên.', $staff->id, ['id_mismatch' => ['Lỗi xác thực ID nhân viên.']]);
        }

        try {
            $dataToUpdate = $request->only(['name', 'phone', 'status']);
            if ($loggedInAdmin->isSuperAdmin() || ($staff->role !== Admin::ROLE_SUPER_ADMIN && $request->role !== Admin::ROLE_SUPER_ADMIN)) {
                if ($staff->id === $loggedInAdmin->id && $staff->isSuperAdmin() && $request->role !== Admin::ROLE_SUPER_ADMIN) {
                } else {
                    $dataToUpdate['role'] = $validatedData['role'];
                }
            }


            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->input('password'));
            }

            if ($request->hasFile('img')) {
                if ($staff->img && Storage::disk('public')->exists($staff->img)) {
                    Storage::disk('public')->delete($staff->img);
                }
                $path = $request->file('img')->store('admin_avatars', 'public');
                $dataToUpdate['img'] = $path;
            }

            $staff->update($dataToUpdate);
            $staff->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật tài khoản nhân viên thành công!',
                    'staff' => [
                        'id' => $staff->id,
                        'name' => $staff->name,
                        'phone' => $staff->phone,
                        'role' => $staff->role,
                        'status' => $staff->status,
                        'avatar_url' => $staff->avatar_url,
                        'role_name' => $staff->role_name,
                        'role_badge_class' => $staff->role_badge_class,
                        'status_text' => $staff->status_text,
                        'status_badge_class' => $staff->status_badge_class,
                    ]
                ]);
            }
            return redirect()->route($this->routeName . '.index')->with('success', 'Cập nhật tài khoản nhân viên thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật nhân viên Admin (ID: ' . $staff->id . '): ' . $e->getMessage());
            return $this->jsonOrRedirectBack($request, 'error_system', 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage(), $staff->id);
        }
    }

    private function jsonOrRedirectBack(Request $request, string $type, string $message, ?int $staffId = null, ?array $errors = null)
    {
        $responsePayload = ['success' => false, 'message' => $message];
        if ($errors) {
            $responsePayload['errors'] = $errors;
        }
        $statusCode = ($type === 'error_validation') ? 422 : (($type === 'error_system') ? 500 : 403);


        if ($request->expectsJson()) {
            return response()->json($responsePayload, $statusCode);
        }

        $redirect = redirect()->back()->withInput();
        if ($type === 'error_validation' || $type === 'error_system' || $type === 'error') {
            $redirect->withErrors($errors ?: [$type => $message], $request->input('_form_identifier', 'default_form_errors'));
        } else {
            $redirect->with($type, $message);
        }

        if ($staffId && $request->input('_form_identifier') === 'update_staff_form') {
            $redirect->with('reopen_modal_update', $staffId);
        } else if ($request->input('_form_identifier') === 'create_staff_form') {
            $redirect->with('reopen_modal_create', true);
        }
        return $redirect;
    }

    /**
     * Đặt lại mật khẩu của nhân viên về giá trị mặc định.
     * Yêu cầu quyền Super Admin.
     */
    public function resetPassword(Request $request, Admin $staff)
    {
        $loggedInAdmin = Auth::guard('admin')->user();

        // Chỉ Super Admin mới có quyền thực hiện
        if (!$loggedInAdmin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.'], 403);
        }
        // Không cho phép reset mật khẩu của tài khoản Super Admin khác
        if ($staff->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không thể đặt lại mật khẩu của tài khoản Super Admin.'], 403);
        }
        // Không cho phép tự đặt lại mật khẩu qua chức năng này
        if ($staff->id === $loggedInAdmin->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không thể tự đặt lại mật khẩu của mình tại đây.'], 403);
        }

        try {
            $newPassword = '12345'; // Mật khẩu mặc định
            $staff->password = Hash::make($newPassword);
            $staff->password_change_required = true; // <-- BẬT CỜ BẮT BUỘC ĐỔI MẬT KHẨU
            $staff->save();

            Log::info("Mật khẩu cho nhân viên '{$staff->email}' (ID: {$staff->id}) đã được đặt lại và bị buộc phải thay đổi bởi Super Admin '{$loggedInAdmin->email}' (ID: {$loggedInAdmin->id}).");

            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu của nhân viên đã được đặt lại thành công. Người dùng sẽ được yêu cầu đổi mật khẩu ở lần đăng nhập tiếp theo.'
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi đặt lại mật khẩu cho Admin (ID: " . $staff->id . "): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể đặt lại mật khẩu. Có lỗi hệ thống xảy ra.'], 500);
        }
    }


    public function toggleStatus(Request $request, Admin $staff)
    {
        $loggedInAdmin = Auth::guard('admin')->user();
        if ($staff->isSuperAdmin() && $staff->id !== $loggedInAdmin->id) {
            return response()->json(['success' => false, 'message' => 'Không thể thay đổi trạng thái của Super Admin.'], 403);
        }
        if ($staff->isSuperAdmin() && $staff->id === $loggedInAdmin->id && $staff->isActive()) {
            return response()->json(['success' => false, 'message' => 'Super Admin không thể tự khóa tài khoản của mình.'], 403);
        }


        try {
            $staff->status = $staff->isActive() ? Admin::STATUS_SUSPENDED : Admin::STATUS_ACTIVE;
            $staff->save();
            $staff->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái tài khoản thành công!',
                'new_status_key' => $staff->status,
                'status_text' => $staff->status_text,
                'status_badge_class' => $staff->status_badge_class,
                'is_active' => $staff->isActive(),
                'button_class_add' => $staff->isActive() ? 'btn-danger action-lock' : 'btn-success action-unlock',
                'button_class_remove' => $staff->isActive() ? 'btn-success action-unlock' : 'btn-danger action-lock',
                'button_icon' => $staff->isActive() ? 'bi-lock-fill' : 'bi-unlock-fill',
                'button_title' => $staff->isActive() ? 'Khóa tài khoản này' : 'Mở khóa tài khoản này',
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi toggle trạng thái Admin (ID: " . $staff->id . "): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật trạng thái.'], 500);
        }
    }

    public function destroy(Request $request, Admin $staff)
    {
        $loggedInAdmin = Auth::guard('admin')->user();

        if (!$loggedInAdmin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Chỉ Super Admin mới có quyền xóa tài khoản.'], 403);
        }
        if ($staff->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa tài khoản Super Admin.'], 403);
        }
        if ($staff->id === $loggedInAdmin->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không thể tự xóa tài khoản của mình.'], 403);
        }

        $validator = ValidatorFacade::make($request->all(), [
            'admin_password_confirm_delete' => 'required|string',
        ], [], ['admin_password_confirm_delete' => 'Mật khẩu xác nhận']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        $passwordToConfirm = $request->input('admin_password_confirm_delete');
        $passwordMatched = false;
        $configDeletionPassword = Config::get('admin.deletion_password');

        if ($configDeletionPassword) {
            if ($passwordToConfirm === $configDeletionPassword) {
                $passwordMatched = true;
            }
        } else {
            if (Hash::check($passwordToConfirm, $loggedInAdmin->password)) {
                $passwordMatched = true;
            }
        }

        if (!$passwordMatched) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu xác nhận không đúng.',
                'errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]
            ], 422);
        }

        try {
            if ($staff->img && Storage::disk('public')->exists($staff->img)) {
                Storage::disk('public')->delete($staff->img);
            }
            $staff->delete();
            return response()->json(['success' => true, 'message' => 'Xóa tài khoản nhân viên thành công!']);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa Admin (ID: " . $staff->id . "): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa tài khoản. Có lỗi xảy ra.'], 500);
        }
    }
}