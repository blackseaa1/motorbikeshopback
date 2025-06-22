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
                        <div class="col-lg-8 border-end pe-lg-4">
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

                            <div id="existingCustomerBlock">
                                <div class="mb-3">
                                    <label for="customer_id_create" class="form-label">Chọn khách hàng <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select selectpicker" id="customer_id_create" name="customer_id"
                                        data-live-search="true" title="Tìm và chọn khách hàng...">
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" data-subtext="{{ $customer->email }}">
                                                {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger mt-1" data-field="customer_id"></div>
                                </div>
                            </div>

                            <h5 class="mb-3 mt-4">2. Địa chỉ giao hàng</h5>
                            <input type="hidden" name="shipping_address_option" id="shipping_address_option_create"
                                value="existing">

                            <div id="existingAddressBlock">
                                <div id="addressListContainer"
                                    class="address-list-container mb-2 p-3 border rounded bg-light"
                                    style="max-height: 200px; overflow-y: auto;">
                                    <p class="text-muted">Vui lòng chọn khách hàng để xem địa chỉ.</p>
                                </div>
                                <div class="text-danger mt-1" data-field="shipping_address_id"></div>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    id="btn-show-new-address-form">
                                    <i class="bi bi-plus-circle"></i> Thêm địa chỉ mới
                                </button>
                            </div>

                            <div id="newAddressBlock" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_shipping_name" class="form-label">Họ tên người nhận <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="new_shipping_name"
                                            name="new_shipping_name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="new_shipping_phone" class="form-label">SĐT người nhận <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="new_shipping_phone"
                                            name="new_shipping_phone">
                                    </div>
                                    <div class="col-md-6 mb-3 guest-only-field">
                                        <label for="guest_name" class="form-label">Họ tên khách vãng lai <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="guest_name" name="guest_name">
                                    </div>
                                    <div class="col-md-6 mb-3 guest-only-field">
                                        <label for="guest_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="guest_email" name="guest_email">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="new_province_id" class="form-label">Tỉnh/Thành <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="new_province_id" name="new_province_id">
                                            <option value="">Chọn Tỉnh/Thành</option>
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="new_district_id" class="form-label">Quận/Huyện <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="new_district_id" name="new_district_id"
                                            disabled>
                                            <option value="">Chọn Quận/Huyện</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="new_ward_id" class="form-label">Phường/Xã <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="new_ward_id" name="new_ward_id" disabled>
                                            <option value="">Chọn Phường/Xã</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="new_address_line" class="form-label">Địa chỉ cụ thể <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new_address_line"
                                        name="new_address_line">
                                </div>
                                <button type="button" class="btn btn-sm btn-link ps-0" id="btn-cancel-new-address">Hủy
                                    và chọn địa chỉ có sẵn</button>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">3. Sản phẩm đơn hàng <span class="text-danger">*</span></h5>
                            <div id="product-items-container">
                                {{-- Các dòng sản phẩm sẽ được JS chèn vào đây --}}
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary" id="add-product-row-btn">
                                    <i class="bi bi-plus-circle me-1"></i>Thêm Sản Phẩm
                                </button>
                            </div>
                            <div class="text-danger mt-1" data-field="items"></div>
                        </div>

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
                                    <div class="text-danger mt-1" data-field="delivery_service_id"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="promotion_id_create" class="form-label">Mã khuyến mãi</label>
                                    <select class="form-select" id="promotion_id_create" name="promotion_id">
                                        <option value="">Không áp dụng</option>
                                        @foreach($promotions as $promo)
                                            <option value="{{ $promo->id }}" data-type="{{ $promo->type }}"
                                                data-value="{{ $promo->value }}">{{ $promo->code }} -
                                                {{ $promo->description }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
                                    <p class="mb-2 d-flex justify-content-between text-success"><span>Giảm giá:</span>
                                        <span id="summary-discount">-0 ₫</span></p>
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
        'allProductsForJs' => $allProductsForJs,
        'formIdentifier' => 'createOrderForm'
    ])
</template>