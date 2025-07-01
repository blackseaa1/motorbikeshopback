<tr id="promotion-row-{{ $promotion->id }}" class="{{ !$promotion->isManuallyActive() ? 'row-inactive' : '' }}">
    <td>
        <input type="checkbox" class="promotion-checkbox" value="{{ $promotion->id }}">
    </td>
    <th scope="row">{{ $loopIndex + $startIndex }}</th>
    <td class="fw-bold text-primary">{{ $promotion->code }}</td>
    <td>{{ Str::limit($promotion->description, 50) }}</td>
    <td class="text-danger fw-bold text-center">
        {{ $promotion->display_discount_value }}
    </td>
    <td>
        <small>
            Từ: {{ $promotion->start_date->format('d/m/Y H:i') }}<br>
            Đến: {{ $promotion->end_date->format('d/m/Y H:i') }}
        </small>
    </td>
    <td class="text-center">{{ $promotion->uses_count }} /
        {{ $promotion->max_uses ?? '∞' }}
    </td>
    <td class="text-center" id="promotion-status-config-{{ $promotion->id }}">
        <span
            class="badge {{ $promotion->manual_status_badge_class }}">{{ $promotion->manual_status_text }}</span>
    </td>
    <td class="text-center" id="promotion-status-display-{{ $promotion->id }}">
        <span
            class="badge {{ $promotion->effective_status_badge_class }}">{{ $promotion->effective_status_text }}</span>
    </td>
    <td class="text-center">
        <div class="d-flex justify-content-center">
            {{-- Nút Bật/Tắt --}}
            <button class="btn btn-sm toggle-status-btn {{ $promotion->isManuallyActive() ? 'btn-outline-secondary' : 'btn-danger' }} me-2"
                data-id="{{ $promotion->id }}"
                data-url="{{ route('admin.sales.promotions.toggleStatus', $promotion->id) }}"
                title="{{ $promotion->isManuallyActive() ? 'Ẩn mã này' : 'Hiển thị mã này' }}">
                <i class="bi bi-power"></i>
            </button>

            {{-- Nút Xem --}}
            <button class="btn btn-sm btn-success me-2 view-promotion-btn"
                data-bs-toggle="modal" data-bs-target="#viewPromotionModal"
                data-id="{{ $promotion->id }}"
                data-url="{{ route('admin.sales.promotions.show', $promotion->id) }}"
                title="Xem chi tiết">
                <i class="bi bi-eye-fill"></i>
            </button>

            {{-- Nút Sửa --}}
            <button class="btn btn-sm btn-info me-2 edit-promotion-btn"
                data-bs-toggle="modal" data-bs-target="#updatePromotionModal"
                data-id="{{ $promotion->id }}"
                data-update-url="{{ route('admin.sales.promotions.update', $promotion->id) }}"
                data-url="{{ route('admin.sales.promotions.show', $promotion->id) }}"
                title="Chỉnh sửa">
                <i class="bi bi-pencil-square"></i>
            </button>

            {{-- Nút Xóa --}}
            <button class="btn btn-sm btn-danger delete-promotion-btn"
                data-bs-toggle="modal" data-bs-target="#deletePromotionModal"
                data-id="{{ $promotion->id }}" data-code="{{ $promotion->code }}"
                data-delete-url="{{ route('admin.sales.promotions.destroy', $promotion->id) }}"
                title="Xóa">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    </td>
</tr>