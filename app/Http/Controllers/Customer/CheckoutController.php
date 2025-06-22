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

        $customerAddresses = Auth::guard('customer')->check()
            ? Auth::guard('customer')->user()->addresses()->with(['province', 'district', 'ward'])->get()
            : collect();

        // Lấy danh sách tỉnh/thành cho khách vãng lai
        $provinces = Province::orderBy('name')->get();

        return view('customer.checkout.index', compact('cartDetails', 'deliveryServices', 'customerAddresses', 'provinces'));
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
            'payment_method' => ['required', Rule::in(['cod', 'vnpay'])],
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

            // Fetch shipping fee
            $deliveryService = DeliveryService::find($validatedData['delivery_service_id']);
            if (!$deliveryService) {
                return back()->with('error', 'Dịch vụ vận chuyển không hợp lệ.')->withInput();
            }

            // Get subtotal, shipping_fee, and discount_amount from CartManager or recalculate
            $subtotal = $cartDetails['subtotal'];
            $shippingFee = $deliveryService->shipping_fee; // Get shipping fee from the selected service
            $discountAmount = $cartDetails['discount_amount']; // Get discount from CartManager after applying promo

            // Calculate total price including subtotal, shipping, and discount
            $totalPrice = $subtotal + $shippingFee - $discountAmount;

            $order = Order::create([
                'customer_id' => $customer?->id,
                'guest_name' => $customer ? null : $shippingAddressInfo['name'],
                'guest_email' => $customer ? null : $shippingAddressInfo['email'],
                'guest_phone' => $customer ? null : $shippingAddressInfo['phone'],
                'shipping_address_line' => $shippingAddressInfo['full_address_line'],
                'province_id' => $shippingAddressInfo['province_id'],
                'district_id' => $shippingAddressInfo['district_id'],
                'ward_id' => $shippingAddressInfo['ward_id'],
                'status' => Order::STATUS_PENDING,
                'total_price' => $totalPrice, // Update to calculated total_price
                'subtotal' => $subtotal, // ADD THIS LINE
                'shipping_fee' => $shippingFee, // ADD THIS LINE
                'discount_amount' => $discountAmount, // ADD THIS LINE
                'promotion_id' => $cartDetails['promotion_info']->id ?? null,
                'delivery_service_id' => $validatedData['delivery_service_id'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'] ?? null,
                'created_by_admin_id' => null, // Set to null for customer orders
            ]);

            foreach ($cartDetails['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                // Deduct stock quantity
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('stock_quantity', $item->quantity);
                }
            }

            DB::commit();

            $this->cartManager->clear();

            if ($customer) {
                return redirect()->route('account.orders.show', $order->id)
                    ->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng.');
            } else {
                return redirect()->route('guest.order.show', $order->id)
                    ->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng. Vui lòng ghi lại **Mã đơn hàng: #' . $order->id . '** và email/số điện thoại bạn đã dùng để tra cứu sau này.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng: ' . $e->getMessage());
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

        $order->load(['items.product.images', 'deliveryService', 'promotion', 'province', 'district', 'ward']);

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
        $oldStatus = $order->status; // Lấy trạng thái cũ trước khi thay đổi

        // 1. Xác thực quyền hủy đơn hàng
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

        // 2. Kiểm tra trạng thái đơn hàng có cho phép hủy không
        if (!$order->isCancellable()) {
            return back()->with('error', 'Đơn hàng này không thể hủy vì đã được xử lý hoặc hoàn thành.');
        }

        DB::beginTransaction();
        try {
            // 3. Cập nhật trạng thái đơn hàng thành 'cancelled'
            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            // SỬA ĐỔI: Chỉ hoàn trả số lượng sản phẩm vào kho nếu trạng thái cũ là ĐÃ DUYỆT
            if ($oldStatus === Order::STATUS_APPROVED) {
                $order->load('items.product'); // Tải lại items.product để đảm bảo dữ liệu mới nhất
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                // SỬA ĐỔI: Giảm số lượt sử dụng của mã giảm giá (nếu có)
                if ($order->promotion_id) {
                    $promotion = Promotion::find($order->promotion_id);
                    if ($promotion && $promotion->uses_count > 0) { // Đảm bảo uses_count không âm
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
