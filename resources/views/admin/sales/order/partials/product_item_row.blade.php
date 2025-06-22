@php
    $index = $index ?? 'NEW_ROW_INDEX';
    $product_id = $product_id ?? null;
    $quantity = $quantity ?? 1;
    $formIdentifier = $formIdentifier ?? 'createOrderForm';
@endphp

<div class="card card-body mb-3 product-item-row" data-row-index="{{ $index }}">
    <div class="row g-3">
        <div class="col-md-2 d-flex justify-content-center align-items-center">
            <img src="{{ asset('assets_admin/images/no-image.png') }}" alt="Ảnh sản phẩm"
                class="img-fluid rounded border product-image" style="width: 80px; height: 80px; object-fit: contain;">
        </div>

        <div class="col-md-10">
            <div class="row g-2">
                <div class="col-12">
                    <label for="product_id_{{ $index }}" class="form-label mb-1 visually-hidden">Sản phẩm</label>
                    <select class="form-control selectpicker product-select" data-live-search="true"
                        id="product_id_{{ $index }}" name="items[{{ $index }}][product_id]"
                        title="-- Tìm & chọn sản phẩm --" required>
                        @foreach($allProductsForJs as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                data-stock="{{ $product->stock_quantity }}"
                                data-image-url="{{ $product->first_image->image_url_small ?? asset('assets_admin/images/no-image.png') }}"
                                data-subtext="Tồn kho: {{ $product->stock_quantity }}" @if($product_id == $product->id)
                                selected @endif>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="quantity_{{ $index }}" class="form-label mb-1">Số lượng</label>
                    <input type="number" class="form-control quantity-input" id="quantity_{{ $index }}"
                        name="items[{{ $index }}][quantity]" min="1" value="{{ $quantity }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label mb-1">Thành tiền</label>
                    <p class="form-control-plaintext product-subtotal-display fw-bold text-success fs-5 mb-0">
                        <span class="product-subtotal-value">0</span> ₫
                    </p>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-danger remove-product-item" title="Xóa sản phẩm">
                        <i class="bi bi-trash"></i> Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>