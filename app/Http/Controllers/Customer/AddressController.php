<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\District;
use App\Models\Ward;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource (customer addresses).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();
        // Lấy tất cả địa chỉ của khách hàng và phân trang 10 địa chỉ mỗi trang.
        $addresses = $customer->addresses()->with(['province', 'district', 'ward'])->paginate(10);
        return view('customer.account.addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $provinces = Province::orderBy('name')->get();
        return view('customer.account.addresses.create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validatedData = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'exists:provinces,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'is_default' => ['boolean'],
        ]);

        // Nếu địa chỉ mới được đặt làm mặc định, đặt tất cả các địa chỉ khác của khách hàng về không mặc định
        if (isset($validatedData['is_default']) && $validatedData['is_default']) {
            $customer->addresses()->update(['is_default' => false]);
        } else {
            // Nếu đây là địa chỉ đầu tiên và không được đặt làm mặc định, tự động đặt làm mặc định
            if ($customer->addresses->isEmpty()) {
                $validatedData['is_default'] = true;
            }
        }

        $customer->addresses()->create($validatedData);

        return redirect()->route('account.addresses.index')->with('success', 'Thêm địa chỉ mới thành công!');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerAddress  $address
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(CustomerAddress $address)
    {
        // Kiểm tra xem địa chỉ có thuộc về khách hàng hiện tại không
        if (Auth::guard('customer')->id() !== $address->customer_id) {
            return redirect()->route('account.addresses.index')->with('error', 'Bạn không có quyền chỉnh sửa địa chỉ này.');
        }

        $provinces = Province::orderBy('name')->get();
        $districts = District::where('province_id', $address->province_id)->orderBy('name')->get();
        $wards = Ward::where('district_id', $address->district_id)->orderBy('name')->get();

        return view('customer.account.addresses.edit', compact('address', 'provinces', 'districts', 'wards'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerAddress  $address
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CustomerAddress $address)
    {
        // Kiểm tra quyền sở hữu
        if (Auth::guard('customer')->id() !== $address->customer_id) {
            return redirect()->route('account.addresses.index')->with('error', 'Bạn không có quyền chỉnh sửa địa chỉ này.');
        }

        $validatedData = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'exists:provinces,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'is_default' => ['boolean'],
        ]);

        // Nếu địa chỉ hiện tại được đặt làm mặc định, đặt tất cả các địa chỉ khác của khách hàng về không mặc định
        if (isset($validatedData['is_default']) && $validatedData['is_default']) {
            Auth::guard('customer')->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validatedData);

        return redirect()->route('account.addresses.index')->with('success', 'Cập nhật địa chỉ thành công!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerAddress  $address
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CustomerAddress $address)
    {
        // Kiểm tra quyền sở hữu
        if (Auth::guard('customer')->id() !== $address->customer_id) {
            return redirect()->route('account.addresses.index')->with('error', 'Bạn không có quyền xóa địa chỉ này.');
        }

        // Không cho phép xóa địa chỉ mặc định nếu có địa chỉ khác
        if ($address->is_default && Auth::guard('customer')->user()->addresses()->count() > 1) {
            return back()->with('error', 'Không thể xóa địa chỉ mặc định khi còn địa chỉ khác. Vui lòng đặt một địa chỉ khác làm mặc định trước.');
        }

        $address->delete();

        // Nếu không còn địa chỉ nào, hoặc địa chỉ bị xóa là địa chỉ mặc định cuối cùng,
        // không cần đặt lại mặc định. Laravel sẽ tự động xử lý.
        return redirect()->route('account.addresses.index')->with('success', 'Xóa địa chỉ thành công!');
    }

    /**
     * Set the specified address as default.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\CustomerAddress $address
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setDefault(Request $request, CustomerAddress $address)
    {
        // Kiểm tra quyền sở hữu
        if (Auth::guard('customer')->id() !== $address->customer_id) {
            return redirect()->route('account.addresses.index')->with('error', 'Bạn không có quyền đặt địa chỉ này làm mặc định.');
        }

        // Đặt tất cả các địa chỉ khác về không mặc định
        Auth::guard('customer')->user()->addresses()->update(['is_default' => false]);

        // Đặt địa chỉ được chọn làm mặc định
        $address->update(['is_default' => true]);

        return redirect()->route('account.addresses.index')->with('success', 'Địa chỉ đã được đặt làm mặc định thành công!');
    }
}
