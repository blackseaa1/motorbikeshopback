@extends('customer.layouts.app')

@section('title', 'Giỏ hàng của bạn')

@push('styles')
    {{-- CSS cho thư viện bootstrap-select --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush

@section('content')
    <div class="container py-5">
        <div class="row g-5">
            {{-- Cột bên trái: Danh sách sản phẩm trong giỏ --}}
            <div class="col-lg-8">
                <h2 class="mb-4">Giỏ hàng</h2>
                <div id="cart-items-container">
                    {{-- File partial _cart_items.blade.php sẽ được nạp vào đây --}}
                    @include('customer.cart.partials._cart_items', ['cartItems' => $cartDetails['items']])
                </div>
            </div>

            {{-- Cột bên phải: Tóm tắt và các tùy chọn thanh toán --}}
            <div class="col-lg-4">
                <div class="card shadow-sm" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Tóm tắt đơn hàng</h5>

                        {{-- =============================================================== --}}
                        {{-- == PHÂN CHIA GIAO DIỆN CHO KHÁCH VÀ USER ĐÃ LOGIN == --}}
                        {{-- =============================================================== --}}

                        @auth('customer')
                            {{-- GIAO DIỆN CHO NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP --}}

                            <div class="mb-3">
                                <label for="shipping_address_id" class="form-label fw-bold">Giao đến</label>
                                <select class="form-select" id="shipping_address_id" name="shipping_address_id">
                                    @forelse($customerAddresses as $address)
                                        <option value="{{ $address->id }}" {{ $address->is_default ? 'selected' : '' }}>
                                            {{ $address->full_name }} - {{ Str::limit($address->address_line, 25) }}
                                        </option>
                                    @empty
                                        <option value="">Bạn chưa có địa chỉ nào</option>
                                    @endforelse
                                </select>
                                <a href="{{ route('account.addresses.create', ['redirect' => 'cart']) }}"
                                    class="form-text d-inline-block mt-2">Quản lý sổ địa chỉ</a>
                            </div>
                            <hr>

                            <div class="mb-3">
                                <label for="delivery_service_id" class="form-label fw-bold">Đơn vị vận chuyển</label>
                                <select class="selectpicker form-control" id="delivery_service_id" name="delivery_service_id"
                                    data-live-search="true" title="Chọn đơn vị vận chuyển...">
                                    @foreach($deliveryServices as $service)
                                        <option value="{{ $service->id }}" {{ ($cartDetails['shipping_info']['id'] ?? null) == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }} (+{{ number_format($service->shipping_fee) }} ₫)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <hr>

                            <div class="mb-4">
                                <label for="promotion_code" class="form-label fw-bold">Mã giảm giá</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="promotion_code" placeholder="Nhập mã..."
                                        value="{{ $cartDetails['promotion_info']['code'] ?? '' }}">
                                    <button class="btn btn-outline-secondary" type="button" id="apply-promo-btn">Áp
                                        dụng</button>
                                </div>
                                <div id="promo-feedback" class="mt-2"></div>
                            </div>
                            <hr>

                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Tạm tính</span>
                                    <strong id="cart-subtotal">{{ number_format($cartDetails['subtotal'] ?? 0) }} ₫</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Phí vận chuyển</span>
                                    <strong id="cart-shipping-fee">{{ number_format($cartDetails['shipping_fee'] ?? 0) }}
                                        ₫</strong>
                                </li>
                                <li id="discount-row"
                                    class="list-group-item d-flex justify-content-between text-success {{ ($cartDetails['discount_amount'] ?? 0) > 0 ? '' : 'd-none' }}">
                                    <span>Giảm giá</span>
                                    <strong id="cart-discount">-{{ number_format($cartDetails['discount_amount'] ?? 0) }}
                                        ₫</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between fs-5 border-top pt-3">
                                    <strong>Tổng cộng</strong>
                                    <strong id="cart-grand-total"
                                        class="text-danger">{{ number_format($cartDetails['grand_total'] ?? 0) }} ₫</strong>
                                </li>
                            </ul>
                            <div class="d-grid mt-4">
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">Tiến hành đặt hàng</a>
                            </div>

                        @else
                            {{-- GIAO DIỆN CHO KHÁCH VÃNG LAI (CHƯA ĐĂNG NHẬP) --}}

                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between fs-5">
                                    <strong>Tạm tính</strong>
                                    <strong class="text-danger">{{ number_format($cartDetails['subtotal'] ?? 0) }} ₫</strong>
                                </li>
                            </ul>
                            <div class="alert alert-light mt-4 text-center">
                                <p class="mb-2">Vui lòng chuyển đến trang thanh toán để nhập địa chỉ và xem chi phí vận chuyển.
                                </p>
                                <a href="{{ route('login') }}?redirect_to=checkout" class="fw-bold">Đăng nhập</a> để lưu thông tin giỏ hàng.
                            </div>
                            <div class="d-grid mt-3">
                                {{-- Nút này sẽ đưa cả khách vãng lai đến trang checkout --}}
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">Tiến hành thanh toán</a>
                            </div>
                        @endauth

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_customer/js/cart.js') }}" defer></script>
@endpush