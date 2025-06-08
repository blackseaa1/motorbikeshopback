<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    /**
     * Hiển thị danh sách các mã khuyến mãi.
     */
    public function index(Request $request)
    {
        $promotions = Promotion::latest()->paginate(10);
        return view('admin.sales.promotion.promotions', compact('promotions'));
    }

    /**
     * Lưu một mã khuyến mãi mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validateWithBag('create_promotion_form', [
            'code' => 'required|string|max:50|unique:promotions,code|uppercase',
            'description' => 'nullable|string|max:255',
            'discount_percentage' => 'required|numeric|min:0.01|max:100.00',
            'start_date' => 'required|date_format:Y-m-d\TH:i',
            'end_date' => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
            'status' => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
            '_form_identifier' => 'required|string',
        ]);

        unset($validatedData['_form_identifier']);
        Promotion::create($validatedData);

        return redirect()->route('admin.sales.promotions.index')
            ->with('success', 'Tạo mã khuyến mãi thành công!');
    }

    /**
     * Cập nhật thông tin mã khuyến mãi.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $validatedData = $request->validateWithBag('update_promotion_form', [
            'code' => ['required', 'string', 'max:50', 'uppercase', Rule::unique('promotions')->ignore($promotion->id)],
            'description' => 'nullable|string|max:255',
            'discount_percentage' => 'required|numeric|min:0.01|max:100.00',
            'start_date' => 'required|date_format:Y-m-d\TH:i',
            'end_date' => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
            'status' => ['required', Rule::in([Promotion::STATUS_MANUAL_ACTIVE, Promotion::STATUS_MANUAL_INACTIVE])],
        ]);

        // Lưu ID vào session để mở lại đúng modal nếu có lỗi validation (khi không dùng AJAX)
        $request->session()->flash('reopen_update_modal_promotion_id', $promotion->id);
        $promotion->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật mã khuyến mãi thành công!',
                'promotion' => $promotion->refresh(),
            ]);
        }

        return redirect()->route('admin.sales.promotions.index')
            ->with('success', 'Cập nhật mã khuyến mãi thành công!');
    }

    /**
     * Xóa một mã khuyến mãi.
     */
    public function destroy(Request $request, Promotion $promotion)
    {
        $adminDeletionPassword = Config::get('admin.deletion_password');
        $formIdentifier = 'delete_promotion_form';

        if ($adminDeletionPassword) {
            $request->validateWithBag($formIdentifier, ['admin_password_delete_promotion' => 'required|string']);
            if ($request->input('admin_password_delete_promotion') !== $adminDeletionPassword) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Mật khẩu xác nhận xóa không chính xác.'], 422);
                }
                $request->session()->flash('reopen_delete_modal_promotion_id', $promotion->id);
                return redirect()->back()->withErrors(['admin_password_delete_promotion' => 'Mật khẩu xác nhận xóa không chính xác.'], $formIdentifier);
            }
        }

        if ($promotion->uses_count > 0) {
            $message = 'Mã khuyến mãi này đã được sử dụng và không thể xóa để đảm bảo toàn vẹn dữ liệu.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $promotion->delete();
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Xóa mã khuyến mãi thành công!']);
            }
            return redirect()->route('admin.sales.promotions.index')->with('success', 'Xóa mã khuyến mãi thành công!');
        } catch (\Exception $e) {
            // ... (xử lý lỗi)
        }
    }

    /**
     * Bật/tắt trạng thái cài đặt thủ công của một mã khuyến mãi.
     */
    public function toggleStatus(Request $request, Promotion $promotion)
    {
        $promotion->status = $promotion->isManuallyActive()
            ? Promotion::STATUS_MANUAL_INACTIVE
            : Promotion::STATUS_MANUAL_ACTIVE;
        $promotion->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái cài đặt thành công!',
            'promotion' => $promotion->refresh(),
        ]);
    }
}
