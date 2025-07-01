<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse; // Import
use Illuminate\Http\RedirectResponse; // Import
use Illuminate\View\View; // Import
use Illuminate\Support\Facades\Log; // Import

class VehicleBrandController extends Controller
{
    /**
     * Trả về dữ liệu chi tiết của một hãng xe dưới dạng JSON cho AJAX. (NEW)
     *
     * @param Request $request
     * @param VehicleBrand $vehicleBrand
     * @return JsonResponse
     */
    public function show(Request $request, VehicleBrand $vehicleBrand): JsonResponse
    {
        // $vehicleBrand đã được Route Model Binding. Các accessor sẽ tự động được thêm vào JSON.
        return response()->json($vehicleBrand);
    }

    /**
     * Lưu một hãng xe mới vào cơ sở dữ liệu.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:vehicle_brands,name',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([VehicleBrand::STATUS_ACTIVE, VehicleBrand::STATUS_INACTIVE])],
        ]);

        if ($request->hasFile('logo_url')) {
            $validatedData['logo_url'] = $request->file('logo_url')->store('vehicle_brand_logos', 'public');
        }

        $vehicleBrand = VehicleBrand::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo hãng xe thành công!',
                'vehicleBrand' => $vehicleBrand->refresh(), // Trả về đối tượng hãng xe vừa tạo
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
            ->with('success', 'Tạo hãng xe thành công!');
    }

    /**
     * Cập nhật thông tin hãng xe đã tồn tại.
     *
     * @param Request $request
     * @param VehicleBrand $vehicleBrand
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, VehicleBrand $vehicleBrand): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:vehicle_brands,name,' . $vehicleBrand->id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => ['required', Rule::in([VehicleBrand::STATUS_ACTIVE, VehicleBrand::STATUS_INACTIVE])],
        ]);

        if ($request->hasFile('logo_url')) {
            if ($vehicleBrand->logo_url) {
                Storage::disk('public')->delete($vehicleBrand->logo_url);
            }
            $validatedData['logo_url'] = $request->file('logo_url')->store('vehicle_brand_logos', 'public');
        }

        $vehicleBrand->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hãng xe thành công!',
                'vehicleBrand' => $vehicleBrand->refresh() // Trả về dữ liệu mới để JS cập nhật UI nếu cần
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
            ->with('success', 'Cập nhật hãng xe thành công!');
    }

    /**
     * Xóa một hãng xe khỏi cơ sở dữ liệu.
     *
     * @param Request $request
     * @param VehicleBrand $vehicleBrand
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, VehicleBrand $vehicleBrand): JsonResponse|RedirectResponse
    {
        $masterDeletePassword = Config::get('admin.deletion_password');
        if ($masterDeletePassword) {
            $request->validate([
                'deletion_password' => 'required|string', // Đổi tên trường password để nhất quán
            ], ['deletion_password.required' => 'Vui lòng nhập mật khẩu xóa.']);

            if ($request->input('deletion_password') !== $masterDeletePassword) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Mật khẩu xóa không chính xác.'], 422);
                }
                return back()->with('error', 'Mật khẩu xóa không chính xác.');
            }
        }

        try {
            if ($vehicleBrand->vehicleModels()->exists()) {
                $errorMessage = 'Không thể xóa hãng xe này vì vẫn còn dòng xe liên quan.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMessage], 422);
                }
                return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
                    ->with('error', $errorMessage);
            }

            if ($vehicleBrand->logo_url) {
                Storage::disk('public')->delete($vehicleBrand->logo_url);
            }
            $vehicleBrand->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa hãng xe thành công!',
                    'deleted_ids' => [$vehicleBrand->id] // Trả về ID của hãng xe đã xóa
                ]);
            }
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
                ->with('success', 'Xóa hãng xe thành công!');
        } catch (QueryException $e) {
            Log::error("Lỗi khi xóa hãng xe ID {$vehicleBrand->id}: " . $e->getMessage());
            $errorMessage = 'Đã xảy ra lỗi khi xóa hãng xe.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'brands'])
                ->with('error', $errorMessage);
        }
    }

    /**
     * Bật/tắt trạng thái cài đặt thủ công của một hãng xe.
     *
     * @param Request $request
     * @param VehicleBrand $vehicleBrand
     * @return JsonResponse
     */
    public function toggleStatus(Request $request, VehicleBrand $vehicleBrand): JsonResponse
    {
        $newStatus = ($vehicleBrand->status === VehicleBrand::STATUS_ACTIVE) ? VehicleBrand::STATUS_INACTIVE : VehicleBrand::STATUS_ACTIVE;
        $vehicleBrand->status = $newStatus;
        $vehicleBrand->save();

        // Nếu hãng xe bị ẩn, ẩn tất cả các dòng xe thuộc hãng này
        if ($newStatus === VehicleBrand::STATUS_INACTIVE) {
            $vehicleBrand->vehicleModels()->update(['status' => VehicleModel::STATUS_INACTIVE]);
        } else { // Nếu hãng xe được kích hoạt (newStatus là active)
            // KÍCH HOẠT LẠI TẤT CẢ CÁC DÒNG XE CỦA HÃNG NÀY (chỉ nếu chúng không bị ẩn thủ công trước đó hoặc không có ràng buộc khác)
            // Để đơn giản, kích hoạt lại tất cả các model của hãng này khi hãng được kích hoạt.
            $vehicleBrand->vehicleModels()->update(['status' => VehicleModel::STATUS_ACTIVE]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái hãng xe thành công!',
            'vehicleBrand' => $vehicleBrand->refresh(), // Trả về đối tượng để JS cập nhật UI (bao gồm accessors)
        ]);
    }

    /**
     * Xóa hàng loạt các hãng xe. (NEW)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
        ]);

        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ hoặc rỗng.'], 400);
        }

        $masterDeletePassword = Config::get('admin.deletion_password');
        if ($masterDeletePassword) {
            $request->validate([
                'deletion_password' => 'required|string', // Đổi tên trường password để nhất quán
            ], ['deletion_password.required' => 'Vui lòng nhập mật khẩu xác nhận.']);

            if ($request->input('deletion_password') !== $masterDeletePassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu xác nhận không đúng.',
                    'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                ], 422);
            }
        }

        $successfullyDeletedIds = [];
        $errors = [];

        $brandsToDelete = VehicleBrand::whereIn('id', $ids)->get();

        foreach ($brandsToDelete as $brand) {
            if ($brand->vehicleModels()->exists()) {
                $errors[] = "Hãng xe '{$brand->name}' không thể xóa vì vẫn còn dòng xe liên quan.";
            } else {
                try {
                    if ($brand->logo_url) {
                        Storage::disk('public')->delete($brand->logo_url);
                    }
                    $brand->delete();
                    $successfullyDeletedIds[] = $brand->id;
                } catch (QueryException $e) {
                    Log::error("Lỗi khi xóa hàng loạt hãng xe ID {$brand->id}: " . $e->getMessage());
                    $errors[] = "Xảy ra lỗi khi xóa hãng xe '{$brand->name}'.";
                }
            }
        }

        $deletedCount = count($successfullyDeletedIds);
        $message = '';

        if ($deletedCount > 0) {
            $message = "Đã xóa thành công {$deletedCount} hãng xe.";
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_ids' => $successfullyDeletedIds,
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có hãng xe nào được xóa. Lỗi: " . implode('; ', $errors) : "Không có hãng xe nào được chọn để xóa hoặc xảy ra lỗi không xác định.",
            'errors' => $errors,
        ], 422);
    }

    /**
     * Bật/tắt trạng thái của nhiều hãng xe. (NEW)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
            'status' => ['required', Rule::in([VehicleBrand::STATUS_ACTIVE, VehicleBrand::STATUS_INACTIVE])],
        ]);

        $ids = json_decode($request->input('ids'), true);
        $targetStatus = $request->input('status');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu ID không hợp lệ.'], 400);
        }

        $masterDeletePassword = Config::get('admin.deletion_password');
        if ($masterDeletePassword) {
            $request->validate([
                'deletion_password' => 'required|string', // Đổi tên trường password để nhất quán
            ], ['deletion_password.required' => 'Vui lòng nhập mật khẩu xác nhận.']);

            if ($request->input('deletion_password') !== $masterDeletePassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu xác nhận không đúng.',
                    'errors' => ['deletion_password' => ['Mật khẩu xác nhận không đúng.']]
                ], 422);
            }
        }

        $updatedCount = 0;
        $updatedBrands = [];
        $errors = [];

        $brandsToUpdate = VehicleBrand::whereIn('id', $ids)->get();

        foreach ($brandsToUpdate as $brand) {
            try {
                if ($brand->status !== $targetStatus) {
                    $brand->status = $targetStatus;
                    $brand->save();
                    $updatedCount++;

                    // Áp dụng trạng thái cho các dòng xe liên quan
                    if ($targetStatus === VehicleBrand::STATUS_INACTIVE) {
                        $brand->vehicleModels()->update(['status' => VehicleModel::STATUS_INACTIVE]);
                    } else {
                        $brand->vehicleModels()->update(['status' => VehicleModel::STATUS_ACTIVE]);
                    }
                }
                $updatedBrands[] = $brand->refresh();
            } catch (\Exception $e) {
                Log::error("Lỗi khi cập nhật trạng thái hãng xe ID {$brand->id}: " . $e->getMessage());
                $errors[] = "Không thể cập nhật trạng thái hãng xe '{$brand->name}'.";
            }
        }

        if ($updatedCount > 0) {
            $message = "Đã cập nhật trạng thái thành công cho " . $updatedCount . " hãng xe.";
            if (!empty($errors)) {
                $message .= " Một số hãng xe có lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'brands' => $updatedBrands, // Đổi tên key thành 'brands' để JS dễ xử lý
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có hãng xe nào được cập nhật. Lỗi: " . implode('; ', $errors) : "Không có hãng xe nào được chọn hoặc các hãng xe đã ở trạng thái đích.",
            'errors' => $errors,
        ], 422);
    }
}