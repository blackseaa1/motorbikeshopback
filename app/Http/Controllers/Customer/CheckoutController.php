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
            $shippingFee = 0; // Phí vận chuyển luôn là 0
            $discountAmount = $cartDetails['discount_amount'];
            $totalPrice = $subtotal + $shippingFee - $discountAmount;

            // Lấy thông tin phương thức thanh toán
            $paymentMethod = PaymentMethod::find($validatedData['payment_method_id']);
            if (!$paymentMethod) {
                DB::rollBack();
                return back()->with('error', 'Phương thức thanh toán không hợp lệ.')->withInput();
            }

            // Xác định trạng thái ban đầu của đơn hàng
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

            DB::commit();

            // DI CHUYỂN DÒNG NÀY: cartManager->clear() chỉ gọi khi đã xác định được chuyển hướng thành công
            // $this->cartManager->clear();

            // LOGIC MỚI: Xử lý chuyển hướng dựa trên phương thức thanh toán
            if ($paymentMethod->code === 'cod') {
                $this->cartManager->clear(); // Xóa giỏ hàng khi COD
                if ($customer) {
                    return redirect()->route('account.orders.show', $order->id)
                        ->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng.');
                } else {
                    return redirect()->route('guest.order.show', $order->id)
                        ->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng. Vui lòng ghi lại **Mã đơn hàng: #' . $order->id . '** và email/số điện thoại bạn đã dùng để tra cứu sau này.');
                }
            } else if ($paymentMethod->code === 'momo') {
                $this->cartManager->clear(); // Xóa giỏ hàng khi chuyển hướng đến Momo
                // Chuyển hướng đến controller Momo để khởi tạo thanh toán
                return redirect()->route('payment.momo.initiate', ['order_id' => $order->id]);
            }
            // else if ($paymentMethod->code === 'vnpay') {
            //     $this->cartManager->clear(); // Xóa giỏ hàng khi chuyển hướng đến Vnpay
            //     return redirect()->route('payment.vnpay.initiate', ['order_id' => $order->id]);
            // }
            else if ($paymentMethod->code === 'bank_transfer') {
                $this->cartManager->clear(); // Xóa giỏ hàng khi chuyển hướng đến trang hướng dẫn chuyển khoản
                return redirect()->route('payment.bank_transfer.details', ['order_id' => $order->id])
                    ->with('info', 'Vui lòng chuyển khoản với nội dung đơn hàng để hoàn tất thanh toán.');
            } else {
                // Nếu đến đây, có nghĩa là paymentMethod->code không khớp với bất kỳ case nào
                // Đây là nơi lỗi của bạn đang xảy ra
                DB::rollBack(); // Hoàn tác đơn hàng nếu không xử lý được
                Log::error('Phương thức thanh toán này hiện không khả dụng hoặc chưa tích hợp: ' . $paymentMethod->code . ' - Order ID: ' . $order->id);
                return back()->with('error', 'Phương thức thanh toán này hiện không khả dụng. Vui lòng chọn phương thức khác.')->withInput();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            // Giỏ hàng không bị xóa nếu có lỗi trước khi commit DB
            return back()->with('error', 'Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * Hiển thị trang chi tiết đơn hàng cho khách vãng lai.
     */
    public function showGuestOrder(Order $order)
    {
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

    public function lookupGuestOrder(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'string'],
            'guest_contact' => ['required', 'string'],
        ]);

        $orderId = trim($request->input('order_id'));
        $guestContact = trim($request->input('guest_contact'));

        $order = Order::find($orderId);

        if (!$order) {
            return back()->with('error', 'Mã đơn hàng không tồn tại. Vui lòng kiểm tra lại.')->withInput();
        }

        $isGuestOrder = ($order->customer_id === null);
        $contactMatches = ($order->guest_email === $guestContact || $order->guest_phone === $guestContact);

        if ($isGuestOrder && $contactMatches) {
            return redirect()->route('guest.order.show', $order->id)
                ->with('success', 'Tìm thấy đơn hàng của bạn.');
        } else {
            return back()->with('error', 'Thông tin tra cứu không hợp lệ. Vui lòng kiểm tra mã đơn hàng và email/số điện thoại đã dùng khi đặt hàng.')->withInput();
        }
    }

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
            return back()->with('error', 'Đã xảy ra lỗi khi hủy đơn hàng. Vui lòng thử lại.')->withInput();
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
                'phone' => $address->phone,
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
