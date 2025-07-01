<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade
use Database\Seeders\OrderSeeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\VehicleBrandSeeder;
use Database\Seeders\VehicleModelSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\DeliveryServiceSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\PromotionSeeder;
// use Database\Seeders\BlogPostSeeder; // Uncomment if you have BlogPostSeeder
// use Database\Seeders\ReviewSeeder; // Uncomment if you have ReviewSeeder

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạm thời tắt kiểm tra khóa ngoại để có thể truncate các bảng có quan hệ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // TRUNCATE CÁC BẢNG THEO THỨ TỰ AN TOÀN (từ con đến cha)
        // Đây là nơi chúng ta đảm bảo không có ràng buộc khóa ngoại nào bị vi phạm khi xóa dữ liệu.
        DB::table('order_items')->truncate();
        DB::table('product_vehicle_models')->truncate();
        DB::table('product_images')->truncate();
        DB::table('reviews')->truncate();
        DB::table('cart_items')->truncate();
        DB::table('carts')->truncate();
        DB::table('orders')->truncate();
        DB::table('products')->truncate();
        DB::table('customer_addresses')->truncate();
        DB::table('customer_saved_payment_methods')->truncate();
        DB::table('customers')->truncate();
        DB::table('promotions')->truncate();
        DB::table('delivery_services')->truncate();
        DB::table('payment_methods')->truncate();
        DB::table('blog_posts')->truncate(); // Nếu có bảng blog_posts
        DB::table('vehicle_models')->truncate();
        DB::table('vehicle_brands')->truncate();
        DB::table('brands')->truncate();
        DB::table('categories')->truncate();
        DB::table('wards')->truncate();
        DB::table('districts')->truncate();
        DB::table('provinces')->truncate();
        DB::table('admins')->truncate(); // Truncate admin cuối cùng hoặc đầu tiên tùy thuộc vào mối quan hệ của nó


        // Gọi các seeder theo thứ tự phụ thuộc (từ cha đến con)
        $this->call([
            // Đảm bảo VietnamZoneImporter chạy trước để có dữ liệu địa lý
            // VietnamZoneImporter::class, // Bỏ comment nếu bạn cần import dữ liệu địa lý từ file CSV
            AdminSeeder::class, // Tạo tài khoản admin
            // Địa lý cần chạy trước nếu các seeder khác phụ thuộc vào nó
            // Nếu VietnamZoneImporter không được dùng, đảm bảo bạn có dữ liệu trong provinces, districts, wards
            // Hoặc tạo giả định trong các seeder phụ thuộc (như CustomerSeeder)
            CategorySeeder::class, // Tạo danh mục
            BrandSeeder::class, // Tạo thương hiệu
            VehicleBrandSeeder::class, // Tạo hãng xe
            VehicleModelSeeder::class, // Tạo dòng xe (phụ thuộc VehicleBrand)
            ProductSeeder::class, // Tạo sản phẩm (phụ thuộc Category, Brand, VehicleModel)
            CustomerSeeder::class, // Tạo khách hàng (phụ thuộc Province, District, Ward)
            DeliveryServiceSeeder::class, // Tạo dịch vụ giao hàng
            PaymentMethodSeeder::class, // Tạo phương thức thanh toán
            PromotionSeeder::class, // Tạo khuyến mãi
            OrderSeeder::class, // Tạo đơn hàng (phụ thuộc Customer, Product, DeliveryService, Promotion, PaymentMethod, Province, District, Ward)
            // BlogPostSeeder::class, // Nếu bạn có blog posts
            // ReviewSeeder::class, // Nếu bạn có reviews
        ]);

        // Bật lại kiểm tra khóa ngoại sau khi đã seed xong
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
