<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\BlogPost;
use App\Models\Category;
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
                ->paginate(6, ['*'], 'products_page')
                ->withQueryString();

            // Tìm kiếm và phân trang trong Thương hiệu
            $brands = Brand::where('status', Brand::STATUS_ACTIVE)
                ->where('name', 'like', '%' . $query . '%')
                ->paginate(5, ['*'], 'brands_page')
                ->withQueryString();

            // Tìm kiếm và phân trang trong Bài Blog
            $blogPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                        ->orWhere('content', 'like', '%' . $query . '%');
                })
                ->paginate(4, ['*'], 'blog_posts_page')
                ->withQueryString();

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

    /**
     * Xử lý tìm kiếm autocomplete và trả về kết quả dưới dạng JSON.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocompleteSearch(Request $request)
    {
        $query = $request->input('query');
        $suggestions = [
            'products' => [],
            'categories' => [],
            'brands' => [],
            'blogPosts' => [],
        ];

        if ($query) {
            // Tìm kiếm Sản phẩm
            $products = Product::where('status', Product::STATUS_ACTIVE)
                ->where('name', 'like', '%' . $query . '%')
                ->select('id', 'name')
                ->with('firstImage')
                ->limit(5)
                ->get();
            foreach ($products as $product) {
                $suggestions['products'][] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->thumbnail_url,
                    'url' => route('products.show', $product->id),
                ];
            }

            // Tìm kiếm Danh mục
            $categories = Category::where('name', 'like', '%' . $query . '%')
                ->select('id', 'name')
                ->limit(3)
                ->get();
            foreach ($categories as $category) {
                $suggestions['categories'][] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => null,
                    'url' => route('categories.show', $category->id),
                ];
            }

            // Tìm kiếm Thương hiệu
            // LƯU Ý QUAN TRỌNG: Route 'customer.shop.brand' KHÔNG CÓ TRONG ĐOẠN MÃ web.php BẠN CUNG CẤP.
            // BẠN CẦN ĐỊNH NGHĨA ROUTE NÀY TRONG web.php ĐỂ TRÁNH LỖI 'Route not defined'.
            $brands = Brand::where('status', Brand::STATUS_ACTIVE)
                ->where('name', 'like', '%' . $query . '%')
                ->select('id', 'name', 'logo_url')
                ->limit(3)
                ->get();
            foreach ($brands as $brand) {
                $suggestions['brands'][] = [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'image' => asset('storage/' . $brand->logo_url),
                    'url' => route('customer.shop.brand', $brand->id), // Vẫn còn nguy cơ lỗi nếu route này không tồn tại
                ];
            }

            // Tìm kiếm Bài Blog
            $blogPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
                ->where('title', 'like', '%' . $query . '%')
                ->select('id', 'title', 'image_url')
                ->limit(3)
                ->get();
            foreach ($blogPosts as $blogPost) {
                $suggestions['blogPosts'][] = [
                    'id' => $blogPost->id,
                    'name' => $blogPost->title,
                    'image' => asset('storage/' . $blogPost->image_url),
                    'url' => route('blog.show', $blogPost->id), // ĐÃ SỬA ĐỂ KHỚP VỚI 'blog.show'
                ];
            }
        }

        return response()->json($suggestions);
    }
}
