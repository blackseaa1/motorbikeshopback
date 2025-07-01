<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // Gọi các seeder theo thứ tự phụ thuộc (nếu có)
        // AdminSeeder được giả định là đã tồn tại hoặc không cần tạo lại như yêu cầu người dùng
        $this->call(AdminSeeder::class); // Đảm bảo có admin để tạo blog posts

        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(VehicleBrandSeeder::class);
        $this->call(VehicleModelSeeder::class);
        $this->call(ProductSeeder::class);

        $this->call(CustomerSeeder::class);
        // CustomerAddressSeeder phụ thuộc vào địa lý (Province, District, Ward)
        // và Customer. Vì người dùng đã loại trừ địa lý,
        // nếu không có dữ liệu địa lý, seeder này có thể thất bại
        $this->call(CustomerAddressSeeder::class);

        $this->call(PaymentMethodSeeder::class);
        $this->call(DeliveryServiceSeeder::class);

        // Các seeder này có thể tạo ra dữ liệu phức tạp hơn và có thể phụ thuộc vào nhau
        $this->call(PromotionSeeder::class);
        $this->call(CartSeeder::class); // Phụ thuộc Customer và Product
        $this->call(OrderSeeder::class); // Phụ thuộc Customer, Product, DeliveryService, PaymentMethod, Promotion, Address
        $this->call(ReviewSeeder::class); // Phụ thuộc Customer và Product
        $this->call(BlogPostSeeder::class); // Phụ thuộc Admin và Customer (cho author_type)

        // Các seeder khác nếu có, ví dụ:
        // $this->call(OtherSeeder::class);
    }
}
