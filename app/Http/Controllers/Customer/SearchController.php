<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product; // Import Product model
use App\Models\Brand; // Import Brand model
use App\Models\BlogPost; // Import BlogPost model
use App\Models\Review; // Import Review model (để tính review count/avg cho sản phẩm)

class SearchController extends Controller
{
    /**
     * Xử lý tìm kiếm toàn cầu và hiển thị kết quả.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->input('query'); // Lấy từ khóa tìm kiếm
        $results = [];

        if ($query) {
            // Tìm kiếm trong Sản phẩm
            $products = Product::where('status', Product::STATUS_ACTIVE)
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->withCount(['reviews' => function ($q) { // Tải số lượng reviews
                    $q->where('status', Review::STATUS_APPROVED);
                }])
                ->withAvg(['reviews' => function ($q) { // Tải điểm review trung bình
                    $q->where('status', Review::STATUS_APPROVED);
                }], 'rating')
                ->limit(10) // Giới hạn số lượng kết quả cho mỗi loại
                ->get();

            // Tìm kiếm trong Thương hiệu
            $brands = Brand::where('status', Brand::STATUS_ACTIVE)
                ->where('name', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            // Tìm kiếm trong Bài Blog
            $blogPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                      ->orWhere('content', 'like', '%' . $query . '%');
                })
                ->limit(5)
                ->get();

            $results = [
                'products' => $products,
                'brands' => $brands,
                'blogPosts' => $blogPosts,
            ];
        }

        return view('customer.search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
