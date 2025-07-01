<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Thanh toán khi nhận hàng (COD)',
                'code' => 'cod',
                'description' => 'Thanh toán trực tiếp bằng tiền mặt khi đơn hàng được giao đến.',
                'logo_path' => 'payment_methods/cod_logo.png', // Giả định có file này
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'Chuyển khoản ngân hàng',
                'code' => 'bank_transfer',
                'description' => 'Thanh toán bằng cách chuyển khoản vào tài khoản ngân hàng của cửa hàng.',
                'logo_path' => 'payment_methods/bank_logo.png', // Giả định có file này
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'Thanh toán qua MoMo',
                'code' => 'momo',
                'description' => 'Thanh toán nhanh chóng và tiện lợi qua ví điện tử MoMo.',
                'logo_path' => 'payment_methods/momo_logo.png', // Giả định có file này
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'Thanh toán qua VNPAY',
                'code' => 'vnpay',
                'description' => 'Thanh toán qua cổng thanh toán VNPAY.',
                'logo_path' => 'payment_methods/vnpay_logo.png', // Giả định có file này
                'status' => PaymentMethod::STATUS_ACTIVE,
            ],
            [
                'name' => 'Thanh toán qua ZaloPay',
                'code' => 'zalopay',
                'description' => 'Thanh toán qua ví điện tử ZaloPay.',
                'logo_path' => 'payment_methods/zalopay_logo.png', // Giả định có file này
                'status' => PaymentMethod::STATUS_INACTIVE, // Tạm ẩn
            ],
        ];

        foreach ($paymentMethods as $methodData) {
            PaymentMethod::firstOrCreate(
                ['code' => $methodData['code']], // Sử dụng 'code' làm tiêu chí duy nhất
                $methodData
            );
        }
    }
}