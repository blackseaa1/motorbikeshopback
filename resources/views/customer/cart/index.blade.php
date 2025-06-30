
@extends('customer.layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')
    <div class="container py-5">
        <div class="row g-5">
            {{-- Cột bên trái: Danh sách sản phẩm trong giỏ --}}
            <div class="col-lg-8">
                <h2 class="mb-4">Giỏ hàng</h2>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select-all-items">
                        <label class="form-check-label" for="select-all-items">Chọn tất cả</label>
                    </div>
                    <button class="btn btn-danger btn-sm" id="delete-selected-items" disabled>
                        <i class="bi bi-trash"></i> Xóa đã chọn
                    </button>
                </div>
                <div id="cart-items-container">
                    {{-- File partial _cart_items.blade.php will be loaded here --}}
                    @include('customer.cart.partials._cart_items', ['cartItems' => $cartDetails['items']])
                </div>
            </div>

            {{-- Cột bên phải: Tóm tắt và các tùy chọn thanh toán --}}
            <div class="col-lg-4">
                {{-- Wrap summary section in container ID for JS to update --}}
                <div id="cart-summary-container">
                    <div class="card shadow-sm" style="top: 20px;">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Tóm tắt đơn hàng</h5>

                            {{-- =============================================================== --}}
                            {{-- == INTERFACE DIVISION FOR GUESTS AND LOGGED-IN USERS == --}}
                            {{-- =============================================================== --}}

                            @auth('customer')
                                {{-- INTERFACE FOR LOGGED-IN USERS --}}
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
                                {{-- INTERFACE FOR GUEST USERS (NOT LOGGED IN) --}}

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
                                    {{-- This button will take even guest users to the checkout page --}}
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
    {{-- JS for bootstrap-select library --}}
    <script src="{{ asset('assets_customer/js/cart.js') }}" defer></script>
@endpush