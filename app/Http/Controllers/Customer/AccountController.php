<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\CustomerSavedPaymentMethod;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Import Storage facade

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('customer.account.index');
    }

    /**
     * Show the account info form.
     */
    public function showAccountInfo()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.account.info', compact('customer'));
    }

    /**
     * Update account info.
     * Phương thức này tương ứng với route 'account.updateProfile' trong info.blade.php
     */
    public function updateInfo(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers')->ignore($customer->id)],
        ]);

        $customer->update([
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
        ]);

        // SỬA ĐỔI: Thay $request->ajax() bằng $request->wantsJson()
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin cá nhân thành công!',
                'customer' => $customer
            ]);
        }

        return back()->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }


    /**
     * Change password.
     * Phương thức này tương ứng với route 'account.updatePassword' trong info.blade.php
     */
    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validatedData = $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.regex' => 'Mật khẩu mới phải bao gồm ít nhất 8 ký tự, 1 chữ hoa, 1 chữ thường, 1 chữ số và 1 ký tự đặc biệt.',
        ]);

        $customer->update(['password' => bcrypt($validatedData['password'])]);

        // SỬA ĐỔI: Thay $request->ajax() bằng $request->wantsJson()
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công!'
            ]);
        }

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Update avatar.
     * Phương thức này tương ứng với route 'account.updateAvatar' trong info.blade.php
     */
    public function updateAvatar(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($customer->img && !str_contains($customer->img, 'default_avatar.png')) {
                Storage::disk('public')->delete($customer->img);
            }

            $path = $request->file('avatar')->store('avatars/customers', 'public');
            $customer->img = $path;
            $customer->save();

            // SỬA ĐỔI: Thay $request->ajax() bằng $request->wantsJson()
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật ảnh đại diện thành công!',
                    'avatar_url' => $customer->avatar_url
                ]);
            }

            return back()->with('success', 'Cập nhật ảnh đại diện thành công!');
        }

        // SỬA ĐỔI: Thay $request->ajax() bằng $request->wantsJson()
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có ảnh nào được tải lên.'
            ], 400);
        }

        return back()->with('error', 'Không có ảnh nào được tải lên.');
    }


    /**
     * SỬA ĐỔI: Hiển thị danh sách đơn hàng của khách hàng với tìm kiếm, lọc, sắp xếp.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function showOrdersIndex(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        $query = $customer->orders()->with(['paymentMethod', 'deliveryService']);

        // Tìm kiếm theo ID đơn hàng hoặc tên sản phẩm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%') // Tìm kiếm theo ID đơn hàng
                    ->orWhereHas('items.product', function ($p) use ($search) { // Tìm kiếm theo tên sản phẩm trong đơn hàng
                        $p->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Lọc theo trạng thái đơn hàng
        if ($statusFilter = $request->input('status_filter')) {
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }
        }

        // Sắp xếp đơn hàng
        $sortBy = $request->input('sort_by', 'created_at_desc'); // Mặc định sắp xếp theo ngày tạo giảm dần
        switch ($sortBy) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'total_price_desc':
                $query->orderBy('total_price', 'desc');
                break;
            case 'total_price_asc':
                $query->orderBy('total_price', 'asc');
                break;
            case 'status_asc': // Sắp xếp theo bảng chữ cái của trạng thái
                $query->orderBy('status', 'asc');
                break;
            case 'status_desc': // Sắp xếp theo bảng chữ cái của trạng thái
                $query->orderBy('status', 'desc');
                break;
            default: // created_at_desc
                $query->orderBy('created_at', 'desc');
                break;
        }

        $orders = $query->paginate(10)->withQueryString(); // Phân trang và giữ lại query string

        // Truyền các giá trị đã chọn ra view để giữ trạng thái trên form
        $selectedFilters = [
            'search' => $search,
            'status_filter' => $statusFilter,
            'sort_by' => $sortBy,
        ];

        // Lấy danh sách các trạng thái để hiển thị trong bộ lọc
        $orderStatuses = Order::STATUSES;

        return view('customer.account.orders_index', compact('orders', 'selectedFilters', 'orderStatuses'));
    }

    /**
     * SỬA ĐỔI: Hiển thị chi tiết một đơn hàng của khách hàng.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */ public function showOrdersShow(Order $order)
    {
        // Đảm bảo người dùng hiện tại có quyền xem đơn hàng này
        if (Auth::guard('customer')->id() !== $order->customer_id) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        }

        // SỬA ĐỔI: Thêm 'customer' vào mảng load()
        $order->load(['items.product.images', 'deliveryService', 'paymentMethod', 'customer', 'province', 'district', 'ward']);

        return view('customer.account.orders_show', compact('order'));
    }

    /**
     * Show the saved payment methods list.
     */
    public function showSavedPaymentMethodsIndex()
    {
        $customer = Auth::guard('customer')->user();
        $savedPaymentMethods = $customer->savedPaymentMethods()->with('paymentMethod')->get();
        $paymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)->get();

        return view('customer.account.saved_payment_methods.index', compact('savedPaymentMethods', 'paymentMethods'));
    }
}
