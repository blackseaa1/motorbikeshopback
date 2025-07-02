<tr id="product-row-{{ $product->id }}" class="{{ $product->trashed() ? 'row-trashed' : ($product->status === 'inactive' ? 'row-inactive' : '') }}">
    <td>
        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
    </td>
    <th scope="row">{{ $loopIndex + $startIndex + 1 }}</th>
    <td>
        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-thumbnail img-thumbnail-small">
    </td>
    <td class="fw-bold text-primary product-name">{{ $product->name }}</td>
    <td>{{ $product->category->name ?? 'N/A' }}</td>
    <td>{{ $product->brand->name ?? 'N/A' }}</td>
    <td class="text-danger fw-bold text-center">{{ $product->formatted_price }}</td>
    <td class="text-center">{{ $product->stock_quantity }}</td>
    <td class="text-center status-cell" id="product-status-config-{{ $product->id }}">
        @if($product->trashed())
            <span class="badge bg-danger">Trong thùng rác</span>
        @else
            <span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span>
        @endif
    </td>
    <td class="text-center action-buttons">
        @if ($product->trashed())
            <button class="btn btn-success btn-sm btn-action btn-restore-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Khôi phục">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button class="btn btn-danger btn-sm btn-action btn-force-delete-product" data-delete-url="{{ route('admin.productManagement.products.forceDelete', $product->id) }}" data-name="{{ $product->name }}" title="Xóa vĩnh viễn">
                <i class="bi bi-trash-fill"></i>
            </button>
        @else
            <button class="btn btn-info btn-sm btn-action btn-view" data-id="{{ $product->id }}" title="Xem chi tiết">
                <i class="bi bi-eye-fill"></i>
            </button>
            <button class="btn btn-warning btn-sm btn-action btn-edit" data-id="{{ $product->id }}" title="Cập nhật">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-action toggle-status-product-btn {{ $product->status === 'active' ? 'btn-secondary' : 'btn-success' }}" data-url="{{ route('admin.productManagement.products.toggleStatus', $product) }}" title="{{ $product->status === 'active' ? 'Dừng bán' : 'Mở bán' }}">
                <i class="bi {{ $product->status === 'active' ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }}"></i>
            </button>
            <button class="btn btn-danger btn-sm btn-action btn-delete" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Xóa">
                <i class="bi bi-trash"></i>
            </button>
        @endif
    </td>
</tr>
