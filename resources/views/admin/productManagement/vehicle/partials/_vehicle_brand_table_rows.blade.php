{{-- File này chứa các hàng của bảng Hãng xe, được include từ vehicles.blade.php hoặc BrandController cho AJAX. --}}
@forelse ($brands as $brand)
    <tr id="vehicle-brand-row-{{ $brand->id }}"
        class="{{ !$brand->isActive() ? 'row-inactive' : '' }}">
        <td>
            <input type="checkbox" class="brand-checkbox" value="{{ $brand->id }}">
        </td>
        {{-- STT: $loop->index là 0-based, $startIndex là 0-based từ controller --}}
        <th scope="row">{{ $loop->index + $startIndex + 1 }}</th>
        <td class="text-center">
            <img src="{{ $brand->logo_full_url }}" alt="{{ $brand->name }}"
                class="img-thumbnail"
                style="width: 50px; height: 50px; object-fit: contain;">
        </td>
        <td>{{ $brand->name }}</td>
        <td>{{ Str::limit($brand->description, 60) ?? 'Không có mô tả' }}</td>
        <td class="text-center status-cell" id="vehicle-brand-status-{{ $brand->id }}">
            <span class="badge {{ $brand->status_badge_class }}">{{ $brand->status_text }}</span>
        </td>
        <td class="text-center action-buttons">
            <div class="d-flex justify-content-center">
                {{-- NÚT XEM --}}
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

                {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI --}}
                <button type="button"
                    class="btn btn-sm toggle-status-brand-btn {{ $brand->isActive() ? 'btn-outline-secondary' : 'btn-danger' }} me-2" {{-- ID riêng --}}
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
@empty
    <tr id="no-vehicle-brands-row">
        <td colspan="7" class="text-center">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>Hiện chưa có hãng xe nào.
            </div>
        </td>
    </tr>
@endforelse