{{-- This file renders a single category table row --}}
<tr id="category-row-{{ $category->id }}"
    class="{{ !$category->isActive() ? 'row-inactive' : '' }}">
    <td>
        <input type="checkbox" class="category-checkbox" value="{{ $category->id }}">
    </td>
    <th scope="row">{{ $loopIndex + $startIndex + 1 }}</th> {{-- Adjusted for correct STT from 0-based loopIndex --}}
    <td>{{ $category->name }}</td>
    <td>{{ Str::limit($category->description, 70) ?? 'Không có mô tả' }}</td>
    <td class="text-center status-cell" id="category-status-{{ $category->id }}">
        <span class="badge {{ $category->status_badge_class }}">{{ $category->status_text }}</span>
    </td>
    <td class="text-center action-buttons">
        <div class="d-flex justify-content-center">
            {{-- NÚT XEM --}}
            <button type="button" class="btn btn-sm btn-success me-2 btn-view-category"
                data-id="{{ $category->id }}"
                data-name="{{ $category->name }}"
                data-description="{{ $category->description ?? '' }}"
                data-status="{{ $category->status }}"
                data-status-text="{{ $category->status_text }}"
                data-status-badge-class="{{ $category->status_badge_class }}"
                data-created-at="{{ $category->created_at->format('H:i:s d/m/Y') }}"
                data-updated-at="{{ $category->updated_at->format('H:i:s d/m/Y') }}"
                data-url="{{ route('admin.productManagement.categories.show', $category->id) }}"
                title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#viewCategoryModal">
                <i class="bi bi-eye-fill"></i>
            </button>

            {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI --}}
            <button type="button"
                class="btn btn-sm toggle-status-btn {{ $category->isActive() ? 'btn-outline-secondary' : 'btn-danger' }} me-2"
                data-id="{{ $category->id }}"
                data-url="{{ route('admin.productManagement.categories.toggleStatus', $category->id) }}"
                title="{{ $category->isActive() ? 'Ẩn danh mục này' : 'Hiển thị danh mục này' }}">
                <i class="bi bi-power"></i>
            </button>

            {{-- NÚT SỬA --}}
            <button type="button" class="btn btn-sm btn-info me-2 btn-edit-category"
                data-id="{{ $category->id }}"
                data-url="{{ route('admin.productManagement.categories.show', $category->id) }}"
                data-update-url="{{ route('admin.productManagement.categories.update', $category->id) }}"
                title="Cập nhật" data-bs-toggle="modal" data-bs-target="#updateCategoryModal">
                <i class="bi bi-pencil-square"></i>
            </button>

            {{-- NÚT XÓA --}}
            <button type="button" class="btn btn-sm btn-danger btn-delete-category"
                data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                data-delete-url="{{ route('admin.productManagement.categories.destroy', $category->id) }}"
                title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    </td>
</tr>