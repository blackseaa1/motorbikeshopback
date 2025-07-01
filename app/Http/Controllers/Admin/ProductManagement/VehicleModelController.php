<?php

namespace App\Http\Controllers\Admin\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use App\Models\VehicleBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse; // Import
use Illuminate\Http\RedirectResponse; // Import
use Illuminate\View\View; // Import
use Illuminate\Support\Facades\Log; // Import


class VehicleModelController extends Controller
{
    /**
     * Trả về dữ liệu chi tiết của một dòng xe dưới dạng JSON cho AJAX. (NEW)
     *
     * @param Request $request
     * @param VehicleModel $vehicleModel
     * @return JsonResponse
     */
    public function show(Request $request, VehicleModel $vehicleModel): JsonResponse
    {
        // Load relationship vehicleBrand để có thể truy cập tên hãng xe trong JS
        $vehicleModel->load('vehicleBrand');
        return response()->json($vehicleModel);
    }

    /**
     * Lưu một dòng xe mới vào cơ sở dữ liệu.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:150',
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([VehicleModel::STATUS_ACTIVE, VehicleModel::STATUS_INACTIVE])],
        ]);

        $vehicleModel = VehicleModel::create($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo dòng xe thành công!',
                'vehicleModel' => $vehicleModel->refresh()->load('vehicleBrand') // Load brand để trả về
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models', 'filter_vehicle_brand_id' => $request->vehicle_brand_id])
            ->with('success', 'Tạo dòng xe thành công!');
    }

    /**
     * Cập nhật thông tin dòng xe đã tồn tại.
     *
     * @param Request $request
     * @param VehicleModel $vehicleModel
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, VehicleModel $vehicleModel): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:150',
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
            'status' => ['required', Rule::in([VehicleModel::STATUS_ACTIVE, VehicleModel::STATUS_INACTIVE])],
        ]);

        $vehicleModel->update($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật dòng xe thành công!',
                'vehicleModel' => $vehicleModel->refresh()->load('vehicleBrand') // Load cả brand để JS có thể cập nhật
            ]);
        }
        return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models', 'filter_vehicle_brand_id' => $request->vehicle_brand_id])
            ->with('success', 'Cập nhật dòng xe thành công!');
    }

    /**
     * Xóa một dòng xe khỏi cơ sở dữ liệu.
     *
     * @param Request $request
     * @param VehicleModel $vehicleModel
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, VehicleModel $vehicleModel): JsonResponse|RedirectResponse
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
            if ($vehicleModel->products()->exists()) {
                $msg = 'Không thể xóa dòng xe này vì vẫn còn sản phẩm liên quan.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models'])->with('error', $msg);
            }

            $vehicleModel->delete();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa dòng xe thành công!',
                    'deleted_ids' => [$vehicleModel->id] // Trả về ID của dòng xe đã xóa
                ]);
            }
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models'])->with('success', 'Xóa dòng xe thành công!');
        } catch (QueryException $e) {
            Log::error("Lỗi khi xóa dòng xe ID {$vehicleModel->id}: " . $e->getMessage());
            $msg = 'Đã xảy ra lỗi khi xóa dòng xe.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->route('admin.productManagement.vehicle.index', ['tab' => 'models'])->with('error', $msg);
        }
    }

    /**
     * Thay đổi trạng thái (active/inactive) của một dòng xe.
     *
     * @param Request $request
     * @param VehicleModel $vehicleModel
     * @return JsonResponse
     */
    public function toggleStatus(Request $request, VehicleModel $vehicleModel): JsonResponse
    {
        $vehicleModel->status = ($vehicleModel->status === VehicleModel::STATUS_ACTIVE) ? VehicleModel::STATUS_INACTIVE : VehicleModel::STATUS_ACTIVE;
        $vehicleModel->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái dòng xe thành công!',
            'vehicleModel' => $vehicleModel->refresh()->load('vehicleBrand'), // Trả về đối tượng đã refresh và load brand
        ]);
    }

    /**
     * Xóa hàng loạt các dòng xe. (NEW)
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

        $modelsToDelete = VehicleModel::whereIn('id', $ids)->get();

        foreach ($modelsToDelete as $model) {
            if ($model->products()->exists()) {
                $errors[] = "Dòng xe '{$model->name}' không thể xóa vì vẫn còn sản phẩm liên quan.";
            } else {
                try {
                    $model->delete();
                    $successfullyDeletedIds[] = $model->id;
                } catch (QueryException $e) {
                    Log::error("Lỗi khi xóa hàng loạt dòng xe ID {$model->id}: " . $e->getMessage());
                    $errors[] = "Xảy ra lỗi khi xóa dòng xe '{$model->name}'.";
                }
            }
        }

        $deletedCount = count($successfullyDeletedIds);
        $message = '';

        if ($deletedCount > 0) {
            $message = "Đã xóa thành công {$deletedCount} dòng xe.";
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
            'message' => count($errors) > 0 ? "Không có dòng xe nào được xóa. Lỗi: " . implode('; ', $errors) : "Không có dòng xe nào được chọn để xóa hoặc xảy ra lỗi không xác định.",
            'errors' => $errors,
        ], 422);
    }

    /**
     * Bật/tắt trạng thái của nhiều dòng xe. (NEW)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|json',
            'status' => ['required', Rule::in([VehicleModel::STATUS_ACTIVE, VehicleModel::STATUS_INACTIVE])],
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
        $updatedModels = [];
        $errors = [];

        $modelsToUpdate = VehicleModel::whereIn('id', $ids)->get();

        foreach ($modelsToUpdate as $model) {
            try {
                if ($model->status !== $targetStatus) {
                    $model->status = $targetStatus;
                    $model->save();
                    $updatedCount++;
                }
                $updatedModels[] = $model->refresh()->load('vehicleBrand'); // Load brand để trả về
            } catch (\Exception $e) {
                Log::error("Lỗi khi cập nhật trạng thái dòng xe ID {$model->id}: " . $e->getMessage());
                $errors[] = "Không thể cập nhật trạng thái dòng xe '{$model->name}'.";
            }
        }

        if ($updatedCount > 0) {
            $message = "Đã cập nhật trạng thái thành công cho " . $updatedCount . " dòng xe.";
            if (!empty($errors)) {
                $message .= " Một số dòng xe có lỗi: " . implode('; ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'vehicleModels' => $updatedModels, // Đổi tên key thành 'vehicleModels'
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => count($errors) > 0 ? "Không có dòng xe nào được cập nhật. Lỗi: " . implode('; ', $errors) : "Không có dòng xe nào được chọn hoặc các dòng xe đã ở trạng thái đích.",
            'errors' => $errors,
        ], 422);
    }
}