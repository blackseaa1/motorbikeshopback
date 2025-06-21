{{-- VIẾT LẠI TOÀN BỘ FILE NÀY --}}
@extends('customer.account.layouts.app')

@section('account_content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết đơn hàng #{{ $order->id }}</h5>
            <a href="{{ route('account.orders.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="card-body">
            {{-- Thông tin tóm tắt đơn hàng --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6><strong>Thông tin người nhận:</strong></h6>
                    <p class="mb-1"><strong>Họ tên:</strong> {{ $order->guest_name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->guest_email }}</p>
                    <p class="mb-1"><strong>Điện thoại:</strong> {{ $order->guest_phone }}</p>
                    <p class="mb-0"><strong>Địa chỉ giao hàng:</strong> {{ $order->shipping_address }}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Thông tin đơn hàng:</strong></h6>
                    <p class="mb-1">
                        <strong>Trạng thái:</strong>
                        <span class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                    </p>
                    <p class="mb-1"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> {{ strtoupper($order->payment_method) }}</p>
                    @if ($order->deliveryService)
                        <p class="mb-0"><strong>Đơn vị vận chuyển:</strong> {{ $order->deliveryService->name }}</p>
                    @endif
                </div>
            </div>

            <hr>

            {{-- Bảng chi tiết sản phẩm --}}
            <h6 class="mb-3"><strong>Các sản phẩm đã đặt:</strong></h6>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($item->product)
                                            <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}"
                                                class="img-fluid rounded me-3"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0">{{ $item->product->name }}</h6>
                                            </div>
                                        @else
                                            Sản phẩm không còn tồn tại
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">{{ $item->formatted_price }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>

            {{-- Tổng kết đơn hàng --}}
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tạm tính:</span>
                            <span>{{ number_format($order->subtotal, 0, ',', '.') }} ₫</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Phí vận chuyển:</span>
                            <span>{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</span>
                        </li>
                        @if ($order->discount_amount > 0)
                            <li class="list-group-item d-flex justify-content-between text-success">
                                <span>Giảm giá:</span>
                                <span>-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between fs-5 fw-bold border-top pt-3">
                            <span>Tổng cộng:</span>
                            <span class="text-danger">{{ $order->formatted_total_price }}</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection