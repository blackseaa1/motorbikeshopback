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
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm, hỗ trợ tìm kiếm, lọc, sắp xếp và phân trang AJAX, xem cả thùng rác.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Product::with(['category', 'brand', 'images']);
        $status_query_param = $request->query('status'); // Lấy tham số status từ URL

        // 1. Xử lý tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // 2. Xử lý lọc
        $filter = $request->input('filter', 'all');

        // Xử lý riêng trường hợp "trashed" để tránh xung đột với các filter khác
        if ($filter === 'trashed' || $status_query_param === 'trashed') {
            $query->onlyTrashed();
        } else {
            // Nếu không phải trashed, áp dụng các filter trạng thái khác
            switch ($filter) {
                case 'active':
                    $query->active();
                    break;
                case 'inactive':
                    $query->inactive();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
                case 'low_stock':
                    $query->lowStock();
                    break;
                    // 'all' sẽ không thêm điều kiện lọc nào
            }
        }


        // 3. Xử lý sắp xếp
        $sortBy = $request->input('sort_by', 'latest');
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

            foreach ($products as $index => $product) {
                $tableRowsHtml .= view('admin.productManagement.product.partials._product_table_row', [
                    'product' => $product,
                    'loopIndex' => $index,
                    'startIndex' => $startIndex,
                ])->render();
            }

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $products->links('admin.vendor.pagination')->render(),
            ]);
        }

        // Nếu không phải AJAX, trả về view đầy đủ với dữ liệu đã phân trang
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        $vehicleBrands = VehicleBrand::with(['vehicleModels' => fn($q) => $q->where('status', 'active')->orderBy('name')])
            ->where('status', 'active')->orderBy('name')->get();

        // Truyền biến $status_query_param để hiển thị tab đúng trên giao diện
        return view('admin.productManagement.product.products', compact('products', 'categories', 'brands', 'vehicleBrands', 'status_query_param'));
    }

    /**
     * Lưu một sản phẩm mới vào cơ sở dữ liệu.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
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
     * Được sử dụng bởi modal "Xem chi tiết" và "Cập nhật" trên trang.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function getProductDetailsApi(Product $product): JsonResponse
    {
        // Tải các mối quan hệ cần thiết để hiển thị trong modal
        $product->load('category', 'brand', 'vehicleModels.vehicleBrand', 'images');

        // Trả về sản phẩm dưới dạng JSON. Laravel sẽ tự động chuyển đổi các accessors.
        return response()->json($product);
    }

    /**
     * API: Cập nhật số lượng tồn kho của một sản phẩm.
     *
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function updateStockQuantity(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stock_quantity' => 'required|integer|min:0', // Đảm bảo số lượng là số nguyên không âm
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        try {
            $product->stock_quantity = $request->input('stock_quantity');
            $product->save();

            // Trả về phản hồi thành công cùng số lượng mới
            return response()->json(['success' => true, 'message' => 'Cập nhật tồn kho thành công!', 'new_stock' => $product->stock_quantity]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật tồn kho Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật tồn kho. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Cập nhật thông tin sản phẩm.
     * Sử dụng Route-Model Binding, Laravel sẽ tự động tìm sản phẩm.
     * Để nó tìm được sản phẩm trong thùng rác, cần thêm ->withTrashed() ở file routes.
     *
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(Request $request, Product $product): JsonResponse
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
     * Chuyển đổi trạng thái (Mở bán/Dừng bán) của một sản phẩm.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function toggleStatus(Product $product): JsonResponse
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
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        // Kiểm tra xem sản phẩm đã từng được sử dụng trong đơn hàng nào chưa
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
                'message' => "Đã chuyển sản phẩm '{$product->name}' vào thùng rác.",
                'deleted_ids' => [$product->id] // [FIX] Return deleted_ids
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm Sản phẩm (ID: {$product->id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa sản phẩm này.'], 500);
        }
    }

    /**
     * Khôi phục sản phẩm từ thùng rác.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        try {
            $product->restore();
            return response()->json([
                'success' => true,
                'message' => "Đã khôi phục sản phẩm '{$product->name}'.",
                'restored_ids' => [$product->id] // [FIX] Return restored_ids
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục Sản phẩm (ID: {$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể khôi phục sản phẩm này.'], 500);
        }
    }

    /**
     * Xóa vĩnh viễn sản phẩm.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        // Xác thực mật khẩu admin nếu cấu hình yêu cầu
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

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa vĩnh viễn sản phẩm!',
                'deleted_ids' => [$product->id] // [FIX] Return deleted_ids
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa vĩnh viễn Sản phẩm (ID: {$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa vĩnh viễn sản phẩm này.'], 500);
        }
    }

    /**
     * API để tìm kiếm sản phẩm cho các chức năng như thêm vào đơn hàng.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchProductsApi(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');

        if (strlen($searchTerm) < 2) {
            return response()->json([]);
        }

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->select(['id', 'name', 'price', 'stock_quantity'])
            ->with('firstImage') // Tải ảnh đầu tiên để hiển thị
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Xóa hàng loạt các sản phẩm (chuyển vào thùng rác).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json', // Expecting a JSON string of IDs
        ]);

        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ hoặc rỗng.'], 400);
        }

        $successfullyDeletedIds = [];
        $errors = [];

        // Lấy các sản phẩm tồn tại trong CSDL từ danh sách IDs
        $productsToProcess = Product::whereIn('id', $ids)->get();
        // Xác định các IDs không tìm thấy
        $notFoundIds = array_diff($ids, $productsToProcess->pluck('id')->toArray());
        foreach ($notFoundIds as $id) {
            $errors[] = "Sản phẩm với ID '{$id}' không tồn tại.";
        }

        foreach ($productsToProcess as $product) {
            // Chỉ xóa mềm nếu sản phẩm chưa từng có trong đơn hàng
            if ($product->orderItems()->exists()) {
                $errors[] = "Sản phẩm '{$product->name}' đã tồn tại trong đơn hàng và không thể xóa.";
            } else {
                try {
                    $product->delete(); // Thực hiện soft delete
                    $successfullyDeletedIds[] = $product->id;
                } catch (\Exception $e) {
                    Log::error("Lỗi khi xóa mềm hàng loạt sản phẩm ID {$product->id}: " . $e->getMessage());
                    $errors[] = "Xảy ra lỗi khi xóa mềm sản phẩm '{$product->name}'.";
                }
            }
        }

        $totalProcessed = count($ids); // Tổng số ID được gửi
        $totalDeleted = count($successfullyDeletedIds); // Số lượng sản phẩm đã chuyển vào thùng rác thành công
        $totalFailed = count($errors); // Số lượng lỗi không thể xử lý

        $message = "";
        $success = false;

        if ($totalDeleted > 0) {
            $success = true;
            $message = "Đã chuyển thành công {$totalDeleted} sản phẩm vào thùng rác.";
        }

        if ($totalFailed > 0) {
            if ($message) $message .= " ";
            $message .= "Một số lỗi xảy ra: " . implode('; ', $errors);
        }

        if ($totalDeleted === 0 && $totalFailed === 0 && $totalProcessed > 0) {
            $message = "Không có sản phẩm nào được chuyển vào thùng rác hoặc đủ điều kiện xóa.";
            $success = false; // Vẫn coi là thất bại nếu không có sản phẩm nào được xóa thành công.
        } else if ($totalProcessed === 0) {
            $message = "Không có sản phẩm nào được chọn.";
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'deleted_ids' => $successfullyDeletedIds, // Trả về ID đã xóa để JS có thể cập nhật
            'errors' => $errors,
        ], $success ? 200 : 422); // 200 cho thành công/thành công một phần, 422 cho thất bại hoàn toàn
    }

    /**
     * Bật/tắt trạng thái của nhiều sản phẩm.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json', // Expecting a JSON string of IDs
            'status' => ['required', \Illuminate\Validation\Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
        ]);

        $ids = json_decode($request->input('ids'), true);
        $targetStatus = $request->input('status');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ.'], 400);
        }

        $successfullyUpdatedIds = [];
        $alreadyInTargetStateCount = 0;
        $errors = [];

        $productsToProcess = Product::whereIn('id', $ids)->get();
        $foundIds = $productsToProcess->pluck('id')->toArray();
        $notFoundIds = array_diff($ids, $foundIds);
        foreach ($notFoundIds as $id) {
            $errors[] = "Sản phẩm với ID '{$id}' không tồn tại.";
        }

        foreach ($productsToProcess as $product) {
            if ($product->status === $targetStatus) {
                $alreadyInTargetStateCount++;
            } else {
                try {
                    $product->status = $targetStatus;
                    $product->save();
                    $successfullyUpdatedIds[] = $product->id;
                } catch (\Exception $e) {
                    Log::error("Lỗi khi cập nhật trạng thái sản phẩm ID {$product->id}: " . $e->getMessage());
                    $errors[] = "Không thể cập nhật trạng thái sản phẩm '{$product->name}'.";
                }
            }
        }

        $totalProcessed = count($ids);
        $totalUpdated = count($successfullyUpdatedIds);
        $totalFailed = count($errors);

        $message = "";
        $success = false;

        if ($totalUpdated > 0) {
            $success = true;
            $message = "Đã cập nhật trạng thái thành công cho {$totalUpdated} sản phẩm.";
        }

        if ($alreadyInTargetStateCount > 0) {
            if ($message) $message .= " ";
            $message .= "({$alreadyInTargetStateCount} sản phẩm đã ở trạng thái đích).";
            $success = true;
        }

        if ($totalFailed > 0) {
            if ($message) $message .= " ";
            $message .= "Một số lỗi xảy ra: " . implode('; ', $errors);
        }

        if ($totalUpdated === 0 && $alreadyInTargetStateCount === 0 && $totalFailed === 0 && $totalProcessed > 0) {
            $message = "Không có sản phẩm nào được cập nhật hoặc thay đổi trạng thái.";
            $success = false;
        } else if ($totalProcessed === 0) {
            $message = "Không có sản phẩm nào được chọn.";
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'products' => Product::whereIn('id', $successfullyUpdatedIds)->get(), // Trả về các product đã được cập nhật
            'updated_ids' => $successfullyUpdatedIds,
            'errors' => $errors,
        ], $success ? 200 : 422);
    }

    /**
     * Khôi phục hàng loạt sản phẩm từ thùng rác.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
        ]);

        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ hoặc rỗng.'], 400);
        }

        $successfullyRestoredIds = [];
        $errors = [];

        $productsToProcess = Product::onlyTrashed()->whereIn('id', $ids)->get();
        $notFoundIds = array_diff($ids, $productsToProcess->pluck('id')->toArray());
        foreach ($notFoundIds as $id) {
            $errors[] = "Sản phẩm với ID '{$id}' không tồn tại trong thùng rác.";
        }

        foreach ($productsToProcess as $product) {
            try {
                $product->restore();
                $successfullyRestoredIds[] = $product->id;
            } catch (\Exception $e) {
                Log::error("Lỗi khi khôi phục hàng loạt sản phẩm ID {$product->id}: " . $e->getMessage());
                $errors[] = "Xảy ra lỗi khi khôi phục sản phẩm '{$product->name}'.";
            }
        }

        $totalProcessed = count($ids);
        $totalRestored = count($successfullyRestoredIds);
        $totalFailed = count($errors);

        $message = "";
        $success = false;

        if ($totalRestored > 0) {
            $success = true;
            $message = "Đã khôi phục thành công {$totalRestored} sản phẩm.";
        }

        if ($totalFailed > 0) {
            if ($message) $message .= " ";
            $message .= "Một số lỗi xảy ra: " . implode('; ', $errors);
        }

        if ($totalRestored === 0 && $totalFailed === 0 && $totalProcessed > 0) {
            $message = "Không có sản phẩm nào được khôi phục hoặc đủ điều kiện khôi phục.";
            $success = false;
        } else if ($totalProcessed === 0) {
            $message = "Không có sản phẩm nào được chọn.";
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'restored_ids' => $successfullyRestoredIds,
            'errors' => $errors,
        ], $success ? 200 : 422);
    }

    /**
     * Xóa vĩnh viễn hàng loạt sản phẩm từ thùng rác.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkForceDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
            'admin_password_bulk_force_delete' => 'required|string', // Mật khẩu xác nhận
        ]);

        $configPassword = Config::get('admin.deletion_password');
        if (!$configPassword || $request->input('admin_password_bulk_force_delete') !== $configPassword) {
            return response()->json(['errors' => ['admin_password_bulk_force_delete' => ['Mật khẩu xác nhận không đúng.']]], 422);
        }

        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ hoặc rỗng.'], 400);
        }

        $successfullyForceDeletedIds = [];
        $errors = [];

        $productsToProcess = Product::onlyTrashed()->whereIn('id', $ids)->get();
        $notFoundIds = array_diff($ids, $productsToProcess->pluck('id')->toArray());
        foreach ($notFoundIds as $id) {
            $errors[] = "Sản phẩm với ID '{$id}' không tồn tại trong thùng rác.";
        }

        DB::beginTransaction();
        try {
            foreach ($productsToProcess as $product) {
                // Xóa các ảnh liên quan trong storage
                foreach ($product->images as $image) {
                    if ($image->image_url && Storage::disk('public')->exists($image->image_url)) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                }
                // Xóa các record ảnh và quan hệ many-to-many
                $product->images()->delete();
                $product->vehicleModels()->detach();
                $product->forceDelete();
                $successfullyForceDeletedIds[] = $product->id;
            }
            DB::commit();

            $totalProcessed = count($ids);
            $totalForceDeleted = count($successfullyForceDeletedIds);
            $totalFailed = count($errors);

            $message = "";
            $success = false;

            if ($totalForceDeleted > 0) {
                $success = true;
                $message = "Đã xóa vĩnh viễn thành công {$totalForceDeleted} sản phẩm.";
            }

            if ($totalFailed > 0) {
                if ($message) $message .= " ";
                $message .= "Một số lỗi xảy ra: " . implode('; ', $errors);
            }

            if ($totalForceDeleted === 0 && $totalFailed === 0 && $totalProcessed > 0) {
                $message = "Không có sản phẩm nào được xóa vĩnh viễn hoặc đủ điều kiện xóa.";
                $success = false;
            } else if ($totalProcessed === 0) {
                $message = "Không có sản phẩm nào được chọn.";
                $success = false;
            }

            return response()->json([
                'success' => $success,
                'message' => $message,
                'deleted_ids' => $successfullyForceDeletedIds,
                'errors' => $errors,
            ], $success ? 200 : 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa vĩnh viễn hàng loạt sản phẩm: " . $e->getMessage());
            $errorMessage = 'Đã xảy ra lỗi hệ thống khi xóa vĩnh viễn hàng loạt sản phẩm.';
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }
}