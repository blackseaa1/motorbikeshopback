<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse; // Import
use Illuminate\Http\RedirectResponse; // Import
use Illuminate\View\View; // Import
use Illuminate\Support\Facades\Log; // Import

class BrandController extends Controller
{
    /**
     * Hiển thị danh sách các thương hiệu với phân trang, tìm kiếm, lọc và sắp xếp.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Brand::query();

        // 1. Xử lý tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // 2. Xử lý lọc
        $filter = $request->input('filter', Brand::FILTER_STATUS_ALL);
        switch ($filter) {
            case Brand::FILTER_STATUS_ACTIVE_ONLY:
                $query->active();
                break;
            case Brand::FILTER_STATUS_INACTIVE_ONLY:
                $query->inactive();
                break;
            // 'all' hoặc các giá trị không hợp lệ khác sẽ không áp dụng bộ lọc nào
        }

        // 3. Xử lý sắp xếp
        $sortBy = $request->input('sort_by', Brand::SORT_BY_LATEST);
        switch ($sortBy) {
            case Brand::SORT_BY_OLDEST:
                $query->orderBy('created_at', 'asc');
                break;
            case Brand::SORT_BY_NAME_ASC:
                $query->orderBy('name', 'asc');
                break;
            case Brand::SORT_BY_NAME_DESC:
                $query->orderBy('name', 'desc');
                break;
            case Brand::SORT_BY_LATEST:
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $brands = $query->paginate(10)->withQueryString(); // Đổi từ 5 sang 10 cho nhất quán

        if ($request->expectsJson()) {
            $tableRowsHtml = '';
            $startIndex = $brands->firstItem() ? ($brands->firstItem() - 1) : 0; // STT bắt đầu

            foreach ($brands as $index => $brand) {
                $tableRowsHtml .= view('admin.productManagement.brand.partials._brand_table_row', [
                    'brand' => $brand,
                    'loopIndex' => $index,
                    'startIndex' => $startIndex,
                ])->render();
            }

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $brands->links('admin.vendor.pagination')->render(),
            ]);
        }

        return view('admin.productManagement.brand.brands', compact('brands'));
    }

    /**
     * Trả về dữ liệu chi tiết của một thương hiệu dưới dạng JSON cho AJAX. (NEW)
     *
     * @param Request $request
     * @param Brand $brand
     * @return JsonResponse|RedirectResponse
     */
    public function show(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($brand); // $brand đã có các accessor như logo_full_url, status_text
        }
        // If not an AJAX request, redirect to the index page
        return redirect()->route('admin.productManagement.brands.index');
    }


    /**
     * Lưu một thương hiệu mới vào cơ sở dữ liệu.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:100|unique:brands,name',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([Brand::STATUS_ACTIVE, Brand::STATUS_INACTIVE])],
        ];

        $validatedData = $request->validate($rules);

        $logoPath = null;
        if ($request->hasFile('logo_url')) {
            $logoPath = $request->file('logo_url')->store('brand_logos', 'public');
        }

        $brand = Brand::create(array_merge($validatedData, ['logo_url' => $logoPath]));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo thương hiệu thành công!',
                'brand' => $brand->refresh(), // Refresh để lấy các accessor mới nhất
            ]);
        }

        return redirect()->route('admin.productManagement.brands.index')
            ->with('success', 'Tạo thương hiệu thành công!');
    }

    /**
     * Cập nhật thông tin thương hiệu đã tồn tại.
     *
     * @param Request $request
     * @param Brand $brand
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:100|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([Brand::STATUS_ACTIVE, Brand::STATUS_INACTIVE])],
        ];

        $validatedData = $request->validate($rules);

        $logoPath = $brand->logo_url;
        if ($request->hasFile('logo_url')) {
            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            $logoPath = $request->file('logo_url')->store('brand_logos', 'public');
        }

        $brand->update(array_merge($validatedData, ['logo_url' => $logoPath]));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thương hiệu thành công!',
                'brand' => $brand->refresh(), // Refresh để đảm bảo lấy dữ liệu mới nhất (bao gồm logo_full_url)
            ]);
        }

        return redirect()->route('admin.productManagement.brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công!');
    }

    /**
     * Xóa một thương hiệu khỏi cơ sở dữ liệu.
     *
     * @param Request $request
     * @param Brand $brand
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        $adminDeletionPassword = Config::get('admin.deletion_password');
        if ($adminDeletionPassword) {
            $request->validate([
                'deletion_password' => 'required|string',
            ]);
            if ($request->input('deletion_password') !== $adminDeletionPassword) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mật khẩu xác nhận không đúng.',
                        'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                    ], 422);
                }
                return redirect()->back()->with('error', 'Mật khẩu xác nhận không đúng.');
            }
        }

        try {
            // Kiểm tra xem thương hiệu có sản phẩm liên quan không
            if ($brand->products()->exists()) {
                $errorMessage = 'Không thể xóa thương hiệu này vì vẫn còn sản phẩm liên quan.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMessage], 422);
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            $brand->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa thương hiệu thành công!',
                    'deleted_ids' => [$brand->id] // Trả về ID của thương hiệu đã xóa
                ]);
            }
            return redirect()->route('admin.productManagement.brands.index')
                ->with('success', 'Xóa thương hiệu thành công!');
        } catch (QueryException $e) {
            Log::error("Lỗi khi xóa thương hiệu ID {$brand->id}: " . $e->getMessage());
            $errorMessage = 'Đã xảy ra lỗi. Không thể xóa thương hiệu.';
            if ($e->getCode() === '23000') {
                $errorMessage = 'Không thể xóa thương hiệu này vì vẫn còn sản phẩm liên quan.';
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            return redirect()->route('admin.productManagement.brands.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Thay đổi trạng thái (active/inactive) của một thương hiệu.
     *
     * @param Request $request
     * @param Brand $brand
     * @return JsonResponse|RedirectResponse
     */
    public function toggleStatus(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        $brand->status = ($brand->isActive()) ? Brand::STATUS_INACTIVE : Brand::STATUS_ACTIVE;
        $brand->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thương hiệu thành công.',
                'brand' => $brand->refresh(), // Trả về đối tượng để cập nhật UI (bao gồm status_text, status_badge_class)
            ]);
        }
        $message = $brand->isActive() ? 'Thương hiệu đã được hiển thị.' : 'Thương hiệu đã được ẩn.';
        return redirect()->route('admin.productManagement.brands.index')->with('success', $message);
    }

    /**
     * Xóa hàng loạt các thương hiệu. (NEW)
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

        $adminDeletionPassword = Config::get('admin.deletion_password');
        if ($adminDeletionPassword) {
            $request->validate([
                'deletion_password' => 'required|string',
            ]);
            if ($request->input('deletion_password') !== $adminDeletionPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu xác nhận không đúng.',
                    'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                ], 422);
            }
        }

        $successfullyDeletedIds = [];
        $errors = [];

        $brandsToDelete = Brand::whereIn('id', $ids)->get();

        foreach ($brandsToDelete as $brand) {
            if ($brand->products()->exists()) {
                $errors[] = "Thương hiệu '{$brand->name}' không thể xóa vì vẫn còn sản phẩm liên quan.";
            } else {
                try {
                    if ($brand->logo_url) { // Xóa logo nếu có
                        Storage::disk('public')->delete($brand->logo_url);
                    }
                    $brand->delete();
                    $successfullyDeletedIds[] = $brand->id;
                } catch (QueryException $e) {
                    Log::error("Lỗi khi xóa hàng loạt thương hiệu ID {$brand->id}: " . $e->getMessage());
                    $errors[] = "Xảy ra lỗi khi xóa thương hiệu '{$brand->name}'.";
                }
            }
        }

        $deletedCount = count($successfullyDeletedIds);
        $message = '';

        if ($deletedCount > 0) {
            $message = "Đã xóa thành công {$deletedCount} thương hiệu.";
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_ids' => $successfullyDeletedIds,
                'errors' => $errors // Trả về cả lỗi để JS có thể hiển thị
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có thương hiệu nào được xóa. Lỗi: " . implode('; ', $errors) : "Không có thương hiệu nào được chọn để xóa hoặc xảy ra lỗi không xác định.",
            'errors' => $errors,
        ], 422);
    }

    /**
     * Bật/tắt trạng thái của nhiều thương hiệu. (NEW)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
            'status' => ['required', Rule::in([Brand::STATUS_ACTIVE, Brand::STATUS_INACTIVE])],
        ]);

        $ids = json_decode($request->input('ids'), true);
        $targetStatus = $request->input('status');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ hoặc rỗng.'], 400);
        }

        $adminDeletionPassword = Config::get('admin.deletion_password');
        if ($adminDeletionPassword) {
            $request->validate([
                'deletion_password' => 'required|string',
            ]);
            if ($request->input('deletion_password') !== $adminDeletionPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu xác nhận không đúng.',
                    'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                ], 422);
            }
        }

        $updatedCount = 0;
        $updatedBrands = [];
        $errors = [];

        $brandsToUpdate = Brand::whereIn('id', $ids)->get();

        foreach ($brandsToUpdate as $brand) {
            try {
                if ($brand->status !== $targetStatus) {
                    $brand->status = $targetStatus;
                    $brand->save();
                    $updatedCount++;
                }
                $updatedBrands[] = $brand->refresh(); // Lấy lại thuộc tính sau khi save
            } catch (\Exception $e) {
                Log::error("Lỗi khi cập nhật trạng thái thương hiệu ID {$brand->id}: " . $e->getMessage());
                $errors[] = "Không thể cập nhật trạng thái thương hiệu '{$brand->name}'.";
            }
        }

        if ($updatedCount > 0) {
            $message = "Đã cập nhật trạng thái thành công cho " . $updatedCount . " thương hiệu.";
            if (!empty($errors)) {
                $message .= " Một số thương hiệu có lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'brands' => $updatedBrands,
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có thương hiệu nào được cập nhật. Lỗi: " . implode('; ', $errors) : "Không có thương hiệu nào được chọn hoặc các thương hiệu đã ở trạng thái đích.",
            'errors' => $errors,
        ], 422);
    }
}