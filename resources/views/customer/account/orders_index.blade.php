{{-- VIẾT LẠI TOÀN BỘ FILE NÀY --}}

@extends('customer.account.layouts.app')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lịch sử đơn hàng</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                <td>{{ number_format($order->total_amount, 0, ',', '.') }} ₫</td>
                                <td><span class="badge bg-info">{{ $order->status }}</span></td>
                                <td>
                                    {{-- Sửa 'customer.account.orders.show' thành 'account.orders.show' --}}
                                    <a href="{{ route('account.orders.show', $order->id) }}"
                                        class="btn btn-sm btn-primary">Xem</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Bạn chưa có đơn hàng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Phân trang --}}
            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection