@php
    // Sử dụng biến $index được truyền vào hoặc một placeholder.
    $rowIndex = $index ?? 'NEW_ROW_INDEX'; 
@endphp

<div class="card card-body mb-3 product-item-row" data-row-index="{{ $rowIndex }}">
    <div class="row g-3 align-items-center">
        <div class="col-md-2 d-flex justify-content-center">
            <img src="{{ asset('assets_admin/images/no-image.png') }}" alt="Ảnh sản phẩm"
                class="img-fluid rounded border product-image" style="width: 80px; height: 80px; object-fit: contain;">
        </div>

        <div class="col-md-10">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="product_id_{{ $rowIndex }}" class="form-label mb-1 visually-hidden">Sản phẩm</label>
                    {{-- Đặt tên (name) cho select để gửi product_id đi --}}
                    <select class="form-control selectpicker product-select" data-live-search="true"
                        id="product_id_{{ $rowIndex }}" name="items[{{ $rowIndex }}][product_id]"
                        title="-- Tìm & chọn sản phẩm --" required>
                        @foreach($allProductsForJs as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                data-stock="{{ $product->stock_quantity }}"
                                data-image-url="{{ optional($product->firstImage)->image_url_small ?? asset('assets_admin/images/no-image.png') }}"
                                data-subtext="Tồn kho: {{ $product->stock_quantity }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="quantity_{{ $rowIndex }}" class="form-label mb-1">Số lượng</label>
                    {{-- Đặt tên (name) cho input và hardcode giá trị ban đầu là 1 --}}
                    <input type="number" class="form-control quantity-input" id="quantity_{{ $rowIndex }}"
                        name="items[{{ $rowIndex }}][quantity]" min="1" value="1" required>
                </div>
                <div class="col-6 col-lg-3">
                    <label class="form-label mb-1">Thành tiền</label>
                    <p class="form-control-plaintext product-subtotal-display fw-bold text-success fs-5 mb-0">
                        <span class="product-subtotal-value">0 ₫</span>
                    </p>
                </div>
                <div class="col-12 col-lg-2 d-flex justify-content-end">
                    <button type="button" class="btn btn-danger remove-product-item" title="Xóa sản phẩm">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>