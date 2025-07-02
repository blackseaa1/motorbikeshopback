<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Review;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\DB;

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
        $query = $request->input('query');
        $keywords = $query ? array_filter(explode(' ', $query)) : [];

        // === TỐI ƯU HÓA: Lấy trước ID của các mục liên quan ===
        $brandIds = [];
        $categoryIds = [];
        $vehicleModelIds = [];

        if (!empty($keywords)) {
            $brandIds = Brand::where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'))->pluck('id');
            $categoryIds = Category::where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'))->pluck('id');
            $vehicleModelIds = VehicleModel::where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'))
                ->orWhereHas('vehicleBrand', fn($q) => $this->addKeywordSearch($q, $keywords, 'name'))
                ->pluck('id');
        }

        // === SẢN PHẨM ===
        $productsQuery = Product::query()->where('status', Product::STATUS_ACTIVE);
        if (!empty($keywords)) {
            $productsQuery->where(function ($q) use ($keywords, $brandIds, $categoryIds, $vehicleModelIds) {
                // Tìm theo tên/mô tả sản phẩm
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                }
                // *** FIX: Lọc theo ID thương hiệu và danh mục đã tìm thấy ***
                if ($brandIds->isNotEmpty()) {
                    $q->orWhereIn('brand_id', $brandIds);
                }
                if ($categoryIds->isNotEmpty()) {
                    $q->orWhereIn('category_id', $categoryIds);
                }
                // Lọc theo model xe tương thích (dùng orWhereHas vì là quan hệ nhiều-nhiều)
                if ($vehicleModelIds->isNotEmpty()) {
                    $q->orWhereHas('vehicleModels', function ($vmq) use ($vehicleModelIds) {
                        $vmq->whereIn('vehicle_models.id', $vehicleModelIds);
                    });
                }
            });
        } else {
            $productsQuery->whereRaw('1 = 0');
        }
        $products = $productsQuery->with(['firstImage', 'brand', 'category'])
            ->withCount(['reviews' => fn($q) => $q->where('status', Review::STATUS_APPROVED)])
            ->withAvg(['reviews' => fn($q) => $q->where('status', Review::STATUS_APPROVED)], 'rating')
            ->paginate(6, ['*'], 'products_page')->withQueryString();

        // === CÁC KẾT QUẢ KHÁC (GIỮ NGUYÊN LOGIC CŨ VÌ ĐÃ ỔN ĐỊNH) ===

        // THƯƠNG HIỆU SẢN PHẨM
        $brandsQuery = Brand::query()->where('status', Brand::STATUS_ACTIVE);
        if (!empty($keywords)) {
            $brandsQuery->where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'));
        } else {
            $brandsQuery->whereRaw('1 = 0');
        }
        $brandsQuery->addSelect(['products_count' => Product::select(DB::raw('count(id)'))->whereColumn('brands.id', 'products.brand_id')->where('status', Product::STATUS_ACTIVE)]);
        $brands = $brandsQuery->paginate(5, ['*'], 'brands_page')->withQueryString();

        // DANH MỤC
        $categoriesQuery = Category::query();
        if (!empty($keywords)) {
            $categoriesQuery->where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'));
        } else {
            $categoriesQuery->whereRaw('1 = 0');
        }
        $categoriesQuery->addSelect(['products_count' => Product::select(DB::raw('count(id)'))->whereColumn('categories.id', 'products.category_id')->where('status', Product::STATUS_ACTIVE)]);
        $categories = $categoriesQuery->paginate(5, ['*'], 'categories_page')->withQueryString();

        // HÃNG XE
        $vehicleBrandsQuery = VehicleBrand::query();
        if (!empty($keywords)) {
            $vehicleBrandsQuery->where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'));
        } else {
            $vehicleBrandsQuery->whereRaw('1 = 0');
        }
        $vehicleBrandsQuery->addSelect(['products_count' => Product::select(DB::raw('count(distinct products.id)'))->join('product_vehicle_models', 'products.id', '=', 'product_vehicle_models.product_id')->join('vehicle_models', 'product_vehicle_models.vehicle_model_id', '=', 'vehicle_models.id')->whereColumn('vehicle_models.vehicle_brand_id', 'vehicle_brands.id')->where('products.status', Product::STATUS_ACTIVE)]);
        $vehicleBrands = $vehicleBrandsQuery->paginate(5, ['*'], 'vehicle_brands_page')->withQueryString();

        // DÒNG XE
        $vehicleModelsQuery = VehicleModel::query();
        if (!empty($keywords)) {
            $vehicleModelsQuery->where(fn($q) => $this->addKeywordSearch($q, $keywords, 'name'));
        } else {
            $vehicleModelsQuery->whereRaw('1 = 0');
        }
        $vehicleModels = $vehicleModelsQuery->with('vehicleBrand')->withCount(['products' => fn($q) => $q->where('status', Product::STATUS_ACTIVE)])->paginate(10, ['*'], 'vehicle_models_page')->withQueryString();

        // BÀI BLOG
        $blogPostsQuery = BlogPost::query()->where('status', BlogPost::STATUS_PUBLISHED);
        if (!empty($keywords)) {
            $blogPostsQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'like', '%' . $keyword . '%')->orWhere('content', 'like', '%' . $keyword . '%');
                }
            });
        } else {
            $blogPostsQuery->whereRaw('1 = 0');
        }
        $blogPosts = $blogPostsQuery->paginate(4, ['*'], 'blog_posts_page')->withQueryString();

        // Tập hợp tất cả kết quả
        $results = [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'vehicleBrands' => $vehicleBrands,
            'vehicleModels' => $vehicleModels,
            'blogPosts' => $blogPosts,
        ];

        return view('customer.search.index', ['query' => $query, 'results' => $results]);
    }

    /**
     * Helper function to add keyword search conditions to a query.
     */
    private function addKeywordSearch($query, $keywords, $field)
    {
        foreach ($keywords as $keyword) {
            $query->orWhere($field, 'like', '%' . $keyword . '%');
        }
    }

    /**
     * Xử lý tìm kiếm autocomplete và trả về kết quả dưới dạng JSON.
     */
    public function autocompleteSearch(Request $request)
    {
        // Logic autocomplete giữ nguyên vì đã hoạt động tốt
        $query = $request->input('query');
        $suggestions = ['products' => [], 'categories' => [], 'brands' => [], 'vehicleBrands' => [], 'blogPosts' => []];
        if (strlen($query) > 1) {
            $products = Product::where('status', Product::STATUS_ACTIVE)->where('name', 'like', '%' . $query . '%')->select('id', 'name')->with('firstImage')->limit(4)->get();
            foreach ($products as $item) {
                $suggestions['products'][] = ['name' => $item->name, 'image' => $item->thumbnail_url, 'url' => route('products.show', $item->id)];
            }
            $categories = Category::where('name', 'like', '%' . $query . '%')->select('id', 'name')->limit(3)->get();
            foreach ($categories as $item) {
                $suggestions['categories'][] = ['name' => $item->name, 'image' => null, 'url' => route('categories.show', $item->id)];
            }
            $brands = Brand::where('status', Brand::STATUS_ACTIVE)->where('name', 'like', '%' . $query . '%')->select('id', 'name', 'logo_url')->limit(3)->get();
            foreach ($brands as $item) {
                $suggestions['brands'][] = ['name' => $item->name, 'image' => $item->logo_full_url, 'url' => route('customer.shop.brand', $item->id)];
            }
            $vehicleBrands = VehicleBrand::where('name', 'like', '%' . $query . '%')->select('id', 'name')->limit(3)->get();
            foreach ($vehicleBrands as $item) {
                $suggestions['vehicleBrands'][] = ['name' => $item->name, 'image' => null, 'url' => route('products.index', ['vehicle_brand_id' => $item->id])];
            }
            $blogPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)->where('title', 'like', '%' . $query . '%')->select('id', 'title', 'image_url')->limit(3)->get();
            foreach ($blogPosts as $item) {
                $suggestions['blogPosts'][] = ['name' => $item->title, 'image' => $item->image_full_url, 'url' => route('blog.show', $item->id)];
            }
        }
        return response()->json($suggestions);
    }
}
