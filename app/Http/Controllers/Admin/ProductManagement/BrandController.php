<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;

class BrandController extends Controller
{
    public function index()
    {
        // SỬA ĐỔI: Chuyển từ get() sang paginate() để phân trang
        // Giúp trang tải nhanh hơn khi có nhiều dữ liệu.
        $brands = Brand::latest()->paginate(10); // Lấy 10 thương hiệu mỗi trang

        return view('admin.productManagement.brand.brands', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:brands,name',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([Brand::STATUS_ACTIVE, Brand::STATUS_INACTIVE])],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo_url')) {
            $logoPath = $request->file('logo_url')->store('brand_logos', 'public');
        }

        Brand::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo_url' => $logoPath,
            'status' => $request->status,
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
            'status' => ['required', Rule::in([Brand::STATUS_ACTIVE, Brand::STATUS_INACTIVE])],
        ]);

        $logoPath = $brand->logo_url;
        if ($request->hasFile('logo_url')) {
            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            $logoPath = $request->file('logo_url')->store('brand_logos', 'public');
        }

        $brand->update([
            'name' => $request->name,
            'description' => $request->description,
            'logo_url' => $logoPath,
            'status' => $request->status,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thương hiệu thành công!',
                // Nhờ $appends, $brand->refresh() sẽ tự động có 'logo_full_url'
                'brand' => $brand->refresh(),
                'redirect_url' => route('admin.productManagement.brands.index')
            ]);
        }

        return redirect()->route('admin.productManagement.brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công!');
    }

    public function destroy(Request $request, Brand $brand)
    {
        $adminDeletionPassword = Config::get('admin.deletion_password');
        if ($adminDeletionPassword) {
            $request->validate([
                'deletion_password' => 'required|string',
            ]);
            if ($request->input('deletion_password') !== $adminDeletionPassword) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mật khẩu xác nhận không đúng.',
                        'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                    ], 422);
                }
                return redirect()->back()->with('error', 'Mật khẩu xác nhận không đúng.');
            }
        }

        try {
            if ($brand->logo_url) {
                Storage::disk('public')->delete($brand->logo_url);
            }
            $brand->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa thương hiệu thành công!',
                    'redirect_url' => route('admin.productManagement.brands.index')
                ]);
            }
            return redirect()->route('admin.productManagement.brands.index')
                ->with('success', 'Xóa thương hiệu thành công!');
        } catch (QueryException $e) {
            $errorMessage = 'Đã xảy ra lỗi. Không thể xóa thương hiệu.';
            if ($e->getCode() === '23000') {
                $errorMessage = 'Không thể xóa thương hiệu này vì vẫn còn sản phẩm liên quan.';
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            return redirect()->route('admin.productManagement.brands.index')
                ->with('error', $errorMessage);
        }
    }

    public function toggleStatus(Request $request, Brand $brand)
    {
        $brand->status = ($brand->status === Brand::STATUS_ACTIVE) ? Brand::STATUS_INACTIVE : Brand::STATUS_ACTIVE;
        $brand->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thương hiệu thành công.',
                'new_status' => $brand->status,
                'status_text' => $brand->isActive() ? 'Hoạt động' : 'Đã ẩn',
                // --- ĐÃ THAY ĐỔI ---
                'new_icon_class' => 'bi-power',
                'new_button_title' => $brand->isActive() ? 'Ẩn thương hiệu này' : 'Hiển thị thương hiệu này'
            ]);
        }
        $message = $brand->isActive() ? 'Thương hiệu đã được hiển thị.' : 'Thương hiệu đã được ẩn.';
        return redirect()->route('admin.productManagement.brands.index')->with('success', $message);
    }
}
