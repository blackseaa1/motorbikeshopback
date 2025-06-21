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
                                            <strong class="text-danger"
                                                id="cart-subtotal">{{ number_format($cartDetails['subtotal'] ?? 0, 0, ',', '.') }}
                                                ₫</strong>
                                        </li>
                                    </ul>
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
                                        <strong class="text-danger"
                                            id="cart-subtotal">{{ number_format($cartDetails['subtotal'] ?? 0, 0, ',', '.') }}
                                            ₫</strong>
                                    </li>
                                </ul>
                                <div class="alert alert-light mt-4 text-center">

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