@extends('customer.layouts.app')

@section('title', 'Giỏ hàng của bạn')

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
                {{-- Bọc phần tóm tắt trong container ID để JS có thể cập nhật --}}
                <div id="cart-summary-container">
                    <div class="card shadow-sm" style="top: 20px;">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Tóm tắt đơn hàng</h5>

                            {{-- =============================================================== --}}
                            {{-- == PHÂN CHIA GIAO DIỆN CHO KHÁCH VÀ USER ĐÃ ĐĂNG NHẬP == --}}
                            {{-- =============================================================== --}}

                            @auth('customer')
                                {{-- GIAO DIỆN CHO USER ĐÃ ĐĂNG NHẬP --}}
                                @php
                                    $customer = Auth::guard('customer')->user();
                                    $defaultAddress = $customer->defaultAddress;
                                    $cartDetails['shipping_fee'] = $cartDetails['shipping_fee'] ?? 0;
                                @endphp
                                <form id="cart-summary-form">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Tạm tính</span>
                                            <span id="subtotal">{{ number_format($cartDetails['subtotal'] ?? 0) }} ₫</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Phí vận chuyển</span>
                                            <span id="shipping-fee">{{ number_format($cartDetails['shipping_fee']) }}
                                                ₫</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Giảm giá</span>
                                            <span class="text-success" id="promotion-value">-{{ number_format($cartDetails['promotion_value'] ?? 0) }} ₫</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between fs-5">
                                            <strong>Tổng cộng</strong>
                                            <strong class="text-danger"
                                                id="grand-total">{{ number_format($cartDetails['grand_total'] ?? 0) }} ₫</strong>
                                        </li>
                                    </ul>

                                    <div class="mt-4">
                                        <label for="delivery_service_id" class="form-label">Dịch vụ vận chuyển</label>
                                        <select class="form-control selectpicker" id="delivery_service_id"
                                            name="delivery_service_id" data-live-search="true"
                                            title="Chọn dịch vụ vận chuyển">
                                            @foreach ($deliveryServices as $service)
                                                <option value="{{ $service->id }}"
                                                    {{ ($cartDetails['delivery_service_id'] ?? '') == $service->id ? 'selected' : '' }}>
                                                    {{ $service->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mt-3">
                                        <label for="promo_code" class="form-label">Mã giảm giá</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="promo_code" id="promo_code"
                                                placeholder="Nhập mã giảm giá"
                                                value="{{ $cartDetails['promo_code'] ?? '' }}">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="apply-promo-btn">Áp dụng</button>
                                        </div>
                                        <div id="promo-message" class="mt-2"></div>
                                    </div>
                                </form>
                                <div class="d-grid mt-4">
                                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">Tiến hành thanh
                                        toán</a>
                                </div>
                            @else
                                {{-- GIAO DIỆN CHO KHÁCH VÃNG LAI (CHƯA ĐĂNG NHẬP) --}}

                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between fs-5">
                                        <strong>Tạm tính</strong>
                                        <strong class="text-danger">{{ number_format($cartDetails['subtotal'] ?? 0) }}
                                            ₫</strong>
                                    </li>
                                </ul>
                                <div class="alert alert-light mt-4 text-center">
                                    <p class="mb-2">Vui lòng chuyển đến trang thanh toán để nhập địa chỉ và xem chi phí vận
                                        chuyển.
                                    </p>
                                    <a href="{{ route('login') }}?redirect_to=checkout" class="fw-bold">Đăng nhập</a> để
                                    lưu thông tin giỏ hàng.
                                </div>
                                <div class="d-grid mt-3">
                                    {{-- Nút này sẽ đưa cả khách vãng lai đến trang checkout --}}
                                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">Tiến hành thanh
                                        toán</a>
                                </div>
                            @endauth

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- JS cho thư viện bootstrap-select --}}
    <script src="{{ asset('assets_customer/js/cart.js') }}" defer></script>
@endpush