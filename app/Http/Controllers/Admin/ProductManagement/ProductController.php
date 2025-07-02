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
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm, hỗ trợ xem cả thùng rác.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images']);
        $status = $request->query('status');
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'latest');

        // Apply filters based on status
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'active_only') {
            $query->active();
        } elseif ($status === 'inactive_only') {
            $query->inactive();
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(10)->withQueryString();

        if ($request->expectsJson()) {
            $tableRowsHtml = '';
            $startIndex = $products->firstItem() ? ($products->firstItem() - 1) : 0;

            $tableRowsHtml .= view('admin.productManagement.product.partials._product_table_rows', [
                'products' => $products,
                'startIndex' => $startIndex,
            ])->render();

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $products->links('admin.vendor.pagination')->render(),
            ]);
        }

        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        $vehicleBrands = VehicleBrand::with(['vehicleModels' => fn($q) => $q->where('status', 'active')->orderBy('name')])
            ->where('status', 'active')->orderBy('name')->get();

        return view('admin.productManagement.product.products', compact('products', 'categories', 'brands', 'vehicleBrands', 'status'));
    }

    /**
     * Lưu một sản phẩm mới vào cơ sở dữ liệu.
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
     * API: Lấy thông tin chi tiết của một sản phẩm dưới dạng JSON.
     */
    public function details(Product $product): JsonResponse
    {
        $product->load('category', 'brand', 'vehicleModels.vehicleBrand', 'images');
        return response()->json($product);
    }

    /**
     * API: Cập nhật số lượng tồn kho của một sản phẩm.
     */
    public function updateStockQuantity(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        try {
            $product->stock_quantity = $request->input('stock_quantity');
            $product->save();
            return response()->json(['success' => true, 'message' => 'Cập nhật tồn kho thành công!', 'new_stock' => $product->stock_quantity]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật tồn kho Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật tồn kho. Vui lòng thử lại.'], 500);
        }
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
     */
    public function toggleStatus(Product $product)
    {
        try {
            $product->status = ($product->status === Product::STATUS_ACTIVE) ? Product::STATUS_INACTIVE : Product::STATUS_ACTIVE;
            $product->save();
            $product->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!',
                'product' => $product,
                'updated_ids' => [$product->id]
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi bật/tắt trạng thái Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật trạng thái.'], 500);
        }
    }

    /**
     * Chuyển sản phẩm vào thùng rác (Xóa mềm).
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
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => "Đã chuyển sản phẩm '{$product->name}' vào thùng rác.",
                'deleted_ids' => [$product->id]
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa sản phẩm này.'], 500);
        }
    }

    /**
     * Khôi phục sản phẩm từ thùng rác.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        try {
            $product->restore();
            return response()->json([
                'success' => true,
                'message' => "Đã khôi phục sản phẩm '{$product->name}'.",
                'restored_ids' => [$product->id]
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục Sản phẩm (ID: {$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể khôi phục sản phẩm này.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn sản phẩm.
     */
    public function forceDelete(Request $request, $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $request->validate(['admin_password_confirm_delete' => 'required|string']);
        $configPassword = Config::get('admin.deletion_password');

        if ($configPassword && $request->input('admin_password_confirm_delete') !== $configPassword) {
            return response()->json(['errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($product->images as $image) {
                if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
            $product->images()->delete();
            $product->vehicleModels()->detach();

            $product->forceDelete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa vĩnh viễn sản phẩm!',
                'force_deleted_ids' => [$product->id]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa vĩnh viễn Sản phẩm (ID: {$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * API: Tìm kiếm sản phẩm (cho autocomplete, v.v.).
     */
    public function searchProductsApi(Request $request): JsonResponse
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
            ->with('firstImage')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Xóa mềm hàng loạt sản phẩm.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'admin_password_confirm_delete' => 'nullable|string', // nullable nếu không cấu hình password
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configPassword = Config::get('admin.deletion_password');
        if ($configPassword && $request->input('admin_password_confirm_delete') !== $configPassword) {
            return response()->json(['errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        $successfullyDeletedIds = [];
        $errors = [];

        DB::beginTransaction();
        try {
            $productsToSoftDelete = Product::whereIn('id', $request->ids)->get();

            foreach ($productsToSoftDelete as $product) {
                if ($product->orderItems()->exists()) {
                    $errors[] = "Sản phẩm '{$product->name}' không thể xóa mềm vì đã tồn tại trong các đơn hàng.";
                } else {
                    $product->delete();
                    $successfullyDeletedIds[] = $product->id;
                }
            }
            DB::commit();

            $message = '';
            if (count($successfullyDeletedIds) > 0) {
                $message .= "Đã chuyển thành công " . count($successfullyDeletedIds) . " sản phẩm vào thùng rác.";
            }
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }

            if (count($successfullyDeletedIds) === 0 && count($errors) > 0) {
                return response()->json(['success' => false, 'message' => $message, 'deleted_ids' => $successfullyDeletedIds, 'errors' => $errors], 422);
            }

            return response()->json(['success' => true, 'message' => $message, 'deleted_ids' => $successfullyDeletedIds, 'errors' => $errors]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa mềm hàng loạt sản phẩm: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống khi xóa mềm hàng loạt. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Bật/tắt trạng thái hàng loạt sản phẩm.
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
            'admin_password_confirm_delete' => 'nullable|string', // nullable
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configPassword = Config::get('admin.deletion_password');
        if ($configPassword && $request->input('admin_password_confirm_delete') !== $configPassword) {
            return response()->json(['errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        $targetStatus = $request->input('status');
        $updatedCount = 0;
        $updatedProducts = [];
        $errors = [];
        $updatedIds = [];

        DB::beginTransaction();
        try {
            $productsToUpdate = Product::whereIn('id', $request->ids)->get();

            foreach ($productsToUpdate as $product) {
                if ($product->status !== $targetStatus) {
                    $product->status = $targetStatus;
                    $product->save();
                    $updatedCount++;
                    $updatedIds[] = $product->id;
                }
                $updatedProducts[] = $product->refresh();
            }
            DB::commit();

            $message = '';
            if ($updatedCount > 0) {
                $message .= "Đã cập nhật trạng thái thành công cho " . $updatedCount . " sản phẩm.";
            } else {
                $message .= "Không có sản phẩm nào được cập nhật hoặc các sản phẩm đã ở trạng thái đích.";
            }
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'products' => $updatedProducts,
                'updated_ids' => $updatedIds,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật trạng thái hàng loạt sản phẩm: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống khi cập nhật trạng thái hàng loạt. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Khôi phục hàng loạt sản phẩm từ thùng rác.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $successfullyRestoredIds = [];
        $errors = [];

        DB::beginTransaction();
        try {
            $productsToRestore = Product::onlyTrashed()->whereIn('id', $request->ids)->get();

            foreach ($productsToRestore as $product) {
                try {
                    $product->restore();
                    $successfullyRestoredIds[] = $product->id;
                } catch (\Exception $e) {
                    Log::error("Lỗi khi khôi phục sản phẩm ID {$product->id}: " . $e->getMessage());
                    $errors[] = "Không thể khôi phục sản phẩm '{$product->name}'.";
                }
            }
            DB::commit();

            $message = '';
            if (count($successfullyRestoredIds) > 0) {
                $message .= "Đã khôi phục thành công " . count($successfullyRestoredIds) . " sản phẩm.";
            }
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }

            if (count($successfullyRestoredIds) === 0 && count($errors) > 0) {
                return response()->json(['success' => false, 'message' => $message, 'restored_ids' => $successfullyRestoredIds, 'errors' => $errors], 422);
            }

            return response()->json(['success' => true, 'message' => $message, 'restored_ids' => $successfullyRestoredIds, 'errors' => $errors]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi khôi phục hàng loạt sản phẩm: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống khi khôi phục hàng loạt. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn hàng loạt sản phẩm.
     */
    public function bulkForceDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'admin_password_confirm_delete' => 'nullable|string', // nullable
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configPassword = Config::get('admin.deletion_password');
        if ($configPassword && $request->input('admin_password_confirm_delete') !== $configPassword) {
            return response()->json(['errors' => ['admin_password_confirm_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        $successfullyForceDeletedIds = [];
        $errors = [];

        DB::beginTransaction();
        try {
            $productsToForceDelete = Product::onlyTrashed()->whereIn('id', $request->ids)->get();

            foreach ($productsToForceDelete as $product) {
                try {
                    foreach ($product->images as $image) {
                        if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                            Storage::disk('public')->delete($image->image_url);
                        }
                    }
                    $product->images()->delete();
                    $product->vehicleModels()->detach();

                    $product->forceDelete();
                    $successfullyForceDeletedIds[] = $product->id;
                } catch (\Exception $e) {
                    Log::error("Lỗi khi xóa vĩnh viễn sản phẩm ID {$product->id}: " . $e->getMessage());
                    $errors[] = "Không thể xóa vĩnh viễn sản phẩm '{$product->name}'.";
                }
            }
            DB::commit();

            $message = '';
            if (count($successfullyForceDeletedIds) > 0) {
                $message .= "Đã xóa vĩnh viễn thành công " . count($successfullyForceDeletedIds) . " sản phẩm.";
            }
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }

            if (count($successfullyForceDeletedIds) === 0 && count($errors) > 0) {
                return response()->json(['success' => false, 'message' => $message, 'force_deleted_ids' => $successfullyForceDeletedIds, 'errors' => $errors], 422);
            }

            return response()->json(['success' => true, 'message' => $message, 'force_deleted_ids' => $successfullyForceDeletedIds, 'errors' => $errors]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa vĩnh viễn hàng loạt sản phẩm: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống khi xóa vĩnh viễn hàng loạt. Vui lòng thử lại.'], 500);
        }
    }
}
