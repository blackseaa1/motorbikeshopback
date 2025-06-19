<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

/**
 * Quản lý giỏ hàng cho cả khách đã đăng nhập (database) và khách vãng lai (session).
 */
class CartManager
{
    protected const SESSION_KEY = 'cart';

    /**
     * Thêm hoặc cập nhật một sản phẩm trong giỏ hàng.
     *
     * @param int $productId
     * @param int $quantity
     * @return void
     */
    public function add(int $productId, int $quantity = 1): void
    {
        if (Auth::guard('customer')->check()) {
            $this->addToDatabase($productId, $quantity);
        } else {
            $this->addToSession($productId, $quantity);
        }
    }

    /**
     * Lấy toàn bộ giỏ hàng.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCartItems(): Collection
    {
        if (Auth::guard('customer')->check()) {
            $cart = Auth::guard('customer')->user()->cart()->with('items.product.images')->first();
            return $cart ? $cart->items : collect();
        }

        // Đối với khách, tạo collection từ session để có cấu trúc tương tự
        $sessionCart = session(self::SESSION_KEY, []);
        if (empty($sessionCart)) {
            return collect();
        }

        $productIds = array_keys($sessionCart);
        $products = Product::with('images')->find($productIds);

        return collect($sessionCart)->map(function ($quantity, $productId) use ($products) {
            $product = $products->find($productId);
            if (!$product) {
                return null;
            }
            // Tạo một object giả có cấu trúc giống CartItem
            return (object) [
                'product_id' => $productId,
                'quantity' => $quantity,
                'product' => $product,
                'subtotal' => $product->price * $quantity
            ];
        })->filter(); // Loại bỏ các item null nếu product không tìm thấy
    }

    /**
     * Lấy tổng số lượng sản phẩm trong giỏ.
     *
     * @return int
     */
    public function getCartCount(): int
    {
        if (Auth::guard('customer')->check()) {
            $cart = Auth::guard('customer')->user()->cart;
            return $cart ? $cart->item_count : 0;
        }

        return collect(session(self::SESSION_KEY, []))->sum();
    }


    /**
     * Gộp giỏ hàng từ session vào database sau khi người dùng đăng nhập.
     *
     * @return void
     */
    public function mergeSessionCartToDatabase(): void
    {
        $sessionCart = session(self::SESSION_KEY, []);
        if (empty($sessionCart) || !Auth::guard('customer')->check()) {
            return;
        }

        foreach ($sessionCart as $productId => $quantity) {
            $this->addToDatabase($productId, $quantity);
        }

        // Xóa giỏ hàng trong session sau khi đã gộp
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Xử lý thêm sản phẩm vào database cho người dùng đã đăng nhập.
     */
    protected function addToDatabase(int $productId, int $quantity): void
    {
        $customer = Auth::guard('customer')->user();
        $cart = $customer->cart()->firstOrCreate([]);

        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * Xử lý thêm sản phẩm vào session cho khách vãng lai.
     */
    protected function addToSession(int $productId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);
        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }
        session([self::SESSION_KEY => $cart]);
    }
}
