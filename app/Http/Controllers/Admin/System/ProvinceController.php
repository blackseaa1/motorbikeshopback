<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Ward;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProvinceController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.system.geography.index');
    }

    public function store(Request $request)
    {
        // Sử dụng error bag tĩnh 'storeProvince'
        $request->validateWithBag('storeProvince', [
            'name' => 'required|string|max:255|unique:provinces,name',
            'gso_id' => ['nullable', 'string', 'max:100', Rule::unique('provinces', 'gso_id')->where(fn($query) => $query->whereNotNull('gso_id'))],
        ], [
            'name.required' => 'Tên Tỉnh/Thành phố không được để trống.',
            'name.unique' => 'Tên Tỉnh/Thành phố này đã tồn tại.',
            'gso_id.unique' => 'Mã GSO này đã tồn tại.',
        ]);

        Province::create($request->only(['name', 'gso_id']));

        return redirect()->route('admin.system.geography.index', ['tab' => 'provinces'])
            ->with('success', 'Thêm Tỉnh/Thành phố mới thành công!');
    }

    public function update(Request $request, Province $province)
    {
        try {
            // Sử dụng error bag tĩnh 'updateProvince'
            $validatedData = $request->validateWithBag('updateProvince', [
                'name' => ['required', 'string', 'max:255', Rule::unique('provinces')->ignore($province->id)],
                'gso_id' => ['nullable', 'string', 'max:100', Rule::unique('provinces')->ignore($province->id)->where(fn($query) => $query->whereNotNull('gso_id'))],
            ], [
                'name.required' => 'Tên Tỉnh/Thành phố không được để trống.',
                'name.unique' => 'Tên Tỉnh/Thành phố này đã tồn tại.',
                'gso_id.unique' => 'Mã GSO này đã tồn tại.',
            ]);

            $province->update($validatedData);

            return redirect()->route('admin.system.geography.index', ['tab' => 'provinces'])
                ->with('success', 'Cập nhật thông tin Tỉnh/Thành phố thành công!');
        } catch (ValidationException $e) {
            // Nếu validation thất bại, redirect về cùng với lỗi và ID của tỉnh
            return redirect()->route('admin.system.geography.index', ['tab' => 'provinces'])
                ->withErrors($e->validator, 'updateProvince')
                ->withInput()
                ->with('error_update_province_id', $province->id);
        }
    }

    public function destroy(Province $province)
    {
        $districtIds = $province->districts()->pluck('id');
        $wardIds = collect();
        if ($districtIds->isNotEmpty()) {
            $wardIds = Ward::whereIn('district_id', $districtIds)->pluck('id');
        }

        $hasOrdersInProvince = Order::where('province_id', $province->id)->exists();
        $hasOrdersInDistricts = $districtIds->isNotEmpty() ? Order::whereIn('district_id', $districtIds)->exists() : false;
        $hasOrdersInWards = $wardIds->isNotEmpty() ? Order::whereIn('ward_id', $wardIds)->exists() : false;

        if ($hasOrdersInProvince || $hasOrdersInDistricts || $hasOrdersInWards) {
            return back()->with('error', 'Không thể xóa Tỉnh/Thành phố này vì có đơn hàng đang liên kết đến nó hoặc các Quận/Huyện, Phường/Xã trực thuộc.');
        }

        try {
            $province->delete();
            return redirect()->route('admin.system.geography.index', ['tab' => 'provinces'])
                ->with('success', 'Xóa Tỉnh/Thành phố và các đơn vị hành chính liên quan thành công!');
        } catch (QueryException $e) {
            Log::error('Lỗi xóa Province (QueryException): ' . $e->getMessage());
            return back()->with('error', 'Lỗi CSDL: Không thể xóa Tỉnh/Thành phố do ràng buộc dữ liệu.');
        } catch (\Exception $e) {
            Log::error('Lỗi xóa Province (Exception): ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi không mong muốn trong quá trình xóa.');
        }
    }
}
