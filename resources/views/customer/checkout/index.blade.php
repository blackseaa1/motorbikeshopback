@extends('customer.layouts.app')

@section('title', 'Thanh toán')
@section('content')
    <div class="container py-5">
        <h2 class="text-center mb-5">Hoàn tất đơn hàng</h2>
        <form id="checkout-form" action="{{ route('checkout.placeOrder') }}" method="POST">
            @csrf
            <div class="row g-5">
                {{-- Cột bên trái: Thông tin giao hàng và thanh toán --}}
                <div class="col-lg-7">
                    <h4 class="mb-3">Thông tin giao hàng</h4>
                    @auth('customer')
                        {{-- Dành cho người dùng đã đăng nhập --}}
                        <div class="mb-3">
                            <label for="shipping_address_id" class="form-label">Chọn địa chỉ đã lưu</label>
                            <select class="form-select" id="shipping_address_id" name="shipping_address_id" required>
                                @forelse($customerAddresses as $address)
                                    <option value="{{ $address->id }}" {{ $address->is_default ? 'selected' : '' }}>
                                        {{ $address->full_name }} - {{ Str::limit($address->address_line, 40) }}
                                    </option>
                                @empty
                                    <option value="">Vui lòng thêm địa chỉ trong sổ địa chỉ</option>
                                @endforelse
                            </select>
                            <a href="{{ route('account.addresses.create', ['redirect' => 'checkout']) }}" class="form-text">Thêm
                                địa chỉ mới</a>
                        </div>
                    @else
                        {{-- Dành cho khách vãng lai --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_name" class="form-label">Họ và tên</label>
                                <input type="text" class="form-control @error('guest_name') is-invalid @enderror"
                                    name="guest_name" id="guest_name" value="{{ old('guest_name') }}" required>
                                @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guest_phone" class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control @error('guest_phone') is-invalid @enderror"
                                    name="guest_phone" id="guest_phone" value="{{ old('guest_phone') }}" required>
                                @error('guest_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label for="guest_email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('guest_email') is-invalid @enderror"
                                    name="guest_email" id="guest_email" value="{{ old('guest_email') }}" required>
                                @error('guest_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- === SỬA ĐỔI: THÊM CÁC DROPDOWN ĐỊA CHỈ === --}}
                            <div class="col-md-4 mb-3">
                                <label for="guest_province_id" class="form-label">Tỉnh/Thành phố</label>
                                <select class="form-select @error('guest_province_id') is-invalid @enderror"
                                    id="guest_province_id" name="guest_province_id" required>
                                    <option value="">Chọn Tỉnh/Thành</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('guest_province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                    @endforeach
                                </select>
                                @error('guest_province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="guest_district_id" class="form-label">Quận/Huyện</label>
                                <select class="form-select @error('guest_district_id') is-invalid @enderror"
                                    id="guest_district_id" name="guest_district_id" required disabled>
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                                @error('guest_district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="guest_ward_id" class="form-label">Phường/Xã</label>
                                <select class="form-select @error('guest_ward_id') is-invalid @enderror" id="guest_ward_id"
                                    name="guest_ward_id" required disabled>
                                    <option value="">Chọn Phường/Xã</option>
                                </select>
                                @error('guest_ward_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="guest_address_line" class="form-label">Địa chỉ chi tiết (Số nhà, tên
                                    đường...)</label>
                                <input type="text" class="form-control @error('guest_address_line') is-invalid @enderror"
                                    name="guest_address_line" id="guest_address_line" value="{{ old('guest_address_line') }}"
                                    required>
                                @error('guest_address_line')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            {{-- ============================================= --}}
                        </div>
                    @endauth

                    <hr class="my-4">
                    <h4 class="mb-3">Phương thức thanh toán</h4>
                    <div class="form-check">
                        <input id="cod" name="payment_method" type="radio" class="form-check-input" value="cod" checked
                            required>
                        <label class="form-check-label" for="cod">Thanh toán khi nhận hàng (COD)</label>
                    </div>
                    <div class="form-check">
                        <input id="bank_transfer" name="payment_method" type="radio" class="form-check-input"
                            value="bank_transfer" required>
                        <label class="form-check-label" for="bank_transfer">Chuyển khoản ngân hàng</label>
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Cột bên phải: Tóm tắt đơn hàng --}}
                <div class="col-lg-5">
                    <div class="card shadow-sm" style="top: 20px;">
                        <div class="card-body p-4">
                            {{-- Header có thể bấm để xổ ra --}}
                            <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#orderSummaryCollapse"
                                role="button" aria-expanded="true" aria-controls="orderSummaryCollapse">
                                <h4 class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-primary">Đơn hàng của bạn</span>
                                    <span class="d-flex align-items-center">
                                        <span class="badge bg-primary rounded-pill me-2">{{ $cartDetails['count'] }}</span>
                                        <i class="bi bi-chevron-down"></i>
                                    </span>
                                </h4>
                            </a>

                            <div class="collapse show" id="orderSummaryCollapse">
                                <ul class="list-group mb-3">
                                    @foreach($cartDetails['items'] as $item)
                                        <li class="list-group-item d-flex justify-content-between lh-sm align-items-center">
                                            <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}"
                                                class="me-3 rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="my-0">{{ $item->product->name }}</h6>
                                                <small class="text-muted">Số lượng: {{ $item->quantity }}</small>
                                            </div>
                                            <span class="text-muted">{{ number_format($item->subtotal) }} ₫</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <hr>
                            <div class="mb-3">
                                <label for="delivery_service_id" class="form-label fw-bold">Đơn vị vận chuyển</label>
                                <select class="selectpicker form-control" id="delivery_service_id"
                                    name="delivery_service_id" data-live-search="true" title="Chọn đơn vị vận chuyển..."
                                    required>
                                    @if($deliveryServices->isNotEmpty())
                                        @foreach($deliveryServices as $service)
                                            <option value="{{ $service->id }}"
                                                data-content="{{ $service->name }} <span class='text-muted float-end'>+{{ number_format($service->shipping_fee) }} ₫</span>"
                                                {{ ($cartDetails['shipping_info']['id'] ?? null) == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <hr>
                            {{-- Mã giảm giá --}}
                            <div class="mb-4">
                                <label for="promotion_code" class="form-label fw-bold">Mã giảm giá <span>"Vui lòng bấm áp
                                        dụng để được sử dụng"</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="promotion_code" placeholder="Nhập mã..."
                                        value="{{ $cartDetails['promotion_info']['code'] ?? '' }}">
                                    <button class="btn btn-outline-secondary" type="button" id="apply-promo-btn">Áp
                                        dụng</button>
                                    <button class="btn btn-outline-secondary d-none" type="button" id="clear-promo-btn"
                                        title="Xóa mã">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div id="promo-feedback" class="mt-2"></div>
                            </div>
                            <hr>

                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Tạm tính</span>
                                    <strong id="summary-subtotal">{{ number_format($cartDetails['subtotal'] ?? 0) }}
                                        ₫</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Phí vận chuyển</span>
                                    <strong id="summary-shipping-fee">{{ number_format($cartDetails['shipping_fee'] ?? 0) }}
                                        ₫</strong>
                                </li>
                                <li id="summary-discount-row"
                                    class="list-group-item d-flex justify-content-between text-success {{ ($cartDetails['discount_amount'] ?? 0) > 0 ? '' : 'd-none' }}">
                                    <span>Giảm giá</span>
                                    <strong id="summary-discount">-{{ number_format($cartDetails['discount_amount'] ?? 0) }}
                                        ₫</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between fs-5 border-top pt-3">
                                    <strong>Tổng cộng</strong>
                                    <strong id="summary-grand-total"
                                        class="text-danger">{{ number_format($cartDetails['grand_total'] ?? 0) }} ₫</strong>
                                </li>
                            </ul>

                            <div class="d-grid mt-4">
                                <button class="btn btn-primary btn-lg" type="submit">Đặt Hàng</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_customer/js/checkout.js') }}" defer></script>
@endpush