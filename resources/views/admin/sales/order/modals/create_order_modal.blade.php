<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createOrderModalLabel"><i class="bi bi-plus-circle me-2"></i>Tạo Đơn Hàng
                    Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm" action="{{ route('admin.sales.orders.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="row">
                        {{-- Cột trái: Thông tin khách hàng và sản phẩm --}}
                        <div class="col-lg-8 border-end pe-lg-4">

                            {{-- 1. Thông tin khách hàng --}}
                            <h5 class="mb-3">1. Thông tin khách hàng</h5>
                            <div class="mb-3">
                                <label class="form-label d-block">Loại khách hàng <span
                                        class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="customer_type"
                                        id="customerTypeExisting" value="existing" checked>
                                    <label class="form-check-label" for="customerTypeExisting">Khách hàng có sẵn</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="customer_type"
                                        id="customerTypeGuest" value="guest">
                                    <label class="form-check-label" for="customerTypeGuest">Khách vãng lai</label>
                                </div>
                            </div>

                            {{-- Block cho Khách hàng có sẵn --}}
                            <div id="existing_customer_block">
                                <div class="mb-3">
                                    <label for="customer_id_create" class="form-label">Chọn khách hàng <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select selectpicker " id="customer_id_create" name="customer_id"
                                        data-live-search="true" title="Tìm và chọn khách hàng...">
                                        <option value="">Chọn khách hàng...</option> {{-- Added empty option for initial state --}}
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" data-subtext="{{ $customer->email }}">
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="error-customer_id"></div>
                                </div>
                                {{-- Removed existing_address_block and new_address_form as per new logic --}}
                                {{-- Address fields will be populated in guest_customer_block --}}
                            </div>

                            {{-- Block cho Khách vãng lai (hoặc thông tin địa chỉ hiển thị cho khách có sẵn) --}}
                            <div id="guest_customer_block"> {{-- No longer hidden by default, visibility managed by JS --}}
                                <h5 class="mb-3 mt-4">2. Thông tin giao hàng</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3" >
                                        <label for="guest_name" class="form-label">Tên người nhận <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="guest_name" name="guest_name">
                                        <div class="invalid-feedback" id="error-guest_name"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="guest_phone" class="form-label">Số điện thoại <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="guest_phone" name="guest_phone">
                                        <div class="invalid-feedback" id="error-guest_phone"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_email" class="form-label">Email (Tùy chọn)</label>
                                    <input type="email" class="form-control" id="guest_email" name="guest_email">
                                    <div class="invalid-feedback" id="error-guest_email"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="guest_province_id" class="form-label">Tỉnh/Thành <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="guest_province_id" name="guest_province_id"
                                            data-type="guest">
                                            <option value="">Chọn Tỉnh/Thành</option>
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="error-guest_province_id"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="guest_district_id" class="form-label">Quận/Huyện <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="guest_district_id" name="guest_district_id"
                                            data-type="guest" disabled>
                                            <option value="">Chọn Quận/Huyện</option>
                                        </select>
                                        <div class="invalid-feedback" id="error-guest_district_id"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="guest_ward_id" class="form-label">Phường/Xã <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="guest_ward_id" name="guest_ward_id"
                                            data-type="guest" disabled>
                                            <option value="">Chọn Phường/Xã</option>
                                        </select>
                                        <div class="invalid-feedback" id="error-guest_ward_id"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_address_line" class="form-label">Địa chỉ cụ thể <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_address_line"
                                        name="guest_address_line">
                                    <div class="invalid-feedback" id="error-guest_address_line"></div>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- 3. Sản phẩm đơn hàng --}}
                            <h5 class="mb-3">3. Sản phẩm đơn hàng <span class="text-danger">*</span></h5>
                            <div id="product-items-container">
                                {{-- Các dòng sản phẩm sẽ được JS chèn vào đây --}}
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary" id="add-product-row-btn">
                                    <i class="bi bi-plus-circle me-1"></i>Thêm Sản Phẩm
                                </button>
                            </div>
                            <div class="invalid-feedback d-block" id="error-items"></div>
                        </div>

                        {{-- Cột phải: Vận chuyển, thanh toán và tóm tắt --}}
                        <div class="col-lg-4">
                            <div class="p-3 bg-light rounded sticky-top" style="top: 1rem;">
                                <h5 class="mb-3">4. Vận chuyển & Thanh toán</h5>
                                <div class="mb-3">
                                    <label for="delivery_service_id_create" class="form-label">ĐV Vận chuyển <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="delivery_service_id_create"
                                        name="delivery_service_id">
                                        <option value="">Chọn ĐVVC</option>
                                        @foreach($deliveryServices as $service)
                                            <option value="{{ $service->id }}" data-fee="{{ $service->shipping_fee }}">
                                                {{ $service->name }} ({{ number_format($service->shipping_fee) }} ₫)
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="error-delivery_service_id"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_method_id_create" class="form-label">Phương thức thanh toán
                                        <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method_id_create" name="payment_method_id">
                                        <option value="">Chọn phương thức</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="error-payment_method_id"></div>
                                </div>
                                {{-- Start of NEW Promotion Code input --}}
                                <div class="mb-3">
                                    <label for="promotion_code_create" class="form-label">Mã khuyến mãi <span class="text-muted">(Vui lòng bấm Áp dụng)</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="promotion_code_create" placeholder="Nhập mã...">
                                        <button class="btn btn-outline-secondary" type="button" id="apply-promo-btn-create">Áp dụng</button>
                                        <button class="btn btn-outline-secondary d-none" type="button" id="clear-promo-btn-create" title="Xóa mã">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div id="promo-feedback-create" class="mt-2"></div>
                                    {{-- Hidden input to store validated promotion_id --}}
                                    <input type="hidden" id="promotion_id_for_form" name="promotion_id">
                                </div>
                                {{-- End of NEW Promotion Code input --}}

                                {{-- The original promotion_id_create select is removed --}}
                                {{--
                                <div class="mb-3">
                                    <label for="promotion_id_create" class="form-label">Mã khuyến mãi</label>
                                    <select class="form-select" id="promotion_id_create" name="promotion_id">
                                        <option value="">Không áp dụng</option>
                                        @foreach($promotions as $promo)
                                            <option value="{{ $promo->id }}" data-type="{{ $promo->discount_type }}"
                                                data-value="{{ $promo->discount_value }}">
                                                {{ $promo->code }} - {{ $promo->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                --}}
                                <div class="mb-3">
                                    <label for="status_create" class="form-label">Trạng thái <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="status_create" name="status">
                                        @foreach($orderStatuses as $key => $value)
                                            <option value="{{ $key }}" {{ $key == \App\Models\Order::STATUS_PENDING ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="notes_create" class="form-label">Ghi chú (nội bộ)</label>
                                    <textarea class="form-control" id="notes_create" name="notes" rows="2"></textarea>
                                </div>

                                <hr>

                                <h5 class="mb-3">5. Tóm tắt giá trị</h5>
                                <div id="orderSummary" class="order-summary-details">
                                    <p class="mb-2 d-flex justify-content-between"><span>Tổng phụ:</span> <span
                                            id="summary-subtotal">0 ₫</span></p>
                                    <p class="mb-2 d-flex justify-content-between"><span>Phí vận chuyển:</span> <span
                                            id="summary-shipping">0 ₫</span></p>
                                    <p class="mb-2 d-flex justify-content-between text-success" id="summary-discount-row"><span>Giảm giá:</span>
                                        <span id="summary-discount">-0 ₫</span>
                                    </p>
                                    <hr class="my-2">
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <h4 class="mb-0">Tổng cộng:</h4>
                                        <h4 id="summary-grand-total" class="text-danger mb-0">0 ₫</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" form="createOrderForm" class="btn btn-primary"><i class="bi bi-save me-2"></i>Tạo
                    đơn hàng</button>
            </div>
        </div>
    </div>
</div>

{{-- Template ẩn cho một dòng sản phẩm mới. JS sẽ dùng cái này để tạo dòng mới --}}
<template id="product-row-template">
    @include('admin.sales.order.partials.product_item_row', [
        'formIdentifier' => 'createOrderForm'
    ])
</template>
    
    
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('guest_name');
        input.addEventListener('input', function () {
            this.value = this.value.replace(/^\s+/, ''); // Xóa khoảng trắng đầu
        });
    });
    let hasLoadedProductDropdown = false;

document.getElementById('productDropdownBtn').addEventListener('click', function () {
    if (hasLoadedProductDropdown) return; // Đã load rồi, không làm lại

    hasLoadedProductDropdown = true;

    // Thực hiện xử lý chỉ 1 lần ở đây, ví dụ gọi API hoặc render danh sách
    console.log("Loading product data...");

    // Ví dụ gọi API (giả lập)
    fetch('/api/products')
        .then(response => response.json())
        .then(data => {
            console.log('Product data:', data);
            // Hiển thị dữ liệu vào dropdown (nếu cần)
        })
        .catch(error => console.error('Lỗi khi load sản phẩm:', error));
});

</script>
<style>
/* Làm nền của dropdown trong suốt */
.bootstrap-select.form-select {
    background-color: #ffffff00 !important;
    /* border: none !important; nếu muốn ẩn luôn viền */
    box-shadow: none !important;
}

/* Làm trong suốt phần hiển thị đã chọn */
.bootstrap-select.form-select .btn {
    background-color: #ffffff00 !important;
    border: none !important;
    box-shadow: none !important;
    color: #000; /* Đặt màu chữ để hiển thị tốt trên nền trong suốt */
}

/* Tùy chọn: ẩn mũi tên nếu muốn */
.bootstrap-select.form-select .dropdown-toggle::after {
    display: none;
}

</style>