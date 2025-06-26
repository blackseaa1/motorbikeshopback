<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;

class DeliveryServiceController extends Controller
{
    public function index()
    {
        $deliveryServices = DeliveryService::latest()->paginate(10);
        return view('admin.system.deliveryServices', compact('deliveryServices'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:150|unique:delivery_services,name',
            // 'shipping_fee' đã được loại bỏ khỏi phần xác thực vì nó sẽ luôn là 0
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([DeliveryService::STATUS_ACTIVE, DeliveryService::STATUS_INACTIVE])],
        ]);

        $validatedData['shipping_fee'] = 0; // Buộc phí vận chuyển là 0 cho giao hàng miễn phí

        if ($request->hasFile('logo_url')) {
            $validatedData['logo_url'] = $request->file('logo_url')->store('delivery_service_logos', 'public');
        } else {
            $validatedData['logo_url'] = null;
        }

        DeliveryService::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm đơn vị giao hàng thành công!',
            ]);
        }
        return redirect()->route('admin.system.deliveryServices.index')
            ->with('success', 'Thêm đơn vị giao hàng thành công!');
    }

    public function update(Request $request, DeliveryService $deliveryService)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:150|unique:delivery_services,name,' . $deliveryService->id,
            // 'shipping_fee' đã được loại bỏ khỏi phần xác thực vì nó sẽ luôn là 0
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'existing_logo_url' => 'nullable|string',
            'status' => ['required', Rule::in([DeliveryService::STATUS_ACTIVE, DeliveryService::STATUS_INACTIVE])],
        ]);

        $validatedData['shipping_fee'] = 0; // Buộc phí vận chuyển là 0 cho giao hàng miễn phí

        // Xử lý cập nhật logo
        if ($request->hasFile('logo_url')) {
            // Xóa logo cũ nếu tồn tại
            if ($deliveryService->logo_url) {
                Storage::disk('public')->delete($deliveryService->logo_url);
            }
            $validatedData['logo_url'] = $request->file('logo_url')->store('delivery_service_logos', 'public');
        } else {
            // Giữ lại logo hiện có nếu không có file mới được tải lên
            $validatedData['logo_url'] = $deliveryService->logo_url;
        }

        $deliveryService->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đơn vị giao hàng thành công!',
                'deliveryService' => $deliveryService->refresh()
            ]);
        }
        return redirect()->route('admin.system.deliveryServices.index')
            ->with('success', 'Cập nhật đơn vị giao hàng thành công!');
    }

    public function destroy(Request $request, DeliveryService $deliveryService)
    {
        $masterDeletePassword = Config::get('admin.deletion_password');
        if ($masterDeletePassword) {
            $request->validate([
                'deletion_password' => 'required|string',
            ], ['deletion_password.required' => 'Vui lòng nhập mật khẩu xóa.']);

            if ($request->deletion_password !== $masterDeletePassword) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Mật khẩu xóa không chính xác.', 'errors' => ['deletion_password' => ['Mật khẩu xóa không chính xác.']]], 422);
                }
                return back()->with('error', 'Mật khẩu xóa không chính xác.');
            }
        }

        try {
            if ($deliveryService->orders()->exists()) {
                $msg = 'Không thể xóa đơn vị này vì đã được sử dụng trong các đơn hàng.';
                if ($request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
                return redirect()->route('admin.system.deliveryServices.index')->with('error', $msg);
            }

            if ($deliveryService->logo_url) {
                Storage::disk('public')->delete($deliveryService->logo_url);
            }
            $deliveryService->delete();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Xóa đơn vị giao hàng thành công!']);
            }
            return redirect()->route('admin.system.deliveryServices.index')
                ->with('success', 'Xóa đơn vị giao hàng thành công!');
        } catch (QueryException $e) {
            $errorMessage = 'Đã xảy ra lỗi khi xóa đơn vị giao hàng.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->route('admin.system.deliveryServices.index')
                ->with('error', $errorMessage);
        }
    }

    public function toggleStatus(Request $request, DeliveryService $deliveryService)
    {
        $deliveryService->status = ($deliveryService->status === DeliveryService::STATUS_ACTIVE) ? DeliveryService::STATUS_INACTIVE : DeliveryService::STATUS_ACTIVE;
        $deliveryService->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn vị giao hàng thành công!',
                'new_status' => $deliveryService->status,
                'status_text' => $deliveryService->isActive() ? 'Hoạt động' : 'Đã ẩn',
                'new_icon_class' => 'bi-power',
                'new_button_title' => $deliveryService->isActive() ? 'Ẩn đơn vị này' : 'Hiển thị đơn vị này'
            ]);
        }

        $message = $deliveryService->isActive() ? 'Đơn vị giao hàng đã được hiển thị.' : 'Đơn vị giao hàng đã được ẩn.';
        return redirect()->route('admin.system.deliveryServices.index')
            ->with('success', $message);
    }
}
