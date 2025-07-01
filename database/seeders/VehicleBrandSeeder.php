<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use Illuminate\Support\Str;

class VehicleBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $vehicleBrands = [
            [
                'name' => 'Honda',
                'description' => 'Hãng xe máy hàng đầu Nhật Bản.',
                'logo_url' => 'vehicle_brand_logos/honda_logo.png',
                'status' => VehicleBrand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Yamaha',
                'description' => 'Hãng xe máy đa dạng sản phẩm từ Nhật Bản.',
                'logo_url' => 'vehicle_brand_logos/yamaha_logo.png',
                'status' => VehicleBrand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Suzuki',
                'description' => 'Hãng xe máy và ô tô từ Nhật Bản.',
                'logo_url' => 'vehicle_brand_logos/suzuki_logo.png',
                'status' => VehicleBrand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Kawasaki',
                'description' => 'Hãng xe máy thể thao hiệu suất cao từ Nhật Bản.',
                'logo_url' => 'vehicle_brand_logos/kawasaki_logo.png',
                'status' => VehicleBrand::STATUS_ACTIVE,
            ],
            [
                'name' => 'Ducati',
                'description' => 'Thương hiệu mô tô thể thao cao cấp từ Ý.',
                'logo_url' => 'vehicle_brand_logos/ducati_logo.png',
                'status' => VehicleBrand::STATUS_ACTIVE,
            ],
            [
                'name' => 'BMW Motorrad',
                'description' => 'Thương hiệu mô tô hạng sang từ Đức.',
                'logo_url' => 'vehicle_brand_logos/bmw_motorrad_logo.png',
                'status' => VehicleBrand::STATUS_ACTIVE,
            ],
            [
                'name' => 'KTM',
                'description' => 'Nhà sản xuất mô tô địa hình và thể thao từ Áo.',
                'logo_url' => 'vehicle_brand_logos/ktm_logo.png',
                'status' => VehicleBrand::STATUS_INACTIVE, // Ví dụ: Tạm ẩn
            ],
        ];

        foreach ($vehicleBrands as $brandData) {
            VehicleBrand::firstOrCreate(
                ['name' => $brandData['name']],
                $brandData
            );
        }
    }
}