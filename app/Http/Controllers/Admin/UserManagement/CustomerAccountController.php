<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerAccountController extends Controller
{
    /**
     * Hiển thị danh sách khách hàng, bao gồm cả trong thùng rác.
     */
    public function index(Request $request)
    {
        $query = Customer::query();
        $status = $request->query('status');
        if ($status === 'trashed') {
            $query->onlyTrashed();
        }
        $customers = $query->latest('id')->paginate(10);
        return view('admin.userManagement.customers', compact('customers', 'status'));
    }

    /**
     * Lưu một khách hàng mới vào database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers,email',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'phone')->whereNull('deleted_at')],
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([Customer::STATUS_ACTIVE, Customer::STATUS_SUSPENDED])],
        ]);
        try {
            $dataToCreate = $validatedData;
            $dataToCreate['password'] = Hash::make('12345');
            $dataToCreate['password_change_required'] = true;
            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('customer_avatars', 'public');
                $dataToCreate['img'] = $path;
            }
            Customer::create($dataToCreate);
            return response()->json(['success' => true, 'message' => 'Tạo tài khoản khách hàng thành công!'], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo Khách hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra trong quá trình tạo tài khoản.'], 500);
        }
    }

    /**
     * Cập nhật thông tin khách hàng.
     */
    public function update(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([Customer::STATUS_ACTIVE, Customer::STATUS_SUSPENDED])],
        ]);
        try {
            $dataToUpdate = $validatedData;
            if ($request->hasFile('img')) {
                if ($customer->img && Storage::disk('public')->exists($customer->img)) {
                    Storage::disk('public')->delete($customer->img);
                }
                $path = $request->file('img')->store('customer_avatars', 'public');
                $dataToUpdate['img'] = $path;
            }
            $customer->update($dataToUpdate);
            return response()->json(['success' => true, 'message' => 'Cập nhật tài khoản thành công!']);
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật Khách hàng (ID: {$customer->id}): " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra, không thể cập nhật.'], 500);
        }
    }

    /**
     * Chuyển đổi trạng thái (active/suspended) của khách hàng.
     */
    public function toggleStatus(Customer $customer)
    {
        Log::info("Bắt đầu toggle trạng thái cho khách hàng ID: {$customer->id}, trạng thái hiện tại: {$customer->status}");
        try {
            $customer->status = $customer->isActive() ? Customer::STATUS_SUSPENDED : Customer::STATUS_ACTIVE;
            $customer->save();
            $customer->refresh();
            Log::info("Toggle trạng thái thành công cho khách hàng ID: {$customer->id}, trạng thái mới: {$customer->status}");
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi đổi trạng thái Khách hàng (ID: {$customer->id}): " . $e->getMessage());
            return response()->json(['message' => 'Không thể cập nhật trạng thái.'], 500);
        }
    }

    /**
     * Đặt lại mật khẩu của khách hàng về mặc định.
     */
    public function resetPassword(Customer $customer)
    {
        try {
            $customer->password = Hash::make('12345');
            $customer->password_change_required = true;
            $customer->save();
            return response()->json(['success' => true, 'message' => "Đã reset mật khẩu cho '{$customer->name}'."]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi reset mật khẩu Khách hàng (ID: {$customer->id}): " . $e->getMessage());
            return response()->json(['message' => 'Không thể reset mật khẩu.'], 500);
        }
    }

    /**
     * Xóa mềm (chuyển vào thùng rác).
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return response()->json(['success' => true, 'message' => "Đã chuyển khách hàng '{$customer->name}' vào thùng rác."]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm Khách hàng (ID: {$customer->id}): " . $e->getMessage());
            return response()->json(['message' => 'Không thể xóa khách hàng này.'], 500);
        }
    }

    /**
     * Khôi phục khách hàng từ thùng rác.
     */
    public function restore(Customer $customer)
    {
        try {
            $customer->restore();
            return response()->json(['success' => true, 'message' => "Đã khôi phục khách hàng '{$customer->name}'."]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục Khách hàng (ID: {$customer->id}): " . $e->getMessage());
            return response()->json(['message' => 'Không thể khôi phục khách hàng này.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn khách hàng.
     */
    public function forceDelete(Request $request, Customer $customer)
    {
        $request->validate(['admin_password_confirm_delete' => 'required|string']);

        $configPassword = Config::get('admin.deletion_password');
        if ($configPassword && $request->input('admin_password_confirm_delete') === $configPassword) {
            // Mật khẩu đúng
        } else if (!$configPassword && Hash::check($request->input('admin_password_confirm_delete'), Auth::guard('admin')->user()->password)) {
            // Mật khẩu đúng
        } else {
            return response()->json(['errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        try {
            if ($customer->img && Storage::disk('public')->exists($customer->img)) {
                Storage::disk('public')->delete($customer->img);
            }
            $customer->forceDelete();
            return response()->json(['success' => true, 'message' => 'Đã xóa vĩnh viễn khách hàng!']);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa vĩnh viễn Khách hàng (ID: {$customer->id}): " . $e->getMessage());
            return response()->json(['message' => 'Không thể xóa vĩnh viễn khách hàng này.'], 500);
        }
    }
    public function getAddressesApi(Customer $customer): JsonResponse
    {
        // Tải các địa chỉ cùng với thông tin tỉnh/huyện/xã
        $addresses = $customer->addresses()->with(['province', 'district', 'ward'])->get()->map(function ($address) {
            // Tạo một chuỗi địa chỉ đầy đủ để hiển thị
            $address->full_address_string = implode(', ', array_filter([
                $address->address_line,
                optional($address->ward)->name,
                optional($address->district)->name,
                optional($address->province)->name,
            ]));
            return $address;
        });

        return response()->json($addresses);
    }
}
