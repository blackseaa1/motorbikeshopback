<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use App\Models\VehicleBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;


class VehicleModelController extends Controller // Đổi tên class nếu bạn muốn
{
    // index() không cần nữa nếu trang chính là VehicleManagementController

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:150',
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([VehicleModel::STATUS_ACTIVE, VehicleModel::STATUS_INACTIVE])],
        ]);

        VehicleModel::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo dòng xe thành công!',
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models', 'filter_vehicle_brand_id' => $request->vehicle_brand_id])
            ->with('success', 'Tạo dòng xe thành công!');
    }

    public function update(Request $request, VehicleModel $vehicleModel)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:150',
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([VehicleModel::STATUS_ACTIVE, VehicleModel::STATUS_INACTIVE])],
        ]);

        $vehicleModel->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật dòng xe thành công!',
                'vehicleModel' => $vehicleModel->refresh()->load('vehicleBrand') // Load cả brand để JS có thể cập nhật
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models', 'filter_vehicle_brand_id' => $request->vehicle_brand_id])
            ->with('success', 'Cập nhật dòng xe thành công!');
    }

    public function destroy(Request $request, VehicleModel $vehicleModel) // Giữ nguyên logic từ file bạn gửi, chỉ thêm JSON response
    {
        $masterDeletePassword = Config::get('admin.deletion_password'); // Sửa key config nếu khác
        if ($masterDeletePassword) {
            $request->validate([
                'admin_password_delete_vehicle_model' => 'required|string',
            ], ['admin_password_delete_vehicle_model.required' => 'Vui lòng nhập mật khẩu xóa.']);

            if ($request->admin_password_delete_vehicle_model !== $masterDeletePassword) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Mật khẩu xóa không chính xác.'], 422);
                }
                return back()->with('error', 'Mật khẩu xóa không chính xác.');
            }
        }


        try {
            if ($vehicleModel->products()->exists()) {
                $msg = 'Không thể xóa dòng xe này vì vẫn còn sản phẩm liên quan.';
                if ($request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
                return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models'])->with('error', $msg);
            }

            $vehicleModel->delete();
            if ($request->expectsJson()) return response()->json(['success' => true, 'message' => 'Xóa dòng xe thành công!']);
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models'])->with('success', 'Xóa dòng xe thành công!');
        } catch (QueryException $e) {
            $msg = 'Đã xảy ra lỗi khi xóa dòng xe.';
            if ($request->expectsJson()) return response()->json(['success' => false, 'message' => $msg], 500);
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models'])->with('error', $msg);
        }
    }

    public function toggleStatus(Request $request, VehicleModel $vehicleModel) // Giữ nguyên từ file bạn gửi
    {
        $vehicleModel->status = ($vehicleModel->status === VehicleModel::STATUS_ACTIVE) ? VehicleModel::STATUS_INACTIVE : VehicleModel::STATUS_ACTIVE;
        $vehicleModel->save();

        return response()->json([ // Luôn trả JSON
            'success' => true,
            'message' => 'Cập nhật trạng thái dòng xe thành công!',
            'new_status' => $vehicleModel->status,
            'status_text' => $vehicleModel->isActive() ? 'Hoạt động' : 'Đã ẩn',
            'new_icon_class' => $vehicleModel->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill',
            'new_button_title' => $vehicleModel->isActive() ? 'Ẩn dòng xe' : 'Hiện dòng xe',
        ]);
    }
}
