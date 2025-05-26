<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class WardController extends Controller
{
    public function store(Request $request)
    {
        $request->validateWithBag('storeWard', [
            'name' => ['required', 'string', 'max:255', Rule::unique('wards')->where('district_id', $request->district_id)],
            'gso_id' => ['nullable', 'string', 'max:100', Rule::unique('wards', 'gso_id')->where(fn ($query) => $query->whereNotNull('gso_id'))],
            'district_id' => 'required|exists:districts,id',
        ], [
            'name.required' => 'Tên Phường/Xã không được để trống.',
            'name.unique' => 'Tên Phường/Xã này đã tồn tại trong Quận/Huyện đã chọn.',
            'gso_id.unique' => 'Mã GSO này đã tồn tại.',
            'district_id.required' => 'Vui lòng chọn Quận/Huyện.',
        ]);

        Ward::create($request->only(['name', 'gso_id', 'district_id']));

        return back()->with('success', 'Thêm Phường/Xã mới thành công!')->with('active_tab', 'wards-tab-pane');
    }

    public function update(Request $request, Ward $ward)
    {
        try {
            $validatedData = $request->validateWithBag('updateWard', [
                'name' => ['required', 'string', 'max:255', Rule::unique('wards')->where('district_id', $request->district_id)->ignore($ward->id)],
                'gso_id' => ['nullable', 'string', 'max:100', Rule::unique('wards', 'gso_id')->where(fn ($query) => $query->whereNotNull('gso_id'))->ignore($ward->id)],
                'district_id' => 'required|exists:districts,id',
            ], [
                'name.required' => 'Tên Phường/Xã không được để trống.',
                'name.unique' => 'Tên Phường/Xã này đã tồn tại trong Quận/Huyện đã chọn.',
                'gso_id.unique' => 'Mã GSO này đã tồn tại.',
                'district_id.required' => 'Vui lòng chọn Quận/Huyện.',
            ]);

            $ward->update($validatedData);

            return back()->with('success', 'Cập nhật Phường/Xã thành công!')->with('active_tab', 'wards-tab-pane');
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator, 'updateWard')
                         ->withInput()
                         ->with('error_update_ward_id', $ward->id)
                         ->with('active_tab', 'wards-tab-pane');
        }
    }

    public function destroy(Ward $ward)
    {
        if (Order::where('ward_id', $ward->id)->exists()) {
            return back()->with('error', 'Không thể xóa Phường/Xã này vì có đơn hàng đang liên kết đến nó.')->with('active_tab', 'wards-tab-pane');
        }

        try {
            $ward->delete();
            return back()->with('success', 'Xóa Phường/Xã thành công!')->with('active_tab', 'wards-tab-pane');
        } catch (QueryException $e) {
            Log::error('Lỗi xóa Ward (QueryException): ' . $e->getMessage());
            return back()->with('error', 'Lỗi CSDL: Không thể xóa Phường/Xã do ràng buộc dữ liệu.')->with('active_tab', 'wards-tab-pane');
        } catch (\Exception $e) {
            Log::error('Lỗi xóa Ward (Exception): ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi không mong muốn trong quá trình xóa.')->with('active_tab', 'wards-tab-pane');
        }
    }
}