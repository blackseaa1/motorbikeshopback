<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\CustomerSavedPaymentMethod;
use App\Models\Order;
use App\Models\PaymentMethod; // Thêm import cho PaymentMethod
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
     */
    public function updateAccountInfo(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers')->ignore($customer->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id)],
        ]);

        $customer->update($validatedData);

        return back()->with('success', 'Cập nhật thông tin tài khoản thành công!');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validatedData = $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $customer->update(['password' => bcrypt($validatedData['password'])]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
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
     */
    public function showOrdersShow(Order $order)
    {
        // Đảm bảo người dùng hiện tại có quyền xem đơn hàng này
        if (Auth::guard('customer')->id() !== $order->customer_id) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        }

        // Tải các mối quan hệ cần thiết để hiển thị chi tiết
        $order->load(['items.product.images', 'deliveryService', 'promotion', 'province', 'district', 'ward', 'paymentMethod']);

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
