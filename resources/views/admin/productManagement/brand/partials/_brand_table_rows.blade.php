{{-- File này chỉ chứa các hàng của bảng thương hiệu, được sử dụng cho cả lần tải đầu và AJAX --}}
@forelse ($brands as $brand)
    <tr id="brand-row-{{ $brand->id }}"
        class="{{ !$brand->isActive() ? 'row-inactive' : '' }}">
        <td>
            <input type="checkbox" class="brand-checkbox" value="{{ $brand->id }}">
        </td>
        {{-- Sử dụng $loop->index và $brands->firstItem() để tính toán STT chính xác --}}
        <th scope="row">{{ $loop->index + $brands->firstItem() }}</th>
        <td class="text-center">
            <img src="{{ $brand->logo_full_url }}" alt="{{ $brand->name }}"
                class="img-thumbnail"
                style="width: 50px; height: 50px; object-fit: contain;">
        </td>
        <td>{{ $brand->name }}</td>
        <td>{{ Str::limit($brand->description, 60) ?? 'Không có mô tả' }}</td>
        <td class="text-center status-cell" id="brand-status-{{ $brand->id }}">
            <span class="badge {{ $brand->status_badge_class }}">{{ $brand->status_text }}</span>
        </td>
        <td class="text-center action-buttons">
            <div class="d-flex justify-content-center">
                {{-- NÚT XEM --}}
                <button type="button" class="btn btn-sm btn-success me-2 btn-view-brand"
                    data-id="{{ $brand->id }}"
                    data-name="{{ $brand->name }}"
                    data-description="{{ $brand->description ?? '' }}"
                    data-status="{{ $brand->status }}"
                    data-logo-url="{{ $brand->logo_full_url }}"
                    data-created-at="{{ $brand->created_at->format('H:i:s d/m/Y') }}"
                    data-updated-at="{{ $brand->updated_at->format('H:i:s d/m/Y') }}"
                    data-url="{{ route('admin.productManagement.brands.show', $brand->id) }}" {{-- Sử dụng data-url cho AJAX fetch --}}
                    title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#viewBrandModal">
                    <i class="bi bi-eye-fill"></i>
                </button>

                {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI --}}
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
                    data-url="{{ route('admin.productManagement.brands.show', $brand->id) }}" {{-- Sử dụng data-url cho AJAX fetch --}}
                    data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}"
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
@empty
    <tr id="no-brands-row">
        <td colspan="7" class="text-center"> {{-- Cập nhật colspan cho đúng số cột --}}
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>Hiện chưa có thương hiệu nào.
            </div>
        </td>
    </tr>
@endforelse