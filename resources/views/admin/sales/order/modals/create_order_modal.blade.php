<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOrderModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tạo Đơn Hàng Mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm" action="{{ route('admin.sales.orders.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_form_identifier" value="create_order_form">
                    <div id="removed_items_container"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>1. Thông tin Khách hàng & Giao hàng</h5>
                            <hr>

                            {{-- Chọn khách hàng --}}
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Chọn Khách hàng</label>
                                <select class="form-control selectpicker" data-live-search="true" id="customer_id"
                                    name="customer_id">
                                    <option value="" selected>-- Khách vãng lai --</option>
                                    @if(isset($customers))
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" data-customer-name="{{ $customer->name }}"
                                                data-customer-phone="{{ $customer->phone }}">{{ $customer->name }} -
                                                {{ $customer->email }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            {{-- Khối 1: Dropdown địa chỉ cho khách đã đăng ký --}}
                            <div id="customer_address_id_group" style="display: none;">
                                <div class="mb-3">
                                    <label for="customer_address_id" class="form-label">Chọn địa chỉ đã lưu</label>
                                    <select class="form-control selectpicker" data-live-search="true"
                                        id="customer_address_id" name="customer_address_id">
                                        {{-- JS sẽ điền vào đây --}}
                                    </select>
                                </div>
                            </div>

                            {{-- Khối 2: Các trường nhập địa chỉ thủ công cho khách vãng lai --}}
                            <div id="manual_address_fields">
                                <div class="mb-3">
                                    <label for="guest_name" class="form-label">Tên người nhận <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_phone" class="form-label">Số điện thoại người nhận <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_phone" name="guest_phone"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="shipping_address_line" class="form-label">Địa chỉ chi tiết (Số nhà,
                                        đường...)</label>
                                    <input type="text" class="form-control" id="shipping_address_line"
                                        name="shipping_address_line">
                                </div>
                                <div class="mb-3">
                                    <label for="guest_province_id_modal" class="form-label">Tỉnh/Thành phố</label>
                                    <select class="form-select selectpicker" data-live-search="true"
                                        id="guest_province_id_modal" name="province_id" data-width="100%">
                                        <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                        @if(isset($provinces))
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_district_id_modal" class="form-label">Quận/Huyện</label>
                                    <select class="form-select selectpicker" data-live-search="true"
                                        id="guest_district_id_modal" name="district_id" data-width="100%">
                                        <option value="">-- Chọn Quận/Huyện --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="guest_ward_id_modal" class="form-label">Phường/Xã</label>
                                    <select class="form-select selectpicker" data-live-search="true"
                                        id="guest_ward_id_modal" name="ward_id" data-width="100%">
                                        <option value="">-- Chọn Phường/Xã --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>2. Thông tin Đơn hàng</h5>
                            <hr>
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-control selectpicker" id="status" name="status">
                                    @if(isset($initialStatuses))
                                        @foreach($initialStatuses as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="promotion_id" class="form-label">Mã giảm giá</label>
                                <select class="form-control selectpicker" data-live-search="true" id="promotion_id"
                                    name="promotion_id">
                                    {{-- JS sẽ điền vào đây từ biến allPromotions --}}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_service_id" class="form-label">Dịch vụ vận chuyển</label>
                                <select class="form-control selectpicker" id="delivery_service_id"
                                    name="delivery_service_id">
                                    @if(isset($deliveryServices))
                                        @foreach($deliveryServices as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }} -
                                                {{ number_format($service->shipping_fee, 0, ',', '.') }}đ
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Ghi chú của Admin</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>3. Sản phẩm trong đơn hàng</h5>

                    <div id="product_items_container_modal">
                        {{-- Các dòng sản phẩm sẽ được JS chèn động vào đây --}}
                    </div>

                    <div class="text-start mt-2">
                        <button type="button" id="add_product_item_modal" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="createOrderForm" id="saveNewOrderBtn">Tạo Đơn
                    Hàng</button>
            </div>
        </div>
    </div>
</div>