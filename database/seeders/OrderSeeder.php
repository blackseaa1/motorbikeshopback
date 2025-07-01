<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\DeliveryService;
use App\Models\PaymentMethod;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $promotions = Promotion::all();
        $deliveryServices = DeliveryService::all();
        $paymentMethods = PaymentMethod::all();

        // Lấy hoặc tạo dữ liệu địa lý mặc định
        $defaultProvince = Province::first() ?? Province::create(['name' => 'Tỉnh/Thành phố mặc định', 'gso_id' => '00']);
        $defaultDistrict = District::first() ?? District::create(['name' => 'Quận/Huyện mặc định', 'gso_id' => '000', 'province_id' => $defaultProvince->id]);
        $defaultWard = Ward::first() ?? Ward::create(['name' => 'Phường/Xã mặc định', 'gso_id' => '0000', 'district_id' => $defaultDistrict->id]);

        if ($customers->isEmpty() || $products->isEmpty() || $deliveryServices->isEmpty() || $paymentMethods->isEmpty()) {
            $this->command->warn('Không đủ dữ liệu cơ sở để tạo đơn hàng. Hãy chạy ProductSeeder, CustomerSeeder, DeliveryServiceSeeder, PaymentMethodSeeder trước.');
            return;
        }

        // Đơn hàng cho khách hàng A (có tài khoản)
        $customerA = $customers->firstWhere('email', 'customer_a@example.com');
        if ($customerA) {
            $product1 = $products->firstWhere('name', 'Heo dầu Brembo M4');
            $product2 = $products->firstWhere('name', 'Dầu nhớt Motul 300V Factory Line 10W40');
            $promoWelcome = $promotions->firstWhere('code', 'WELCOME50K');
            $deliveryGHTK = $deliveryServices->firstWhere('name', 'Giao hàng Tiết kiệm');
            $paymentCOD = $paymentMethods->firstWhere('code', 'cod');

            if ($product1 && $product2 && $promoWelcome && $deliveryGHTK && $paymentCOD) {
                // Lấy địa chỉ mặc định của khách hàng A
                $addressA = $customerA->addresses()->where('is_default', true)->first() ?? $customerA->addresses()->first();

                $order = Order::firstOrCreate(
                    ['customer_id' => $customerA->id, 'created_at' => Carbon::now()->subDays(10)->startOfDay()],
                    [
                        'guest_name' => $customerA->name,
                        'guest_email' => $customerA->email,
                        'guest_phone' => $customerA->phone,
                        'shipping_address_line' => $addressA->address_line ?? 'Không có địa chỉ',
                        'province_id' => $addressA->province_id ?? $defaultProvince->id,
                        'district_id' => $addressA->district_id ?? $defaultDistrict->id,
                        'ward_id' => $addressA->ward_id ?? $defaultWard->id,
                        'payment_method_id' => $paymentCOD->id,
                        'delivery_service_id' => $deliveryGHTK->id,
                        'status' => Order::STATUS_COMPLETED, // Hoàn thành
                        'notes' => 'Giao hàng vào buổi sáng.',
                        'created_by_admin_id' => null,
                        'created_at' => Carbon::now()->subDays(10),
                    ]
                );

                if ($order->wasRecentlyCreated) {
                    $order->items()->create([
                        'product_id' => $product1->id,
                        'quantity' => 1,
                        'price' => $product1->price,
                    ]);
                    $order->items()->create([
                        'product_id' => $product2->id,
                        'quantity' => 2,
                        'price' => $product2->price,
                    ]);

                    // Cập nhật tổng tiền và giảm giá sau khi có item
                    $subtotal = ($product1->price * 1) + ($product2->price * 2);
                    $discount = $promoWelcome->calculateDiscount($subtotal);
                    $totalPrice = $subtotal + $deliveryGHTK->shipping_fee - $discount;

                    $order->update([
                        'subtotal' => $subtotal,
                        'shipping_fee' => $deliveryGHTK->shipping_fee,
                        'discount_amount' => $discount,
                        'total_price' => $totalPrice,
                        'promotion_id' => $promoWelcome->id,
                    ]);
                    $promoWelcome->increment('uses_count'); // Tăng lượt sử dụng
                }
            }
        }

        // Đơn hàng cho khách vãng lai (GUEST)
        $product3 = $products->firstWhere('name', 'Pô Yoshimura R77 Full System');
        $product4 = $products->firstWhere('name', 'Lốp Michelin Pilot Road 5');
        $deliveryGHN = $deliveryServices->firstWhere('name', 'Giao hàng Nhanh');
        $paymentBANK = $paymentMethods->firstWhere('code', 'bank_transfer');

        if ($product3 && $product4 && $deliveryGHN && $paymentBANK) {
            $orderGuest = Order::firstOrCreate(
                ['guest_email' => 'guest@example.com', 'created_at' => Carbon::now()->subDays(5)->startOfDay()],
                [
                    'customer_id' => null,
                    'guest_name' => 'Khách vãng lai',
                    'guest_email' => 'guest@example.com',
                    'guest_phone' => '0912345678',
                    'shipping_address_line' => 'Số 123 Đường ABC',
                    'province_id' => $defaultProvince->id,
                    'district_id' => $defaultDistrict->id,
                    'ward_id' => $defaultWard->id,
                    'payment_method_id' => $paymentBANK->id,
                    'delivery_service_id' => $deliveryGHN->id,
                    'status' => Order::STATUS_PENDING, // Đang chờ thanh toán
                    'notes' => null,
                    'created_by_admin_id' => null,
                    'created_at' => Carbon::now()->subDays(5),
                ]
            );

            if ($orderGuest->wasRecentlyCreated) {
                $orderGuest->items()->create([
                    'product_id' => $product3->id,
                    'quantity' => 1,
                    'price' => $product3->price,
                ]);
                $orderGuest->items()->create([
                    'product_id' => $product4->id,
                    'quantity' => 1,
                    'price' => $product4->price,
                ]);

                $subtotalGuest = ($product3->price * 1) + ($product4->price * 1);
                $totalPriceGuest = $subtotalGuest + $deliveryGHN->shipping_fee;

                $orderGuest->update([
                    'subtotal' => $subtotalGuest,
                    'shipping_fee' => $deliveryGHN->shipping_fee,
                    'discount_amount' => 0,
                    'total_price' => $totalPriceGuest,
                    'promotion_id' => null,
                ]);
            }
        }

        // Đơn hàng bị hủy
        $product5 = $products->firstWhere('name', 'Phuộc Ohlin bình dầu sau');
        if ($product5 && $customerA && $deliveryGHTK && $paymentCOD) {
            $orderCancelled = Order::firstOrCreate(
                ['customer_id' => $customerA->id, 'status' => Order::STATUS_CANCELLED, 'created_at' => Carbon::now()->subDays(20)->startOfDay()],
                [
                    'guest_name' => $customerA->name,
                    'guest_email' => $customerA->email,
                    'guest_phone' => $customerA->phone,
                    'shipping_address_line' => $customerA->addresses->first()->address_line ?? 'Không có địa chỉ',
                    'province_id' => $customerA->addresses->first()->province_id ?? $defaultProvince->id,
                    'district_id' => $customerA->addresses->first()->district_id ?? $defaultDistrict->id,
                    'ward_id' => $customerA->addresses->first()->ward_id ?? $defaultWard->id,
                    'payment_method_id' => $paymentCOD->id,
                    'delivery_service_id' => $deliveryGHTK->id,
                    'status' => Order::STATUS_CANCELLED,
                    'notes' => 'Khách hàng yêu cầu hủy đơn.',
                    'created_by_admin_id' => null,
                    'created_at' => Carbon::now()->subDays(20),
                ]
            );
            if ($orderCancelled->wasRecentlyCreated) {
                $orderCancelled->items()->create([
                    'product_id' => $product5->id,
                    'quantity' => 1,
                    'price' => $product5->price,
                ]);
                $orderCancelled->update([
                    'subtotal' => $product5->price,
                    'shipping_fee' => $deliveryGHTK->shipping_fee,
                    'discount_amount' => 0,
                    'total_price' => $product5->price + $deliveryGHTK->shipping_fee,
                    'promotion_id' => null,
                ]);
            }
        }
    }
}