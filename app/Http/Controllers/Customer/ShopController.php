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
        $selectedCategories = $request->input('categories', []);
        if ($category && $category->exists) {
            $selectedCategories[] = $category->id;
        }
        if (!empty($selectedCategories)) {
            $query->whereIn('category_id', $selectedCategories);
        }

        if ($request->filled('brands')) {
            $query->whereIn('brand_id', $request->input('brands'));
        }

        $selectedVehicleBrand = $request->input('vehicle_brand_id');
        $selectedVehicleModel = $request->input('vehicle_model_id');

        // Áp dụng bộ lọc Dòng xe nếu có
        if ($selectedVehicleBrand) {
            $query->whereHas('vehicleModels.vehicleBrand', function ($q) use ($selectedVehicleBrand) {
                $q->where('vehicle_brands.id', $selectedVehicleBrand);
            });
        }

        // Áp dụng bộ lọc Mẫu xe nếu có (sẽ tinh chỉnh thêm nếu Dòng xe cũng được chọn)
        if ($selectedVehicleModel) {
            $query->whereHas('vehicleModels', function ($q) use ($selectedVehicleModel) {
                $q->where('vehicle_models.id', $selectedVehicleModel);
            });
        }

        return $query;
    }

    /**
     * Hiển thị trang liệt kê tất cả sản phẩm với bộ lọc nâng cao.
     *
     * @param Request $request
     * @param Category|null $category
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Category $category = null)
    {
        $query = $this->getFilteredProductsQuery($request, $category);
        $products = $query->latest()->paginate(12)->withQueryString();

        // --- Lấy dữ liệu cho các bộ lọc bên sidebar ---
        $categories = Category::where('status', Category::STATUS_ACTIVE)->orderBy('name')->get();
        $brands = Brand::where('status', Brand::STATUS_ACTIVE)->orderBy('name')->get();
        $vehicleBrands = VehicleBrand::where('status', VehicleBrand::STATUS_ACTIVE)
            ->with(['vehicleModels' => function ($query) {
                $query->where('status', VehicleModel::STATUS_ACTIVE)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('customer.shops.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'vehicleBrands' => $vehicleBrands,
            'request' => $request,
        ]);
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
        $products = $query->latest()->paginate(12)->withQueryString();

        return response()->json([
            'products_html' => view('customer.shops.partials.product_list', ['products' => $products])->render(),
            'pagination_html' => $products->links()->toHtml(),
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

        return view('customer.products.show', compact('product'));
    }
}
