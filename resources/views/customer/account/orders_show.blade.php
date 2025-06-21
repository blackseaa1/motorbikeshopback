@extends('customer.account.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('account_content')

    <div class="card mb-4">
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
                    <p class="mb-1"><strong>Họ tên:</strong> {{ $order->customer_name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->guest_email ?? ($order->customer->email ?? 'N/A') }}
                    </p>
                    <p class="mb-1"><strong>Điện thoại:</strong>
                        {{ $order->guest_phone ?? ($order->customer->phone ?? 'N/A') }}</p>
                    {{-- SỬA ĐỔI: Sử dụng accessor full_address --}}
                    <p class="mb-0"><strong>Địa chỉ giao hàng:</strong> {{ $order->full_address }}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Thông tin đơn hàng:</strong></h6>
                    <p class="mb-1">
                        <strong>Trạng thái:</strong>
                        <span class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                    </p>
                    <p class="mb-1"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Phương thức TT:</strong>
                        {{ $order->payment_method == 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'VNPAY' }}</p>
                    <p class="mb-0"><strong>Dịch vụ VC:</strong> {{ $order->deliveryService->name ?? 'Không xác định' }}</p>
                    <p class="mb-0"><strong>Ghi chú:</strong> {{ $order->notes ?? 'Không có ghi chú' }}</p>
                </div>
            </div>

            {{-- Các sản phẩm --}}
            <h6 class="mt-4 mb-3"><strong>Sản phẩm trong đơn hàng:</strong></h6>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    @if($item->product && $item->product->thumbnail_url)
                                        <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=No+Image" alt="No Image">
                                    @endif
                                </td>
                                <td>
                                    {{-- SỬA ĐỔI: Thay đổi route('products.show', $item->product->slug) thành
                                    route('products.show', $item->product->id) --}}
                                    @if($item->product && $item->product->id)
                                        <a href="{{ route('products.show', $item->product->id) }}"
                                            class="text-dark text-decoration-none">
                                            {{ $item->product->name }}
                                        </a>
                                    @else
                                        {{ $item->product->name ?? 'Sản phẩm không tồn tại' }}
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price) }} ₫</td>
                                <td>{{ number_format($item->quantity * $item->price) }} ₫</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Tổng phụ:</strong></td>
                            <td>{{ number_format($order->subtotal) }} ₫</td>
                        </tr>
                        @if ($order->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="text-end text-success"><strong>Giảm giá:</strong></td>
                                <td class="text-success">-{{ number_format($order->discount_amount) }} ₫</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="text-end"><strong>Phí vận chuyển:</strong></td>
                            <td>{{ number_format($order->shipping_fee) }} ₫</td>
                        </tr>
                        <tr class="table-info">
                            <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong>{{ number_format($order->total_price) }} ₫</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Các nút hành động và thông báo --}}
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('account.orders.index') }}" class="btn btn-secondary">Quay lại danh sách đơn hàng</a>

                @if ($order->isCancellable())
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                        Hủy đơn hàng
                    </button>
                @else
                    <button type="button" class="btn btn-secondary" disabled>
                        Không thể hủy đơn hàng này
                    </button>
                @endif
            </div>

        </div>
    </div>

    {{-- Modal xác nhận hủy đơn hàng cho khách hàng đã đăng nhập --}}
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Xác nhận hủy đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('account.orders.cancel', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn hủy đơn hàng **#{{ $order->id }}** không?</p>
                        <p class="text-danger">Đơn hàng sau khi hủy không thể hoàn tác.</p>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Vui lòng nhập mật khẩu tài khoản của bạn để xác
                                nhận:</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                required>
                            @error('password_confirm')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection