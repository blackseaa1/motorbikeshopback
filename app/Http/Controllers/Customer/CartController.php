<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryService;
use App\Support\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * Trả về dữ liệu cơ bản (chỉ subtotal) cho các hành động nhanh.
     */
    protected function getSimpleCartResponse()
    {
        // Chỉ lấy những thông tin cơ bản nhất, không có grand_total
        return response()->json([
            'count' => $this->cartManager->getCartCount(),
            'subtotal' => $this->cartManager->getCartTotal(),
            'items' => $this->cartManager->getItems()->values(),
        ]);
    }

    /**
     * Trả về dữ liệu đầy đủ cho trang cart/checkout.
     */
    protected function getFullCartDetailsResponse()
    {
        return response()->json($this->cartManager->getCartDetails());
    }

    /**
     * Hiển thị trang giỏ hàng.
     */
    public function index()
    {
        $cartDetails = $this->cartManager->getCartDetails();
        $deliveryServices = DeliveryService::where('status', 'active')->get();
        $customerAddresses = Auth::guard('customer')->check()
            ? Auth::guard('customer')->user()->addresses()->with(['province', 'district', 'ward'])->get()
            : collect();

        return view('customer.cart.index', compact('cartDetails', 'deliveryServices', 'customerAddresses'));
    }

    /**
     * API: Lấy dữ liệu giỏ hàng ban đầu (dùng dữ liệu đầy đủ).
     */
    public function getCartData()
    {
        return $this->getFullCartDetailsResponse();
    }

    /**
     * API: Cập nhật tóm tắt đơn hàng (vận chuyển, giảm giá).
     */
    public function updateSummary(Request $request)
    {
        if ($request->has('delivery_service_id') && !empty($request->delivery_service_id)) {
            $this->cartManager->applyShipping($request->delivery_service_id);
        }

        if ($request->has('promotion_code')) {
            if (empty($request->promotion_code)) {
                $this->cartManager->clearPromotion();
            } else {
                $this->cartManager->applyPromotion($request->promotion_code);
            }
        }

        return $this->getFullCartDetailsResponse();
    }

    /**
     * API: Thêm sản phẩm - Dùng hàm response đơn giản.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $this->cartManager->add($validated['product_id'], $validated['quantity']);
        return $this->getSimpleCartResponse();
    }

    /**
     * API: Cập nhật số lượng - Dùng hàm response đầy đủ.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);
        $this->cartManager->update($validated['product_id'], $validated['quantity']);
        return $this->getFullCartDetailsResponse(); // Cập nhật ở trang cart nên cần full response
    }

    /**
     * API: Xóa sản phẩm khỏi giỏ - Dùng hàm response đầy đủ.
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        $this->cartManager->remove($validated['product_id']);
        return $this->getFullCartDetailsResponse(); // Xóa ở trang cart nên cần full response
    }
}