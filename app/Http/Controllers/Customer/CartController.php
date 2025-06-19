<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CartManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    protected $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * API: Thêm một sản phẩm vào giỏ hàng.
     */
    public function add(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $this->cartManager->add($validated['product_id'], $validated['quantity']);

            return response()->json([
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng!',
                'cart_item_count' => $this->cartManager->getCartCount(),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log lỗi ở đây nếu cần
            return response()->json(['message' => 'Có lỗi xảy ra, không thể thêm sản phẩm.'], 500);
        }
    }

    /**
     * API: Lấy thông tin giỏ hàng hiện tại (số lượng item).
     */
    public function getCartInfo()
    {
        return response()->json([
            'item_count' => $this->cartManager->getCartCount(),
        ]);
    }

    // Bạn có thể thêm các phương thức khác ở đây: update, remove,...
}
