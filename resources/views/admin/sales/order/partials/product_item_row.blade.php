{{-- resources/views/admin/sales/order/partials/product_item_row.blade.php --}}
@php
    // Default values if not provided
    $index = $index ?? 'NEW_ROW_INDEX'; // For new rows, will be replaced by JS
    $product_id = $product_id ?? null;
    $quantity = $quantity ?? null;
    $order_item_id = $order_item_id ?? null; // For update modal, to track existing items
    $formIdentifier = $formIdentifier ?? 'create_order_form'; // Default for create form
@endphp

<div class="card card-body mb-2 product-item-row-modal" data-row-index="{{ $index }}">
    <div class="row align-items-center">
        <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $order_item_id }}">
        <div class="col-md-5">
            <label for="product_id_{{ $index }}" class="form-label">Sản phẩm <span class="text-danger">*</span></label>
            <select
                class="form-control selectpicker product-select @error("items.{$index}.product_id", $formIdentifier) is-invalid @enderror"
                data-live-search="true" id="product_id_{{ $index }}" name="items[{{ $index }}][product_id]" required>
                <option value="">-- Chọn sản phẩm --</option>
                @foreach($allProductsForJs as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                        data-stock="{{ $product->stock_quantity }}" @if($product_id == $product->id) selected @endif>
                        {{ $product->name }} (Tồn: {{ $product->stock_quantity }})
                    </option>
                @endforeach
            </select>
            @error("items.{$index}.product_id", $formIdentifier)
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="quantity_{{ $index }}" class="form-label">Số lượng <span class="text-danger">*</span></label>
            <input type="number"
                class="form-control quantity-input @error("items.{$index}.quantity", $formIdentifier) is-invalid @enderror"
                id="quantity_{{ $index }}" name="items[{{ $index }}][quantity]" min="1" value="{{ $quantity ?? 1 }}"
                required>
            @error("items.{$index}.quantity", $formIdentifier)
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Thành tiền</label>
            <p class="form-control-plaintext product-subtotal-display">
                <span class="product-subtotal-value">0</span> ₫
            </p>
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-danger btn-sm remove-product-item" title="Xóa sản phẩm">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>
</div>