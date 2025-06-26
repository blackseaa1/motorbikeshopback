<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use Illuminate\Support\Str; // Có thể bỏ nếu không dùng cho mục đích khác

class VehicleBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleBrands = [
            ['name' => 'Honda', 'logo_url' => 'logos/honda_logo.png'],
            ['name' => 'Yamaha', 'logo_url' => 'logos/yamaha_logo.png'],
            ['name' => 'Suzuki', 'logo_url' => 'logos/suzuki_logo.png'],
            ['name' => 'Kawasaki', 'logo_url' => 'logos/kawasaki_logo.png'],
            ['name' => 'Ducati', 'logo_url' => 'logos/ducati_logo.png'],
            ['name' => 'BMW Motorrad', 'logo_url' => 'logos/bmw_motorrad_logo.png'],
        ];

        foreach ($vehicleBrands as $brandData) {
            VehicleBrand::firstOrCreate(
                ['name' => $brandData['name']],
                [
                    // 'slug' => Str::slug($brandData['name']), // Đã bỏ trường slug
                    'logo_url' => $brandData['logo_url'],
                    'description' => 'Thương hiệu xe máy ' . $brandData['name'],
                ]
            );
        }
    }
}