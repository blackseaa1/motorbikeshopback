<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\BlogPost;
use App\Models\Review;

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
            // Tìm kiếm và phân trang trong Sản phẩm
            $products = Product::where('status', Product::STATUS_ACTIVE)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->withCount(['reviews' => function ($q) {
                    $q->where('status', Review::STATUS_APPROVED);
                }])
                ->withAvg(['reviews' => function ($q) {
                    $q->where('status', Review::STATUS_APPROVED);
                }], 'rating')
                ->paginate(6, ['*'], 'products_page') // Phân trang 6 sản phẩm/trang, dùng 'products_page' làm tên tham số trang
                ->withQueryString(); // Giữ lại các tham số truy vấn khác

            // Tìm kiếm và phân trang trong Thương hiệu
            $brands = Brand::where('status', Brand::STATUS_ACTIVE)
                ->where('name', 'like', '%' . $query . '%')
                ->paginate(5, ['*'], 'brands_page') // Phân trang 5 thương hiệu/trang, dùng 'brands_page'
                ->withQueryString(); // Giữ lại các tham số truy vấn khác

            // Tìm kiếm và phân trang trong Bài Blog
            $blogPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                        ->orWhere('content', 'like', '%' . $query . '%');
                })
                ->paginate(4, ['*'], 'blog_posts_page') // Phân trang 4 bài blog/trang, dùng 'blog_posts_page'
                ->withQueryString(); // Giữ lại các tham số truy vấn khác

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
