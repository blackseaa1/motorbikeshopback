<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class DistrictController extends Controller
{
    public function store(Request $request)
    {
        $request->validateWithBag('storeDistrict', [
            'name' => ['required', 'string', 'max:255', Rule::unique('districts')->where('province_id', $request->province_id)],
            'gso_id' => ['nullable', 'string', 'max:100', Rule::unique('districts', 'gso_id')->where(fn ($query) => $query->whereNotNull('gso_id'))],
            'province_id' => 'required|exists:provinces,id',
        ], [
            'name.required' => 'Tên Quận/Huyện không được để trống.',
            'name.unique' => 'Tên Quận/Huyện này đã tồn tại trong Tỉnh/Thành đã chọn.',
            'gso_id.unique' => 'Mã GSO này đã tồn tại.',
            'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
        ]);

        District::create($request->only(['name', 'gso_id', 'province_id']));

        return back()->with('success', 'Thêm Quận/Huyện mới thành công!')->with('active_tab', 'districts-tab-pane');
    }

    public function update(Request $request, District $district)
    {
        try {
            $validatedData = $request->validateWithBag('updateDistrict', [
                'name' => ['required', 'string', 'max:255', Rule::unique('districts')->where('province_id', $request->province_id)->ignore($district->id)],
                'gso_id' => ['nullable', 'string', 'max:100', Rule::unique('districts', 'gso_id')->where(fn ($query) => $query->whereNotNull('gso_id'))->ignore($district->id)],
                'province_id' => 'required|exists:provinces,id',
            ], [
                'name.required' => 'Tên Quận/Huyện không được để trống.',
                'name.unique' => 'Tên Quận/Huyện này đã tồn tại trong Tỉnh/Thành đã chọn.',
                'gso_id.unique' => 'Mã GSO này đã tồn tại.',
                'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
            ]);

            $district->update($validatedData);

            return back()->with('success', 'Cập nhật Quận/Huyện thành công!')->with('active_tab', 'districts-tab-pane');
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator, 'updateDistrict')
                         ->withInput()
                         ->with('error_update_district_id', $district->id)
                         ->with('active_tab', 'districts-tab-pane');
        }
    }

    public function destroy(District $district)
    {
        $wardIds = $district->wards()->pluck('id');
        $hasOrdersInDistrict = Order::where('district_id', $district->id)->exists();
        $hasOrdersInWards = $wardIds->isNotEmpty() ? Order::whereIn('ward_id', $wardIds)->exists() : false;

        if ($hasOrdersInDistrict || $hasOrdersInWards) {
            return back()->with('error', 'Không thể xóa Quận/Huyện này vì có đơn hàng đang liên kết đến nó hoặc các Phường/Xã trực thuộc.')->with('active_tab', 'districts-tab-pane');
        }

        try {
            $district->delete();
            return back()->with('success', 'Xóa Quận/Huyện và các Phường/Xã liên quan thành công!')->with('active_tab', 'districts-tab-pane');
        } catch (QueryException $e) {
            Log::error('Lỗi xóa District (QueryException): ' . $e->getMessage());
            return back()->with('error', 'Lỗi CSDL: Không thể xóa Quận/Huyện do ràng buộc dữ liệu.')->with('active_tab', 'districts-tab-pane');
        } catch (\Exception $e) {
            Log::error('Lỗi xóa District (Exception): ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi không mong muốn trong quá trình xóa.')->with('active_tab', 'districts-tab-pane');
        }
    }
}