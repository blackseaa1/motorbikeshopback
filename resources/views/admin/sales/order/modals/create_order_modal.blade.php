<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createOrderModalLabel"><i class="bi bi-plus-circle me-2"></i>Tạo Đơn Hàng Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm" action="{{ route('admin.sales.orders.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label d-block">Loại khách hàng <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="customer_type" id="existingCustomer" value="existing" checked>
                            <label class="form-check-label" for="existingCustomer">Khách hàng có sẵn</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="customer_type" id="guestCustomer" value="guest">
                            <label class="form-check-label" for="guestCustomer">Khách vãng lai</label>
                        </div>
                        <div class="text-danger mt-1" data-field="customer_type"></div>
                    </div>

                    <!-- Khách hàng có sẵn -->
                    <div id="existingCustomerFields">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Chọn khách hàng <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer_id" name="customer_id" title="Chọn khách hàng...">
                                <option value="">Chọn khách hàng...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-tokens="{{ $customer->name }} {{ $customer->email }} {{ $customer->phone }}">
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-danger mt-1" data-field="customer_id"></div>
                        </div>
                        <div id="existingCustomerAddress">
                            <div class="mb-3">
                                <label for="shipping_address_id" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <select class="form-select" id="shipping_address_id" name="shipping_address_id" title="Chọn địa chỉ..."></select>
                                <div class="text-danger mt-1" data-field="shipping_address_id"></div>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addNewAddressBtn">
                                    <i class="bi bi-plus-lg me-2"></i>Thêm địa chỉ mới
                                </button>
                            </div>
                        </div>
                        <!-- Trường nhập địa chỉ mới cho khách có tài khoản -->
                        <div id="newAddressFields" class="d-none">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_full_name" class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new_full_name" name="new_full_name">
                                    <div class="text-danger mt-1" data-field="new_full_name"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="new_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new_phone" name="new_phone">
                                    <div class="text-danger mt-1" data-field="new_phone"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_province_id" class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                    <select class="form-select" id="new_province_id" name="new_province_id" title="Chọn tỉnh/thành...">
                                        <option value="">Chọn tỉnh/thành...</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger mt-1" data-field="new_province_id"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="new_district_id" class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                    <select class="form-select" id="new_district_id" name="new_district_id" title="Chọn quận/huyện...">
                                        <option value="">Chọn quận/huyện...</option>
                                    </select>
                                    <div class="text-danger mt-1" data-field="new_district_id"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_ward_id" class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                    <select class="form-select" id="new_ward_id" name="new_ward_id" title="Chọn phường/xã...">
                                        <option value="">Chọn phường/xã...</option>
                                    </select>
                                    <div class="text-danger mt-1" data-field="new_ward_id"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="new_address_line" class="form-label">Địa chỉ chi tiết (số nhà, đường) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new_address_line" name="new_address_line">
                                    <div class="text-danger mt-1" data-field="new_address_line"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="set_default_address" name="set_default_address">
                                    Đặt làm địa chỉ mặc định
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Khách vãng lai -->
                    <div id="guestCustomerFields" class="d-none">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_name" class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name">
                                <div class="text-danger mt-1" data-field="guest_name"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guest_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="guest_email" name="guest_email">
                                <div class="text-danger mt-1" data-field="guest_email"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_phone" name="guest_phone">
                                <div class="text-danger mt-1" data-field="guest_phone"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="province_id_create" class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                <select class="form-select" id="province_id_create" name="guest_province_id" title="Chọn tỉnh/thành...">
                                    <option value="">Chọn tỉnh/thành...</option>
                                    @foreach ($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger mt-1" data-field="guest_province_id"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="district_id_create" class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <select class="form-select" id="district_id_create" name="guest_district_id" title="Chọn quận/huyện...">
                                    <option value="">Chọn quận/huyện...</option>
                                </select>
                                <div class="text-danger mt-1" data-field="guest_district_id"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ward_id_create" class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                <select class="form-select" id="ward_id_create" name="guest_ward_id" title="Chọn phường/xã...">
                                    <option value="">Chọn phường/xã...</option>
                                </select>
                                <div class="text-danger mt-1" data-field="guest_ward_id"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address_line" class="form-label">Địa chỉ chi tiết (số nhà, đường) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shipping_address_line" name="guest_address_line">
                            <div class="text-danger mt-1" data-field="guest_address_line"></div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Sản phẩm đơn hàng</h5>
                    <div id="productItemsContainer" class="mb-3">
                        <!-- Product item rows will be added here by JavaScript -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addProductItemBtn">
                        <i class="bi bi-plus-lg me-2"></i>Thêm sản phẩm
                    </button>
                    <div class="text-danger mt-1" data-field="product_ids"></div>
                    <div class="text-danger mt-1" data-field="quantities"></div>
                    <div class="text-danger mt-1" id="product_stock_error_create"></div>

                    <hr>

                    <h5 class="mb-3">Thông tin thanh toán & vận chuyển</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method_create" class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method_create" name="payment_method" title="Chọn phương thức...">
                                <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                <option value="vnpay">Thanh toán qua VNPay</option>
                            </select>
                            <div class="text-danger mt-1" data-field="payment_method"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="delivery_service_id_create" class="form-label">Dịch vụ vận chuyển <span class="text-danger">*</span></label>
                            <select class="form-select" id="delivery_service_id_create" name="delivery_service_id" title="Chọn dịch vụ...">
                                @foreach ($deliveryServices as $service)
                                    <option value="{{ $service->id }}" data-shipping-fee="{{ $service->shipping_fee }}">
                                        {{ $service->name }} ({{ number_format($service->shipping_fee) }} ₫)
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-danger mt-1" data-field="delivery_service_id"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="promotion_id_create" class="form-label">Mã khuyến mãi</label>
                        <select class="form-select" id="promotion_id_create" name="promotion_id" title="Chọn mã khuyến mãi...">
                            <option value="">Không áp dụng</option>
                            @foreach ($promotions as $promo)
                                <option value="{{ $promo->id }}" data-discount-percentage="{{ $promo->discount_percentage }}">
                                    {{ $promo->code }} ({{ $promo->formatted_discount }})
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="promotion_id"></div>
                    </div>

                    <div class="mb-3">
                        <label for="status_create" class="form-label">Trạng thái đơn hàng <span class="text-danger">*</span></label>
                        <select class="form-select" id="status_create" name="status" title="Chọn trạng thái...">
                            @foreach ($initialOrderStatuses as $key => $value)
                                <option value="{{ $key }}" {{ $key === \App\Models\Order::STATUS_PENDING ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="status"></div>
                    </div>

                    <div class="mb-3">
                        <label for="notes_create" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes_create" name="notes" rows="3"></textarea>
                        <div class="text-danger mt-1" data-field="notes"></div>
                    </div>

                    <hr>

                    <div class="order-summary-details text-end">
                        <p class="mb-1"><strong>Tổng phụ:</strong> <span id="create-order-subtotal">0 ₫</span></p>
                        <p class="mb-1"><strong>Phí vận chuyển:</strong> <span id="create-order-shipping-fee">0 ₫</span></p>
                        <p class="mb-1 text-success"><strong>Giảm giá:</strong> <span id="create-order-discount">-0 ₫</span></p>
                        <h4 class="mt-2"><strong>Tổng cộng:</strong> <span id="create-order-grand-total" class="text-danger">0 ₫</span></h4>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" form="createOrderForm" class="btn btn-primary">Tạo đơn hàng</button>
            </div>
        </div>
    </div>
</div>