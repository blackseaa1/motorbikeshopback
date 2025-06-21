<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOrderModalLabel">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tạo Đơn Hàng Mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm" action="{{ route('admin.sales.orders.store') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="_form_identifier" value="create_order_form"> {{-- THÊM DÒNG NÀY --}}

                    {{-- THÔNG TIN KHÁCH HÀNG --}}
                    <h5 class="mb-3">1. Thông tin khách hàng</h5>
                    <div class="mb-3">
                        <label class="form-label">Loại khách hàng</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="customer_type_existing_modal" value="existing" {{ old('customer_type', 'existing') == 'existing' ? 'checked' : '' }}>
                                <label class="form-check-label" for="customer_type_existing_modal">Khách hàng hiện có</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="customer_type_guest_modal" value="guest" {{ old('customer_type') == 'guest' ? 'checked' : '' }}>
                                <label class="form-check-label" for="customer_type_guest_modal">Khách vãng lai</label>
                            </div>
                        </div>
                        @error('customer_type')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    {{-- Khối cho khách hàng hiện có --}}
                    <div id="existing_customer_fields_modal" class="mb-3" style="{{ old('customer_type', 'existing') == 'existing' ? '' : 'display:none;' }}">
                        <label for="customer_id_modal" class="form-label">Chọn khách hàng</label>
                        <select class="form-select selectpicker @error('customer_id') is-invalid @enderror" data-live-search="true" id="customer_id_modal" name="customer_id">
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->full_name }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    {{-- Khối cho khách vãng lai --}}
                    <div id="guest_customer_fields_modal" class="row" style="{{ old('customer_type') == 'guest' ? '' : 'display:none;' }}">
                        <div class="col-md-4 mb-3">
                            <label for="guest_name_modal" class="form-label">Tên khách hàng</label>
                            <input type="text" class="form-control @error('guest_name') is-invalid @enderror" id="guest_name_modal" name="guest_name" value="{{ old('guest_name') }}">
                            @error('guest_name')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guest_email_modal" class="form-label">Email</label>
                            <input type="email" class="form-control @error('guest_email') is-invalid @enderror" id="guest_email_modal" name="guest_email" value="{{ old('guest_email') }}">
                            @error('guest_email')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guest_phone_modal" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control @error('guest_phone') is-invalid @enderror" id="guest_phone_modal" name="guest_phone" value="{{ old('guest_phone') }}">
                            @error('guest_phone')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="guest_address_line_modal" class="form-label">Dòng địa chỉ cụ thể (ví dụ: Số nhà, tên đường)</label>
                            <input type="text" class="form-control @error('guest_address_line') is-invalid @enderror" id="guest_address_line_modal" name="guest_address_line" value="{{ old('guest_address_line') }}">
                            @error('guest_address_line')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guest_province_id_modal" class="form-label">Tỉnh/Thành phố</label>
                            <select class="form-select selectpicker @error('guest_province_id') is-invalid @enderror" data-live-search="true" id="guest_province_id_modal" name="guest_province_id">
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ old('guest_province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                @endforeach
                            </select>
                            @error('guest_province_id')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guest_district_id_modal" class="form-label">Quận/Huyện</label>
                            <select class="form-select selectpicker @error('guest_district_id') is-invalid @enderror" data-live-search="true" id="guest_district_id_modal" name="guest_district_id">
                                <option value="">-- Chọn Quận/Huyện --</option>
                                {{-- Districts sẽ được load động bằng JS --}}
                            </select>
                            @error('guest_district_id')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guest_ward_id_modal" class="form-label">Phường/Xã</label>
                            <select class="form-select selectpicker @error('guest_ward_id') is-invalid @enderror" data-live-search="true" id="guest_ward_id_modal" name="guest_ward_id">
                                <option value="">-- Chọn Phường/Xã --</option>
                                {{-- Wards sẽ được load động bằng JS --}}
                            </select>
                            @error('guest_ward_id')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- SẢN PHẨM TRONG ĐƠN HÀNG --}}
                    <h5 class="mb-3">2. Sản phẩm trong đơn hàng</h5>
                    <div id="product_items_container_modal">
                        @if(old('product_ids'))
                            @foreach(old('product_ids') as $index => $oldProductId)
                                <div class="product-item-row-modal" data-index="{{ $index }}">
                                    <select name="product_ids[]" class="form-select selectpicker @error('product_ids.'.$index) is-invalid @enderror" data-live-search="true">
                                        <option value="">-- Chọn sản phẩm --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ $oldProductId == $product->id ? 'selected' : '' }} data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}">
                                                {{ $product->name }} (Kho: {{ $product->stock_quantity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="quantities[]" class="form-control @error('quantities.'.$index) is-invalid @enderror" placeholder="Số lượng" min="1" value="{{ old('quantities.'.$index) }}">
                                    <button type="button" class="btn btn-danger remove-product-item-modal"><i class="bi bi-trash"></i></button>
                                </div>
                                @error('product_ids.'.$index)<div class="text-danger">{{ $message }}</div>@enderror
                                @error('quantities.'.$index)<div class="text-danger">{{ $message }}</div>@enderror
                            @endforeach
                        @else
                            <div class="product-item-row-modal" data-index="0">
                                <select name="product_ids[]" class="form-select selectpicker" data-live-search="true">
                                    <option value="">-- Chọn sản phẩm --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}">
                                            {{ $product->name }} (Kho: {{ $product->stock_quantity }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantities[]" class="form-control" placeholder="Số lượng" min="1" value="1">
                                <button type="button" class="btn btn-danger remove-product-item-modal"><i class="bi bi-trash"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-success mt-2" id="add_product_item_modal"><i class="bi bi-plus"></i> Thêm sản phẩm</button>
                    @error('product_ids')<div class="text-danger mt-2">{{ $message }}</div>@enderror
                    @error('quantities')<div class="text-danger mt-2">{{ $message }}</div>@enderror

                    <hr class="my-4">

                    {{-- THÔNG TIN VẬN CHUYỂN, THANH TOÁN, KHUYẾN MÃI, GHI CHÚ --}}
                    <h5 class="mb-3">3. Thông tin khác</h5>
                    <div class="mb-3">
                        <label for="delivery_service_id_modal" class="form-label">Dịch vụ vận chuyển</label>
                        <select class="form-select selectpicker @error('delivery_service_id') is-invalid @enderror" id="delivery_service_id_modal" name="delivery_service_id">
                            <option value="">-- Chọn dịch vụ vận chuyển --</option>
                            @foreach($deliveryServices as $service)
                                <option value="{{ $service->id }}" {{ old('delivery_service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} (Phí: {{ number_format($service->shipping_fee) }} ₫)
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_service_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="payment_method_modal" class="form-label">Phương thức thanh toán</label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method_modal" name="payment_method">
                            <option value="">-- Chọn phương thức thanh toán --</option>
                            <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>Thanh toán khi nhận hàng (COD)</option>
                            <option value="vnpay" {{ old('payment_method') == 'vnpay' ? 'selected' : '' }}>VNPAY</option>
                        </select>
                        @error('payment_method')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="promotion_id_modal" class="form-label">Mã khuyến mãi (tùy chọn)</label>
                        <select class="form-select selectpicker @error('promotion_id') is-invalid @enderror" data-live-search="true" id="promotion_id_modal" name="promotion_id">
                            <option value="">-- Không áp dụng --</option>
                            @foreach($promotions as $promotion)
                                <option value="{{ $promotion->id }}" {{ old('promotion_id') == $promotion->id ? 'selected' : '' }}>
                                    {{ $promotion->code }} (Giảm: {{ $promotion->discount_percentage }}%)
                                </option>
                            @endforeach
                        </select>
                        @error('promotion_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes_modal" class="form-label">Ghi chú cho đơn hàng (tùy chọn)</label>
                        <textarea class="form-control" id="notes_modal" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="status_modal" class="form-label">Trạng thái ban đầu của đơn hàng</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status_modal" name="status">
                            @foreach($initialOrderStatuses as $key => $value)
                                <option value="{{ $key }}" {{ old('status', \App\Models\Order::STATUS_PENDING) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="createOrderForm">
                    <i class="bi bi-floppy-fill me-1"></i> Tạo Đơn Hàng
                </button>
            </div>
        </div>
    </div>
</div>