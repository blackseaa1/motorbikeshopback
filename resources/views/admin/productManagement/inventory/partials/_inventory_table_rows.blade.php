{{-- File: resources/views/admin/productManagement/inventory/partials/_inventory_table_rows.blade.php --}}
{{-- This file renders a single row for the inventory table. --}}
{{-- It is included by inventory.blade.php for initial load and by InventoryController for AJAX. --}}

@forelse ($lowStockProducts as $product)
    <tr>
        <td>
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                class="img-fluid rounded border"
                style="width: 60px; height: 60px; object-fit: contain;"
                onerror="this.src='https://placehold.co/60x60/grey/white?text=Img'">
        </td>
        <td>{{ $product->name }}</td>
        <td>{{ $product->category->name ?? 'N/A' }}</td>
        <td>{{ $product->brand->name ?? 'N/A' }}</td>
        <td class="text-center">
            <span class="badge bg-danger">{{ $product->stock_quantity }}</span>
        </td>
        <td class="text-end">{{ $product->formatted_price }}</td> {{-- Sử dụng accessor formatted_price --}}
        <td class="text-center">
            <button type="button" class="btn btn-info btn-sm view-product-details-btn"
                data-bs-toggle="modal" data-bs-target="#viewProductDetailsModal"
                data-id="{{ $product->id }}" title="Xem chi tiết">
                <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-success btn-sm open-update-quantity-modal-btn"
                data-bs-toggle="modal" data-bs-target="#updateQuantityModal"
                data-id="{{ $product->id }}" title="Cập nhật số lượng">
                <i class="bi bi-pencil"></i>
            </button>
        </td>
    </tr>
@empty
    <tr id="no-low-stock-products-row">
        <td colspan="7" class="text-center">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>Không có sản phẩm nào sắp hết hàng.
            </div>
        </td>
    </tr>
@endforelse