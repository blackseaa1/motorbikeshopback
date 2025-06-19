{{-- VIẾT LẠI TOÀN BỘ FILE NÀY --}}

@extends('customer.account.layouts.app')

@section('account_content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết đơn hàng #${{ $order->id }}</h5>
            {{-- Sửa 'customer.account.orders' thành 'account.orders' --}}
            <a href="{{ route('account.orders') }}" class="btn btn-secondary">Quay lại</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6><strong>Thông tin người nhận:</strong></h6>
                    <p class="mb-1"><strong>Họ tên:</strong> {{ $order->customer_name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email }}</p>
                    <p class="mb-1"><strong>Điện thoại:</strong> {{ $order->customer_phone }}</p>
                    <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Thông tin đơn hàng:</strong></h6>
                    <p class="mb-1"><strong>Trạng thái:</strong> <span class="badge bg-info">{{ $order->status }}</span></p>
                    <p class="mb-1"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> {{ $order->payment_method }}</p>
                    <p class="mb-1"><strong>Ghi chú:</strong> {{ $order->notes ?? 'Không có' }}</p>
                </div>
            </div>

            <h6><strong>Các sản phẩm đã đặt:</strong></h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} ₫</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                            <td>{{ number_format($order->subtotal, 0, ',', '.') }} ₫</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Phí vận chuyển:</strong></td>
                            <td>{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong>{{ number_format($order->total_amount, 0, ',', '.') }} ₫</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection