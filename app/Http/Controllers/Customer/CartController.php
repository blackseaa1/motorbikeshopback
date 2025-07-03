<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryService;
use App\Support\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product; // Import Product model
use Illuminate\Validation\Rule; // Import Rule for advanced validation
use Illuminate\Validation\ValidationException; // Import ValidationException for custom error throwing

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
    protected function getFullCartDetailsResponse() // Đã sửa lỗi cú pháp "function" thừa trước đó
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
            'product_id' => [
                'required',
                'exists:products,id',
                // Quy tắc tùy chỉnh để kiểm tra số lượng tồn kho
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $product = Product::find($request->product_id);
                    if ($product) {
                        $requestedQuantity = $request->quantity;
                        // Lấy số lượng hiện tại của sản phẩm đó trong giỏ hàng (nếu có)
                        $currentCartQuantity = $this->cartManager->getQuantityInCart($request->product_id); // ĐÃ SỬA TÊN PHƯƠNG THỨC
                        // Tổng số lượng mà người dùng muốn có trong giỏ hàng
                        $totalDesiredQuantity = $requestedQuantity + $currentCartQuantity;

                        // Kiểm tra xem tổng số lượng mong muốn có vượt quá số lượng tồn kho không
                        // và số lượng tồn kho phải lớn hơn 0
                        if ($product->stock_quantity > 0 && $totalDesiredQuantity <= $product->stock_quantity) {
                            $query->where('id', $request->product_id); // Cho phép validate nếu hợp lệ
                        } else {
                            // Nếu không đủ hàng, thêm lỗi vào validator
                            $validator = \Validator::make([], []); // Tạo một validator rỗng
                            if ($product->stock_quantity === 0) {
                                $validator->errors()->add('quantity', 'Sản phẩm này hiện đã hết hàng.');
                            } else {
                                $validator->errors()->add('quantity', 'Số lượng bạn yêu cầu (' . $totalDesiredQuantity . ') vượt quá số lượng tồn kho hiện có (' . $product->stock_quantity . ').');
                            }
                            throw new ValidationException($validator); // Sử dụng ValidationException
                        }
                    } else {
                        // Nếu sản phẩm không tồn tại, quy tắc 'exists' đã bắt rồi, nhưng để an toàn.
                        throw ValidationException::withMessages([ // Sử dụng ValidationException
                            'product_id' => 'Sản phẩm không tồn tại.',
                        ]);
                    }
                }),
            ],
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
            'product_id' => [
                'required',
                'exists:products,id',
                // Quy tắc tùy chỉnh để kiểm tra số lượng tồn kho khi cập nhật
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $product = Product::find($request->product_id);
                    if ($product) {
                        $requestedQuantity = $request->quantity;

                        // Kiểm tra xem số lượng mong muốn có vượt quá số lượng tồn kho không
                        // và số lượng tồn kho phải lớn hơn 0 (trừ khi số lượng yêu cầu là 0 để xóa)
                        if ($requestedQuantity === 0 || ($product->stock_quantity > 0 && $requestedQuantity <= $product->stock_quantity)) {
                            $query->where('id', $request->product_id);
                        } else {
                            $validator = \Validator::make([], []);
                            if ($product->stock_quantity === 0) {
                                $validator->errors()->add('quantity', 'Sản phẩm này hiện đã hết hàng.');
                            } else {
                                $validator->errors()->add('quantity', 'Số lượng bạn yêu cầu (' . $requestedQuantity . ') vượt quá số lượng tồn kho hiện có (' . $product->stock_quantity . ').');
                            }
                            throw new ValidationException($validator); // Sử dụng ValidationException
                        }
                    } else {
                        throw ValidationException::withMessages([ // Sử dụng ValidationException
                            'product_id' => 'Sản phẩm không tồn tại.',
                        ]);
                    }
                }),
            ],
            'quantity' => 'required|integer|min:0', // min:0 để cho phép xóa item bằng cách đặt quantity về 0
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

    /**
     * API: Xóa nhiều sản phẩm khỏi giỏ hàng.
     */
    public function removeMultiple(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id', // Ensure each product ID exists
        ]);

        foreach ($validated['product_ids'] as $productId) {
            $this->cartManager->remove($productId);
        }

        return $this->getFullCartDetailsResponse();
    }
}
