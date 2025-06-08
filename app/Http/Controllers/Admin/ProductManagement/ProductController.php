<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\VehicleBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Hiển thị trang danh sách sản phẩm và các dữ liệu cần thiết cho form.
     */
    public function index()
    {
        // Lấy danh sách sản phẩm với các quan hệ cần thiết để tránh N+1 query
        // Eager loading cũng giúp các accessor trong Model Product (thumbnail_url) hoạt động hiệu quả
        $products = Product::with(['category', 'brand', 'images'])->latest()->paginate(15);

        // Lấy dữ liệu cho các dropdown trong modal
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();

        // Lấy các hãng xe và dòng xe để tạo optgroup cho select2
        $vehicleBrands = VehicleBrand::with(['vehicleModels' => function ($query) {
            $query->where('status', 'active')->orderBy('name');
        }])->where('status', 'active')->orderBy('name')->get();

        return view('admin.productManagement.products', compact('products', 'categories', 'brands', 'vehicleBrands'));
    }

    /**
     * Lưu một sản phẩm mới vào database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'material' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'specifications' => 'nullable|string',
            'vehicle_model_ids' => 'nullable|array',
            'vehicle_model_ids.*' => 'exists:vehicle_models,id',
            'product_images' => 'nullable|array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $productData = $request->except(['product_images', 'vehicle_model_ids']);

            // 1. Tạo sản phẩm
            $product = Product::create($productData);

            // 2. Gắn các dòng xe tương thích (many-to-many)
            if ($request->has('vehicle_model_ids')) {
                $product->vehicleModels()->sync($request->vehicle_model_ids);
            }

            // 3. Xử lý và lưu hình ảnh
            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    // Sử dụng 'image_url' để khớp với Model ProductImage đã cập nhật
                    $product->images()->create([
                        'image_url' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo sản phẩm mới thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết của một sản phẩm để hiển thị trong form update.
     * Route Model Binding sẽ tự động tìm Product theo $id.
     */
    public function show(Product $product)
    {
        // Load các quan hệ cần thiết để trả về JSON đầy đủ
        $product->load('vehicleModels', 'images');
        return response()->json($product);
    }

    /**
     * Cập nhật thông tin sản phẩm.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'material' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'specifications' => 'nullable|string',
            'vehicle_model_ids' => 'nullable|array',
            'vehicle_model_ids.*' => 'exists:vehicle_models,id',
            'product_images' => 'nullable|array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $productData = $request->except(['product_images', 'vehicle_model_ids', '_method', 'existing_images']);

            // 1. Cập nhật thông tin sản phẩm
            $product->update($productData);

            // 2. Đồng bộ lại các dòng xe tương thích
            $product->vehicleModels()->sync($request->input('vehicle_model_ids', []));

            // 3. Xử lý ảnh
            // 3.1 Xóa các ảnh cũ đã bị người dùng loại bỏ
            $existingImageIds = $product->images->pluck('id')->toArray();
            $keptImageIds = $request->input('existing_images', []); // ID của các ảnh được giữ lại
            $imageIdsToDelete = array_diff($existingImageIds, $keptImageIds);

            if (!empty($imageIdsToDelete)) {
                $imagesToDelete = ProductImage::whereIn('id', $imageIdsToDelete)->get();
                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->image_url);
                    $image->delete();
                }
            }

            // 3.2 Thêm ảnh mới
            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    // SỬA ĐỔI 1: Sửa lỗi logic khi tạo ảnh mới và dùng đúng key 'image_url'
                    $product->images()->create([
                        'image_url' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm ' . $product->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm.
     */
    public function destroy(Product $product)
    {
        // SỬA ĐỔI 2: Thêm kiểm tra ràng buộc với các đơn hàng để đảm bảo an toàn dữ liệu
        if ($product->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa sản phẩm này vì đã tồn tại trong các đơn hàng của khách.'
            ], 422); // 422: Unprocessable Entity
        }

        DB::beginTransaction();
        try {
            // Xóa tất cả hình ảnh liên quan trong storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_url);
            }

            // Xóa sản phẩm.
            // Các record trong bảng pivot (product_vehicle_models) và bảng product_images
            // sẽ được tự động xóa theo nếu bạn đã thiết lập 'cascadeOnDelete()' trong migrations.
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa sản phẩm ' . $product->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống khi xóa sản phẩm.'
            ], 500);
        }
    }
}
