@extends('customer.account.layouts.app')

@section('title', 'Lịch sử đơn hàng')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lịch sử đơn hàng</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                {{-- Mã đơn hàng --}}
                                <td><strong>#{{ $order->id }}</strong></td>

                                {{-- Ngày đặt --}}
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>

                                {{-- Tổng tiền (sử dụng accessor đã format trong Model) --}}
                                <td class="text-end">{{ $order->formatted_total_price }}</td>

                                {{-- Trạng thái (sử dụng accessor cho text và class trong Model) --}}
                                <td class="text-center">
                                    <span class="badge {{ $order->status_badge_class }}">
                                        {{ $order->status_text }}
                                    </span>
                                </td>

                                {{-- Hành động --}}
                                <td class="text-center">
                                    <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-sm btn-primary">Xem
                                        chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    Bạn chưa có đơn hàng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang --}}
            @if ($orders->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection