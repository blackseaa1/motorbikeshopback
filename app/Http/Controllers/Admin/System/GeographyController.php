<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VietnamZoneImporter;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;


class GeographyController extends Controller
{
    /**
     * Hiển thị trang quản lý địa lý và xử lý các yêu cầu lọc (bao gồm cả AJAX).
     */
    public function index(Request $request)
    {
        // --- Logic Query để lấy dữ liệu (không đổi) ---
        // Lấy dữ liệu Tỉnh/Thành phố
        $provincesQuery = Province::query()->withCount('districts');
        if ($request->filled('search_name_province')) {
            $provincesQuery->where('name', 'like', '%' . $request->search_name_province . '%');
        }
        $provinces = $provincesQuery->latest()->paginate(10, ['*'], 'provinces_page')->withQueryString();

        // Lấy dữ liệu Quận/Huyện
        $districtsQuery = District::query()->with('province')->withCount('wards');
        if ($request->filled('search_name_district')) {
            $districtsQuery->where('name', 'like', '%' . $request->search_name_district . '%');
        }
        if ($request->filled('filter_province_for_district')) {
            $districtsQuery->where('province_id', $request->filter_province_for_district);
        }
        $districts = $districtsQuery->latest()->paginate(10, ['*'], 'districts_page')->withQueryString();

        // Lấy dữ liệu Phường/Xã
        $wardsQuery = Ward::query()->with('district.province'); // Eager load quận và tỉnh
        if ($request->filled('search_name_ward')) {
            $wardsQuery->where('name', 'like', '%' . $request->search_name_ward . '%');
        }
        if ($request->filled('filter_district_for_ward')) {
            $wardsQuery->where('district_id', $request->filter_district_for_ward);
        }
        if ($request->filled('filter_province_for_ward_display') && !$request->filled('filter_district_for_ward')) {
            $districtIdsInProvince = District::where('province_id', $request->filter_province_for_ward_display)->pluck('id');
            $wardsQuery->whereIn('district_id', $districtIdsInProvince);
        }
        $wards = $wardsQuery->latest()->paginate(10, ['*'], 'wards_page')->withQueryString();

        // Lấy dữ liệu cho các dropdown
        $allProvinces = Province::orderBy('name')->get();
        $allDistricts = collect();
        if ($request->filled('filter_province_for_ward_display') || $request->filled('filter_province_for_district')) {
            $provinceId = $request->input('filter_province_for_ward_display', $request->input('filter_province_for_district'));
            $allDistricts = District::where('province_id', $provinceId)->orderBy('name')->get();
        }

        // --- ĐIỂM THAY ĐỔI QUAN TRỌNG ---
        // Nếu là một yêu cầu AJAX, chỉ trả về HTML của tab tương ứng
        if ($request->ajax()) {
            $tab = $request->input('tab', 'provinces');
            $viewPath = 'admin.system.partials.tabs.' . $tab . '_tab';

            // Kiểm tra xem view partial có tồn tại không
            if (!View::exists($viewPath)) {
                return response()->json(['error' => 'Partial view not found.'], 404);
            }

            // Render view partial thành chuỗi HTML và trả về dưới dạng JSON
            $html = View::make($viewPath, compact('provinces', 'districts', 'wards', 'allProvinces', 'allDistricts'))->render();
            return response()->json(['html' => $html]);
        }

        // Nếu là yêu cầu tải trang bình thường, trả về view đầy đủ
        return view('admin.system.geography', compact('provinces', 'districts', 'wards', 'allProvinces', 'allDistricts'));
    }

    /**
     * Xử lý việc import dữ liệu địa lý từ file Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'geography_file' => 'required|mimes:xls,xlsx'
        ], [
            'geography_file.required' => 'Vui lòng chọn một file Excel.',
            'geography_file.mimes' => 'File phải có định dạng .xls hoặc .xlsx.'
        ]);

        try {
            DB::beginTransaction();
            Excel::import(new VietnamZoneImporter, $request->file('geography_file'));
            DB::commit();
            return back()->with('success', 'Dữ liệu địa lý đã được import thành công!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Lỗi ở dòng " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return back()->with('error', 'Có lỗi xảy ra trong quá trình import file Excel: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi Import Địa lý: ' . $e->getMessage() . ' - Stack: ' . $e->getTraceAsString());
            return back()->with('error', 'Đã xảy ra lỗi không mong muốn khi import. Vui lòng kiểm tra logs.');
        }
    }
}
