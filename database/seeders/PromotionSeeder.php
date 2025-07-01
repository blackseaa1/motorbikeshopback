<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $promotions = [
            [
                'code' => 'WELCOME50K',
                'description' => 'Giảm 50.000 VNĐ cho khách hàng mới khi mua đơn hàng đầu tiên.',
                'discount_percentage' => null,
                'fixed_discount_amount' => 50000,
                'max_discount_amount' => null,
                'min_order_amount' => 200000,
                'start_date' => Carbon::today()->subDays(7),
                'end_date' => Carbon::today()->addMonths(1),
                'max_uses' => 100,
                'uses_count' => 0,
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_FIXED,
            ],
            [
                'code' => 'SALE10PERCENT',
                'description' => 'Giảm 10% cho tất cả các sản phẩm.',
                'discount_percentage' => 10,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 500000,
                'min_order_amount' => 500000,
                'start_date' => Carbon::today()->subDays(15),
                'end_date' => Carbon::today()->addDays(15),
                'max_uses' => null,
                'uses_count' => 0,
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE,
            ],
            [
                'code' => 'FREESHIPNOVIP',
                'description' => 'Miễn phí vận chuyển cho đơn hàng từ 1.000.000 VNĐ.',
                'discount_percentage' => null,
                'fixed_discount_amount' => 30000, // Fixed shipping discount
                'max_discount_amount' => 30000, // Max discount if there was a fee to cover
                'min_order_amount' => 1000000,
                'start_date' => Carbon::today()->subMonths(1),
                'end_date' => Carbon::today()->addMonths(2),
                'max_uses' => null,
                'uses_count' => 0,
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_FIXED, // Treating free shipping as a fixed discount
            ],
            [
                'code' => 'HOLIDAY25',
                'description' => 'Giảm 25% cho tất cả sản phẩm trong dịp lễ lớn.',
                'discount_percentage' => 25,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 1000000,
                'min_order_amount' => null,
                'start_date' => Carbon::parse('2025-12-01'), // Lịch trình trong tương lai
                'end_date' => Carbon::parse('2025-12-31'),
                'max_uses' => 50,
                'uses_count' => 0,
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE,
            ],
            [
                'code' => 'EXPIREDCODE',
                'description' => 'Mã đã hết hạn sử dụng.',
                'discount_percentage' => 15,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 100000,
                'min_order_amount' => 100000,
                'start_date' => Carbon::today()->subMonths(2),
                'end_date' => Carbon::today()->subDays(1),
                'max_uses' => 10,
                'uses_count' => 5,
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE,
            ],
            [
                'code' => 'FULLUSE',
                'description' => 'Mã đã hết lượt sử dụng.',
                'discount_percentage' => 5,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 50000,
                'min_order_amount' => 100000,
                'start_date' => Carbon::today()->subWeek(),
                'end_date' => Carbon::today()->addWeek(),
                'max_uses' => 5,
                'uses_count' => 5, // Đã sử dụng hết lượt
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE,
            ],
            [
                'code' => 'DISABLEDPROMO',
                'description' => 'Mã bị vô hiệu hóa bởi quản trị viên.',
                'discount_percentage' => 20,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 200000,
                'min_order_amount' => 300000,
                'start_date' => Carbon::today()->subWeek(),
                'end_date' => Carbon::today()->addWeek(),
                'max_uses' => null,
                'uses_count' => 0,
                'status' => Promotion::STATUS_MANUAL_INACTIVE, // Bị vô hiệu hóa
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE,
            ],
            [
                'code' => 'MINORDERTEST',
                'description' => 'Giảm 100.000đ cho đơn hàng từ 1.500.000đ.',
                'discount_percentage' => null,
                'fixed_discount_amount' => 100000,
                'max_discount_amount' => null,
                'min_order_amount' => 1500000,
                'start_date' => Carbon::today()->subDays(5),
                'end_date' => Carbon::today()->addDays(5),
                'max_uses' => null,
                'uses_count' => 0,
                'status' => Promotion::STATUS_MANUAL_ACTIVE,
                'discount_type' => Promotion::DISCOUNT_TYPE_FIXED,
            ],
        ];

        foreach ($promotions as $promoData) {
            Promotion::firstOrCreate(
                ['code' => $promoData['code']],
                $promoData
            );
        }
    }
}