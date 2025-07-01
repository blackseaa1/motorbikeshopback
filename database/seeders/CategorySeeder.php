<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Phụ tùng động cơ',
                'description' => 'Các loại phụ tùng và linh kiện dành cho động cơ xe máy.',
                'status' => Category::STATUS_ACTIVE,
            ],
           [
                'name' => 'Phụ tùng khung gầm',
                'description' => 'Các loại phụ tùng và linh kiện dành cho động cơ xe máy.',
                'status' => Category::STATUS_ACTIVE,
            ],
             [
                'name' => 'Hệ thống điện',
                'description' => 'Các loại phụ tùng và linh kiện dành cho động cơ xe máy.',
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Hệ thống phanh',
                'description' => 'Các thành phần của hệ thống phanh xe máy, đảm bảo an toàn vận hành.',
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Lốp xe',
                'description' => 'Đa dạng các loại lốp xe máy cho mọi địa hình và nhu cầu sử dụng.',
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Dầu nhớt và hóa chất',
                'description' => 'Các sản phẩm dầu nhớt, dầu thắng, nước làm mát và hóa chất bảo dưỡng xe.',
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Phụ kiện độ xe',
                'description' => 'Các phụ kiện giúp cá nhân hóa và nâng cấp hiệu suất xe máy.',
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Đèn và Điện',
                'description' => 'Các loại đèn chiếu sáng, còi, ắc quy và phụ tùng điện khác.',
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Hệ thống truyền động',
                'description' => 'Nhông sên đĩa, dây curoa và các bộ phận truyền động khác.',
                'status' => Category::STATUS_INACTIVE, // Ví dụ: tạm thời ẩn
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }
    }
}