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

/**
 * Class PromotionController
 *
 * Xử lý tất cả các hoạt động CRUD cho Mã Khuyến Mãi.
 * Toàn bộ logic validation và xử lý dữ liệu được đặt bên trong controller này.
 */
class PromotionController extends Controller
{
    /**
     * Hiển thị trang danh sách các mã khuyến mãi.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $promotions = Promotion::latest()->paginate(10);
        return view('admin.sales.promotion.promotions', compact('promotions'));
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
        // Bước 1: Chuẩn hóa dữ liệu (Logic từ prepareForValidation trong FormRequest cũ)
        // Tự động chuyển đổi mã code thành chữ hoa trước khi validate.
        $request->merge(['code' => strtoupper($request->input('code'))]);

        // Bước 2: Kiểm tra dữ liệu (Logic từ rules() trong FormRequest cũ)
        // Tự động trả về lỗi 422 JSON nếu request là AJAX và validation thất bại.
        $validatedData = $request->validate([
            'code'              => 'required|string|max:50|unique:promotions,code|uppercase',
            'description'       => 'nullable|string|max:255',
            'discount_percentage' => 'required|numeric|min:0.01|max:100.00',
            'start_date'        => 'required|date_format:Y-m-d\TH:i',
            'end_date'          => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses'          => 'nullable|integer|min:1',
            'status'            => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
        ]);

        // Bước 3: Tạo mới đối tượng và lưu vào DB.
        $promotion = Promotion::create($validatedData);

        // Bước 4: Trả về phản hồi tùy theo loại request.
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
        // Bước 1: Chuẩn hóa dữ liệu.
        $request->merge(['code' => strtoupper($request->input('code'))]);

        // Bước 2: Kiểm tra dữ liệu.
        // Dùng validateWithBag để không xung đột lỗi với các form khác trên cùng trang khi không dùng AJAX.
        $validatedData = $request->validateWithBag('update_promotion_form', [
            'code'              => ['required', 'string', 'max:50', 'uppercase', Rule::unique('promotions')->ignore($promotion->id)],
            'description'       => 'nullable|string|max:255',
            'discount_percentage' => 'required|numeric|min:0.01|max:100.00',
            'start_date'        => 'required|date_format:Y-m-d\TH:i',
            'end_date'          => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses'          => 'nullable|integer|min:1',
            'status'            => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
        ]);

        // Bước 3: Cập nhật đối tượng.
        $promotion->update($validatedData);

        // Bước 4: Trả về phản hồi.
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
        // Điều kiện: Chỉ cho phép xóa nếu mã chưa từng được sử dụng.
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
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('admin.sales.promotions.index')->with('success', $message);
        } catch (Exception $e) {
            // Có thể ghi log lỗi tại đây: \Illuminate\Support\Facades\Log::error($e->getMessage());
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
}
