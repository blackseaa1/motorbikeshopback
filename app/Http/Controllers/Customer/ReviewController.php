<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Lưu một đánh giá mới cho sản phẩm.
     */
    public function store(Request $request, Product $product)
    {
        // 1. Chỉ cho phép khách hàng đã đăng nhập gửi đánh giá
        if (!Auth::guard('customer')->check()) {
            return back()->with('error', 'Vui lòng đăng nhập để gửi đánh giá.');
        }

        // 2. Validate dữ liệu đầu vào
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá.',
            'comment.min' => 'Nội dung đánh giá cần có ít nhất 10 ký tự.',
        ]);

        $customer = Auth::guard('customer')->user();

        // 3. Kiểm tra xem khách hàng đã từng đánh giá sản phẩm này chưa
        $existingReview = $product->reviews()->where('customer_id', $customer->id)->first();
        if ($existingReview) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // 4. Tạo và lưu đánh giá mới
        // Trạng thái mặc định là 'pending' (chờ duyệt) theo cài đặt trong migration
        $product->reviews()->create([
            'customer_id' => $customer->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        // 5. Trả về thông báo thành công
        return back()->with('success', 'Cảm ơn bạn đã gửi đánh giá! Đánh giá của bạn sẽ được hiển thị sau khi được quản trị viên phê duyệt.');
    }
}
