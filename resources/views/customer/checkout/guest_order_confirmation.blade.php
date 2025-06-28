@extends('customer.layouts.app')

@section('title', 'Xác nhận đơn hàng')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0 text-center">Đặt hàng thành công!</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead text-center">Cảm ơn bạn đã mua hàng tại cửa hàng của chúng tôi!</p>
                        <p class="text-center">Đơn hàng của bạn **#{{ $order->id }}** đã được tiếp nhận và đang chờ xử lý.
                        </p>
                        <hr>
                        <h5 class="mb-3">Chi tiết đơn hàng:</h5>
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Mã đơn hàng</h6>
                                </div>
                                <span class="text-muted">#{{ $order->id }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Tên người nhận</h6>
                                </div>
                                <span class="text-muted">{{ $order->guest_name ?? $order->customer_name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Email</h6>
                                </div>
                                <span class="text-muted">{{ $order->guest_email }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Số điện thoại</h6>
                                </div>
                                <span class="text-muted">{{ $order->guest_phone }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Địa chỉ giao hàng</h6>
                                </div>
                                <span class="text-muted">{{ $order->full_address }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Trạng thái đơn hàng</h6>
                                </div>
                                <span class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Phương thức thanh toán</h6>
                                </div>
                                <span class="text-muted">{{ $order->paymentMethod->name ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Dịch vụ vận chuyển</h6>
                                </div>
                                <span class="text-muted">{{ $order->deliveryService->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Phí vận chuyển</h6>
                                </div>
                                <span class="text-muted">{{ number_format($order->shipping_fee) }} ₫</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between lh-sm text-success">
                                <div>
                                    <h6 class="my-0">Giảm giá</h6>
                                </div>
                                <span>-{{ number_format($order->discount_amount) }} ₫</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between bg-light">
                                <h6 class="my-0">Tổng cộng</h6>
                                <strong>{{ number_format($order->total_price) }} ₫</strong>
                            </li>
                        </ul>

                        <h5 class="mb-3">Sản phẩm đã đặt:</h5>
                        <ul class="list-group mb-3">
                            @foreach ($order->items as $item)
                                <li class="list-group-item d-flex justify-content-between lh-sm">
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->thumbnail_url)
                                            <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}"
                                                class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=No+Image" alt="No Image"
                                                class="me-3">
                                        @endif

                                        <div>
                                            <h6 class="my-0">
                                                @if($item->product && $item->product->id)
                                                    <a href="{{ route('products.show', $item->product->id) }}"
                                                        class="text-dark text-decoration-none">
                                                        {{ $item->product->name }}
                                                    </a>
                                                @else
                                                    {{ $item->product->name ?? 'Sản phẩm không tồn tại' }}
                                                @endif
                                            </h6>
                                            <small class="text-muted">Số lượng: {{ $item->quantity }} x
                                                {{ number_format($item->price) }} ₫</small>
                                        </div>
                                    </div>
                                    <span class="text-muted">{{ number_format($item->quantity * $item->price) }} ₫</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('home') }}" class="btn btn-primary">Tiếp tục mua sắm</a>

                            @if ($order->isCancellable())
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#cancelOrderModal">
                                    Hủy đơn hàng
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary" disabled>
                                    Không thể hủy đơn hàng này
                                </button>
                            @endif
                        </div>

                        {{-- Nút "Thanh toán lại" cho đơn hàng chưa thành công --}}
                        @if($order->isRetriable())
                            <div class="alert alert-warning mt-4 text-center">
                                <p class="mb-2">Đơn hàng này đang ở trạng thái chưa hoàn tất thanh toán.</p>
                                <a href="{{ route('payment.momo.initiate', ['order_id' => $order->id]) }}"
                                    class="btn btn-primary">
                                    <i class="bi bi-wallet-fill"></i> Thanh toán lại ngay
                                </a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal xác nhận hủy đơn hàng cho khách vãng lai --}}
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Xác nhận hủy đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('guest.order.cancel', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn hủy đơn hàng **#{{ $order->id }}** không?</p>
                        <p class="text-danger">Đơn hàng sau khi hủy không thể hoàn tác.</p>
                        <div class="mb-3">
                            <label for="guest_contact_confirm" class="form-label">Vui lòng nhập lại email hoặc số điện thoại
                                đã dùng để xác nhận:</label>
                            <input type="text" class="form-control" id="guest_contact_confirm" name="guest_contact_confirm"
                                required>
                            <small class="text-muted">Chúng tôi cần xác nhận bạn là chủ sở hữu đơn hàng này.</small>
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