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
use App\Models\Product;
use Illuminate\Validation\Rule;

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
        // 1. Kiểm tra giỏ hàng có trống không
        if ($this->cartManager->getCartCount() === 0) {
            return redirect()->route('cart.index')->with('info', 'Giỏ hàng của bạn đang trống để đặt hàng.');
        }

        /** @var \App\Models\Customer|null $customer */
        $customer = Auth::guard('customer')->user();

        // 2. Validate dữ liệu gửi lên
        $validationRules = [
            'payment_method' => ['required', Rule::in(['cod', 'vnpay'])],
            'delivery_service_id' => ['required', 'exists:delivery_services,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($customer) {
            // Quy tắc cho khách hàng đã đăng nhập
            $validationRules['shipping_address_id'] = ['required', Rule::exists('customer_addresses', 'id')->where('customer_id', $customer->id)];
        } else {
            // Quy tắc cho khách vãng lai
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

        // 3. Lấy thông tin chi tiết cuối cùng của giỏ hàng
        $cartDetails = $this->cartManager->getCartDetails();

        DB::beginTransaction();
        try {
            // 4. Lấy thông tin địa chỉ giao hàng
            $shippingAddressInfo = $this->getShippingAddressInfo($validatedData, $customer);

            // 5. Tạo đơn hàng (Order)
            $order = Order::create([
                'customer_id' => $customer?->id,
                'guest_name' => $shippingAddressInfo['name'],
                'guest_email' => $shippingAddressInfo['email'],
                'guest_phone' => $shippingAddressInfo['phone'],
                'shipping_address' => $shippingAddressInfo['full_address'],
                'status' => Order::STATUS_PENDING, // Trạng thái chờ xử lý
                'total_price' => $cartDetails['grand_total'],
                'subtotal' => $cartDetails['subtotal'],
                'shipping_fee' => $cartDetails['shipping_fee'],
                'discount_amount' => $cartDetails['discount_amount'],
                'promotion_id' => $cartDetails['promotion']['id'] ?? null,
                'delivery_service_id' => $validatedData['delivery_service_id'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            // 6. Tạo các mục trong đơn hàng (Order Items) và trừ kho
            foreach ($cartDetails['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['product']['price'], // Lưu giá tại thời điểm mua
                ]);

                // Trừ số lượng tồn kho
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            DB::commit(); // Hoàn tất giao dịch

            // 7. Xóa giỏ hàng
            $this->cartManager->clear();

            // 8. Chuyển hướng tới trang chi tiết đơn hàng vừa tạo
            return redirect()->route('account.orders.show', $order->id)
                ->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã mua hàng.');
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu có lỗi
            // Ghi log lỗi để debug
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
