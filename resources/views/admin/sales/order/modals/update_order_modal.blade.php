<div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateOrderModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Đơn Hàng: <strong
                        id="updateModalOrderIdStrong">N/A</strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateOrderForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form_identifier" value="update_order_form">
                    <div id="removed_items_container_update"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin người nhận</h5>
                            <div class="mb-3">
                                <label for="guest_name_update" class="form-label">Tên khách hàng <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_name_update" name="guest_name">
                            </div>
                            <div class="mb-3">
                                <label for="guest_phone_update" class="form-label">Điện thoại <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guest_phone_update" name="guest_phone">
                            </div>
                            <div class="mb-3">
                                <label for="guest_email_update" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="guest_email_update" name="guest_email">
                            </div>
                            <hr>
                            <h5>Địa chỉ giao hàng</h5>
                            <div class="mb-3">
                                <label for="shipping_address_line_update" class="form-label">Địa chỉ chi tiết <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping_address_line_update"
                                    name="shipping_address_line">
                            </div>
                            <div class="mb-3">
                                <label for="province_id_update" class="form-label">Tỉnh/Thành phố <span
                                        class="text-danger">*</span></label>
                                {{-- SỬA LỖI: Thêm data-width="100%" --}}
                                <select class="form-select selectpicker" data-live-search="true" id="province_id_update"
                                    name="province_id" data-width="100%">
                                    <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="district_id_update" class="form-label">Quận/Huyện <span
                                        class="text-danger">*</span></label>
                                <select class="form-select selectpicker" data-live-search="true" id="district_id_update"
                                    name="district_id" data-width="100%">
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ward_id_update" class="form-label">Phường/Xã <span
                                        class="text-danger">*</span></label>
                                <select class="form-select selectpicker" data-live-search="true" id="ward_id_update"
                                    name="ward_id" data-width="100%">
                                    <option value="">-- Chọn Phường/Xã --</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Thông tin đơn hàng</h5>
                            <div class="mb-3">
                                <label for="payment_method_update" class="form-label">Phương thức thanh toán <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method_update" name="payment_method">
                                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                    <option value="vnpay">VNPAY</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_service_id_update" class="form-label">Dịch vụ vận chuyển <span
                                        class="text-danger">*</span></label>
                                <select class="form-select selectpicker" id="delivery_service_id_update"
                                    name="delivery_service_id" data-width="100%">
                                    @foreach($deliveryServices as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} (Phí:
                                            {{ number_format($service->shipping_fee) }} ₫)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="promotion_id_update" class="form-label">Mã khuyến mãi</label>
                                <select class="form-select selectpicker" id="promotion_id_update" name="promotion_id"
                                    data-width="100%">
                                    <option value="">Không áp dụng</option>
                                    @foreach($promotions as $promotion)
                                        <option value="{{ $promotion->id }}">{{ $promotion->code }}
                                            (-{{$promotion->discount_percentage}}%)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status_update" class="form-label">Trạng thái đơn hàng <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="status_update" name="status">
                                    @foreach(\App\Models\Order::STATUSES as $statusValue => $statusText)
                                        <option value="{{ $statusValue }}">{{ $statusText }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="notes_update" class="form-label">Ghi chú</label>
                                <textarea class="form-control" id="notes_update" name="notes" rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Sản phẩm trong đơn hàng</h5>
                    {{-- NÂNG CẤP: Thay table bằng div container để thêm/xóa động --}}
                    <div id="product_items_container_update">
                        {{-- Các dòng sản phẩm sẽ được JS chèn vào đây --}}
                    </div>
                    <div class="text-start mt-2">
                        <button type="button" id="add_product_item_update_btn" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="updateOrderForm" id="saveUpdateOrderBtn">Lưu thay
                    đổi</button>
            </div>
        </div>
    </div>
</div>