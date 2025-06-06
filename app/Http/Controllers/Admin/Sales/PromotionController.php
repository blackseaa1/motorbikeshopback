<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::latest()->paginate(10); // Hoặc số lượng tùy ý
        return view('admin.sales.promotions', compact('promotions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validateWithBag('create_promotion_form', [
            'code' => 'required|string|max:50|unique:promotions,code|uppercase',
            'description' => 'nullable|string|max:255',
            'discount_percentage' => 'required|numeric|min:0.01|max:100.00',
            'start_date' => 'required|date_format:Y-m-d\TH:i', // Khớp với datetime-local
            'end_date' => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
            'status' => ['required', Rule::in([Promotion::STATUS_ACTIVE, Promotion::STATUS_INACTIVE])],
            '_form_identifier' => 'required|string', // Để xác định form khi có lỗi
        ]);

        // Bỏ _form_identifier trước khi tạo
        unset($validatedData['_form_identifier']);

        Promotion::create($validatedData);

        return redirect()->route('admin.sales.promotions.index')
            ->with('success', 'Tạo mã khuyến mãi thành công!');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validatedData = $request->validateWithBag('update_promotion_form', [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promotions')->ignore($promotion->id),
                'uppercase'
            ],
            'description' => 'nullable|string|max:255',
            'discount_percentage' => 'required|numeric|min:0.01|max:100.00',
            'start_date' => 'required|date_format:Y-m-d\TH:i',
            'end_date' => 'required|date_format:Y-m-d\TH:i|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
            'status' => ['required', Rule::in([Promotion::STATUS_ACTIVE, Promotion::STATUS_INACTIVE])],
        ]);

        // Lưu ID để mở lại modal nếu có lỗi từ submit không AJAX (ít xảy ra với AJAX)
        $request->session()->flash('reopen_update_modal_promotion_id', $promotion->id);

        $promotion->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật mã khuyến mãi thành công!',
                'promotion' => $promotion->refresh(), // Trả về dữ liệu đã làm mới
            ]);
        }

        // Fallback nếu không phải AJAX (nên tránh)
        return redirect()->route('admin.sales.promotions.index')
            ->with('success', 'Cập nhật mã khuyến mãi thành công!');
    }

    public function destroy(Request $request, Promotion $promotion)
    {
        $adminDeletionPassword = Config::get('admin.deletion_password');
        $formIdentifier = 'delete_promotion_form';

        if ($adminDeletionPassword) {
            $request->validateWithBag($formIdentifier, [
                'admin_password_delete_promotion' => 'required|string',
            ]);
            if ($request->input('admin_password_delete_promotion') !== $adminDeletionPassword) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mật khẩu xác nhận xóa không chính xác.',
                        'errors' => ['admin_password_delete_promotion' => ['Mật khẩu xác nhận xóa không chính xác.']]
                    ], 422);
                }
                // Flash ID để mở lại modal
                $request->session()->flash('reopen_delete_modal_promotion_id', $promotion->id);
                return redirect()->back()
                    ->withErrors(['admin_password_delete_promotion' => 'Mật khẩu xác nhận xóa không chính xác.'], $formIdentifier)
                    ->withInput();
            }
        }

        // Kiểm tra xem mã đã được sử dụng chưa
        if ($promotion->uses_count > 0 && !$request->has('force_delete')) { // Thêm cờ force_delete nếu muốn cho phép xóa mạnh
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã khuyến mãi này đã được sử dụng và không thể xóa. Nếu chắc chắn, hãy thử lại với tùy chọn xóa mạnh (nếu có).',
                    // 'requires_force_delete' => true // Có thể thêm cờ này để JS xử lý
                ], 422); // Lỗi logic nghiệp vụ
            }
            $request->session()->flash('reopen_delete_modal_promotion_id', $promotion->id);
            return redirect()->back()
                ->with('error', 'Mã khuyến mãi này đã được sử dụng và không thể xóa.')
                ->withInput(); // Giữ lại input mật khẩu nếu có
        }


        try {
            $promotion->delete();
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Xóa mã khuyến mãi thành công!']);
            }
            return redirect()->route('admin.sales.promotions.index')
                ->with('success', 'Xóa mã khuyến mãi thành công!');
        } catch (\Exception $e) {
            $errorMessage = 'Đã xảy ra lỗi khi xóa mã khuyến mãi.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            $request->session()->flash('reopen_delete_modal_promotion_id', $promotion->id);
            return redirect()->route('admin.sales.promotions.index')
                ->with('error', $errorMessage);
        }
    }

    public function toggleStatus(Request $request, Promotion $promotion)
    {
        $promotion->status = ($promotion->status === Promotion::STATUS_ACTIVE) ? Promotion::STATUS_INACTIVE : Promotion::STATUS_ACTIVE;
        $promotion->save();

        $promotion->refresh(); // Lấy lại dữ liệu mới nhất

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái cài đặt thành công!',
            'new_manual_status' => $promotion->status,
            'config_status_text' => $promotion->getConfigStatusText(),
            'config_status_badge_class' => $promotion->getConfigStatusBadgeClass(),
            'effective_display_text' => $promotion->getCurrentDisplayStatus(),
            'effective_badge_class' => $promotion->getStatusBadgeClass(),
            'button_title' => ($promotion->status === Promotion::STATUS_INACTIVE) ? 'Bật mã này (thủ công)' : 'Tắt mã này (thủ công)',
            'button_icon_class' => ($promotion->status === Promotion::STATUS_INACTIVE) ? 'bi-power text-success fs-5' : 'bi-power text-danger fs-5',
            'is_disabled_by_date' => ($promotion->getEffectiveStatusKey() === Promotion::STATUS_EFFECTIVE_EXPIRED && $promotion->status === Promotion::STATUS_ACTIVE) // Nút toggle nên disable nếu mã đã hết hạn dù đang bật
        ]);
    }
}
