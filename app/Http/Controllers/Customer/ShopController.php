<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * Xây dựng câu truy vấn sản phẩm với bộ lọc.
     *
     * @param Request $request
     * @param Category|null $category
     * @param Brand|null $brand
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFilteredProductsQuery(Request $request, Category $category = null, Brand $brand = null)
    {
        $query = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['firstImage', 'brand', 'category']) // Tải trước các quan hệ cần thiết
            ->withCount(['reviews' => fn($q) => $q->where('status', Review::STATUS_APPROVED)])
            ->withAvg(['reviews' => fn($q) => $q->where('status', Review::STATUS_APPROVED)], 'rating');

        // Lọc theo từ khóa tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Lọc theo danh mục (ưu tiên route-model binding)
        if ($category) {
            $query->where('category_id', $category->id);
        } elseif ($request->has('categories')) {
            $query->whereIn('category_id', $request->input('categories'));
        }

        // *** FIX: Lọc theo thương hiệu (ưu tiên route-model binding) ***
        if ($brand) {
            $query->where('brand_id', $brand->id);
        } elseif ($request->has('brands')) {
            $query->whereIn('brand_id', $request->input('brands'));
        }

        // Lọc theo khoảng giá
        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        // Lọc theo hãng xe và dòng xe
        if ($vehicleBrandId = $request->input('vehicle_brand_id')) {
            $query->whereHas('vehicleModels.vehicleBrand', fn($q) => $q->where('id', $vehicleBrandId));
        }
        if ($vehicleModelId = $request->input('vehicle_model_id')) {
            $query->whereHas('vehicleModels', fn($q) => $q->where('id', $vehicleModelId));
        }

        // Sắp xếp
        if ($sortBy = $request->input('sort_by')) {
            switch ($sortBy) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * Hiển thị trang cửa hàng với danh sách sản phẩm.
     *
     * @param Request $request
     * @param Category|null $category
     * @param Brand|null $brand
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Category $category = null, Brand $brand = null)
    {
        // Hợp nhất ID từ route vào request để bộ lọc được chọn sẵn
        if ($category && !$request->has('categories')) {
            $request->merge(['categories' => [$category->id]]);
        }
        if ($brand && !$request->has('brands')) {
            $request->merge(['brands' => [$brand->id]]);
        }

        $products = $this->getFilteredProductsQuery($request, $category, $brand)->paginate(9)->withQueryString();

        // Lấy dữ liệu cho các bộ lọc
        $categories = Category::all();
        $brands = Brand::where('status', Brand::STATUS_ACTIVE)->get();
        $vehicleBrands = VehicleBrand::with('vehicleModels')->where('status', VehicleBrand::STATUS_ACTIVE)->get();

        return view('customer.shops.index', compact(
            'products',
            'categories',
            'brands',
            'vehicleBrands',
            'request'
        ));
    }

    /**
     * API: Lấy danh sách sản phẩm đã lọc để hiển thị động.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsApi(Request $request)
    {
        // Không cần truyền $category và $brand vì API luôn dựa vào request params
        $products = $this->getFilteredProductsQuery($request)->paginate(9)->withQueryString();

        return response()->json([
            'products_html' => view('customer.shops.partials.product_list', ['products' => $products])->render(),
            'pagination_html' => $products->links('customer.vendor.pagination')->toHtml(),
            'total' => $products->total()
        ]);
    }

    /**
     * Hiển thị trang chi tiết sản phẩm.
     */
    public function show(Product $product)
    {
        if ($product->status !== Product::STATUS_ACTIVE) {
            abort(404);
        }

        $product->load([
            'category',
            'brand',
            'images',
            'vehicleModels.vehicleBrand',
            'reviews' => fn($query) => $query->where('status', Review::STATUS_APPROVED)->with('customer')->latest()
        ]);

        $hasReviewed = false;
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            $hasReviewed = $product->reviews()->where('customer_id', $customer->id)->exists();
        }

        return view('customer.products.show', compact('product', 'hasReviewed'));
    }
}
