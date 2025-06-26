<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'code' => 'WELCOME50K',
                'description' => 'Giảm 50.000 VNĐ cho khách hàng mới khi mua đơn hàng đầu tiên.',
                'discount_percentage' => null, // Dùng cho giảm phần trăm, null cho giảm cố định
                'fixed_discount_amount' => 50000, // Dùng cho giảm cố định, null cho giảm phần trăm
                'max_discount_amount' => null,
                'min_order_amount' => 200000,
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addMonths(1),
                'max_uses' => 100, // Số lần sử dụng tối đa
                'uses_count' => 0, // Số lần đã sử dụng
                'status' => 'active', // Trạng thái 'active' hoặc 'scheduled'
                'discount_type' => Promotion::DISCOUNT_TYPE_FIXED, // Sử dụng hằng số đã được định nghĩa trong model
            ],
            [
                'code' => 'BRAKE10',
                'description' => 'Giảm 10% cho tất cả các sản phẩm thuộc danh mục "Hệ thống phanh".',
                'discount_percentage' => 10,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 500000,
                'min_order_amount' => 500000,
                'start_date' => Carbon::today()->subDays(7),
                'end_date' => Carbon::today()->addDays(15),
                'max_uses' => null,
                'uses_count' => 0,
                'status' => 'active',
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE, // Sử dụng hằng số đã được định nghĩa trong model
            ],
            [
                'code' => 'FREESHIPVN',
                'description' => 'Miễn phí vận chuyển cho đơn hàng từ 1.000.000 VNĐ.',
                'discount_percentage' => null,
                'fixed_discount_amount' => null,
                'max_discount_amount' => null,
                'min_order_amount' => 1000000,
                'start_date' => Carbon::today()->subMonths(1),
                'end_date' => Carbon::today()->addMonths(2),
                'max_uses' => null,
                'uses_count' => 0,
                'status' => 'active',
                'discount_type' => 'free_shipping', // Dùng giá trị chuỗi vì hằng số TYPE_FREE_SHIPPING không có trong Promotion.php được cung cấp
            ],
            [
                'code' => 'HOLIDAY25',
                'description' => 'Giảm 25% cho tất cả sản phẩm trong dịp lễ lớn.',
                'discount_percentage' => 25,
                'fixed_discount_amount' => null,
                'max_discount_amount' => 1000000,
                'min_order_amount' => null,
                'start_date' => Carbon::parse('2025-12-01'),
                'end_date' => Carbon::parse('2025-12-31'),
                'max_uses' => 50,
                'uses_count' => 0,
                'status' => 'scheduled',
                'discount_type' => Promotion::DISCOUNT_TYPE_PERCENTAGE, // Sử dụng hằng số đã được định nghĩa trong model
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