{{-- resources/views/admin/sales/order/modals/update_order_modal.blade.php --}}
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
                            <hr>

                            {{-- Chọn khách hàng --}}
                            <div class="mb-3">
                                <label for="customer_id_update" class="form-label">Chọn Khách hàng</label>
                                <select
                                    class="form-control selectpicker @error('customer_id', 'update_order_form') is-invalid @enderror"
                                    data-live-search="true" id="customer_id_update" name="customer_id">
                                    <option value="">-- Khách hàng vãng lai --</option>
                                    {{-- Options will be populated by JS --}}
                                </select>
                                @error('customer_id', 'update_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Thông tin khách vãng lai (ẩn/hiện bởi JS) --}}
                            <div id="guest_info_fields_update">
                                <div class="mb-3">
                                    <label for="guest_name_update" class="form-label">Tên người nhận <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('guest_name', 'update_order_form') is-invalid @enderror"
                                        id="guest_name_update" name="guest_name">
                                    @error('guest_name', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="guest_email_update" class="form-label">Email</label>
                                    <input type="email"
                                        class="form-control @error('guest_email', 'update_order_form') is-invalid @enderror"
                                        id="guest_email_update" name="guest_email">
                                    @error('guest_email', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="guest_phone_update" class="form-label">Số điện thoại <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('guest_phone', 'update_order_form') is-invalid @enderror"
                                        id="guest_phone_update" name="guest_phone">
                                    @error('guest_phone', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="province_id_update" class="form-label">Tỉnh/Thành phố <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control selectpicker @error('province_id', 'update_order_form') is-invalid @enderror"
                                        data-live-search="true" id="province_id_update" name="province_id">
                                        <option value="">-- Chọn Tỉnh/Thành --</option>
                                        {{-- Populated by JS --}}
                                    </select>
                                    @error('province_id', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="district_id_update" class="form-label">Quận/Huyện <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control selectpicker @error('district_id', 'update_order_form') is-invalid @enderror"
                                        data-live-search="true" id="district_id_update" name="district_id">
                                        <option value="">-- Chọn Quận/Huyện --</option>
                                        {{-- Populated by JS --}}
                                    </select>
                                    @error('district_id', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="ward_id_update" class="form-label">Phường/Xã <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control selectpicker @error('ward_id', 'update_order_form') is-invalid @enderror"
                                        data-live-search="true" id="ward_id_update" name="ward_id">
                                        <option value="">-- Chọn Phường/Xã --</option>
                                        {{-- Populated by JS --}}
                                    </select>
                                    @error('ward_id', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="shipping_address_line_update" class="form-label">Địa chỉ chi tiết (Số
                                        nhà, đường...) <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('shipping_address_line', 'update_order_form') is-invalid @enderror"
                                        id="shipping_address_line_update" name="shipping_address_line">
                                    @error('shipping_address_line', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Địa chỉ của khách hàng đăng ký (chỉ hiện khi chọn customer_id) --}}
                            <div id="customer_address_fields_update" class="d-none">
                                <div class="mb-3">
                                    <label for="customer_shipping_address_id_update" class="form-label">Chọn địa chỉ đã
                                        lưu</label>
                                    <select
                                        class="form-control selectpicker @error('shipping_address_id', 'update_order_form') is-invalid @enderror"
                                        data-live-search="true" id="customer_shipping_address_id_update"
                                        name="shipping_address_id">
                                        <option value="">-- Chọn địa chỉ --</option>
                                        {{-- Populated by JS when customer is selected --}}
                                    </select>
                                    @error('shipping_address_id', 'update_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Trạng thái & Thanh toán</h5>
                            <hr>
                            <div class="mb-3">
                                <label for="status_update" class="form-label">Trạng thái đơn hàng <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-control selectpicker @error('status', 'update_order_form') is-invalid @enderror"
                                    id="status_update" name="status">
                                    @foreach (\App\Models\Order::STATUSES as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('status', 'update_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="payment_method_update" class="form-label">Phương thức thanh toán <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-control selectpicker @error('payment_method', 'update_order_form') is-invalid @enderror"
                                    id="payment_method_update" name="payment_method">
                                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                                    <option value="online_payment">Thanh toán online (VNPay/Momo)</option>
                                </select>
                                @error('payment_method', 'update_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="delivery_service_id_update" class="form-label">Dịch vụ vận chuyển <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-control selectpicker @error('delivery_service_id', 'update_order_form') is-invalid @enderror"
                                    data-live-search="true" id="delivery_service_id_update" name="delivery_service_id">
                                    <option value="">-- Chọn dịch vụ vận chuyển --</option>
                                    @if(isset($deliveryServices))
                                        @foreach($deliveryServices as $service)
                                            <option value="{{ $service->id }}" data-shipping-fee="{{ $service->shipping_fee }}">
                                                {{ $service->name }} ({{ number_format($service->shipping_fee, 0, ',', '.') }}₫)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('delivery_service_id', 'update_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="promotion_id_update" class="form-label">Mã khuyến mãi</label>
                                <select
                                    class="form-control selectpicker @error('promotion_id', 'update_order_form') is-invalid @enderror"
                                    data-live-search="true" id="promotion_id_update" name="promotion_id">
                                    <option value="">Không áp dụng</option>
                                    @if(isset($promotions))
                                        @foreach($promotions as $promotion)
                                            <option value="{{ $promotion->id }}"
                                                data-discount-percent="{{ $promotion->discount_percentage }}">
                                                {{ $promotion->code }} - {{ $promotion->description }}
                                                ({{ $promotion->discount_percentage }}%)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('promotion_id', 'update_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="notes_update" class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('notes', 'update_order_form') is-invalid @enderror"
                                    id="notes_update" name="notes" rows="4"></textarea>
                                @error('notes', 'update_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Sản phẩm trong đơn hàng</h5>
                    <div id="product_items_container_update">
                        {{-- Các dòng sản phẩm sẽ được JS chèn vào đây --}}
                    </div>
                    <div class="text-start mt-2">
                        <button type="button" id="add_product_item_update_btn" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                        </button>
                    </div>

                    <hr>
                    <h5>Tóm tắt Đơn hàng</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="text-end">Tổng phụ:</td>
                                        <td class="text-end fw-bold" id="update-order-subtotal">0 ₫</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end">Phí vận chuyển:</td>
                                        <td class="text-end fw-bold" id="update-order-shipping-fee">0 ₫</td>
                                    </tr>
                                    <tr id="update-order-discount-row" class="d-none">
                                        <td colspan="2" class="text-end">Giảm giá:</td>
                                        <td class="text-end fw-bold text-danger" id="update-order-discount-amount">0 ₫
                                        </td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="2" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td class="text-end"><strong><span id="update-order-grand-total">0
                                                    ₫</span></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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