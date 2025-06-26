<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Thanh toán khi nhận hàng (COD)',
                'code' => 'COD', // Trường 'code' đã được thêm
                'description' => 'Thanh toán trực tiếp bằng tiền mặt khi đơn hàng được giao đến.',
                'status' => true, // Trường 'status' đã được thêm lại theo migration
            ],
            [
                'name' => 'Chuyển khoản ngân hàng',
                'code' => 'BANK_TRANSFER', // Trường 'code' đã được thêm
                'description' => 'Thanh toán bằng cách chuyển khoản vào tài khoản ngân hàng của cửa hàng.',
                'status' => true, // Trường 'status' đã được thêm lại
            ],
            [
                'name' => 'Thanh toán qua Momo',
                'code' => 'MOMO', // Trường 'code' đã được thêm
                'description' => 'Thanh toán nhanh chóng và tiện lợi qua ví điện tử Momo.',
                'status' => true, // Trường 'status' đã được thêm lại
            ],
            [
                'name' => 'Thanh toán qua ZaloPay',
                'code' => 'ZALOPAY', // Trường 'code' đã được thêm
                'description' => 'Thanh toán qua ví điện tử ZaloPay.',
                'status' => false, // Trường 'status' đã được thêm lại
            ],
        ];

        foreach ($paymentMethods as $methodData) {
            PaymentMethod::firstOrCreate(
                ['name' => $methodData['name']],
                $methodData
            );
        }
    }
}