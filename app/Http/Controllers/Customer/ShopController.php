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
use Illuminate\Support\Facades\Auth; // Import facade Auth

class ShopController extends Controller
{
    /**
     * Phương thức chung để xây dựng câu truy vấn sản phẩm với bộ lọc và dữ liệu đánh giá.
     * Được sử dụng bởi cả index() và getProductsApi() để tránh lặp code.
     *
     * @param Request $request
     * @param Category|null $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFilteredProductsQuery(Request $request, Category $category = null)
    {
        $query = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            // Tải thêm số lượng và điểm đánh giá trung bình của các review đã được duyệt
            ->withCount(['reviews' => function ($query) {
                $query->where('status', Review::STATUS_APPROVED);
            }])
            ->withAvg(['reviews' => function ($query) {
                $query->where('status', Review::STATUS_APPROVED);
            }], 'rating');

        // --- Logic lọc sản phẩm ---
        // THÊM: Tìm kiếm theo tên hoặc mô tả sản phẩm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Lọc theo danh mục
        if ($category) {
            $query->where('category_id', $category->id);
        } elseif ($request->has('categories')) {
            $query->whereIn('category_id', $request->input('categories'));
        }

        // Lọc theo khoảng giá
        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        // Lọc theo thương hiệu sản phẩm
        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        // Lọc theo thương hiệu xe và mẫu xe
        if ($vehicleBrandId = $request->input('vehicle_brand_id')) {
            $query->whereHas('vehicleModels.vehicleBrand', function ($q) use ($vehicleBrandId) {
                $q->where('id', $vehicleBrandId);
            });
        }
        if ($vehicleModelId = $request->input('vehicle_model_id')) {
            $query->whereHas('vehicleModels', function ($q) use ($vehicleModelId) {
                $q->where('id', $vehicleModelId);
            });
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
                case 'latest':
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest(); // Mặc định sắp xếp theo mới nhất
        }

        return $query;
    }


    /**
     * Hiển thị trang cửa hàng với danh sách sản phẩm.
     *
     * @param Request $request
     * @param Category|null $category
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Category $category = null)
    {
        $products = $this->getFilteredProductsQuery($request, $category)->latest()->paginate(9)->withQueryString(); // Thay đổi thành 9 sản phẩm mỗi trang
        $categories = Category::all();
        $brands = Brand::all();
        $vehicleBrands = VehicleBrand::all();
        $vehicleModels = VehicleModel::all(); // Thêm dòng này để truyền tất cả vehicleModels

        return view('customer.shops.index', compact(
            'products',
            'categories',
            'brands',
            'vehicleBrands',
            'vehicleModels',
            'request' // Đảm bảo $request được truyền để giữ lại trạng thái form
        ));
    }

    /**
     * API: Lấy danh sách sản phẩm đã lọc để hiển thị động bằng JavaScript.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsApi(Request $request)
    {
        $query = $this->getFilteredProductsQuery($request);
        $products = $query->latest()->paginate(9)->withQueryString(); // Thay đổi thành 9 sản phẩm mỗi trang

        return response()->json([
            'products_html' => view('customer.shops.partials.product_list', ['products' => $products])->render(),
            'pagination_html' => $products->links('customer.vendor.pagination')->toHtml(),
            'total' => $products->total()
        ]);
    }

    /**
     * Hiển thị trang chi tiết sản phẩm.
     *
     * @param Product $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        // Kiểm tra trạng thái sản phẩm, nếu không active thì báo lỗi 404
        if ($product->status !== Product::STATUS_ACTIVE) {
            abort(404);
        }

        // Tải các quan hệ cần thiết, bao gồm các reviews đã được duyệt
        $product->load([
            'category',
            'brand',
            'images',
            'vehicleModels.vehicleBrand',
            'reviews' => function ($query) {
                $query->where('status', Review::STATUS_APPROVED)
                    ->with('customer') // Lấy luôn thông tin khách hàng để hiển thị tên, avatar
                    ->latest();
            }
        ]);

        $hasReviewed = false;
        // Kiểm tra xem khách hàng đã đăng nhập chưa
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            // Kiểm tra xem khách hàng này đã đánh giá sản phẩm này chưa
            $existingReview = $product->reviews()->where('customer_id', $customer->id)->first();
            if ($existingReview) {
                $hasReviewed = true;
            }
        }

        // Truyền biến $hasReviewed vào view
        return view('customer.products.show', compact('product', 'hasReviewed'));
    }
}
