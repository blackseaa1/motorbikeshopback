<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryService;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Support\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class CheckoutController extends Controller
{
    protected $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * Hiển thị trang thanh toán.
     */
    public function index()
    {
        // Kiểm tra nếu giỏ hàng trống, chuyển hướng về trang giỏ hàng với thông báo.
        if ($this->cartManager->getCartCount() === 0) {
            return redirect()->route('cart.index')->with('info', 'Giỏ hàng của bạn đang trống.');
        }

        // Lấy thông tin chi tiết giỏ hàng.
        $cartDetails = $this->cartManager->getCartDetails();
        // Lấy danh sách các dịch vụ vận chuyển đang hoạt động.
        $deliveryServices = DeliveryService::where('status', 'active')->get();
        // Lấy danh sách các phương thức thanh toán đang hoạt động.
        $paymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)->get();

        // Lấy địa chỉ của khách hàng nếu đã đăng nhập, nếu không thì là một collection rỗng.
        $customerAddresses = Auth::guard('customer')->check()
            ? Auth::guard('customer')->user()->addresses()->with(['province', 'district', 'ward'])->get()
            : collect();

        // Lấy danh sách tỉnh/thành phố để hiển thị trong form địa chỉ.
        $provinces = Province::orderBy('name')->get();

        // Trả về view trang thanh toán với các dữ liệu cần thiết.
        return view('customer.checkout.index', compact(
            'cartDetails',
            'deliveryServices',
            'customerAddresses',
            'provinces',
            'paymentMethods'
        ));
    }

    /**
     * Xử lý việc đặt hàng.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function placeOrder(Request $request)
    {
        // Nếu giỏ hàng trống, không cho phép đặt hàng.
        if ($this->cartManager->getCartCount() === 0) {
            return redirect()->route('cart.index')->with('info', 'Giỏ hàng của bạn đang trống để đặt hàng.');
        }

        /** @var \App\Models\Customer|null $customer */
        $customer = Auth::guard('customer')->user(); // Lấy thông tin khách hàng hiện tại (nếu có).

        // Định nghĩa các quy tắc kiểm tra dữ liệu đầu vào.
        $validationRules = [
            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where(function ($query) {
                    $query->where('status', \App\Models\PaymentMethod::STATUS_ACTIVE);
                }),
            ],
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Nếu khách hàng đã đăng nhập, yêu cầu ID địa chỉ giao hàng.
        if ($customer) {
            $validationRules['shipping_address_id'] = ['required', Rule::exists('customer_addresses', 'id')->where('customer_id', $customer->id)];
        } else {
            // Nếu là khách vãng lai, yêu cầu thông tin cá nhân và địa chỉ đầy đủ.
            $validationRules += [
                'guest_name' => ['required', 'string', 'max:255'],
                'guest_email' => ['required', 'email', 'max:255'],
                'guest_phone' => ['required', 'string', 'max:20'],
                'guest_address_line' => ['required', 'string', 'max:255'],
                'guest_province_id' => ['required', 'exists:provinces,id'],
                'guest_district_id' => ['required', 'exists:districts,id'],
                'guest_ward_id' => ['required', 'exists:wards,id'],
            ];
        }

        // Kiểm tra dữ liệu đầu vào.
        $validatedData = $request->validate($validationRules);
        $cartDetails = $this->cartManager->getCartDetails(); // Lấy lại chi tiết giỏ hàng sau khi xác thực.

        DB::beginTransaction(); // Bắt đầu giao dịch cơ sở dữ liệu để đảm bảo tính toàn vẹn.
        try {
            // Lấy thông tin địa chỉ giao hàng dựa trên việc khách hàng có đăng nhập hay không.
            $shippingAddressInfo = $this->getShippingAddressInfo($validatedData, $customer);
            $deliveryServiceId = $validatedData['delivery_service_id'];

            // Tính toán các giá trị tài chính của đơn hàng.
            $subtotal = $cartDetails['subtotal'];
            $shippingFee = 0; // Phí vận chuyển có thể được tính toán phức tạp hơn.
            $discountAmount = $cartDetails['discount_amount'];
            $totalPrice = $subtotal + $shippingFee - $discountAmount;

            // Tìm phương thức thanh toán đã chọn.
            $paymentMethod = PaymentMethod::find($validatedData['payment_method_id']);
            if (!$paymentMethod) {
                DB::rollBack(); // Hoàn tác giao dịch nếu phương thức thanh toán không hợp lệ.
                return back()->with('error', 'Phương thức thanh toán không hợp lệ.')->withInput();
            }

            $initialStatus = Order::STATUS_PENDING; // Trạng thái ban đầu của đơn hàng.

            // Tạo bản ghi đơn hàng mới trong cơ sở dữ liệu.
            $order = Order::create([
                'customer_id' => $customer?->id, // ID khách hàng nếu đã đăng nhập, ngược lại là null.
                'payment_method_id' => $paymentMethod->id,
                'guest_name' => $customer ? null : $shippingAddressInfo['name'],
                'guest_email' => $customer ? null : $shippingAddressInfo['email'],
                'guest_phone' => $customer ? null : $shippingAddressInfo['phone'],
                'shipping_address_line' => $shippingAddressInfo['full_address_line'],
                'province_id' => $shippingAddressInfo['province_id'],
                'district_id' => $shippingAddressInfo['district_id'],
                'ward_id' => $shippingAddressInfo['ward_id'],
                'status' => $initialStatus,
                'total_price' => $totalPrice,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'promotion_id' => $cartDetails['promotion_info']->id ?? null,
                'delivery_service_id' => $deliveryServiceId,
                'notes' => $validatedData['notes'] ?? null,
                'created_by_admin_id' => null, // Đơn hàng này được tạo bởi khách hàng, không phải admin.
            ]);

            // Thêm các mặt hàng từ giỏ hàng vào đơn hàng.
            foreach ($cartDetails['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price, // Lưu giá sản phẩm tại thời điểm đặt hàng.
                ]);
            }

            DB::commit(); // Hoàn tất giao dịch.

            $this->cartManager->clear(); // Xóa giỏ hàng sau khi đặt hàng thành công.

            // Gửi email xác nhận đơn hàng.
            try {
                $customerEmail = $customer->email ?? $shippingAddressInfo['email'];
                if ($customerEmail) {
                    Mail::to($customerEmail)->send(new OrderConfirmation($order));
                    Log::info('Order confirmation email sent to: ' . $customerEmail . ' for order #' . $order->id);
                } else {
                    Log::warning('No email address found for order #' . $order->id . ' to send confirmation.');
                }
            } catch (\Exception $e) {
                Log::error('Failed to send order confirmation email for order #' . $order->id . ': ' . $e->getMessage());
            }

            // Chuyển hướng người dùng dựa trên phương thức thanh toán đã chọn.
            if ($paymentMethod->code === 'cod') {
                return redirect()->route($customer ? 'account.orders.show' : 'guest.order.show', $order->id)
                    ->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng. Đơn hàng sẽ được xử lý khi nhận hàng.');
            } else if ($paymentMethod->code === 'momo') {
                return redirect()->route('payment.momo.initiate', ['order_id' => $order->id]);
            } else if ($paymentMethod->code === 'vnpay') {
                return redirect()->route('payment.vnpay.initiate', ['order_id' => $order->id]);
            } else if ($paymentMethod->code === 'bank_transfer') {
                return redirect()->route('payment.bank_transfer.details', ['order_id' => $order->id])
                    ->with('info', 'Đặt hàng thành công! Vui lòng chuyển khoản với nội dung đơn hàng để hoàn tất thanh toán.');
            } else {
                DB::rollBack(); // Hoàn tác nếu phương thức thanh toán không được xử lý.
                Log::error('Phương thức thanh toán này hiện không khả dụng hoặc chưa tích hợp: ' . $paymentMethod->code . ' - Order ID: ' . $order->id);
                return back()->with('error', 'Phương thức thanh toán này hiện không khả dụng. Vui lòng chọn phương thức khác.')->withInput();
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác giao dịch nếu có lỗi xảy ra.
            Log::error('Lỗi khi đặt hàng: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return back()->with('error', 'Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * Hiển thị trang chi tiết đơn hàng cho khách vãng lai.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showGuestOrder(Order $order)
    {
        // Đảm bảo chỉ khách vãng lai mới xem được đơn hàng khách vãng lai.
        if ($order->customer_id !== null) {
            return redirect()->route('guest.order.lookup')->with('error', 'Đơn hàng này không phải của khách vãng lai hoặc yêu cầu đăng nhập để xem.');
        }

        // Tải các mối quan hệ cần thiết cho đơn hàng.
        $order->load(['items.product.images', 'deliveryService', 'promotion', 'province', 'district', 'ward', 'paymentMethod']);

        // Trả về view xác nhận đơn hàng cho khách vãng lai.
        return view('customer.checkout.guest_order_confirmation', compact('order'));
    }

    /**
     * Xử lý tra cứu đơn hàng của khách vãng lai (sau khi form được gửi hoặc lọc/phân trang).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function lookupGuestOrder(Request $request)
    {
        $guestContact = null;

        // Xử lý khi người dùng truy cập trực tiếp URL mà không có query param 'guest_phone'
        // Đây là trường hợp người dùng muốn bắt đầu tra cứu mới
        if ($request->isMethod('GET') && !$request->has('guest_phone')) {
            $request->session()->forget('guest_contact_for_lookup'); // Xóa số điện thoại cũ trong session
            $request->session()->forget('error'); // Xóa thông báo lỗi cũ
            return view('customer.checkout.guest_order_lookup'); // Hiển thị form trống
        }

        // Xử lý khi có yêu cầu POST (từ form tra cứu)
        if ($request->isMethod('POST')) {
            $validatedData = $request->validate([
                'guest_phone' => ['required', 'string', 'max:255'],
            ]);
            $guestContact = trim($validatedData['guest_phone']);
            $request->session()->put('guest_contact_for_lookup', $guestContact); // Lưu vào session
        } else { // Xử lý khi có yêu cầu GET (từ bộ lọc hoặc phân trang)
            $guestContact = $request->query('guest_phone'); // Ưu tiên lấy từ query string
            if (empty($guestContact)) {
                $guestContact = $request->session()->get('guest_contact_for_lookup'); // Lấy từ session nếu không có trong query
            }
        }

        // Nếu vẫn không có guestContact, chuyển hướng về form tra cứu với lỗi
        if (empty($guestContact)) {
            return redirect()->route('guest.order.lookup')->with('error', 'Vui lòng nhập số điện thoại hoặc email để tra cứu đơn hàng.');
        }

        // Bắt đầu truy vấn đơn hàng
        $query = Order::whereNull('customer_id')
            ->where(function ($q) use ($guestContact) {
                $q->where('guest_phone', $guestContact)
                    ->orWhere('guest_email', $guestContact);
            });

        // Áp dụng tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhereHas('items.product', function ($p) use ($search) {
                        $p->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Áp dụng lọc theo trạng thái
        if ($statusFilter = $request->input('status_filter')) {
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }
        }

        // Áp dụng sắp xếp
        $sortBy = $request->input('sort_by', 'created_at_desc');

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
            case 'status_asc':
                $query->orderByRaw("CASE
                    WHEN status IN ('pending', 'processing', 'shipped') THEN 1
                    WHEN status = 'delivered' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    ELSE 4
                END ASC");
                break;
            case 'status_desc':
                $query->orderByRaw("CASE
                    WHEN status IN ('pending', 'processing', 'shipped') THEN 1
                    WHEN status = 'delivered' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    ELSE 4
                END DESC");
                break;
            default:
                $query->orderBy('created_at', 'desc');
                $query->orderByRaw("CASE
                    WHEN status IN ('pending', 'processing', 'shipped') THEN 1
                    WHEN status = 'delivered' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    ELSE 4
                END ASC");
                break;
        }

        // Phân trang và giữ lại các tham số truy vấn
        $orders = $query->paginate(10)->appends([
            'guest_phone' => $guestContact,
            'search' => $request->input('search'),
            'status_filter' => $request->input('status_filter'),
            'sort_by' => $request->input('sort_by'),
        ])->withQueryString();

        // Chuẩn bị các bộ lọc đã chọn để hiển thị lại trên form
        $selectedFilters = [
            'search' => $request->input('search'),
            'status_filter' => $request->input('status_filter'),
            'sort_by' => $sortBy,
            'guest_phone' => $guestContact,
        ];

        $orderStatuses = Order::STATUSES;

        return view('customer.checkout.guest_order_list', compact('orders', 'selectedFilters', 'orderStatuses'));
    }

    /**
     * Xử lý yêu cầu hủy đơn hàng.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelOrder(Request $request, Order $order)
    {
        $customer = Auth::guard('customer')->user();
        $oldStatus = $order->status;

        $isGuest = ($order->customer_id === null);

        if ($customer) {
            if ($order->customer_id !== $customer->id) {
                return back()->with('error', 'Bạn không có quyền hủy đơn hàng này.');
            }

            $request->validate([
                'password_confirm' => ['required', 'string'],
            ]);

            if (!Auth::guard('customer')->attempt(['email' => $customer->email, 'password' => $request->password_confirm])) {
                return back()->with('error', 'Mật khẩu xác nhận không đúng.')->withInput();
            }
        } elseif ($isGuest) {
            $request->validate([
                'guest_contact_confirm' => ['required', 'string'],
            ]);

            $guestContact = trim($request->input('guest_contact_confirm'));
            if (!($order->guest_email === $guestContact || $order->guest_phone === $guestContact)) {
                return back()->with('error', 'Xác thực thông tin không thành công. Vui lòng nhập đúng email hoặc số điện thoại đã dùng khi đặt hàng.')->withInput();
            }
        } else {
            return back()->with('error', 'Không thể xác định quyền hủy đơn hàng.');
        }

        if (!$order->isCancellable()) {
            return back()->with('error', 'Đơn hàng này không thể hủy vì đã được xử lý hoặc hoàn thành.');
        }

        DB::beginTransaction();
        try {
            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            if ($oldStatus === Order::STATUS_APPROVED) {
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    if ($promotion && $promotion->uses_count > 0) {
                        $promotion->decrement('uses_count');
                    }
                }
            }

            DB::commit();

            return back()->with('success', 'Đơn hàng #' . $order->id . ' đã được hủy thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hủy đơn hàng: ' . $e->getMessage() . ' - Order ID: ' . $order->id);
            return back()->with('error', 'Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.')->withInput();
        }
    }


    private function getShippingAddressInfo(array $validatedData, ?Customer $customer): array
    {
        $addressInfo = [];

        if ($customer) {
            $address = CustomerAddress::with(['province', 'district', 'ward'])->find($validatedData['shipping_address_id']);
            $addressInfo = [
                'name' => $address->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'province_id' => $address->province_id,
                'district_id' => $address->district_id,
                'ward_id' => $address->ward_id,
                'full_address_line' => $address->address_line,
            ];
        } else {
            $province = Province::find($validatedData['guest_province_id']);
            $district = District::find($validatedData['guest_district_id']);
            $ward = Ward::find($validatedData['guest_ward_id']);

            $addressInfo = [
                'name' => $validatedData['guest_name'],
                'email' => $validatedData['guest_email'],
                'phone' => $validatedData['guest_phone'],
                'province_id' => $validatedData['guest_province_id'],
                'district_id' => $validatedData['guest_district_id'],
                'ward_id' => $validatedData['guest_ward_id'],
                'full_address_line' => $validatedData['guest_address_line'],
            ];
        }

        return $addressInfo;
    }
}
