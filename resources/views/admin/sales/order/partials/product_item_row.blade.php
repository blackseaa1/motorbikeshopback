@php
    // Sử dụng biến $index được truyền vào hoặc một placeholder.
    // Biến $rowIndex này sẽ được thay thế bằng một chỉ mục duy nhất (ví dụ: timestamp) bởi JavaScript.
    $rowIndex = $index ?? 'NEW_ROW_INDEX';
@endphp

<div class="card card-body mb-3 product-item-row shadow-sm" data-row-index="{{ $rowIndex }}">
    <div class="row g-3">
        <div class="col-md-2 d-flex justify-content-center flex-column">
            {{-- Hình ảnh sản phẩm --}}
            <img src="https://placehold.co/400x400/EFEFEF/AAAAAA&text=Product" alt="Ảnh sản phẩm"
                class="img-fluid rounded border product-thumbnail product-image"
                style="width: 80px; height: 80px; object-fit: contain;">
            <div class="text-info mt-1 small stock-info-message"></div>
            <div class="invalid-feedback" data-field="items.{{ $rowIndex }}.product_id"></div>


        </div>

        <div class="col-md-10 ">
            <div class="row g-2 justify-content-between align-items-end">
                <div class="col-8 col-lg-5">
                    <label for="product_id_{{ $rowIndex }}" class="form-label mb-1 visually-hidden">Sản phẩm</label>
                    {{-- Thẻ select để chọn sản phẩm --}}
                    <select class="form-control selectpicker product-select" id="product_id_{{ $rowIndex }}"
                        name="items[{{ $rowIndex }}][product_id]" data-live-search="true" title="Chọn sản phẩm..."
                        required>
                        {{-- Option rỗng này là placeholder, sẽ được JS điền các sản phẩm động vào --}}
                        <option class="product-select-placeholder" value=""></option>
                    </select>
                    {{-- Feedback cho lỗi validation từ backend (sẽ được JS xử lý) --}}
                </div>

                <div class="col-10 col-lg-2">
                    <label for="quantity_{{ $rowIndex }}" class="form-label mb-1">Số lượng</label>
                    <div class="input-group input-group-sm"> {{-- Thêm input-group-sm để nhỏ gọn hơn --}}
                        {{-- Nút giảm số lượng --}}
                        <button type="button" class="btn btn-outline-secondary quantity-minus-btn" tabindex="-1">
                            <i class="bi bi-dash"></i>
                        </button>
                        {{-- Input số lượng --}}
                        <input type="number" class="form-control quantity-input text-center"
                            id="quantity_{{ $rowIndex }}" name="items[{{ $rowIndex }}][quantity]" min="1" value="1" {{--
                            Giá trị mặc định là 1 --}} required>
                        {{-- Nút tăng số lượng --}}
                        <button type="button" class="btn btn-outline-secondary quantity-plus-btn" tabindex="-1">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    {{-- Feedback cho lỗi validation từ backend --}}
                    <div class="text-danger mt-1 small" data-field="items.{{ $rowIndex }}.quantity"></div>
                    {{-- Thông báo số lượng tồn kho --}}
                </div>
                <div class="col-6 col-lg-4">
                    <label class="form-label mb-1">Thành tiền</label>
                    {{-- Hiển thị thành tiền của từng sản phẩm, được cập nhật bởi JS --}}
                    <p class="form-control-plaintext product-subtotal-display fw-bold text-success fs-5 mb-0">
                        <span class="product-subtotal-value">0 ₫</span>
                    </p>
                </div>
                <div class="col-12 col-lg-1 d-flex justify-content-end align-items-center"> {{-- Căn giữa theo chiều dọc
                    --}}
                    {{-- Nút xóa dòng sản phẩm --}}
                    <button type="button" class="btn btn-outline-danger remove-product-item" title="Xóa sản phẩm">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>