<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Import Carbon

class PromotionController extends Controller
{
    /**
     * Hiển thị danh sách các mã khuyến mãi, hỗ trợ tìm kiếm và phân trang AJAX, lọc và sắp xếp.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        // Khởi tạo truy vấn cơ bản
        $query = Promotion::query();

        // 1. Xử lý tìm kiếm (giữ nguyên)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // 2. Xử lý lọc theo trạng thái/kiểu (Filter)
        $filter = $request->input('filter', Promotion::FILTER_STATUS_ALL); // Default to 'all'
        switch ($filter) {
            case Promotion::FILTER_STATUS_ACTIVE:
                $query->activeEffective();
                break;
            case Promotion::FILTER_STATUS_SCHEDULED:
                $query->scheduledEffective();
                break;
            case Promotion::FILTER_STATUS_EXPIRED:
                $query->expiredEffective();
                break;
            case Promotion::FILTER_STATUS_INACTIVE:
                $query->inactiveEffective();
                break;
            case Promotion::FILTER_STATUS_MANUAL_ACTIVE:
                $query->where('status', Promotion::STATUS_MANUAL_ACTIVE);
                break;
            case Promotion::FILTER_STATUS_MANUAL_INACTIVE:
                $query->where('status', Promotion::STATUS_MANUAL_INACTIVE);
                break;
            case Promotion::FILTER_EXPIRY_EXPIRING_SOON:
                $query->expiringSoon();
                break;
            case Promotion::FILTER_EXPIRY_EXPIRED: // Hết hạn theo ngày hoặc hết lượt
                $query->where(function ($q) {
                    $q->expiredByDate()->orWhere->usesExhausted();
                });
                break;
            case Promotion::FILTER_USAGE_HIGHLY_USED:
                $query->highlyUsed();
                break;
            case Promotion::FILTER_USAGE_LOWLY_USED:
                $query->lowlyUsed();
                break;
            case Promotion::FILTER_USAGE_NO_USES:
                $query->noUses();
                break;
                // 'all' hoặc các giá trị không hợp lệ khác sẽ không áp dụng bộ lọc nào
        }

        // 3. Xử lý sắp xếp (Sort By)
        $sortBy = $request->input('sort_by', 'latest'); // Default sort
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'code_asc':
                $query->orderBy('code', 'asc');
                break;
            case 'code_desc':
                $query->orderBy('code', 'desc');
                break;
            case 'end_date_asc': // Sắp hết hạn nhất lên đầu
                $query->orderBy('end_date', 'asc');
                break;
            case 'uses_most': // Dùng nhiều nhất lên đầu
                $query->orderBy('uses_count', 'desc');
                break;
            case 'uses_least': // Dùng ít nhất lên đầu
                $query->orderBy('uses_count', 'asc');
                break;
            case 'discount_highest': // Giá trị giảm giá cao nhất
                // Sắp xếp theo fixed_discount_amount giảm dần, sau đó đến discount_percentage giảm dần
                // Có thể phức tạp nếu muốn so sánh cả hai loại giảm giá một cách công bằng
                // Đơn giản hóa: ưu tiên Fixed cao nhất, sau đó đến Percentage cao nhất
                $query->orderByRaw('CASE WHEN discount_type = ? THEN fixed_discount_amount ELSE 0 END DESC', [Promotion::DISCOUNT_TYPE_FIXED])
                    ->orderByRaw('CASE WHEN discount_type = ? THEN discount_percentage ELSE 0 END DESC', [Promotion::DISCOUNT_TYPE_PERCENTAGE]);
                break;
            case 'min_order_highest':
                $query->orderBy('min_order_amount', 'desc');
                break;
            case 'min_order_lowest':
                $query->orderBy('min_order_amount', 'asc');
                break;
            case 'latest': // Default
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 4. Phân trang
        // Sử dụng $promotions để lấy dữ liệu sau khi áp dụng filter và sort
        $promotions = $query->paginate(10)->withQueryString();

        // Xử lý AJAX request
        if ($request->expectsJson()) {
            $tableRowsHtml = '';
            // Lấy STT bắt đầu của trang hiện tại
            $startIndex = $promotions->firstItem() ? ($promotions->firstItem() - 1) : 0;

            foreach ($promotions as $index => $promotion) {
                $tableRowsHtml .= view('admin.sales.promotion.partials._promotion_table_row', [
                    'promotion' => $promotion,
                    'loopIndex' => $index,
                    'startIndex' => $startIndex,
                ])->render();
            }

            return response()->json([
                'table_rows' => $tableRowsHtml,
                'pagination_links' => $promotions->links('admin.vendor.pagination')->render(),
            ]);
        }

        // Nếu không phải AJAX, trả về view đầy đủ với dữ liệu đã phân trang
        return view('admin.sales.promotion.promotions', ['promotions' => $promotions]);
    }

    /**
     * Trả về dữ liệu chi tiết của một mã khuyến mãi dưới dạng JSON cho AJAX.
     *
     * @param Request $request
     * @param Promotion $promotion
     * @return JsonResponse|RedirectResponse
     */
    public function show(Request $request, Promotion $promotion): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($promotion);
        }
        return redirect()->route('admin.sales.promotions.index');
    }

    /**
     * Lưu một mã khuyến mãi mới vào cơ sở dữ liệu.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge(['code' => strtoupper($request->input('code'))]);

        $rules = [
            'code'              => 'required|string|max:50|unique:promotions,code|uppercase',
            'description'       => 'nullable|string|max:255',
            'discount_type'     => ['required', Rule::in([Promotion::DISCOUNT_TYPE_PERCENTAGE, Promotion::DISCOUNT_TYPE_FIXED])],
            'start_date'        => 'required|date_format:Y-m-d\TH:i',
            'end_date'          => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses'          => 'nullable|integer|min:1',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'status'            => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
        ];

        if ($request->input('discount_type') === Promotion::DISCOUNT_TYPE_PERCENTAGE) {
            $rules['discount_percentage'] = 'required|numeric|min:0.01|max:100.00';
            $rules['fixed_discount_amount'] = 'nullable';
            $rules['max_discount_amount'] = 'nullable|numeric|min:0.01';
        } elseif ($request->input('discount_type') === Promotion::DISCOUNT_TYPE_FIXED) {
            $rules['fixed_discount_amount'] = 'required|numeric|min:1';
            $rules['discount_percentage'] = 'nullable';
            $rules['max_discount_amount'] = 'nullable';
        }

        $validatedData = $request->validate($rules);

        if ($validatedData['discount_type'] === Promotion::DISCOUNT_TYPE_FIXED) {
            $validatedData['discount_percentage'] = null;
            $validatedData['max_discount_amount'] = null;
        } elseif ($validatedData['discount_type'] === Promotion::DISCOUNT_TYPE_PERCENTAGE) {
            $validatedData['fixed_discount_amount'] = null;
        }

        $promotion = Promotion::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Tạo mã khuyến mãi thành công!',
                'promotion' => $promotion->refresh(),
            ]);
        }

        return redirect()->route('admin.sales.promotions.index')
            ->with('success', 'Tạo mã khuyến mãi thành công!');
    }

    /**
     * Cập nhật thông tin mã khuyến mãi đã tồn tại.
     *
     * @param Request $request
     * @param Promotion $promotion
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, Promotion $promotion): JsonResponse|RedirectResponse
    {
        $request->merge(['code' => strtoupper($request->input('code'))]);

        $rules = [
            'code'              => ['required', 'string', 'max:50', 'uppercase', Rule::unique('promotions')->ignore($promotion->id)],
            'description'       => 'nullable|string|max:255',
            'discount_type'     => ['required', Rule::in([Promotion::DISCOUNT_TYPE_PERCENTAGE, Promotion::DISCOUNT_TYPE_FIXED])],
            'start_date'        => 'required|date_format:Y-m-d\TH:i',
            'end_date'          => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses'          => 'nullable|integer|min:1',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'status'            => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
        ];

        if ($request->input('discount_type') === Promotion::DISCOUNT_TYPE_PERCENTAGE) {
            $rules['discount_percentage'] = 'required|numeric|min:0.01|max:100.00';
            $rules['fixed_discount_amount'] = 'nullable';
            $rules['max_discount_amount'] = 'nullable|numeric|min:0.01';
        } elseif ($request->input('discount_type') === Promotion::DISCOUNT_TYPE_FIXED) {
            $rules['fixed_discount_amount'] = 'required|numeric|min:1';
            $rules['discount_percentage'] = 'nullable';
            $rules['max_discount_amount'] = 'nullable';
        }

        $validatedData = $request->validate($rules);

        if ($validatedData['discount_type'] === Promotion::DISCOUNT_TYPE_FIXED) {
            $validatedData['discount_percentage'] = null;
            $validatedData['max_discount_amount'] = null;
        } elseif ($validatedData['discount_type'] === Promotion::DISCOUNT_TYPE_PERCENTAGE) {
            $validatedData['fixed_discount_amount'] = null;
        }

        $promotion->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Cập nhật mã khuyến mãi thành công!',
                'promotion' => $promotion->refresh(),
            ]);
        }

        return redirect()->route('admin.sales.promotions.index')
            ->with('success', 'Cập nhật mã khuyến mãi thành công!');
    }

    /**
     * Xóa một mã khuyến mãi khỏi cơ sở dữ liệu.
     *
     * @param Request $request
     * @param Promotion $promotion
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, Promotion $promotion): JsonResponse|RedirectResponse
    {
        if ($promotion->uses_count > 0) {
            $message = 'Mã khuyến mãi này đã được sử dụng và không thể xóa để đảm bảo toàn vẹn dữ liệu.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $promotion->delete();
            $message = 'Xóa mã khuyến mãi thành công!';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'deleted_ids' => [$promotion->id]]);
            }
            return redirect()->route('admin.sales.promotions.index')->with('success', $message);
        } catch (Exception $e) {
            Log::error("Lỗi khi xóa mã khuyến mãi ID {$promotion->id}: " . $e->getMessage());
            $errorMessage = 'Đã xảy ra lỗi khi xóa mã khuyến mãi.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Bật/tắt trạng thái cài đặt thủ công của một mã khuyến mãi.
     *
     * @param Promotion $promotion
     * @return JsonResponse
     */
    public function toggleStatus(Promotion $promotion): JsonResponse
    {
        $promotion->status = $promotion->isManuallyActive()
            ? Promotion::STATUS_MANUAL_INACTIVE
            : Promotion::STATUS_MANUAL_ACTIVE;
        $promotion->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Cập nhật trạng thái cài đặt thành công!',
            'promotion' => $promotion->refresh(),
        ]);
    }

    /**
     * Xóa hàng loạt các mã khuyến mãi.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|json']);

        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ hoặc rỗng.'], 400);
        }

        $successfullyDeletedIds = [];
        $errors = [];

        $promotionsToDelete = Promotion::whereIn('id', $ids)->get();
        $foundIds = $promotionsToDelete->pluck('id')->all();
        $notFoundIds = array_diff($ids, $foundIds);

        foreach ($notFoundIds as $id) {
            $errors[] = "Mã với ID '{$id}' không tồn tại.";
        }

        foreach ($promotionsToDelete as $promotion) {
            if ($promotion->uses_count > 0) {
                $errors[] = "Mã '{$promotion->code}' đã được sử dụng và không thể xóa.";
            } else {
                try {
                    $promotion->delete();
                    $successfullyDeletedIds[] = $promotion->id;
                } catch (Exception $e) {
                    Log::error("Lỗi khi xóa hàng loạt mã khuyến mãi ID {$promotion->id}: " . $e->getMessage());
                    $errors[] = "Xảy ra lỗi khi xóa mã '{$promotion->code}'.";
                }
            }
        }

        $deletedCount = count($successfullyDeletedIds);
        $message = '';

        if ($deletedCount > 0) {
            $message = "Đã xóa thành công {$deletedCount} mã khuyến mãi.";
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode(' ', $errors);
            }
            return response()->json([
                'success'     => true,
                'message'     => $message,
                'deleted_ids' => $successfullyDeletedIds,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Không có mã nào được xóa. Lỗi: " . implode(' ', $errors),
        ], 422);
    }

    /**
     * Bật/tắt trạng thái cài đặt thủ công của nhiều mã khuyến mãi.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
            'status' => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
        ]);

        $ids = json_decode($request->input('ids'), true);
        $targetStatus = $request->input('status');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ.'], 400);
        }

        $updatedCount = 0;
        $updatedPromotions = [];
        $errors = [];

        // Lấy các promotion cần cập nhật, chỉ lấy những cái tồn tại
        $promotionsToUpdate = Promotion::whereIn('id', $ids)->get();

        foreach ($promotionsToUpdate as $promotion) {
            try {
                // Chỉ cập nhật nếu trạng thái hiện tại khác trạng thái đích
                if ($promotion->status !== $targetStatus) {
                    $promotion->status = $targetStatus;
                    $promotion->save();
                    $updatedCount++;
                }
                $updatedPromotions[] = $promotion->refresh(); // Lấy lại thuộc tính sau khi save
            } catch (Exception $e) {
                Log::error("Lỗi khi cập nhật trạng thái mã khuyến mãi ID {$promotion->id}: " . $e->getMessage());
                $errors[] = "Không thể cập nhật trạng thái mã '{$promotion->code}'.";
            }
        }

        if ($updatedCount > 0) {
            $message = "Đã cập nhật trạng thái thành công cho " . $updatedCount . " mã khuyến mãi.";
            if (!empty($errors)) {
                $message .= " Một số mã có lỗi: " . implode(', ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'promotions' => $updatedPromotions,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có mã nào được cập nhật. Lỗi: " . implode(', ', $errors) : "Không có mã nào được chọn hoặc các mã đã ở trạng thái đích.",
            'errors' => $errors,
        ], 422);
    }
}
