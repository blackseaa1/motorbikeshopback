<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Không đủ dữ liệu khách hàng hoặc sản phẩm để tạo đánh giá. Hãy chạy CustomerSeeder và ProductSeeder trước.');
            return;
        }

        // Lấy một số khách hàng và sản phẩm cụ thể để tạo đánh giá
        $customerA = $customers->firstWhere('email', 'customer_a@example.com');
        $customerB = $customers->firstWhere('email', 'customer_b@example.com');
        $productBrembo = $products->firstWhere('name', 'Heo dầu Brembo M4');
        $productMotul = $products->firstWhere('name', 'Dầu nhớt Motul 300V Factory Line 10W40');
        $productOhlin = $products->firstWhere('name', 'Phuộc Ohlin bình dầu sau');
        $productLS2 = $products->firstWhere('name', 'Nón bảo hiểm fullface LS2 FF800');


        // Đánh giá của Khách hàng A cho sản phẩm Brembo
        if ($customerA && $productBrembo) {
            Review::firstOrCreate(
                ['customer_id' => $customerA->id, 'product_id' => $productBrembo->id],
                [
                    'rating' => 5,
                    'comment' => 'Sản phẩm tuyệt vời, phanh ăn hơn hẳn. Rất đáng tiền!',
                    'status' => Review::STATUS_APPROVED,
                    'created_at' => Carbon::now()->subDays(5),
                ]
            );
        }

        // Đánh giá của Khách hàng B cho sản phẩm Motul (chờ duyệt)
        if ($customerB && $productMotul) {
            Review::firstOrCreate(
                ['customer_id' => $customerB->id, 'product_id' => $productMotul->id],
                [
                    'rating' => 4,
                    'comment' => 'Dầu nhớt tốt, xe chạy êm hơn. Giao hàng nhanh.',
                    'status' => Review::STATUS_PENDING,
                    'created_at' => Carbon::now()->subDays(3),
                ]
            );
        }

        // Đánh giá của Khách hàng A cho sản phẩm Ohlin (bị từ chối)
        if ($customerA && $productOhlin) {
            Review::firstOrCreate(
                ['customer_id' => $customerA->id, 'product_id' => $productOhlin->id],
                [
                    'rating' => 2,
                    'comment' => 'Phuộc khá cứng, không êm như mong đợi. Hơi thất vọng.',
                    'status' => Review::STATUS_REJECTED,
                    'created_at' => Carbon::now()->subDays(7),
                ]
            );
        }

        // Đánh giá của Khách hàng B cho sản phẩm LS2 (đã duyệt)
        if ($customerB && $productLS2) {
            Review::firstOrCreate(
                ['customer_id' => $customerB->id, 'product_id' => $productLS2->id],
                [
                    'rating' => 5,
                    'comment' => 'Mũ đẹp, đội rất thoải mái và an toàn. Rất hài lòng!',
                    'status' => Review::STATUS_APPROVED,
                    'created_at' => Carbon::now()->subDays(2),
                ]
            );
        }

        // Thêm một số đánh giá ngẫu nhiên cho các sản phẩm còn lại
        foreach ($customers as $customer) {
            foreach ($products as $product) {
                // Tránh tạo đánh giá trùng lặp hoặc cho các sản phẩm đã có đánh giá ở trên
                if (!Review::where('customer_id', $customer->id)->where('product_id', $product->id)->exists()) {
                    if (rand(0, 1)) { // 50% cơ hội tạo đánh giá
                        Review::firstOrCreate(
                            ['customer_id' => $customer->id, 'product_id' => $product->id],
                            [
                                'rating' => rand(3, 5),
                                'comment' => 'Sản phẩm này khá tốt, đáp ứng được nhu cầu của tôi.',
                                'status' => Review::STATUS_APPROVED,
                                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                            ]
                        );
                    }
                }
            }
        }
    }
}