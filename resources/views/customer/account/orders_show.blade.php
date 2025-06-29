@extends('customer.account.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->formatted_id)

@section('account_content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết đơn hàng #{{ $order->formatted_id }}</h5>
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
                    <p class="mb-0"><strong>Địa chỉ giao hàng:</strong> {{ $order->full_address }}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Thông tin đơn hàng:</strong></h6>
                    <p class="mb-1">
                        <strong>Trạng thái:</strong>
                        <span class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                    </p>
                    <p class="mb-1"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-1">
                        <strong>Phương thức thanh toán:</strong>
                        {{ $order->paymentMethod->name ?? 'N/A' }}
                    </p>
                    <p class="mb-1"><strong>Dịch vụ VC:</strong> {{ $order->deliveryService->name ?? 'Không xác định' }}</p>
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

            {{-- Nút "Thanh toán lại" cho đơn hàng chưa thành công hoặc thông báo trạng thái --}}
            @if($order->status === \App\Models\Order::STATUS_APPROVED)
                <div class="alert alert-success mt-4 text-center">
                    Đơn hàng đã được thanh toán thành công và đang chờ xử lý.
                </div>
            @elseif($order->isRetriable()) {{-- Nếu đơn hàng có thể thanh toán lại (PENDING/FAILED/CANCELLED + Online
                Method) --}}
                @php
                    $paymentRetryRoute = null;
                    if ($order->paymentMethod) {
                        if ($order->paymentMethod->code === 'momo') {
                            $paymentRetryRoute = route('payment.momo.initiate', ['order_id' => $order->id]);
                        } elseif ($order->paymentMethod->code === 'vnpay') {
                            $paymentRetryRoute = route('payment.vnpay.initiate', ['order_id' => $order->id]);
                        }
                    }
                @endphp

                @if($paymentRetryRoute)
                    <div class="alert alert-warning mt-4 text-center">
                        <p class="mb-2">Đơn hàng này đang ở trạng thái **{{ $order->status_text }}** và chờ hoàn tất thanh toán trực
                            tuyến.</p>
                        <a href="{{ $paymentRetryRoute }}" class="btn btn-primary">
                            <i class="bi bi-wallet-fill"></i> Thanh toán lại ngay
                        </a>
                    </div>
                @else
                    {{-- Trường hợp isRetriable() là true nhưng không tìm thấy route (thường không xảy ra nếu logic đúng) --}}
                    <div class="alert alert-info mt-4 text-center">
                        <p class="mb-2">Đơn hàng này đang ở trạng thái **{{ $order->status_text }}**. Vui lòng kiểm tra trạng thái
                            sau.</p>
                    </div>
                @endif
            @else {{-- Các trạng thái khác không cần nút thanh toán lại (ví dụ: COD, Bank Transfer chờ admin duyệt) --}}
                <div class="alert alert-info mt-4 text-center">
                    <p class="mb-2">Đơn hàng này đang ở trạng thái **{{ $order->status_text }}**. Vui lòng kiểm tra trạng thái
                        sau hoặc liên hệ hỗ trợ nếu cần.</p>
                </div>
            @endif


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
                        <p>Bạn có chắc chắn muốn hủy đơn hàng **#{{ $order->formatted_id }}** không?</p>
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