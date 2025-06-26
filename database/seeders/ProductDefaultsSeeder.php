<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;

class ProductDefaultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo một vài danh mục mẫu (đã loại bỏ slug)
        Category::create(['name' => 'Nhớt & Phụ gia']);
        Category::create(['name' => 'Lốp & Vỏ xe']);
        Category::create(['name' => 'Đèn & Điện']);

        // Tạo một vài thương hiệu mẫu (đã loại bỏ slug)
        Brand::create(['name' => 'Honda']);
        Brand::create(['name' => 'Yamaha']);
        Brand::create(['name' => 'Motul']);
    }
}
