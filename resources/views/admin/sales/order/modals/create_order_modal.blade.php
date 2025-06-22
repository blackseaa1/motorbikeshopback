{{-- resources/views/admin/sales/order/modals/create_order_modal.blade.php --}}
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
                        <label class="form-label d-block">Loại Khách Hàng:</label>
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

                    {{-- Phần cho khách hàng có sẵn --}}
                    <div id="existingCustomerFields">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Chọn Khách Hàng <span class="text-danger">*</span></label>
                            <select class="form-select selectpicker" data-live-search="true" id="customer_id" name="customer_id" title="Chọn khách hàng...">
                                {{-- Options sẽ được điền bằng JS từ window.pageData.customers --}}
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-tokens="{{ $customer->name }} {{ $customer->email }} {{ $customer->phone }}">
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-danger mt-1" data-field="customer_id"></div>
                        </div>
                        <div class="mb-3" id="shippingAddressSelectContainer">
                            <label for="shipping_address_id" class="form-label">Chọn Địa Chỉ Giao Hàng <span class="text-danger">*</span></label>
                            <select class="form-select selectpicker" id="shipping_address_id" name="shipping_address_id" title="Chọn địa chỉ...">
                                {{-- Options sẽ được điền bằng JS khi chọn khách hàng --}}
                            </select>
                            <div class="text-danger mt-1" data-field="shipping_address_id"></div>
                        </div>
                    </div>

                    {{-- Phần cho khách vãng lai --}}
                    <div id="guestCustomerFields" class="d-none">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_name" class="form-label">Tên Khách Hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name">
                                <div class="text-danger mt-1" data-field="guest_name"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guest_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="guest_email" name="guest_email">
                                <div class="text-danger mt-1" data-field="guest_email"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_phone" class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_phone" name="guest_phone">
                                <div class="text-danger mt-1" data-field="guest_phone"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="province_id_create" class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                <select class="form-select selectpicker" data-live-search="true" id="province_id_create" name="province_id" title="Chọn tỉnh/thành...">
                                    <option value="">Chọn tỉnh/thành...</option>
                                    {{-- Options sẽ được điền bằng JS từ window.pageData.provinces --}}
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-danger mt-1" data-field="province_id"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="district_id_create" class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <select class="form-select selectpicker" data-live-search="true" id="district_id_create" name="district_id" title="Chọn quận/huyện...">
                                    <option value="">Chọn quận/huyện...</option>
                                </select>
                                <div class="text-danger mt-1" data-field="district_id"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ward_id_create" class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                <select class="form-select selectpicker" data-live-search="true" id="ward_id_create" name="ward_id" title="Chọn phường/xã...">
                                    <option value="">Chọn phường/xã...</option>
                                </select>
                                <div class="text-danger mt-1" data-field="ward_id"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address_line" class="form-label">Địa chỉ chi tiết (số nhà, đường) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shipping_address_line" name="shipping_address_line">
                            <div class="text-danger mt-1" data-field="shipping_address_line"></div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Sản phẩm đơn hàng</h5>
                    <div id="productItemsContainer">
                        {{-- Product item rows will be added here by JavaScript --}}
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addProductItemBtn">
                        <i class="bi bi-plus-lg me-2"></i>Thêm sản phẩm
                    </button>
                    <div class="text-danger mt-1" data-field="items"></div>
                    <div class="text-danger mt-1" data-field="items.*.product_id"></div>
                    <div class="text-danger mt-1" data-field="items.*.quantity"></div>
                    <div class="text-danger" id="product_stock_error_create"></div>


                    <hr>

                    <h5 class="mb-3">Thông tin thanh toán & vận chuyển</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method_create" class="form-label">Phương Thức Thanh Toán <span class="text-danger">*</span></label>
                            <select class="form-select selectpicker" id="payment_method_create" name="payment_method" title="Chọn phương thức...">
                                <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                            </select>
                            <div class="text-danger mt-1" data-field="payment_method"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="delivery_service_id_create" class="form-label">Dịch Vụ Vận Chuyển <span class="text-danger">*</span></label>
                            <select class="form-select selectpicker" id="delivery_service_id_create" name="delivery_service_id" title="Chọn dịch vụ...">
                                {{-- Options sẽ được điền bằng JS từ window.pageData.deliveryServices --}}
                                @foreach($deliveryServices as $service)
                                    <option value="{{ $service->id }}" data-shipping-fee="{{ $service->shipping_fee }}">
                                        {{ $service->name }} ({{ number_format($service->shipping_fee) }} ₫)
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-danger mt-1" data-field="delivery_service_id"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="promotion_id_create" class="form-label">Mã Khuyến Mãi</label>
                        <select class="form-select selectpicker" data-live-search="true" id="promotion_id_create" name="promotion_id" title="Chọn mã khuyến mãi...">
                            <option value="">Không áp dụng</option>
                            {{-- Options sẽ được điền bằng JS từ window.pageData.promotions --}}
                            @foreach($promotions as $promo)
                                <option value="{{ $promo->id }}"
                                    data-discount-percentage="{{ $promo->discount_percentage }}">
                                    {{ $promo->code }} ({{ $promo->formatted_discount }})
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="promotion_id"></div>
                    </div>

                    <div class="mb-3">
                        <label for="status_create" class="form-label">Trạng Thái Đơn Hàng <span class="text-danger">*</span></label>
                        <select class="form-select selectpicker" id="status_create" name="status" title="Chọn trạng thái...">
                            {{-- Options sẽ được điền bằng JS từ window.pageData.initialOrderStatuses --}}
                            @foreach($initialOrderStatuses as $key => $value)
                                <option value="{{ $key }}" {{ $key === \App\Models\Order::STATUS_PENDING ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="status"></div>
                    </div>

                    <div class="mb-3">
                        <label for="notes_create" class="form-label">Ghi Chú</label>
                        <textarea class="form-control" id="notes_create" name="notes" rows="3"></textarea>
                        <div class="text-danger mt-1" data-field="notes"></div>
                    </div>

                    <hr>

                    <div class="order-summary-details text-end">
                        <p class="mb-1"><span class="strong-label">Tổng phụ:</span> <span id="create-order-subtotal">0 ₫</span></p>
                        <p class="mb-1"><span class="strong-label">Phí vận chuyển:</span> <span id="create-order-shipping-fee">0 ₫</span></p>
                        <p class="mb-1 text-success"><span class="strong-label">Giảm giá:</span> <span id="create-order-discount">-0 ₫</span></p>
                        <h4 class="mt-2"><span class="strong-label">Tổng cộng:</span> <span id="create-order-grand-total" class="text-danger">0 ₫</span></h4>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Tạo Đơn Hàng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>