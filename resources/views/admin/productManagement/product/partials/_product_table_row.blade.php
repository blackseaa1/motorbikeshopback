{{-- resources/views/admin/productManagement/product/partials/_product_table_row.blade.php --}}
{{-- File này render HTML cho một hàng (row) duy nhất của bảng Sản phẩm. --}}
{{-- Nó được include từ _product_table_rows.blade.php hoặc trực tiếp từ ProductController cho AJAX. --}}

<tr id="product-row-{{ $product->id }}" class="{{ $product->trashed() ? 'row-trashed' : ($product->status === 'inactive' ? 'row-inactive' : '') }}">
    <td>
        {{-- Checkbox cho hành động hàng loạt --}}
        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
    </td>
    {{-- STT: $loopIndex là 0-based, $startIndex là 0-based từ Controller --}}
    <td>{{ $loopIndex + $startIndex + 1 }}</td>
    <td>
        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-thumbnail img-thumbnail-small">
    </td>
    <td class="product-name">{{ $product->name }}</td>
    <td>{{ $product->category->name ?? 'N/A' }}</td>
    <td>{{ $product->brand->name ?? 'N/A' }}</td>
    <td>{{ $product->formatted_price }}</td>
    <td>{{ $product->stock_quantity }}</td>
    <td class="status-cell">
        @if($product->trashed())
            <span class="badge bg-danger">{{ $product->status_text }}</span>
        @else
            <span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span>
        @endif
    </td>
    <td class="text-center action-buttons">
        <div class="d-flex justify-content-center">
            @if ($product->trashed())
                {{-- Nút chỉ hiển thị khi ở chế độ thùng rác --}}
                <button class="btn btn-success btn-sm btn-action me-2 btn-restore-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Khôi phục" data-bs-toggle="modal" data-bs-target="#confirmRestoreModal">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button class="btn btn-danger btn-sm btn-action btn-force-delete-product" data-id="{{ $product->id }}" data-delete-url="{{ route('admin.productManagement.products.forceDelete', $product->id) }}" data-name="{{ $product->name }}" title="Xóa vĩnh viễn" data-bs-toggle="modal" data-bs-target="#confirmForceDeleteModal">
                    <i class="bi bi-trash-fill"></i>
                </button>
            @else
                {{-- Các nút này chỉ hiển thị khi không ở chế độ thùng rác --}}
                <button class="btn btn-sm btn-info btn-action me-2 btn-view-product" data-id="{{ $product->id }}" data-url="{{ route('admin.productManagement.products.show', $product->id) }}" title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#viewProductModal">
                    <i class="bi bi-eye-fill"></i>
                </button>
                <button class="btn btn-sm btn-warning btn-action me-2 btn-edit-product" data-id="{{ $product->id }}" data-url="{{ route('admin.productManagement.products.show', $product->id) }}" data-update-url="{{ route('admin.productManagement.products.update', $product->id) }}" title="Cập nhật" data-bs-toggle="modal" data-bs-target="#updateProductModal">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-action me-2 toggle-status-product-btn {{ $product->status === 'active' ? 'btn-outline-secondary' : 'btn-success' }}" data-id="{{ $product->id }}" data-url="{{ route('admin.productManagement.products.toggleStatus', $product->id) }}" title="{{ $product->status === 'active' ? 'Dừng bán' : 'Mở bán' }}">
                    <i class="bi {{ $product->status === 'active' ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }}"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-action btn-delete-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-delete-url="{{ route('admin.productManagement.products.destroy', $product->id) }}" title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                    <i class="bi bi-trash"></i>
                </button>
            @endif
        </div>
    </td>
</tr>