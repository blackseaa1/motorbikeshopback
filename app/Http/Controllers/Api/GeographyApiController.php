<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\District;
use Illuminate\Http\JsonResponse;

class GeographyApiController extends Controller
{
    /**
     * Lấy danh sách các quận/huyện thuộc một tỉnh/thành phố cụ thể.
     *
     * @param Province $province Model của tỉnh/thành phố được inject tự động bởi Laravel.
     * @return JsonResponse
     */
    public function getDistrictsByProvince(Province $province): JsonResponse
    {
        // Lấy các quận/huyện liên quan, chỉ chọn cột id và name, và sắp xếp theo tên.
        $districts = $province->districts()->orderBy('name')->get(['id', 'name']);

        // Trả về dữ liệu dưới dạng JSON.
        return response()->json($districts);
    }
    public function getWardsByDistrict(District $district)
    {
        return response()->json($district->wards);
    }
}
