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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Review::with(['customer', 'product']);

        // Search by review ID, customer name/email, or product name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('comment', 'like', '%' . $search . '%')
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
        if ($statusFilter = $request->input('status_filter')) {
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }
        }

        // Filter by rating
        if ($ratingFilter = $request->input('rating_filter')) {
            if ($ratingFilter !== 'all') {
                $query->where('rating', (int)$ratingFilter);
            }
        }

        // Sort reviews
        $sortBy = $request->input('sort_by', 'created_at_desc'); // Default to latest
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

        $reviews = $query->paginate(10)->withQueryString(); // Paginate results

        // Pass selected filters back to the view to maintain form state
        $selectedFilters = [
            'search' => $search,
            'status_filter' => $statusFilter,
            'rating_filter' => $ratingFilter,
            'sort_by' => $sortBy,
        ];

        $reviewStatuses = Review::STATUSES; // Assuming you have a STATUSES constant in your Review model
        $reviewRatings = [1, 2, 3, 4, 5]; // Possible ratings

        return view('admin.content.review.reviews', compact('reviews', 'selectedFilters', 'reviewStatuses', 'reviewRatings'));
    }

    /**
     * Update the status of the specified review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Review $review)
    {
        // Check if the authenticated user is an admin
        if (!Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Review::STATUSES)], // Validate against your defined statuses
        ]);

        try {
            $review->status = $request->input('status');
            $review->save();

            return response()->json([
                'success' => true,
                'message' => 'Review status updated successfully.',
                'new_status' => $review->status,
                'status_text' => ucfirst($review->status) // Capitalize first letter for display
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating review status: ' . $e->getMessage(), ['review_id' => $review->id, 'admin_id' => Auth::guard('admin')->id()]);
            return response()->json(['success' => false, 'message' => 'Failed to update review status.'], 500);
        }
    }

    /**
     * Remove the specified review from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review)
    {
        // Check if the authenticated user is an admin
        if (!Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $review->delete();
            return response()->json(['success' => true, 'message' => 'Review deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage(), ['review_id' => $review->id, 'admin_id' => Auth::guard('admin')->id()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete review.'], 500);
        }
    }
}
