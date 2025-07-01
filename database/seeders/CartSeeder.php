<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::inRandomOrder()->take(5)->get(); // Lấy 5 sản phẩm ngẫu nhiên

        foreach ($customers as $customer) {
            // Mỗi khách hàng có thể có một giỏ hàng
            $cart = Cart::firstOrCreate(
                ['customer_id' => $customer->id],
                ['customer_id' => $customer->id]
            );

            // Thêm một số sản phẩm vào giỏ hàng
            $maxItems = rand(1, 3); // Mỗi giỏ có từ 1 đến 3 sản phẩm
            $addedProducts = [];

            for ($i = 0; $i < $maxItems; $i++) {
                $product = $products->random();
                // Đảm bảo không thêm trùng sản phẩm vào cùng một giỏ hàng
                if (!in_array($product->id, $addedProducts)) {
                    $quantity = rand(1, 3); // Số lượng từ 1 đến 3

                    // Thêm vào giỏ hàng
                    CartItem::firstOrCreate(
                        ['cart_id' => $cart->id, 'product_id' => $product->id],
                        [
                            'cart_id' => $cart->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                        ]
                    );
                    $addedProducts[] = $product->id;
                }
            }
        }
    }
}