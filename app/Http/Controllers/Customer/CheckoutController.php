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
        if ($this->cartManager->getCartCount() === 0) {
            return redirect()->route('cart.index')->with('info', 'Giỏ hàng của bạn đang trống.');
        }

        $cartDetails = $this->cartManager->getCartDetails();
        $deliveryServices = DeliveryService::where('status', 'active')->get();

        $paymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)->get();

        $customerAddresses = Auth::guard('customer')->check()
            ? Auth::guard('customer')->user()->addresses()->with(['province', 'district', 'ward'])->get()
            : collect();

        $provinces = Province::orderBy('name')->get();

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
     */
    public function placeOrder(Request $request)
    {
        if ($this->cartManager->getCartCount() === 0) {
            return redirect()->route('cart.index')->with('info', 'Giỏ hàng của bạn đang trống để đặt hàng.');
        }

        /** @var \App\Models\Customer|null $customer */
        $customer = Auth::guard('customer')->user();

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

        if ($customer) {
            $validationRules['shipping_address_id'] = ['required', Rule::exists('customer_addresses', 'id')->where('customer_id', $customer->id)];
        } else {
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

        $validatedData = $request->validate($validationRules);
        $cartDetails = $this->cartManager->getCartDetails();

        DB::beginTransaction();
        try {
            $shippingAddressInfo = $this->getShippingAddressInfo($validatedData, $customer);
            $deliveryServiceId = $validatedData['delivery_service_id'];

            $subtotal = $cartDetails['subtotal'];
            $shippingFee = 0;
            $discountAmount = $cartDetails['discount_amount'];
            $totalPrice = $subtotal + $shippingFee - $discountAmount;

            $paymentMethod = PaymentMethod::find($validatedData['payment_method_id']);
            if (!$paymentMethod) {
                DB::rollBack();
                return back()->with('error', 'Phương thức thanh toán không hợp lệ.')->withInput();
            }

            $initialStatus = Order::STATUS_PENDING;

            $order = Order::create([
                'customer_id' => $customer?->id,
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
                'created_by_admin_id' => null,
            ]);

            foreach ($cartDetails['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            DB::commit();

            $this->cartManager->clear();

            // Gửi email xác nhận đơn hàng sau khi tạo thành công
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

            // Logic chuyển hướng dựa trên phương thức thanh toán
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
                DB::rollBack();
                Log::error('Phương thức thanh toán này hiện không khả dụng hoặc chưa tích hợp: ' . $paymentMethod->code . ' - Order ID: ' . $order->id);
                return back()->with('error', 'Phương thức thanh toán này hiện không khả dụng. Vui lòng chọn phương thức khác.')->withInput();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return back()->with('error', 'Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * Hiển thị trang chi tiết đơn hàng cho khách vãng lai.
     */
    public function showGuestOrder(Order $order)
    {
        // Logic truy cập cho khách vãng lai sẽ được điều chỉnh để kiểm tra số điện thoại
        // hoặc email nếu khách hàng đến từ trang tra cứu.
        // Hiện tại, chỉ kiểm tra xem nó có phải đơn hàng khách vãng lai không.
        if ($order->customer_id !== null) {
            return redirect()->route('guest.order.lookup')->with('error', 'Đơn hàng này không phải của khách vãng lai hoặc yêu cầu đăng nhập để xem.');
        }

        $order->load(['items.product.images', 'deliveryService', 'promotion', 'province', 'district', 'ward', 'paymentMethod']);

        return view('customer.checkout.guest_order_confirmation', compact('order'));
    }

    public function showOrderLookupForm()
    {
        return view('customer.checkout.guest_order_lookup');
    }

    /**
     * SỬA ĐỔI: Xử lý tra cứu đơn hàng của khách vãng lai.
     * Sẽ tìm kiếm và hiển thị danh sách đơn hàng tương tự như khách có tài khoản.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function lookupGuestOrder(Request $request)
    {
        // Validate guest_phone only on initial POST request for lookup
        $validationRules = ['guest_phone' => ['required', 'string', 'max:255']];

        // If it's a GET request (after filters are applied), validate only if guest_phone is present
        // Otherwise, it means user is trying to apply filters on an already looked up list
        if ($request->isMethod('GET') && !$request->has('guest_phone')) {
            // If it's a GET request and guest_phone is NOT present, it's an invalid lookup attempt without initial contact
            // Or it's a direct visit to the list page without lookup, which is not allowed.
            return redirect()->route('guest.order.lookup')->with('error', 'Vui lòng nhập số điện thoại hoặc email để tra cứu đơn hàng.');
        }

        // If it's a POST, validate and store in session. If it's a GET with guest_phone, use it.
        $guestContact = $request->isMethod('POST')
            ? trim($request->validate($validationRules)['guest_phone'])
            : trim($request->input('guest_phone'));

        // Store guestContact in session to persist across pagination/filters
        $request->session()->flash('guest_contact', $guestContact);


        $query = Order::whereNull('customer_id') // Đảm bảo là đơn hàng của khách vãng lai
            ->where(function ($q) use ($guestContact) {
                $q->where('guest_phone', $guestContact)
                    ->orWhere('guest_email', $guestContact);
            });

        // Áp dụng tìm kiếm, lọc, sắp xếp (tương tự như AccountController)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%') // Tìm kiếm theo ID đơn hàng
                    ->orWhereHas('items.product', function ($p) use ($search) { // Tìm kiếm theo tên sản phẩm trong đơn hàng
                        $p->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($statusFilter = $request->input('status_filter')) {
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }
        }

        $sortBy = $request->input('sort_by', 'created_at_desc'); // Mặc định là 'Mới nhất'

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
                // Sắp xếp trạng thái theo thứ tự ưu tiên tăng dần (Đang xử lý -> Đã giao -> Đã hủy)
                $query->orderByRaw("CASE
                    WHEN status IN ('pending', 'processing', 'shipped') THEN 1
                    WHEN status = 'delivered' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    ELSE 4
                END ASC");
                break;
            case 'status_desc':
                // Sắp xếp trạng thái theo thứ tự ưu tiên giảm dần (Đã hủy -> Đã giao -> Đang xử lý)
                $query->orderByRaw("CASE
                    WHEN status IN ('pending', 'processing', 'shipped') THEN 1
                    WHEN status = 'delivered' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    ELSE 4
                END DESC");
                break;
            default: // Xử lý trường hợp 'created_at_desc' và các trường hợp mặc định khác
                $query->orderBy('created_at', 'desc');
                // Áp dụng sắp xếp phụ theo trạng thái khi sắp xếp chính là 'Ngày đặt (Mới nhất)'
                // Sắp xếp trạng thái theo thứ tự ưu tiên (Đang xử lý -> Đã giao -> Đã hủy)
                $query->orderByRaw("CASE
                    WHEN status IN ('pending', 'processing', 'shipped') THEN 1
                    WHEN status = 'delivered' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    ELSE 4
                END ASC");
                break;
        }

        $orders = $query->paginate(10)->withQueryString(); // Phân trang

        // Truyền các giá trị đã chọn ra view để giữ trạng thái trên form
        $selectedFilters = [
            'search' => $search,
            'status_filter' => $statusFilter,
            'sort_by' => $sortBy,
            'guest_phone' => $guestContact, // Đảm bảo truyền lại contact đã tra cứu
        ];

        $orderStatuses = Order::STATUSES;

        // SỬA ĐỔI: Trả về view guest_order_list
        return view('customer.checkout.guest_order_list', compact('orders', 'selectedFilters', 'orderStatuses'));
    }
    // ... (Toàn bộ phần còn lại của file giữ nguyên không thay đổi) ...

    /**
     * Xử lý yêu cầu hủy đơn hàng.
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
