<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Promotion;
use App\Models\DeliveryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Đảm bảo import ValidationException

class CartManager
{
    protected const SESSION_KEY = 'cart';
    protected const SHIPPING_SESSION_KEY = 'cart_shipping';
    protected const PROMOTION_SESSION_KEY = 'cart_promotion';

    /**
     * Hàm helper: Tự động kiểm tra và xóa session ship/promo nếu giỏ hàng trống.
     */
    private function checkCartIsEmptyAndClearSessionData(): void
    {
        if ($this->getCartCount() === 0) {
            $this->clearCheckoutData();
        }
    }

    /**
     * Thêm sản phẩm vào giỏ.
     * Sửa đổi: Tự động xóa thông tin ship/promo cũ khi thêm sản phẩm mới từ các trang bên ngoài.
     */
    public function add(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        $currentQuantityInCart = $this->getQuantityInCart($productId);
        if (($currentQuantityInCart + $quantity) > $product->stock_quantity) {
            throw ValidationException::withMessages(['stock' => 'Số lượng sản phẩm trong kho không đủ.']);
        }

        // Tự động xóa thông tin ship/promo không còn liên quan khi người dùng quay lại mua sắm
        $this->clearCheckoutData();

        if (Auth::guard('customer')->check()) {
            $this->addToDatabase($productId, $quantity);
        } else {
            $this->addToSession($productId, $quantity);
        }
    }

    /**
     * Cập nhật số lượng và kiểm tra giỏ hàng trống.
     */
    public function update(int $productId, int $quantity): void
    {
        if (Auth::guard('customer')->check()) {
            $this->updateInDatabase($productId, $quantity);
        } else {
            $this->updateInSession($productId, $quantity);
        }
        $this->checkCartIsEmptyAndClearSessionData();
    }

    /**
     * Xóa sản phẩm và kiểm tra giỏ hàng trống.
     */
    public function remove(int $productId): void
    {
        if (Auth::guard('customer')->check()) {
            $this->removeFromDatabase($productId);
        } else {
            $this->removeFromSession($productId);
        }
        $this->checkCartIsEmptyAndClearSessionData();
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng.
     */
    public function getItems(): Collection
    {
        if (Auth::guard('customer')->check()) {
            $cart = Auth::guard('customer')->user()->cart()->with('items.product.images')->first();
            return $cart ? $cart->items : collect();
        }

        $sessionCart = session(self::SESSION_KEY, []);
        if (empty($sessionCart)) {
            return collect();
        }

        $productIds = array_keys($sessionCart);
        $products = Product::with('images')->find($productIds);

        return $products->map(function ($product) use ($sessionCart) {
            $product->quantity = $sessionCart[$product->id]['quantity'];
            $product->subtotal = $product->price * $product->quantity;
            return (object) [
                'product' => $product,
                'quantity' => $product->quantity,
                'subtotal' => $product->subtotal,
                'product_id' => $product->id,
            ];
        });
    }

    /**
     * Lấy tổng số lượng sản phẩm trong giỏ hàng.
     */
    public function getCartCount(): int
    {
        if (Auth::guard('customer')->check()) {
            $cart = Auth::guard('customer')->user()->cart;
            return $cart ? $cart->items->sum('quantity') : 0;
        }

        return collect(session(self::SESSION_KEY, []))->sum('quantity');
    }

    /**
     * Lấy tổng tiền của giỏ hàng (chưa tính phí vận chuyển và giảm giá).
     */
    public function getCartTotal(): float
    {
        return $this->getItems()->sum('subtotal');
    }

    /**
     * Xóa toàn bộ giỏ hàng và thông tin checkout.
     */
    public function clear(): void
    {
        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->user()->cart?->items()->delete();
        } else {
            session()->forget(self::SESSION_KEY);
        }
        $this->clearCheckoutData();
    }

    /**
     * Gộp giỏ hàng từ session vào database khi đăng nhập.
     */
    public function mergeSessionCartToDatabase(): void
    {
        $sessionItems = session(self::SESSION_KEY, []);
        if (empty($sessionItems) || !Auth::guard('customer')->check()) {
            return;
        }

        foreach ($sessionItems as $productId => $item) {
            $this->add($productId, $item['quantity']);
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * Lấy thông tin chi tiết giỏ hàng (an toàn hơn).
     */
    public function getCartDetails(): array
    {
        $subtotal = $this->getCartTotal();

        if ($subtotal <= 0) {
            return [
                'items' => collect(),
                'count' => 0,
                'subtotal' => 0,
                'shipping_info' => null,
                'promotion_info' => null,
                'shipping_fee' => 0,
                'discount_amount' => 0,
                'grand_total' => 0,
            ];
        }

        $promotionInfo = $this->getPromotionInfo();
        $shippingFee = 0;

        $discountAmount = 0;
        if ($promotionInfo) {
            $calculatedDiscount = $promotionInfo->calculateDiscount($subtotal);

            if ($calculatedDiscount > 0) {
                $discountAmount = $calculatedDiscount;
            } else {
                // Nếu mã không hợp lệ hoặc không có giảm giá, xóa khỏi session
                $this->clearPromotion();
                $promotionInfo = null;
            }
        }

        $grandTotal = $subtotal + $shippingFee - $discountAmount;

        return [
            'items' => $this->getItems(),
            'count' => $this->getCartCount(),
            'subtotal' => $subtotal,
            'shipping_info' => ['id' => null, 'name' => 'Miễn phí vận chuyển', 'fee' => 0],
            'promotion_info' => $promotionInfo,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Áp dụng thông tin vận chuyển và lưu vào session.
     */
    public function applyShipping(int $deliveryServiceId): array
    {
        $shippingInfo = ['id' => $deliveryServiceId, 'name' => 'Miễn phí vận chuyển', 'fee' => 0];
        session([self::SHIPPING_SESSION_KEY => $shippingInfo]);
        return $shippingInfo;
    }

    /**
     * SỬA ĐỔI: Áp dụng mã giảm giá và lưu vào session với thông báo lỗi cụ thể hơn.
     */
    public function applyPromotion(string $promoCode): Promotion
    {
        $promoCode = strtoupper(trim($promoCode));
        $promotion = Promotion::where('code', $promoCode)->first();
        $subtotal = $this->getCartTotal();

        $this->clearPromotion(); // Luôn xóa khuyến mãi cũ trước khi áp dụng cái mới

        if (!$promotion) {
            throw ValidationException::withMessages(['promotion_code' => 'Mã giảm giá không tồn tại.']);
        }

        if (!$promotion->isManuallyActive()) {
            throw ValidationException::withMessages(['promotion_code' => 'Mã giảm giá đã bị vô hiệu hóa bởi quản trị viên.']);
        }

        if (!$promotion->isCurrentlyActive()) {
            // Kiểm tra cụ thể hơn xem đã hết hạn hay chưa bắt đầu
            if ($promotion->start_date && Carbon::now()->lt($promotion->start_date)) {
                throw ValidationException::withMessages(['promotion_code' => 'Mã giảm giá chưa bắt đầu.']);
            }
            if ($promotion->end_date && Carbon::now()->gt($promotion->end_date)) {
                throw ValidationException::withMessages(['promotion_code' => 'Mã giảm giá đã hết hạn.']);
            }
            // Fallback cho trường hợp không xác định rõ
            throw ValidationException::withMessages(['promotion_code' => 'Mã giảm giá không hiệu lực vào thời điểm hiện tại.']);
        }

        if (!$promotion->hasUsesLeft()) {
            throw ValidationException::withMessages(['promotion_code' => 'Mã giảm giá đã hết lượt sử dụng.']);
        }

        // THAY ĐỔI QUAN TRỌNG: Thông báo cụ thể cho điều kiện đơn hàng tối thiểu
        if (!$promotion->meetsMinOrderAmount($subtotal)) {
            $requiredAmount = number_format($promotion->min_order_amount, 0, ',', '.') . ' ₫';
            throw ValidationException::withMessages(['promotion_code' => "Đơn hàng của bạn không đủ điều kiện (tối thiểu {$requiredAmount}) để dùng mã giảm giá."]);
        }

        // Nếu tất cả các điều kiện đều được thỏa mãn, lưu mã giảm giá vào session
        session([self::PROMOTION_SESSION_KEY => $promotion]);
        return $promotion;
    }

    /**
     * Lấy thông tin vận chuyển từ session.
     */
    public function getShippingInfo(): ?array
    {
        return session(self::SHIPPING_SESSION_KEY);
    }

    /**
     * Lấy thông tin giảm giá từ session.
     */
    public function getPromotionInfo(): ?Promotion
    {
        return session(self::PROMOTION_SESSION_KEY);
    }

    /**
     * Xóa thông tin vận chuyển và giảm giá khỏi session.
     */
    public function clearCheckoutData(): void
    {
        session()->forget([self::SHIPPING_SESSION_KEY, self::PROMOTION_SESSION_KEY]);
    }

    /**
     * Xóa mã giảm giá đã áp dụng.
     */
    public function clearPromotion(): void
    {
        session()->forget(self::PROMOTION_SESSION_KEY);
    }

    /**
     * Helper để lấy số lượng hiện tại của sản phẩm trong giỏ hàng.
     */
    protected function getQuantityInCart(int $productId): int
    {
        if (Auth::guard('customer')->check()) {
            $cartItem = Auth::guard('customer')->user()->cart?->items()->where('product_id', $productId)->first();
            return $cartItem ? $cartItem->quantity : 0;
        }

        $cart = session(self::SESSION_KEY, []);
        return $cart[$productId]['quantity'] ?? 0;
    }

    /**
     * Thêm sản phẩm vào database.
     */
    protected function addToDatabase(int $productId, int $quantity): void
    {
        $customer = Auth::guard('customer')->user();
        $cart = $customer->cart()->firstOrCreate([]);
        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            $cart->items()->create(['product_id' => $productId, 'quantity' => $quantity]);
        }
    }

    /**
     * Thêm sản phẩm vào session.
     */
    protected function addToSession(int $productId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = ['quantity' => $quantity];
        }
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Cập nhật số lượng sản phẩm trong database.
     */
    protected function updateInDatabase(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromDatabase($productId);
            return;
        }
        $cart = Auth::guard('customer')->user()->cart;
        $cart?->items()->where('product_id', $productId)->update(['quantity' => $quantity]);
    }

    /**
     * Cập nhật số lượng sản phẩm trong session.
     */
    protected function updateInSession(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromSession($productId);
            return;
        }
        $cart = session(self::SESSION_KEY, []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            session([self::SESSION_KEY => $cart]);
        }
    }

    /**
     * Xóa sản phẩm khỏi database.
     */
    protected function removeFromDatabase(int $productId): void
    {
        Auth::guard('customer')->user()->cart?->items()->where('product_id', $productId)->delete();
    }

    /**
     * Xóa sản phẩm khỏi session.
     */
    protected function removeFromSession(int $productId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
    }
}
