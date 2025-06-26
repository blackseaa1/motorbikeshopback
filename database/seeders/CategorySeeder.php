<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str; // Có thể bỏ nếu không dùng cho mục đích khác

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Phụ tùng động cơ',
            'Phụ tùng khung gầm',
            'Hệ thống phanh',
            'Hệ thống điện',
            'Lốp xe',
            'Phụ kiện độ xe',
            'Dầu nhớt và hóa chất',
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    // 'slug' => Str::slug($categoryName), // Đã bỏ trường slug
                    'description' => 'Mô tả cho danh mục ' . $categoryName,
                ]
            );
        }
    }
}