<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Hiển thị danh sách các đánh giá.
     */
    public function index(Request $request)
    {
        $query = Review::with(['customer', 'product'])->latest();

        // Lọc theo trạng thái
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $reviews = $query->paginate(20);

        return view('admin.content.reviews', compact('reviews')); // Bạn cần tạo view này
    }

    /**
     * Cập nhật trạng thái của một đánh giá (Duyệt/Từ chối).
     */
    public function updateStatus(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([Review::STATUS_APPROVED, Review::STATUS_REJECTED])],
        ]);

        try {
            $review->status = $validated['status'];
            $review->save();

            // Trả về JSON để xử lý bằng AJAX
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đánh giá thành công!',
                'review' => $review->refresh(), // Trả về review đã cập nhật với các accessor
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật trạng thái review (ID: {$review->id}): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra.'
            ], 500);
        }
    }

    /**
     * Xóa một đánh giá.
     */
    public function destroy(Review $review)
    {
        try {
            $review->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa đánh giá thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa review (ID: {$review->id}): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi xóa.'
            ], 500);
        }
    }
}
