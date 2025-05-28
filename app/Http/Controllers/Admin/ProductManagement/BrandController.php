<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller; // <-- THÊM DÒNG NÀY VÀO
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class BrandController extends Controller // Dòng này sẽ hết bị báo lỗi
{
    // ... code của bạn

    public function index()
    {
        $brands = Brand::all();
        return view('admin.productManagement.brands', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:brands,name',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo_url')) {
            $logoPath = $request->file('logo_url')->store('brand_logos', 'public');
        }

        Brand::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo_url' => $logoPath,
        ]);

        return redirect()->route('admin.productManagement.brands.index')
                         ->with('success', 'Tạo thương hiệu thành công!');
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $logoPath = $brand->logo_url;
        if ($request->hasFile('logo_url')) {
            // Xóa logo cũ nếu tồn tại
            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            // Lưu logo mới
            $logoPath = $request->file('logo_url')->store('brand_logos', 'public');
        }

        $brand->update([
            'name' => $request->name,
            'description' => $request->description,
            'logo_url' => $logoPath,
        ]);

        return redirect()->route('admin.productManagement.brands.index')
                         ->with('success', 'Cập nhật thương hiệu thành công!');
    }

    public function destroy(Brand $brand)
    {
        try {
            // Xóa logo trước khi xóa bản ghi
            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            $brand->delete();

            return redirect()->route('admin.productManagement.brands.index')
                             ->with('success', 'Xóa thương hiệu thành công!');
        } catch (QueryException $e) {
            // Bắt lỗi khóa ngoại (ON DELETE RESTRICT)
            if ($e->getCode() === '23000') { // Mã lỗi Integrity constraint violation
                return redirect()->route('admin.productManagement.brands.index')
                                 ->with('error', 'Không thể xóa thương hiệu này vì vẫn còn sản phẩm liên quan.');
            }
            // Lỗi khác
            return redirect()->route('admin.productManagement.brands.index')
                             ->with('error', 'Đã xảy ra lỗi. Không thể xóa thương hiệu.');
        }
    }
}