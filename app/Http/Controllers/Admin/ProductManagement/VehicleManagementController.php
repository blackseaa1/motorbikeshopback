<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class VehicleManagementController extends Controller
{
    public function index(Request $request)
    {
        // Dữ liệu cho tab Dòng xe
        $selectedBrandIdForModelsFilter = $request->query('filter_vehicle_brand_id');
        $vehicleModelsQuery = VehicleModel::with('vehicleBrand')->latest();

        // THÊM DÒNG NÀY: Chỉ hiển thị dòng xe của các hãng xe đang Hoạt động (active)
        $vehicleModelsQuery->whereHas('vehicleBrand', function ($query) {
            $query->where('status', VehicleBrand::STATUS_ACTIVE);
        });

        if ($selectedBrandIdForModelsFilter) {
            $vehicleModelsQuery->where('vehicle_brand_id', $selectedBrandIdForModelsFilter);
        }
        $vehicleModels = $vehicleModelsQuery->paginate(10, ['*'], 'models_page'); // Paginate cho Dòng xe

        // Lấy tất cả hãng xe đang active để dùng cho bộ lọc Dòng xe và form tạo/sửa Dòng xe
        $allActiveVehicleBrands = VehicleBrand::where('status', VehicleBrand::STATUS_ACTIVE)->orderBy('name')->get();

        // Dữ liệu cho tab Hãng xe
        $vehicleBrands = VehicleBrand::latest()->paginate(10, ['*'], 'brands_page'); // Paginate cho Hãng xe

        return view('admin.productManagement.vehicle.vehicles', [
            'vehicleModels' => $vehicleModels,
            'allVehicleBrandsForFilter' => $allActiveVehicleBrands,
            'selectedBrandIdForFilter' => $selectedBrandIdForModelsFilter,

            'vehicleBrands' => $vehicleBrands,
            // 'allVehicleBrands' => $allActiveVehicleBrands, // Có thể dùng chung biến này
        ]);
    }
}
