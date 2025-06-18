<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Hiển thị trang liệt kê tất cả sản phẩm với bộ lọc nâng cao.
     * Xử lý cho route('products.index') và route('categories.show').
     */
    public function index(Request $request, Category $category = null)
    {
        // Bắt đầu một query builder cho Product
        $query = Product::query()->where('status', 'active');

        // --- LOGIC LỌC SẢN PHẨM ---

        // 1. Lọc theo Danh mục (có thể từ URL hoặc từ form filter)
        $selectedCategories = $request->input('categories', []);
        if ($category && $category->exists) {
            // Nếu có category từ URL (Route Model Binding), thêm vào danh sách lọc
            $selectedCategories[] = $category->id;
        }
        if (!empty($selectedCategories)) {
            $query->whereIn('category_id', $selectedCategories);
        }

        // 2. Lọc theo Thương hiệu
        $selectedBrands = $request->input('brands', []);
        if (!empty($selectedBrands)) {
            $query->whereIn('brand_id', $selectedBrands);
        }

        // 3. Lọc theo Loại xe (VehicleBrand) và Mẫu xe (VehicleModel)
        $selectedVehicleBrand = $request->input('vehicle_brand_id');
        $selectedVehicleModel = $request->input('vehicle_model_id');

        if ($selectedVehicleModel) {
            // Nếu lọc theo mẫu xe cụ thể
            $query->whereHas('vehicleModels', function ($q) use ($selectedVehicleModel) {
                $q->where('vehicle_models.id', $selectedVehicleModel);
            });
        } elseif ($selectedVehicleBrand) {
            // Nếu chỉ lọc theo loại xe chung
            $query->whereHas('vehicleModels.vehicleBrand', function ($q) use ($selectedVehicleBrand) {
                $q->where('vehicle_brands.id', $selectedVehicleBrand);
            });
        }

        // --- KẾT THÚC LỌC ---

        // Lấy sản phẩm đã lọc, sắp xếp mới nhất và phân trang
        // withQueryString() để giữ lại các tham số filter khi chuyển trang
        $products = $query->latest()->paginate(12)->withQueryString();

        // --- LẤY DỮ LIỆU CHO BỘ LỌC ---
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();

        // Lấy tất cả Vehicle Brands và Vehicle Models đi kèm để tạo dependent dropdown
        $vehicleBrands = VehicleBrand::where('status', true)
            ->with(['vehicleModels' => function ($query) {
                $query->where('status', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        // Trả về view với tất cả dữ liệu cần thiết
        return view('customer.shops.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'vehicleBrands' => $vehicleBrands,
            'request' => $request, // Truyền request để lấy lại giá trị đã lọc
        ]);
    }

    /**
     * Hiển thị trang chi tiết sản phẩm.
     */
    public function show(Product $product)
    {
        if ($product->status !== 'active') {
            abort(404);
        }
        $product->load('category', 'brand', 'images', 'vehicleModels.vehicleBrand');
        return view('customer.products.show', compact('product'));
    }
}
