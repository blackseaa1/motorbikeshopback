<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Gọi các seeder theo thứ tự phụ thuộc (nếu có)
        $this->call(AdminSeeder::class); // Cho nhân viên (admin)
        $this->call(CustomerSeeder::class); // Cho khách hàng
        $this->call(PaymentMethodSeeder::class); // Cho phương thức thanh toán
        $this->call(DeliveryServiceSeeder::class); // Cho đơn vị giao hàng

        // Các seeder khác đã tạo trước đó
        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(VehicleBrandSeeder::class);
        $this->call(VehicleModelSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(BlogPostSeeder::class);
        $this->call(PromotionSeeder::class);
    }
}
