{{-- File: resources/views/admin/productManagement/product/partials/_product_table_rows.blade.php --}}
{{-- This file is included by products.blade.php for initial load and by ProductController for AJAX. --}}

@forelse ($products as $product)
    <tr id="product-row-{{ $product->id }}" class="{{ $product->trashed() ? 'row-trashed' : ($product->status === 'inactive' ? 'row-inactive' : '') }}">
        <td>
            <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
        </td>
        {{-- STT (Số thứ tự): Sử dụng startIndex và loop.iteration để tính toán đúng --}}
        <th scope="row">{{ $startIndex + $loop->iteration }}</th> {{-- FIX: Changed STT calculation --}}
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
                <span class="badge bg-danger">Trong thùng rác</span>
            @else
                <span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span>
            @endif
        </td>
        <td class="text-center action-buttons">
            <div class="d-flex justify-content-center">
                @if ($product->trashed())
                    <button class="btn btn-success btn-sm btn-action btn-restore-product me-2"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-restore-url="{{ route('admin.productManagement.products.restore', $product->id) }}"
                            title="Khôi phục">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-action btn-force-delete-product"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-force-delete-url="{{ route('admin.productManagement.products.forceDelete', $product->id) }}"
                            title="Xóa vĩnh viễn">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                @else
                    <button class="btn btn-info btn-sm btn-action btn-view me-2"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('admin.productManagement.products.details', $product->id) }}"
                            title="Xem chi tiết">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                    <button class="btn btn-warning btn-sm btn-action btn-edit me-2"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('admin.productManagement.products.details', $product->id) }}"
                            data-update-url="{{ route('admin.productManagement.products.update', $product->id) }}"
                            title="Cập nhật">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-action toggle-status-product-btn me-2 {{ $product->status === 'active' ? 'btn-outline-secondary' : 'btn-success' }}"
                            data-id="{{ $product->id }}"
                            data-status-url="{{ route('admin.productManagement.products.toggleStatus', $product->id) }}"
                            title="{{ $product->status === 'active' ? 'Dừng bán' : 'Mở bán' }}">
                        <i class="bi {{ $product->status === 'active' ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }}"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-action btn-delete-product"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-delete-url="{{ route('admin.productManagement.products.destroy', $product->id) }}"
                            title="Xóa mềm">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr id="no-products-row">
        <td colspan="10" class="text-center">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>Hiện chưa có sản phẩm nào.
            </div>
        </td>
    </tr>
@endforelse