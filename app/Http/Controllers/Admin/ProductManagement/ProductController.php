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
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm, hỗ trợ xem cả thùng rác.
     * Logic này đã chính xác và không cần thay đổi.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images']);
        $status = $request->query('status');

        if ($status === 'trashed') {
            $query->onlyTrashed();
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        $vehicleBrands = VehicleBrand::with(['vehicleModels' => fn($q) => $q->where('status', 'active')->orderBy('name')])
            ->where('status', 'active')->orderBy('name')->get();

        return view('admin.productManagement.product.products', compact('products', 'categories', 'brands', 'vehicleBrands', 'status'));
    }

    /**
     * Lưu một sản phẩm mới vào cơ sở dữ liệu.
     * Logic này đã chính xác và không cần thay đổi.
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $productData = $request->except(['product_images', 'vehicle_model_ids', 'is_active']);
            $productData['status'] = $request->has('is_active') ? 'active' : 'inactive';

            $product = Product::create($productData);

            if ($request->has('vehicle_model_ids')) {
                $product->vehicleModels()->sync($request->vehicle_model_ids);
            }

            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $product->images()->create(['image_url' => $path]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tạo sản phẩm mới thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết của một sản phẩm.
     * Sử dụng Route-Model Binding, Laravel sẽ tự động tìm sản phẩm.
     * Để nó tìm được sản phẩm trong thùng rác, cần thêm ->withTrashed() ở file routes.
     */
    public function show(Product $product)
    {
        $product->load('category', 'brand', 'vehicleModels', 'images');
        return response()->json($product);
    }

    /**
     * Cập nhật thông tin sản phẩm.
     * Sử dụng Route-Model Binding, Laravel sẽ tự động tìm sản phẩm.
     * Để nó tìm được sản phẩm trong thùng rác, cần thêm ->withTrashed() ở file routes.
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
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'integer|exists:product_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $productData = $request->except(['product_images', 'vehicle_model_ids', '_method', 'existing_images', 'is_active']);
            $productData['status'] = $request->has('is_active') ? 'active' : 'inactive';
            $product->update($productData);

            $product->vehicleModels()->sync($request->input('vehicle_model_ids', []));

            $existingImageIds = $product->images->pluck('id')->toArray();
            $keptImageIds = $request->input('existing_images', []);
            $imageIdsToDelete = array_diff($existingImageIds, $keptImageIds);

            if (!empty($imageIdsToDelete)) {
                $imagesToDelete = ProductImage::whereIn('id', $imageIdsToDelete)->get();
                foreach ($imagesToDelete as $image) {
                    if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                    $image->delete();
                }
            }

            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $product->images()->create(['image_url' => $path]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm ' . $product->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Chuyển đổi trạng thái (Mở bán/Dừng bán).
     * Logic này đã chính xác và không cần thay đổi.
     */
    public function toggleStatus(Product $product)
    {
        try {
            $product->status = ($product->status === Product::STATUS_ACTIVE) ? Product::STATUS_INACTIVE : Product::STATUS_ACTIVE;
            $product->save();
            $product->refresh(); // Lấy lại dữ liệu mới nhất (bao gồm cả accessors)

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!',
                'product' => $product // Trả về product để JS cập nhật giao diện
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi đổi trạng thái Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật trạng thái.'], 500);
        }
    }

    /**
     * Chuyển sản phẩm vào thùng rác (Xóa mềm).
     * Logic này đã chính xác và không cần thay đổi.
     */
    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa sản phẩm này vì đã tồn tại trong các đơn hàng.'
            ], 422);
        }

        try {
            $product->delete(); // Thực hiện soft delete
            return response()->json([
                'success' => true,
                'message' => "Đã chuyển sản phẩm '{$product->name}' vào thùng rác."
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa sản phẩm này.'], 500);
        }
    }

    /**
     * Khôi phục sản phẩm từ thùng rác.
     * Logic này đã chính xác và không cần thay đổi.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        try {
            $product->restore();
            return response()->json(['success' => true, 'message' => "Đã khôi phục sản phẩm '{$product->name}'."]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục Sản phẩm (ID: {$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể khôi phục sản phẩm này.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn sản phẩm.
     * Logic này đã chính xác và không cần thay đổi.
     */
    public function forceDelete(Request $request, $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $request->validate(['admin_password_confirm_delete' => 'required|string']);
        $configPassword = Config::get('admin.deletion_password');

        if (!$configPassword || $request->input('admin_password_confirm_delete') !== $configPassword) {
            return response()->json(['errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        DB::beginTransaction();
        try {
            // Xóa các ảnh liên quan trong storage
            foreach ($product->images as $image) {
                if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
            // Xóa các record ảnh và quan hệ many-to-many
            $product->images()->delete();
            $product->vehicleModels()->detach();

            // Xóa vĩnh viễn sản phẩm
            $product->forceDelete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Đã xóa vĩnh viễn sản phẩm!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa vĩnh viễn Sản phẩm (ID: {$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa vĩnh viễn sản phẩm này.'], 500);
        }
    }
    public function searchProductsApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $searchTerm = $request->input('search', '');

        if (strlen($searchTerm) < 2) {
            return response()->json([]);
        }

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sku', 'LIKE', "%{$searchTerm}%");
            })
            ->select(['id', 'name', 'sku', 'price', 'stock_quantity'])
            ->with('firstImage') // Tải ảnh đầu tiên để hiển thị
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
