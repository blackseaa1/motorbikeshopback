<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews with pagination, search, filter, and sort.
     * Hiển thị danh sách các đánh giá với phân trang, tìm kiếm, lọc và sắp xếp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Review::with(['customer', 'product']);

        // Search by review comment, customer name/email, or product name
        // Tìm kiếm theo bình luận, tên/email khách hàng hoặc tên sản phẩm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                // Bỏ tìm kiếm theo 'id' vì không tồn tại cột này trong bảng reviews (với khóa phức hợp)
                $q->where('comment', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter by status (e.g., pending, approved, rejected)
        // Lọc theo trạng thái (ví dụ: pending, approved, rejected)
        if ($statusFilter = $request->input('status_filter')) {
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }
        }

        // Filter by rating
        // Lọc theo đánh giá
        if ($ratingFilter = $request->input('rating_filter')) {
            if ($ratingFilter !== 'all') {
                $query->where('rating', (int)$ratingFilter);
            }
        }

        // Sort reviews
        // Sắp xếp đánh giá
        $sortBy = $request->input('sort_by', 'created_at_desc'); // Default to latest (Mặc định là mới nhất)
        switch ($sortBy) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating_desc':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_asc':
                $query->orderBy('rating', 'asc');
                break;
            default: // created_at_desc
                $query->orderBy('created_at', 'desc');
                break;
        }

        $reviews = $query->paginate(10)->withQueryString(); // Paginate results (Phân trang kết quả)

        // Pass selected filters back to the view to maintain form state
        // Truyền các bộ lọc đã chọn trở lại view để duy trì trạng thái form
        $selectedFilters = [
            'search' => $search,
            'status_filter' => $statusFilter,
            'rating_filter' => $ratingFilter,
            'sort_by' => $sortBy,
        ];

        $reviewStatuses = Review::STATUSES; // Assuming you have a STATUSES constant in your Review model
        $reviewRatings = [1, 2, 3, 4, 5]; // Possible ratings (Các mức đánh giá có thể)

        return view('admin.content.review.reviews', compact('reviews', 'selectedFilters', 'reviewStatuses', 'reviewRatings'));
    }

    /**
     * Update the status of the specified review.
     * Cập nhật trạng thái của đánh giá cụ thể.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $customer_id  The customer_id from the route parameter.
     * @param  int $product_id   The product_id from the route parameter.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $customer_id, $product_id)
    {
        // Check if the authenticated user is an admin
        // Kiểm tra xem người dùng đã xác thực có phải là admin không
        if (!Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        // Validate the incoming request data for status
        // Xác thực dữ liệu yêu cầu đến cho trạng thái
        $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(Review::STATUSES))], // Validate against your defined statuses
        ]);

        try {
            // Find the review using the composite primary key
            // Tìm đánh giá bằng khóa chính phức hợp
            $review = Review::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->first();

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
            }

            $review->status = $request->input('status');
            $review->save(); // This is where the "Illegal offset type" error was occurring if $keyType was missing

            return response()->json([
                'success' => true,
                'message' => 'Review status updated successfully.',
                'new_status' => $review->status,
                'status_text' => $review->status_text // Using the accessor for display text
            ]);
        } catch (\Exception $e) {
            // Log the error with relevant IDs
            // Ghi log lỗi với các ID liên quan
            Log::error('Error updating review status: ' . $e->getMessage(), [
                'customer_id' => $customer_id,
                'product_id' => $product_id,
                'admin_id' => Auth::guard('admin')->id()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update review status.'], 500);
        }
    }

    /**
     * Remove the specified review from storage.
     * Xóa đánh giá cụ thể khỏi bộ nhớ.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $customer_id  The customer_id from the route parameter.
     * @param  int $product_id   The product_id from the route parameter.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $customer_id, $product_id)
    {
        // Check if the authenticated user is an admin
        // Kiểm tra xem người dùng đã xác thực có phải là admin không
        if (!Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            // Find the review using the composite primary key
            // Tìm đánh giá bằng khóa chính phức hợp
            $review = Review::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->first();

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
            }

            $review->delete();
            return response()->json(['success' => true, 'message' => 'Review deleted successfully.']);
        } catch (\Exception $e) {
            // Log the error with relevant IDs
            // Ghi log lỗi với các ID liên quan
            Log::error('Error deleting review: ' . $e->getMessage(), [
                'customer_id' => $customer_id,
                'product_id' => $product_id,
                'admin_id' => Auth::guard('admin')->id()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to delete review.'], 500);
        }
    }
}
