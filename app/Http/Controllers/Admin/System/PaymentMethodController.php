<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::latest()->paginate(10);
        return view('admin.system.paymentMethods', compact('paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name',
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in(PaymentMethod::getAvailableStatus())],
        ]);

        try {
            $paymentMethod = new PaymentMethod($validated);

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('payment_methods', 'public');
                $paymentMethod->logo_path = $path;
            }

            $paymentMethod->save();
            $paymentMethod->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Thêm phương thức thanh toán mới thành công!',
                'paymentMethod' => $paymentMethod,
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo phương thức thanh toán: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể tạo phương thức thanh toán. Vui lòng thử lại.'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_methods')->ignore($paymentMethod->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('payment_methods')->ignore($paymentMethod->id)],
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in(PaymentMethod::getAvailableStatus())],
        ]);

        try {
            $paymentMethod->fill($validated);

            if ($request->hasFile('logo')) {
                // Xóa logo cũ nếu có
                if ($paymentMethod->logo_path) {
                    Storage::disk('public')->delete($paymentMethod->logo_path);
                }
                $path = $request->file('logo')->store('payment_methods', 'public');
                $paymentMethod->logo_path = $path;
            }

            $paymentMethod->save();
            $paymentMethod->refresh(); // Lấy dữ liệu mới nhất, bao gồm cả accessors

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật phương thức thanh toán thành công!',
                'paymentMethod' => $paymentMethod
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật phương thức thanh toán: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật phương thức thanh toán. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        try {
            // Kiểm tra xem có đơn hàng nào đang sử dụng phương thức này không
            if ($paymentMethod->orders()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa phương thức thanh toán này vì nó đã được sử dụng trong các đơn hàng.'
                ], 409); // 409 Conflict
            }

            // Xóa logo nếu có
            if ($paymentMethod->logo_path) {
                Storage::disk('public')->delete($paymentMethod->logo_path);
            }

            $paymentMethod->delete();

            return response()->json(['success' => true, 'message' => 'Xóa phương thức thanh toán thành công!']);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa phương thức thanh toán: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Toggle the status of the payment method.
     */
    // File: app/Http/Controllers/Admin/System/PaymentMethodController.php

    /**
     * Toggle the status of the payment method.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(PaymentMethod $paymentMethod)
    {
        try {
            $newStatus = $paymentMethod->status === PaymentMethod::STATUS_ACTIVE
                ? PaymentMethod::STATUS_INACTIVE
                : PaymentMethod::STATUS_ACTIVE;

            $paymentMethod->status = $newStatus;
            $paymentMethod->save();

            // Refresh model to get updated accessors
            $paymentMethod->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!',
                'paymentMethod' => $paymentMethod,
                'new_button_title' => $paymentMethod->isActive() ? 'Ẩn phương thức này' : 'Hiển thị phương thức này',
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khi thay đổi trạng thái Payment Method: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi thay đổi trạng thái.'
            ], 500);
        }
    }
}
