<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str; // Có thể bỏ nếu không dùng cho mục đích khác

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Brembo',
            'Ohlin',
            'Yoshimura',
            'Michelin',
            'Pirelli',
            'Motul',
            'Castrol',
            'Denso',
        ];

        foreach ($brands as $brandName) {
            Brand::firstOrCreate(
                ['name' => $brandName],
                [
                    // 'slug' => Str::slug($brandName), // Đã bỏ trường slug
                    'description' => 'Mô tả cho thương hiệu ' . $brandName,
                ]
            );
        }
    }
}