{{-- resources/views/admin/sales/order/modals/create_order_modal.blade.php --}}
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
                    <div id="removed_items_container"></div> {{-- Container for tracking removed items --}}

                    <div class="row">
                        <div class="col-md-6">
                            <h5>1. Thông tin Khách hàng & Giao hàng</h5>
                            <hr>

                            {{-- Chọn khách hàng --}}
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Chọn Khách hàng</label>
                                <select
                                    class="form-control selectpicker @error('customer_id', 'create_order_form') is-invalid @enderror"
                                    data-live-search="true" id="customer_id" name="customer_id"
                                    data-selected-id="{{ old('customer_id') }}">
                                    <option value="" selected>-- Khách hàng vãng lai --</option>
                                    {{-- Options will be populated by JS --}}
                                </select>
                                @error('customer_id', 'create_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Thông tin khách vãng lai (ẩn/hiện bởi JS) --}}
                            <div id="guest_info_fields">
                                <div class="mb-3">
                                    <label for="guest_name" class="form-label">Tên người nhận <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('guest_name', 'create_order_form') is-invalid @enderror"
                                        id="guest_name" name="guest_name" value="{{ old('guest_name', '') }}">
                                    @error('guest_name', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="guest_email" class="form-label">Email</label>
                                    <input type="email"
                                        class="form-control @error('guest_email', 'create_order_form') is-invalid @enderror"
                                        id="guest_email" name="guest_email" value="{{ old('guest_email', '') }}">
                                    @error('guest_email', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="guest_phone" class="form-label">Số điện thoại <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('guest_phone', 'create_order_form') is-invalid @enderror"
                                        id="guest_phone" name="guest_phone" value="{{ old('guest_phone', '') }}">
                                    @error('guest_phone', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="province_id" class="form-label">Tỉnh/Thành phố <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control selectpicker @error('province_id', 'create_order_form') is-invalid @enderror"
                                        data-live-search="true" id="province_id" name="province_id"
                                        data-selected-id="{{ old('province_id') }}">
                                        <option value="">-- Chọn Tỉnh/Thành --</option>
                                        {{-- Populated by JS --}}
                                    </select>
                                    @error('province_id', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="district_id" class="form-label">Quận/Huyện <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control selectpicker @error('district_id', 'create_order_form') is-invalid @enderror"
                                        data-live-search="true" id="district_id" name="district_id"
                                        data-selected-id="{{ old('district_id') }}">
                                        <option value="">-- Chọn Quận/Huyện --</option>
                                        {{-- Populated by JS --}}
                                    </select>
                                    @error('district_id', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="ward_id" class="form-label">Phường/Xã <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control selectpicker @error('ward_id', 'create_order_form') is-invalid @enderror"
                                        data-live-search="true" id="ward_id" name="ward_id"
                                        data-selected-id="{{ old('ward_id') }}">
                                        <option value="">-- Chọn Phường/Xã --</option>
                                        {{-- Populated by JS --}}
                                    </select>
                                    @error('ward_id', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="shipping_address_line" class="form-label">Địa chỉ chi tiết (Số nhà,
                                        đường...) <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('shipping_address_line', 'create_order_form') is-invalid @enderror"
                                        id="shipping_address_line" name="shipping_address_line"
                                        value="{{ old('shipping_address_line', '') }}">
                                    @error('shipping_address_line', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Địa chỉ của khách hàng đăng ký (chỉ hiện khi chọn customer_id) --}}
                            <div id="customer_address_fields" class="d-none">
                                <div class="mb-3">
                                    <label for="customer_shipping_address_id" class="form-label">Chọn địa chỉ đã
                                        lưu</label>
                                    <select
                                        class="form-control selectpicker @error('shipping_address_id', 'create_order_form') is-invalid @enderror"
                                        data-live-search="true" id="customer_shipping_address_id"
                                        name="shipping_address_id" data-selected-id="{{ old('shipping_address_id') }}">
                                        <option value="">-- Chọn địa chỉ --</option>
                                        {{-- Populated by JS when customer is selected --}}
                                    </select>
                                    @error('shipping_address_id', 'create_order_form')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>2. Trạng thái & Thanh toán</h5>
                            <hr>
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái đơn hàng <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-control selectpicker @error('status', 'create_order_form') is-invalid @enderror"
                                    id="status" name="status">
                                    @foreach (\App\Models\Order::STATUSES as $key => $value)
                                        <option value="{{ $key }}" @if(old('status') == $key) selected @endif>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status', 'create_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Phương thức thanh toán <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-control selectpicker @error('payment_method', 'create_order_form') is-invalid @enderror"
                                    id="payment_method" name="payment_method">
                                    <option value="cod" @if(old('payment_method') == 'cod') selected @endif>Thanh toán khi
                                        nhận hàng (COD)</option>
                                    <option value="bank_transfer" @if(old('payment_method') == 'bank_transfer') selected
                                    @endif>Chuyển khoản ngân hàng</option>
                                    <option value="online_payment" @if(old('payment_method') == 'online_payment') selected
                                    @endif>Thanh toán online (VNPay/Momo)</option>
                                </select>
                                @error('payment_method', 'create_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="delivery_service_id" class="form-label">Dịch vụ vận chuyển <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-control selectpicker @error('delivery_service_id', 'create_order_form') is-invalid @enderror"
                                    data-live-search="true" id="delivery_service_id" name="delivery_service_id"
                                    data-selected-id="{{ old('delivery_service_id') }}">
                                    <option value="">-- Chọn dịch vụ vận chuyển --</option>
                                    @if(isset($deliveryServices))
                                        @foreach($deliveryServices as $service)
                                            <option value="{{ $service->id }}" data-shipping-fee="{{ $service->shipping_fee }}">
                                                {{ $service->name }} ({{ number_format($service->shipping_fee, 0, ',', '.') }}₫)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('delivery_service_id', 'create_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="promotion_id" class="form-label">Mã khuyến mãi</label>
                                <select
                                    class="form-control selectpicker @error('promotion_id', 'create_order_form') is-invalid @enderror"
                                    data-live-search="true" id="promotion_id" name="promotion_id"
                                    data-selected-id="{{ old('promotion_id') }}">
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
                                @error('promotion_id', 'create_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Ghi chú của Admin</label>
                                <textarea class="form-control @error('notes', 'create_order_form') is-invalid @enderror"
                                    id="notes" name="notes" rows="4">{{ old('notes', '') }}</textarea>
                                @error('notes', 'create_order_form')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>3. Sản phẩm trong đơn hàng</h5>
                    <div id="product_items_container_modal">
                        {{-- Các dòng sản phẩm sẽ được JS chèn động vào đây --}}
                        {{-- Initial row if validation errors exist --}}
                        @if ($errors->has('items.*') && old('items'))
                            @foreach(old('items') as $index => $item)
                                @include('admin.sales.order.partials.product_item_row', [
                                    'index' => $index,
                                    'allProductsForJs' => $allProductsForJs,
                                    'product_id' => $item['product_id'] ?? null,
                                    'quantity' => $item['quantity'] ?? null,
                                    'errors' => $errors,
                                    'formIdentifier' => 'create_order_form'
                                ])
                            @endforeach
                        @endif
                    </div>

                    <div class="text-start mt-2">
                        <button type="button" id="add_product_item_modal" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                        </button>
                    </div>

                    <hr>
                    <h5>4. Tóm tắt Đơn hàng</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="text-end">Tổng phụ:</td>
                                        <td class="text-end fw-bold" id="create-order-subtotal">0 ₫</td>
                                    </tr>

                                                                            <tr>
                                        <td colspan="2" class="text-end">Phí vận chuyển:</td>
                                        <td class="text-end fw-bold" id="create-order-shipping-fee">0 ₫</td>
                                    </tr>

                                                                                       <tr id="create-order-discount-row" class="d-none">
                                        <td colspan="2" class="text-end">Giảm giá:</td>
                                        <td class="text-end fw-bold text-danger" id="create-order-discount-amount">0 ₫</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="2" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td class="text-end"><strong><span id="create-order-grand-total">0 ₫</span></strong></td>
                                    </tr>
                                </tbody>
                            </table>

                                           </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="createOrderForm" id="saveNewOrderBtn">Tạo Đơn Hàng</button>
            </div>
        </div>
    </div>
</div>
{{-- Partial for product item row, usually in a separate file like _product_item_row.blade.php --}}