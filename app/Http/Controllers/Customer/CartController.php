<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryService;
use App\Support\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// File này không trực tiếp dùng CustomerAddress nhưng Auth::user()->addresses() sẽ dùng nó.
// Việc thêm vào đây là một thói quen tốt để làm rõ các model liên quan.
use App\Models\CustomerAddress;

class CartController extends Controller
{
    protected $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * Hiển thị trang giỏ hàng với đầy đủ thông tin để thanh toán.
     */
    public function index()
    {
        // Lấy toàn bộ chi tiết giỏ hàng (sản phẩm, tổng tiền, khuyến mãi, vận chuyển...)
        $cartDetails = $this->cartManager->getCartDetails();

        // Lấy danh sách các đơn vị vận chuyển đang hoạt động
        $deliveryServices = DeliveryService::where('status', 'active')->get();

        // Lấy danh sách địa chỉ đã lưu của khách hàng (nếu đã đăng nhập)
        $customerAddresses = Auth::guard('customer')->check()
            ? Auth::guard('customer')->user()->addresses()->with(['province', 'district', 'ward'])->get()
            : collect();

        // Trả về view của trang giỏ hàng và truyền các dữ liệu cần thiết
        return view('customer.cart.index', compact('cartDetails', 'deliveryServices', 'customerAddresses'));
    }

    /**
     * API: Lấy dữ liệu giỏ hàng để hiển thị trên header.
     */
    public function getCartData()
    {
        // Lấy chi tiết giỏ hàng và chỉ trả về các thông tin cần thiết cho header
        $cartDetails = $this->cartManager->getCartDetails();
        return response()->json([
            'count' => $cartDetails['count'],
            'total' => number_format($cartDetails['grand_total']),
            'items' => $cartDetails['items'],
        ]);
    }

    /**
     * API: Cập nhật toàn bộ tóm tắt giỏ hàng (vận chuyển, giảm giá).
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

        return response()->json($this->cartManager->getCartDetails());
    }

    /**
     * API: Thêm sản phẩm vào giỏ.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $this->cartManager->add($validated['product_id'], $validated['quantity']);
        return response()->json($this->cartManager->getCartDetails());
    }

    /**
     * API: Cập nhật số lượng sản phẩm.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);
        $this->cartManager->update($validated['product_id'], $validated['quantity']);
        return response()->json($this->cartManager->getCartDetails());
    }

    /**
     * API: Xóa sản phẩm khỏi giỏ.
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        $this->cartManager->remove($validated['product_id']);
        return response()->json($this->cartManager->getCartDetails());
    }
}
