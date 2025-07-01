{{-- File này render HTML cho một hàng (row) duy nhất của bảng Dòng xe. --}}
{{-- Nó được include từ _vehicle_model_table_rows.blade.php hoặc trực tiếp từ VehicleModelController cho AJAX. --}}

<tr id="vehicle-model-row-{{ $model->id }}"
    class="{{ !$model->isActive() ? 'row-inactive' : '' }}">
    <td>
        {{-- Checkbox cho hành động hàng loạt --}}
        <input type="checkbox" class="model-checkbox" value="{{ $model->id }}">
    </td>
    {{-- STT: $loopIndex là 0-based từ AJAX, $startIndex là 0-based từ controller --}}
    <th scope="row">{{ $loopIndex + $startIndex + 1 }}</th>
    <td>{{ $model->name }}</td>
    <td>{{ $model->vehicleBrand->name ?? 'N/A' }}</td>
    <td class="text-center">{{ $model->year ?? 'N/A' }}</td>
    <td>{{ Str::limit($model->description, 60) ?? 'Không có mô tả' }}</td>
    <td class="text-center status-cell" id="vehicle-model-status-{{ $model->id }}">
        <span class="badge {{ $model->status_badge_class }}">{{ $model->status_text }}</span>
    </td>
    <td class="text-center action-buttons">
        <div class="d-flex justify-content-center">
            {{-- NÚT XEM --}}
            <button type="button" class="btn btn-sm btn-success me-2 btn-view-vehicle-model"
                data-id="{{ $model->id }}"
                data-name="{{ $model->name }}"
                data-vehicle-brand-id="{{ $model->vehicle_brand_id }}"
                data-vehicle-brand-name="{{ $model->vehicleBrand->name ?? 'N/A' }}"
                data-year="{{ $model->year }}"
                data-description="{{ $model->description ?? '' }}"
                data-status="{{ $model->status }}"
                data-created-at="{{ $model->created_at->format('H:i:s d/m/Y') }}"
                data-updated-at="{{ $model->updated_at->format('H:i:s d/m/Y') }}"
                data-url="{{ route('admin.productManagement.vehicleModels.show', $model->id) }}"
                data-update-url="{{ route('admin.productManagement.vehicleModels.update', $model->id) }}"
                title="Xem chi tiết">
                <i class="bi bi-eye-fill"></i>
            </button>

            {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI --}}
            <button type="button"
                class="btn btn-sm toggle-status-model-btn {{ $model->isActive() ? 'btn-outline-secondary' : 'btn-danger' }} me-2"
                data-id="{{ $model->id }}"
                data-url="{{ route('admin.productManagement.vehicleModels.toggleStatus', $model->id) }}"
                title="{{ $model->isActive() ? 'Ẩn dòng xe này' : 'Hiển thị dòng xe này' }}">
                <i class="bi bi-power"></i>
            </button>

            {{-- NÚT SỬA --}}
            <button type="button" class="btn btn-sm btn-info me-2 btn-edit-vehicle-model"
                data-id="{{ $model->id }}"
                data-url="{{ route('admin.productManagement.vehicleModels.show', $model->id) }}"
                data-update-url="{{ route('admin.productManagement.vehicleModels.update', $model->id) }}"
                title="Cập nhật">
                <i class="bi bi-pencil-square"></i>
            </button>

            {{-- NÚT XÓA --}}
            <button type="button" class="btn btn-sm btn-danger btn-delete-vehicle-model"
                data-id="{{ $model->id }}" data-name="{{ $model->name }}"
                data-delete-url="{{ route('admin.productManagement.vehicleModels.destroy', $model->id) }}"
                title="Xóa">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    </td>
</tr>