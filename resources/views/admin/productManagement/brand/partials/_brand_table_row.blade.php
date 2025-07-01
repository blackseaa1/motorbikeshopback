{{-- File này render HTML cho một hàng (row) duy nhất của bảng Thương hiệu. --}}
{{-- Nó được include từ _brand_table_rows.blade.php hoặc trực tiếp từ BrandController cho AJAX. --}}

<tr id="brand-row-{{ $brand->id }}"
    class="{{ !$brand->isActive() ? 'row-inactive' : '' }}">
    <td>
        {{-- Checkbox cho hành động hàng loạt --}}
        <input type="checkbox" class="brand-checkbox" value="{{ $brand->id }}">
    </td>
    {{-- STT (Số thứ tự): Sử dụng loopIndex (0-based) và startIndex để tính toán đúng --}}
    <th scope="row">{{ $loopIndex + $startIndex + 1 }}</th> {{-- +1 vì loopIndex là 0-based và startIndex cũng là 0-based từ controller --}}
    <td class="text-center">
        {{-- Logo thương hiệu --}}
        <img src="{{ $brand->logo_full_url }}" alt="{{ $brand->name }}"
            class="img-thumbnail"
            style="width: 50px; height: 50px; object-fit: contain;">
    </td>
    <td>{{ $brand->name }}</td>
    <td>{{ Str::limit($brand->description, 60) ?? 'Không có mô tả' }}</td>
    <td class="text-center status-cell" id="brand-status-{{ $brand->id }}">
        {{-- Hiển thị trạng thái bằng badge, sử dụng accessor từ Brand model --}}
        <span class="badge {{ $brand->status_badge_class }}">{{ $brand->status_text }}</span>
    </td>
    <td class="text-center action-buttons">
        <div class="d-flex justify-content-center">
            {{-- NÚT XEM CHI TIẾT --}}
            <button type="button" class="btn btn-sm btn-success me-2 btn-view-brand"
                data-id="{{ $brand->id }}"
                data-name="{{ $brand->name }}"
                data-description="{{ $brand->description ?? '' }}"
                data-status="{{ $brand->status }}"
                data-logo-url="{{ $brand->logo_full_url }}"
                data-created-at="{{ $brand->created_at->format('H:i:s d/m/Y') }}"
                data-updated-at="{{ $brand->updated_at->format('H:i:s d/m/Y') }}"
                data-url="{{ route('admin.productManagement.brands.show', $brand->id) }}" {{-- URL để JS fetch dữ liệu --}}
                data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}" {{-- URL update để truyền cho nút Sửa từ View Modal --}}
                title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#viewBrandModal">
                <i class="bi bi-eye-fill"></i>
            </button>

            {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI (Bật/Tắt) --}}
            <button type="button"
                class="btn btn-sm toggle-status-btn {{ $brand->isActive() ? 'btn-outline-secondary' : 'btn-danger' }} me-2"
                data-id="{{ $brand->id }}"
                data-url="{{ route('admin.productManagement.brands.toggleStatus', $brand->id) }}"
                title="{{ $brand->isActive() ? 'Ẩn thương hiệu này' : 'Hiển thị thương hiệu này' }}">
                <i class="bi bi-power"></i>
            </button>

            {{-- NÚT SỬA --}}
            <button type="button" class="btn btn-sm btn-info me-2 btn-edit-brand"
                data-id="{{ $brand->id }}"
                data-name="{{ $brand->name }}" {{-- Giữ lại data-name, description, status để JS có thể lấy nhanh nếu không fetch lại --}}
                data-description="{{ $brand->description ?? '' }}"
                data-status="{{ $brand->status }}"
                data-logo-url="{{ $brand->logo_full_url }}"
                data-url="{{ route('admin.productManagement.brands.show', $brand->id) }}" {{-- URL để JS fetch dữ liệu --}}
                data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}" {{-- URL để form POST/PUT dữ liệu --}}
                title="Cập nhật" data-bs-toggle="modal" data-bs-target="#updateBrandModal">
                <i class="bi bi-pencil-square"></i>
            </button>

            {{-- NÚT XÓA --}}
            <button type="button" class="btn btn-sm btn-danger btn-delete-brand"
                data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                data-delete-url="{{ route('admin.productManagement.brands.destroy', $brand->id) }}"
                title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteBrandModal">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    </td>
</tr>