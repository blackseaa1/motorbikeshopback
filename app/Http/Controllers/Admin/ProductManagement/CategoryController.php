<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log; // Import Log

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách tất cả danh mục với phân trang, tìm kiếm, lọc và sắp xếp.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Category::query();

        // 1. Xử lý tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // 2. Xử lý lọc
        $filter = $request->input('filter', Category::FILTER_STATUS_ALL);
        switch ($filter) {
            case Category::FILTER_STATUS_ACTIVE_ONLY:
                $query->active();
                break;
            case Category::FILTER_STATUS_INACTIVE_ONLY:
                $query->inactive();
                break;
                // 'all' hoặc các giá trị không hợp lệ khác sẽ không áp dụng bộ lọc nào
        }

        // 3. Xử lý sắp xếp
        $sortBy = $request->input('sort_by', Category::SORT_BY_LATEST);
        switch ($sortBy) {
            case Category::SORT_BY_OLDEST:
                $query->orderBy('created_at', 'asc');
                break;
            case Category::SORT_BY_NAME_ASC:
                $query->orderBy('name', 'asc');
                break;
            case Category::SORT_BY_NAME_DESC:
                $query->orderBy('name', 'desc');
                break;
            case Category::SORT_BY_LATEST:
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $categories = $query->paginate(10)->withQueryString();

        if ($request->expectsJson()) {
            $tableRowsHtml = '';
            $startIndex = $categories->firstItem() ? ($categories->firstItem() - 1) : 0;

            foreach ($categories as $index => $category) {
                $tableRowsHtml .= view('admin.productManagement.category.partials._category_table_row', [
                    'category' => $category,
                    'loopIndex' => $index,
                    'startIndex' => $startIndex,
                ])->render();
            }

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $categories->links('admin.vendor.pagination')->render(),
            ]);
        }

        return view('admin.productManagement.category.categories', compact('categories'));
    }

    /**
     * Trả về dữ liệu chi tiết của một danh mục dưới dạng JSON cho AJAX.
     *
     * @param Request $request
     * @param Category $category
     * @return JsonResponse|RedirectResponse
     */
    public function show(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($category);
        }
        // If not an AJAX request, redirect to the index page
        return redirect()->route('admin.productManagement.categories.index');
    }

    /**
     * Lưu một danh mục mới vào cơ sở dữ liệu.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ];

        $validatedData = $request->validate($rules);

        $category = Category::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục thành công!',
                'category' => $category->refresh(),
            ]);
        }

        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Tạo danh mục thành công!');
    }

    /**
     * Cập nhật một danh mục đã có trong cơ sở dữ liệu.
     */
    public function update(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ];

        $validatedData = $request->validate($rules);

        $category->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công!',
                'category' => $category->refresh(),
            ]);
        }

        return redirect()->route('admin.productManagement.categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    /**
     * Xóa một danh mục khỏi cơ sở dữ liệu.
     *
     * @param Request $request
     * @param Category $category
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, Category $category): JsonResponse|RedirectResponse
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
            if ($category->products()->exists()) {
                $errorMessage = 'Không thể xóa danh mục này vì vẫn còn sản phẩm liên quan.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMessage], 422);
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            $category->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa danh mục thành công!',
                    'deleted_ids' => [$category->id]
                ]);
            }
            return redirect()->route('admin.productManagement.categories.index')
                ->with('success', 'Xóa danh mục thành công!');
        } catch (QueryException $e) {
            Log::error("Lỗi khi xóa danh mục ID {$category->id}: " . $e->getMessage());
            $errorMessage = 'Đã xảy ra lỗi. Không thể xóa danh mục.';
            if ($e->getCode() === '23000') {
                $errorMessage = 'Không thể xóa danh mục này vì vẫn còn sản phẩm liên quan.';
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            return redirect()->route('admin.productManagement.categories.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Thay đổi trạng thái (active/inactive) của một danh mục.
     * @param Request $request
     * @param Category $category
     * @return JsonResponse|RedirectResponse
     */
    public function toggleStatus(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $category->status = ($category->isActive()) ? Category::STATUS_INACTIVE : Category::STATUS_ACTIVE;
        $category->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái danh mục thành công!',
                'category' => $category->refresh(),
            ]);
        }

        $message = $category->isActive() ? 'Danh mục đã được hiển thị.' : 'Danh mục đã được ẩn.';
        return redirect()->route('admin.productManagement.categories.index')->with('success', $message);
    }

    /**
     * Xóa hàng loạt các danh mục.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
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

        $categoriesToDelete = Category::whereIn('id', $ids)->get();

        foreach ($categoriesToDelete as $category) {
            if ($category->products()->exists()) {
                $errors[] = "Danh mục '{$category->name}' không thể xóa vì vẫn còn sản phẩm liên quan.";
            } else {
                try {
                    $category->delete();
                    $successfullyDeletedIds[] = $category->id;
                } catch (QueryException $e) {
                    Log::error("Lỗi khi xóa hàng loạt danh mục ID {$category->id}: " . $e->getMessage());
                    $errors[] = "Xảy ra lỗi khi xóa danh mục '{$category->name}'.";
                }
            }
        }

        $deletedCount = count($successfullyDeletedIds);
        $message = '';

        if ($deletedCount > 0) {
            $message = "Đã xóa thành công {$deletedCount} danh mục.";
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_ids' => $successfullyDeletedIds,
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có danh mục nào được xóa. Lỗi: " . implode('; ', $errors) : "Không có danh mục nào được chọn để xóa hoặc xảy ra lỗi không xác định.",
            'errors' => $errors,
        ], 422);
    }

    /**
     * Bật/tắt trạng thái cài đặt thủ công của nhiều danh mục.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
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
        $updatedCategories = [];
        $errors = [];

        $categoriesToUpdate = Category::whereIn('id', $ids)->get();

        foreach ($categoriesToUpdate as $category) {
            try {
                if ($category->status !== $targetStatus) {
                    $category->status = $targetStatus;
                    $category->save();
                    $updatedCount++;
                }
                $updatedCategories[] = $category->refresh();
            } catch (\Exception $e) {
                Log::error("Lỗi khi cập nhật trạng thái danh mục ID {$category->id}: " . $e->getMessage());
                $errors[] = "Không thể cập nhật trạng thái danh mục '{$category->name}'.";
            }
        }

        if ($updatedCount > 0) {
            $message = "Đã cập nhật trạng thái thành công cho " . $updatedCount . " danh mục.";
            if (!empty($errors)) {
                $message .= " Một số danh mục có lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'categories' => $updatedCategories,
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có danh mục nào được cập nhật. Lỗi: " . implode('; ', $errors) : "Không có danh mục nào được chọn hoặc các danh mục đã ở trạng thái đích.",
            'errors' => $errors,
        ], 422);
    }
}
