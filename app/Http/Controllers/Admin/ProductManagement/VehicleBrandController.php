<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use App\Models\VehicleModel; // Đảm bảo dòng này đã được thêm vào
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;

class VehicleBrandController extends Controller
{
    // index() không cần nữa nếu trang chính là VehicleManagementController

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:vehicle_brands,name',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([VehicleBrand::STATUS_ACTIVE, VehicleBrand::STATUS_INACTIVE])],
        ]);

        if ($request->hasFile('logo_url')) {
            $validatedData['logo_url'] = $request->file('logo_url')->store('vehicle_brand_logos', 'public');
        }

        VehicleBrand::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo hãng xe thành công!',
                // Không cần redirect_url nếu JS tự reload hoặc cập nhật UI
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
            ->with('success', 'Tạo hãng xe thành công!');
    }

    public function update(Request $request, VehicleBrand $vehicleBrand)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:vehicle_brands,name,' . $vehicleBrand->id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([VehicleBrand::STATUS_ACTIVE, VehicleBrand::STATUS_INACTIVE])],
        ]);

        if ($request->hasFile('logo_url')) {
            if ($vehicleBrand->logo_url) {
                Storage::disk('public')->delete($vehicleBrand->logo_url);
            }
            $validatedData['logo_url'] = $request->file('logo_url')->store('vehicle_brand_logos', 'public');
        }

        $vehicleBrand->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hãng xe thành công!',
                'vehicleBrand' => $vehicleBrand->refresh() // Trả về dữ liệu mới để JS cập nhật UI nếu cần
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
            ->with('success', 'Cập nhật hãng xe thành công!');
    }

    public function destroy(Request $request, VehicleBrand $vehicleBrand)
    {
        $masterDeletePassword = Config::get('admin.deletion_password');
        if ($masterDeletePassword) {
            $request->validate([
                'admin_password_delete_vehicle_brand' => 'required|string',
            ], ['admin_password_delete_vehicle_brand.required' => 'Vui lòng nhập mật khẩu xóa.']);

            if ($request->admin_password_delete_vehicle_brand !== $masterDeletePassword) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Mật khẩu xóa không chính xác.'], 422);
                }
                return back()->with('error', 'Mật khẩu xóa không chính xác.');
            }
        }

        try {
            if ($vehicleBrand->vehicleModels()->exists()) { // Kiểm tra ràng buộc
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Không thể xóa hãng xe này vì vẫn còn dòng xe liên quan.'], 422);
                }
                return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
                    ->with('error', 'Không thể xóa hãng xe này vì vẫn còn dòng xe liên quan.');
            }

            if ($vehicleBrand->logo_url) {
                Storage::disk('public')->delete($vehicleBrand->logo_url);
            }
            $vehicleBrand->delete();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Xóa hãng xe thành công!']);
            }
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
                ->with('success', 'Xóa hãng xe thành công!');
        } catch (QueryException $e) {
            // Log error $e
            $errorMessage = 'Đã xảy ra lỗi khi xóa hãng xe.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
                ->with('error', $errorMessage);
        }
    }

    public function toggleStatus(Request $request, VehicleBrand $vehicleBrand)
    {
        // Xác định trạng thái mới của hãng xe
        $newStatus = ($vehicleBrand->status === VehicleBrand::STATUS_ACTIVE) ? VehicleBrand::STATUS_INACTIVE : VehicleBrand::STATUS_ACTIVE;
        $vehicleBrand->status = $newStatus;
        $vehicleBrand->save();

        // Nếu hãng xe bị ẩn, ẩn tất cả các dòng xe thuộc hãng này
        if ($newStatus === VehicleBrand::STATUS_INACTIVE) {
            $vehicleBrand->vehicleModels()->update(['status' => VehicleModel::STATUS_INACTIVE]);
        } else { // Nếu hãng xe được kích hoạt (newStatus là active)
            // KÍCH HOẠT LẠI TẤT CẢ CÁC DÒNG XE CỦA HÃNG NÀY
            $vehicleBrand->vehicleModels()->update(['status' => VehicleModel::STATUS_ACTIVE]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái hãng xe thành công!',
            'new_status' => $vehicleBrand->status,
            'status_text' => $vehicleBrand->isActive() ? 'Hoạt động' : 'Đã ẩn',
            'new_icon_class' => $vehicleBrand->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill',
            'new_button_title' => $vehicleBrand->isActive() ? 'Ẩn hãng xe' : 'Hiện hãng xe',
        ]);
    }
}
