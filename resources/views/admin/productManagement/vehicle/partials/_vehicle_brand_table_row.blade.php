{{-- File này render HTML cho một hàng (row) duy nhất của bảng Hãng xe. --}}
{{-- Nó được include từ _vehicle_brand_table_rows.blade.php hoặc trực tiếp từ BrandController cho AJAX. --}}

<tr id="vehicle-brand-row-{{ $brand->id }}"
    class="{{ !$brand->isActive() ? 'row-inactive' : '' }}">
    <td>
        {{-- Checkbox cho hành động hàng loạt --}}
        <input type="checkbox" class="brand-checkbox" value="{{ $brand->id }}">
    </td>
    {{-- STT: $loopIndex là 0-based từ AJAX, $startIndex là 0-based từ controller --}}
    <th scope="row">{{ $loopIndex + $startIndex + 1 }}</th>
    <td class="text-center">
        {{-- Logo thương hiệu --}}
        <img src="{{ $brand->logo_full_url }}" alt="{{ $brand->name }}"
            class="img-thumbnail"
            style="width: 50px; height: 50px; object-fit: contain;">
    </td>
    <td>{{ $brand->name }}</td>
    <td>{{ Str::limit($brand->description, 60) ?? 'Không có mô tả' }}</td>
    <td class="text-center status-cell" id="vehicle-brand-status-{{ $brand->id }}">
        {{-- Hiển thị trạng thái bằng badge, sử dụng accessor từ VehicleBrand model --}}
        <span class="badge {{ $brand->status_badge_class }}">{{ $brand->status_text }}</span>
    </td>
    <td class="text-center action-buttons">
        <div class="d-flex justify-content-center">
            {{-- NÚT XEM CHI TIẾT --}}
            <button type="button" class="btn btn-sm btn-success me-2 btn-view-vehicle-brand"
                data-id="{{ $brand->id }}"
                data-name="{{ $brand->name }}"
                data-description="{{ $brand->description ?? '' }}"
                data-status="{{ $brand->status }}"
                data-logo-url="{{ $brand->logo_full_url }}"
                data-created-at="{{ $brand->created_at->format('H:i:s d/m/Y') }}"
                data-updated-at="{{ $brand->updated_at->format('H:i:s d/m/Y') }}"
                data-url="{{ route('admin.productManagement.vehicleBrands.show', $brand->id) }}"
                data-update-url="{{ route('admin.productManagement.vehicleBrands.update', $brand->id) }}"
                title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#viewVehicleBrandModal">
                <i class="bi bi-eye-fill"></i>
            </button>

            {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI (Bật/Tắt) --}}
            <button type="button"
                class="btn btn-sm toggle-status-brand-btn {{ $brand->isActive() ? 'btn-outline-secondary' : 'btn-danger' }} me-2"
                data-id="{{ $brand->id }}"
                data-url="{{ route('admin.productManagement.vehicleBrands.toggleStatus', $brand->id) }}"
                title="{{ $brand->isActive() ? 'Ẩn hãng xe này' : 'Hiển thị hãng xe này' }}">
                <i class="bi bi-power"></i>
            </button>

            {{-- NÚT SỬA --}}
            <button type="button" class="btn btn-sm btn-info me-2 btn-edit-vehicle-brand"
                data-id="{{ $brand->id }}"
                data-url="{{ route('admin.productManagement.vehicleBrands.show', $brand->id) }}"
                data-update-url="{{ route('admin.productManagement.vehicleBrands.update', $brand->id) }}"
                title="Cập nhật" data-bs-toggle="modal" data-bs-target="#updateVehicleBrandModal">
                <i class="bi bi-pencil-square"></i>
            </button>

            {{-- NÚT XÓA --}}
            <button type="button" class="btn btn-sm btn-danger btn-delete-vehicle-brand"
                data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                data-delete-url="{{ route('admin.productManagement.vehicleBrands.destroy', $brand->id) }}"
                title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteVehicleBrandModal">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    </td>
</tr>