<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryService;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Province;
use App\Support\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // Lấy thông tin giỏ hàng và thanh toán từ session/manager
        $cartDetails = $this->cartManager->getCartDetails();

        // Validate dữ liệu chung
        $validatorRules = [
            'payment_method' => 'required|string|in:cod,bank_transfer',
            'delivery_service_id' => 'required|exists:delivery_services,id',
            'notes' => 'nullable|string|max:1000',
        ];

        $customer = Auth::guard('customer')->user();

        if ($customer) { // Nếu khách hàng đã đăng nhập
            $validatorRules['shipping_address_id'] = 'required|exists:customer_addresses,id,customer_id,' . $customer->id;
        } else { // Nếu là khách vãng lai
            $validatorRules['guest_name'] = 'required|string|max:255';
            $validatorRules['guest_phone'] = ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:15'];
            $validatorRules['guest_email'] = 'required|email|max:255';
            $validatorRules['guest_address_line'] = 'required|string|max:255';
            $validatorRules['guest_province_id'] = 'required|exists:provinces,id';
            $validatorRules['guest_district_id'] = 'required|exists:districts,id';
            $validatorRules['guest_ward_id'] = 'required|exists:wards,id';
        }

        $validatedData = $request->validate($validatorRules);

        // Bắt đầu một transaction để đảm bảo toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            // Lấy thông tin địa chỉ
            $shippingAddressInfo = $this->getShippingAddressInfo($validatedData, $customer);

            // Tạo đơn hàng
            $order = Order::create([
                'customer_id' => $customer?->id,
                'promotion_id' => $cartDetails['promotion_info']?->id,
                'delivery_service_id' => $validatedData['delivery_service_id'],

                'customer_name' => $shippingAddressInfo['name'],
                'customer_email' => $shippingAddressInfo['email'],
                'customer_phone' => $shippingAddressInfo['phone'],
                'shipping_address' => $shippingAddressInfo['full_address'],

                'notes' => $validatedData['notes'],
                'payment_method' => $validatedData['payment_method'],

                'subtotal' => $cartDetails['subtotal'],
                'shipping_fee' => $cartDetails['shipping_fee'],
                'discount_amount' => $cartDetails['discount_amount'],
                'total_amount' => $cartDetails['grand_total'],
                'status' => Order::STATUS_PENDING,
            ]);

            // Thêm các sản phẩm trong giỏ vào đơn hàng
            foreach ($cartDetails['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
                // Trừ số lượng tồn kho
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            // Tăng số lượt sử dụng của mã giảm giá (nếu có)
            if ($cartDetails['promotion_info']) {
                $cartDetails['promotion_info']->increment('uses_count');
            }

            // Xóa giỏ hàng và các thông tin liên quan trong session
            $this->cartManager->clear();
            $this->cartManager->clearCheckoutData();

            DB::commit();

            // Chuyển đến trang cảm ơn hoặc chi tiết đơn hàng
            return redirect()->route('home')->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log lỗi và báo cho người dùng
            // Log::error('Lỗi khi đặt hàng: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * Helper để lấy thông tin địa chỉ từ request.
     */
    private function getShippingAddressInfo(array $validatedData, ?Customer $customer): array
    {
        if ($customer) {
            $address = CustomerAddress::with(['province', 'district', 'ward'])->find($validatedData['shipping_address_id']);
            return [
                'name' => $address->full_name,
                'email' => $customer->email,
                'phone' => $address->phone,
                'full_address' => "{$address->address_line}, {$address->ward->name}, {$address->district->name}, {$address->province->name}",
            ];
        }

        // Tải các quan hệ địa lý cho khách vãng lai
        $province = \App\Models\Province::find($validatedData['guest_province_id']);
        $district = \App\Models\District::find($validatedData['guest_district_id']);
        $ward = \App\Models\Ward::find($validatedData['guest_ward_id']);

        return [
            'name' => $validatedData['guest_name'],
            'email' => $validatedData['guest_email'],
            'phone' => $validatedData['guest_phone'],
            'full_address' => "{$validatedData['guest_address_line']}, {$ward->name}, {$district->name}, {$province->name}",
        ];
    }
}
