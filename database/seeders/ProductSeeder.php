<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
// use App\Models\VehicleBrand; // Không cần thiết nếu vehicle_brand_id bị loại bỏ

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy IDs của các category, brand đã seed
        $categoryIds = Category::pluck('id')->all();
        $brandIds = Brand::pluck('id')->all();

        // Đảm bảo có ít nhất một category và brand để tạo sản phẩm
        if (empty($categoryIds) || empty($brandIds)) {
            $this->call(CategorySeeder::class);
            $this->call(BrandSeeder::class);
            $categoryIds = Category::pluck('id')->all();
            $brandIds = Brand::pluck('id')->all();
        }

        $products = [
            [
                'name' => 'Heo dầu Brembo M4',
                'description' => 'Heo dầu Brembo M4 chính hãng, cải thiện hiệu suất phanh.',
                // 'long_description' đã bị loại bỏ
                'price' => 5500000,
                'stock_quantity' => 20,
                'status' => 'active',
                // 'weight' đã bị loại bỏ
                // 'dimensions' đã bị loại bỏ
                'category_name' => 'Hệ thống phanh',
                'brand_name' => 'Brembo',
            ],
            [
                'name' => 'Phuộc Ohlin bình dầu sau',
                'description' => 'Phuộc Ohlin cao cấp cho hiệu suất giảm xóc tối ưu.',
                // 'long_description' đã bị loại bỏ
                'price' => 12000000,
                'stock_quantity' => 15,
                'status' => 'active',
                // 'weight' đã bị loại bỏ
                // 'dimensions' đã bị loại bỏ
                'category_name' => 'Phụ tùng khung gầm',
                'brand_name' => 'Ohlin',
            ],
            [
                'name' => 'Pô Yoshimura R77 Full System',
                'description' => 'Hệ thống ống xả full system tăng cường sức mạnh và âm thanh.',
                // 'long_description' đã bị loại bỏ
                'price' => 8500000,
                'stock_quantity' => 10,
                'status' => 'active',
                // 'weight' đã bị loại bỏ
                // 'dimensions' đã bị loại bỏ
                'category_name' => 'Phụ tùng động cơ',
                'brand_name' => 'Yoshimura',
            ],
            [
                'name' => 'Lốp Michelin Pilot Road 5',
                'description' => 'Lốp xe hiệu suất cao, bám đường tốt trong mọi điều kiện.',
                // 'long_description' đã bị loại bỏ
                'price' => 2800000,
                'stock_quantity' => 30,
                'status' => 'active',
                // 'weight' đã bị loại bỏ
                // 'dimensions' đã bị loại bỏ
                'category_name' => 'Lốp xe',
                'brand_name' => 'Michelin',
            ],
            [
                'name' => 'Dầu nhớt Motul 300V Factory Line 10W40',
                'description' => 'Dầu nhớt tổng hợp cao cấp cho động cơ xe máy.',
                // 'long_description' đã bị loại bỏ
                'price' => 450000,
                'stock_quantity' => 50,
                'status' => 'active',
                // 'weight' đã bị loại bỏ
                // 'dimensions' đã bị loại bỏ
                'category_name' => 'Dầu nhớt và hóa chất',
                'brand_name' => 'Motul',
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('name', $productData['category_name'])->first();
            $brand = Brand::where('name', $productData['brand_name'])->first();

            if ($category && $brand) {
                Product::firstOrCreate(
                    ['name' => $productData['name']],
                    [
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        // 'vehicle_brand_id' đã được loại bỏ
                        'description' => $productData['description'],
                        // 'long_description' đã được loại bỏ
                        'price' => $productData['price'],
                        'stock_quantity' => $productData['stock_quantity'],
                        'status' => $productData['status'],
                        // 'weight' đã được loại bỏ
                        // 'dimensions' đã được loại bỏ
                    ]
                );
            } else {
                $this->command->warn("Bỏ qua sản phẩm '{$productData['name']}' do thiếu danh mục hoặc thương hiệu.");
            }
        }
    }
}