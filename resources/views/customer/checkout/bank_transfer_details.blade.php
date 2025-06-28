// File: resources/views/customer/checkout/bank_transfer_details.blade.php

@extends('customer.layouts.app')

@section('title', 'Chi tiết chuyển khoản ngân hàng')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Chi tiết chuyển khoản ngân hàng</h4>
                </div>
                <div class="card-body text-center">
                    <p class="lead">Cảm ơn bạn đã đặt hàng! Vui lòng chuyển khoản với thông tin dưới đây để hoàn tất đơn hàng **#{{ $order->id }}**.</p>
                    <p>Đơn hàng của bạn sẽ được xử lý sau khi chúng tôi xác nhận đã nhận được thanh toán.</p>

                    <hr>

                    <h5 class="fw-bold">Thông tin chuyển khoản:</h5>
                    <p><strong>Ngân hàng:</strong> Ngân hàng ABC</p>
                    <p><strong>Số tài khoản:</strong> 1234567890</p>
                    <p><strong>Chủ tài khoản:</strong> Cửa Hàng Xe Máy XYZ</p>
                    <p class="fw-bold text-danger">
                        Nội dung chuyển khoản: <span class="text-primary">"Thanh toan don hang #{{ $order->id }}"</span>
                    </p>
                    <p>Số tiền cần chuyển: <strong class="text-success">{{ number_format($order->total_price) }} ₫</strong></p>

                    <hr>

                    <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua điện thoại hoặc email.</p>
                    <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-primary mt-3">Xem chi tiết đơn hàng</a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-3">Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection