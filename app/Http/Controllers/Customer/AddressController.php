<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    /**
     * Hiển thị trang danh sách địa chỉ (Sổ địa chỉ).
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses()->with(['province', 'district', 'ward'])->get();
        return view('customer.account.addresses.index', compact('addresses'));
    }

    /**
     * Hiển thị form thêm địa chỉ mới.
     */
    public function create()
    {
        $provinces = Province::all();
        return view('customer.account.addresses.create', compact('provinces'));
    }

    /**
     * Lưu địa chỉ mới vào database.
     */
    public function store(Request $request)
    {
        $validated = $this->validateAddress($request);

        $customer = Auth::guard('customer')->user();

        $address = $customer->addresses()->create($validated);

        if ($request->boolean('is_default')) {
            $this->setDefaultAddress($address);
        }

        return redirect()->route('account.addresses.index')->with('success', 'Thêm địa chỉ mới thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa địa chỉ.
     */
    public function edit(CustomerAddress $address)
    {
        // Chính sách bảo mật: Đảm bảo khách hàng chỉ sửa được địa chỉ của chính mình.
        abort_if($address->customer_id !== Auth::guard('customer')->id(), 403);

        $provinces = Province::all();
        return view('customer.account.addresses.edit', compact('address', 'provinces'));
    }

    /**
     * Cập nhật địa chỉ đã có.
     */
    public function update(Request $request, CustomerAddress $address)
    {
        abort_if($address->customer_id !== Auth::guard('customer')->id(), 403);

        $validated = $this->validateAddress($request);
        $address->update($validated);

        if ($request->boolean('is_default')) {
            $this->setDefaultAddress($address);
        }

        return redirect()->route('account.addresses.index')->with('success', 'Cập nhật địa chỉ thành công!');
    }

    /**
     * Xóa một địa chỉ.
     */
    public function destroy(CustomerAddress $address)
    {
        abort_if($address->customer_id !== Auth::guard('customer')->id(), 403);

        $address->delete();

        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    /**
     * Đặt một địa chỉ làm mặc định.
     */
    public function setDefault(CustomerAddress $address)
    {
        abort_if($address->customer_id !== Auth::guard('customer')->id(), 403);

        $this->setDefaultAddress($address);

        return back()->with('success', 'Đã đặt địa chỉ làm mặc định.');
    }

    /**
     * Logic để đặt địa chỉ mặc định.
     */
    protected function setDefaultAddress(CustomerAddress $address)
    {
        // Bỏ tất cả các địa chỉ khác khỏi trạng thái mặc định
        $address->customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        // Đặt địa chỉ hiện tại làm mặc định
        $address->update(['is_default' => true]);
    }

    /**
     * Logic validate dùng chung cho store và update.
     */
    protected function validateAddress(Request $request)
    {
        return $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:15'],
            'address_line' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'ward_id' => 'required|exists:wards,id',
            'is_default' => 'nullable|boolean',
        ]);
    }

    public function getAddressesByCustomerApi(\App\Models\Customer $customer): \Illuminate\Http\JsonResponse
    {
        // Kiểm tra quyền: chỉ admin hoặc chính khách hàng mới có thể xem địa chỉ này
        if (!Auth::guard('admin')->check() && (!Auth::guard('customer')->check() || Auth::guard('customer')->id() !== $customer->id)) {
            abort(403, 'Unauthorized');
        }

        $addresses = $customer->addresses()->with(['province', 'district', 'ward'])->get();

        return response()->json($addresses);
    }
}
