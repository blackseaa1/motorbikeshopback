<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Brembo',
                'description' => 'Thương hiệu phanh xe máy nổi tiếng toàn cầu từ Ý.',
                'logo_url' => 'brand_logos/brembo_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Ohlin',
                'description' => 'Nhà sản xuất hệ thống treo cao cấp của Thụy Điển.',
                'logo_url' => 'brand_logos/ohlin_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Yoshimura',
                'description' => 'Thương hiệu ống xả hiệu suất cao từ Nhật Bản.',
                'logo_url' => 'brand_logos/yoshimura_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Michelin',
                'description' => 'Thương hiệu lốp xe hàng đầu thế giới từ Pháp.',
                'logo_url' => 'brand_logos/michelin_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Motul',
                'description' => 'Dầu nhớt và chất bôi trơn cao cấp từ Pháp.',
                'logo_url' => 'brand_logos/motul_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Pirelli',
                'description' => 'Lốp xe hiệu suất cao và thể thao từ Ý.',
                'logo_url' => 'brand_logos/pirelli_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Castrol',
                'description' => 'Nhà sản xuất dầu nhớt hàng đầu toàn cầu.',
                'logo_url' => 'brand_logos/castrol_logo.png',
                'status' => Brand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Denso',
                'description' => 'Linh kiện ô tô, xe máy và hệ thống đánh lửa từ Nhật Bản.',
                'logo_url' => 'brand_logos/denso_logo.png',
                'status' => Brand::STATUS_INACTIVE, // Ví dụ: Tạm ẩn
            ],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(
                ['name' => $brandData['name']],
                $brandData
            );
        }
    }
}