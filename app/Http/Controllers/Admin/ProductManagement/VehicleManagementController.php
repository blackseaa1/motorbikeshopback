<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Import
use Illuminate\View\View; // Import

class VehicleManagementController extends Controller
{
    /**
     * Hiển thị trang quản lý Hãng xe và Dòng xe, hỗ trợ AJAX cho cả hai tab.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $activeTab = $request->query('tab', 'brands'); // Mặc định hiển thị tab Hãng xe

        // --- Logic cho TAB HÃNG XE (Vehicle Brands) ---
        $vehicleBrandsQuery = VehicleBrand::query();
        $brandSearch = $request->query('brand_search');
        $brandFilter = $request->query('brand_filter', VehicleBrand::FILTER_STATUS_ALL);
        $brandSortBy = $request->query('brand_sort_by', VehicleBrand::SORT_BY_LATEST);

        if ($activeTab === 'brands') { // Chỉ áp dụng search/filter/sort nếu tab brands đang active
            if ($brandSearch) {
                $vehicleBrandsQuery->where(function ($q) use ($brandSearch) {
                    $q->where('name', 'like', '%' . $brandSearch . '%')
                      ->orWhere('description', 'like', '%' . $brandSearch . '%');
                });
            }
            switch ($brandFilter) {
                case VehicleBrand::FILTER_STATUS_ACTIVE_ONLY:
                    $vehicleBrandsQuery->active();
                    break;
                case VehicleBrand::FILTER_STATUS_INACTIVE_ONLY:
                    $vehicleBrandsQuery->inactive();
                    break;
            }
            switch ($brandSortBy) {
                case VehicleBrand::SORT_BY_OLDEST:
                    $vehicleBrandsQuery->orderBy('created_at', 'asc');
                    break;
                case VehicleBrand::SORT_BY_NAME_ASC:
                    $vehicleBrandsQuery->orderBy('name', 'asc');
                    break;
                case VehicleBrand::SORT_BY_NAME_DESC:
                    $vehicleBrandsQuery->orderBy('name', 'desc');
                    break;
                case VehicleBrand::SORT_BY_LATEST:
                default:
                    $vehicleBrandsQuery->orderBy('created_at', 'desc');
                    break;
            }
        } else { // Nếu không phải tab brands, chỉ sắp xếp mặc định để tránh lỗi
             $vehicleBrandsQuery->latest();
        }
        $vehicleBrands = $vehicleBrandsQuery->paginate(10, ['*'], 'brands_page')->withQueryString();


        // --- Logic cho TAB DÒNG XE (Vehicle Models) ---
        $vehicleModelsQuery = VehicleModel::with('vehicleBrand');
        $modelSearch = $request->query('model_search');
        $modelFilter = $request->query('model_filter', VehicleModel::FILTER_STATUS_ALL);
        $modelSortBy = $request->query('model_sort_by', VehicleModel::SORT_BY_LATEST);
        $selectedBrandIdForModelsFilter = $request->query('brand_id_filter'); // Changed parameter name to avoid conflict with 'filter_vehicle_brand_id'

        // SỬA ĐỔI: Chỉ hiển thị dòng xe của các hãng xe đang Hoạt động (active) HOẶC nếu đang lọc theo một hãng cụ thể (cho phép cả inactive nếu được chọn thủ công)
        if (!$selectedBrandIdForModelsFilter) { //
            $vehicleModelsQuery->whereHas('vehicleBrand', function ($query) { //
                $query->where('status', VehicleBrand::STATUS_ACTIVE); //
            }); //
        } //

        if ($activeTab === 'models') { // Chỉ áp dụng search/filter/sort nếu tab models đang active
            if ($modelSearch) {
                $vehicleModelsQuery->where(function ($q) use ($modelSearch) {
                    $q->where('name', 'like', '%' . $modelSearch . '%')
                      ->orWhere('description', 'like', '%' . $modelSearch . '%');
                });
            }
            switch ($modelFilter) {
                case VehicleModel::FILTER_STATUS_ACTIVE_ONLY:
                    $vehicleModelsQuery->active();
                    break;
                case VehicleModel::FILTER_STATUS_INACTIVE_ONLY:
                    $vehicleModelsQuery->inactive();
                    break;
                case VehicleModel::FILTER_BY_BRAND: // Lọc theo hãng
                    if ($selectedBrandIdForModelsFilter) {
                         $vehicleModelsQuery->byBrand($selectedBrandIdForModelsFilter);
                    }
                    break;
            }
            switch ($modelSortBy) {
                case VehicleModel::SORT_BY_OLDEST:
                    $vehicleModelsQuery->orderBy('created_at', 'asc');
                    break;
                case VehicleModel::SORT_BY_NAME_ASC:
                    $vehicleModelsQuery->orderBy('name', 'asc');
                    break;
                case VehicleModel::SORT_BY_NAME_DESC:
                    $vehicleModelsQuery->orderBy('name', 'desc');
                    break;
                case VehicleModel::SORT_BY_YEAR_ASC:
                    $vehicleModelsQuery->orderBy('year', 'asc');
                    break;
                case VehicleModel::SORT_BY_YEAR_DESC:
                    $vehicleModelsQuery->orderBy('year', 'desc');
                    break;
                case VehicleModel::SORT_BY_BRAND_NAME_ASC:
                    $vehicleModelsQuery->join('vehicle_brands', 'vehicle_models.vehicle_brand_id', '=', 'vehicle_brands.id')
                                       ->orderBy('vehicle_brands.name', 'asc')
                                       ->select('vehicle_models.*'); // Select original columns to avoid conflicts
                    break;
                case VehicleModel::SORT_BY_LATEST:
                default:
                    $vehicleModelsQuery->orderBy('created_at', 'desc');
                    break;
            }
        } else { // Nếu không phải tab models, chỉ sắp xếp mặc định
             $vehicleModelsQuery->latest();
        }
        $vehicleModels = $vehicleModelsQuery->paginate(10, ['*'], 'models_page')->withQueryString();

        // Lấy tất cả hãng xe đang active để dùng cho bộ lọc Dòng xe và form tạo/sửa Dòng xe
        $allActiveVehicleBrands = VehicleBrand::where('status', VehicleBrand::STATUS_ACTIVE)->orderBy('name')->get();


        // --- Xử lý AJAX request (MỚI) ---
        if ($request->expectsJson()) {
            if ($activeTab === 'brands') {
                $tableRowsHtml = '';
                $startIndex = $vehicleBrands->firstItem() ? ($vehicleBrands->firstItem() - 1) : 0;
                foreach ($vehicleBrands as $index => $brand) {
                    $tableRowsHtml .= view('admin.productManagement.vehicle.partials._vehicle_brand_table_row', [
                        'brand' => $brand,
                        'loopIndex' => $index,
                        'startIndex' => $startIndex,
                    ])->render();
                }
                return response()->json([
                    'table_rows' => $tableRowsHtml,
                    'pagination_links' => $vehicleBrands->links('admin.vendor.pagination')->render(),
                    'active_tab' => 'brands'
                ]);
            } elseif ($activeTab === 'models') {
                $tableRowsHtml = '';
                $startIndex = $vehicleModels->firstItem() ? ($vehicleModels->firstItem() - 1) : 0;
                foreach ($vehicleModels as $index => $model) {
                    $tableRowsHtml .= view('admin.productManagement.vehicle.partials._vehicle_model_table_row', [
                        'model' => $model,
                        'loopIndex' => $index,
                        'startIndex' => $startIndex,
                    ])->render();
                }
                return response()->json([
                    'table_rows' => $tableRowsHtml,
                    'pagination_links' => $vehicleModels->links('admin.vendor.pagination')->render(),
                    'active_tab' => 'models'
                ]);
            }
        }

        // --- Trả về View đầy đủ (nếu không phải AJAX) ---
        return view('admin.productManagement.vehicle.vehicles', [
            'vehicleModels' => $vehicleModels,
            'allVehicleBrandsForFilter' => $allActiveVehicleBrands,
            'selectedBrandIdForFilter' => $selectedBrandIdForModelsFilter,

            'vehicleBrands' => $vehicleBrands,
            'allVehicleBrands' => $allActiveVehicleBrands, // Dùng cho dropdown tạo/sửa model
            'activeTab' => $activeTab // Truyền tab active để Blade biết hiển thị tab nào
        ]);
    }
}