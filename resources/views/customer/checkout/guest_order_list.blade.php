@extends('customer.layouts.app')

@section('title', 'Đơn hàng của bạn (' . $selectedFilters['guest_phone'] . ')')

@section('content')
    <div class="container py-5">
        <h2 class="text-center mb-4">Các đơn hàng của bạn (SĐT/Email: {{ $selectedFilters['guest_phone'] }})</h2>

        {{-- Form tìm kiếm, lọc, sắp xếp --}}
        {{-- ĐÃ SỬA: Form action trỏ đến route của phương thức listGuestOrders mới --}}
        <form action="{{ route('guest.order.list') }}" method="GET" class="mb-4 p-3 border rounded shadow-sm bg-light">
            <input type="hidden" name="guest_phone" value="{{ $selectedFilters['guest_phone'] }}"> {{-- Giữ lại số điện
            thoại đã tra cứu --}}
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label for="search" class="form-label visually-hidden">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search"
                        placeholder="Tìm kiếm theo mã đơn hàng hoặc tên sản phẩm..."
                        value="{{ $selectedFilters['search'] }}">
                </div>
                <div class="col-md-3">
                    <label for="status_filter" class="form-label visually-hidden">Lọc trạng thái</label>
                    <select class="form-select" id="status_filter" name="status_filter">
                        <option value="all">Tất cả trạng thái</option>
                        @foreach ($orderStatuses as $key => $value)
                            <option value="{{ $key }}" {{ $selectedFilters['status_filter'] == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort_by" class="form-label visually-hidden">Sắp xếp theo</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="latest" {{ $selectedFilters['sort_by'] == 'latest' ? 'selected' : '' }}>Mới nhất
                        </option>
                        <option value="oldest" {{ $selectedFilters['sort_by'] == 'oldest' ? 'selected' : '' }}>Cũ nhất
                        </option>
                        <option value="total_desc" {{ $selectedFilters['sort_by'] == 'total_desc' ? 'selected' : '' }}>Tổng
                            tiền (Giảm dần)</option>
                        <option value="total_asc" {{ $selectedFilters['sort_by'] == 'total_asc' ? 'selected' : '' }}>Tổng tiền
                            (Tăng dần)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                </div>
            </div>
        </form>

        @if ($orders->isEmpty())
            <div class="alert alert-info text-center" role="alert">
                Không tìm thấy đơn hàng nào phù hợp với số điện thoại/email và tiêu chí tìm kiếm của bạn.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Phương thức TT</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>#{{ $order->formatted_id }}</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ number_format($order->total_price) }} ₫</td>
                                <td><span class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span></td>
                                <td>{{ $order->paymentMethod->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('guest.order.show', $order->id) }}" class="btn btn-sm btn-info text-white">Xem
                                        chi tiết</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{-- ĐÃ SỬA: Sử dụng template phân trang tùy chỉnh của bạn --}}
                {{ $orders->appends(['guest_phone' => $selectedFilters['guest_phone']])->links('customer.vendor.pagination') }}
            </div>
        @endif
        <div class="text-center mt-4">
            <a href="{{ route('guest.order.lookup') }}" class="btn btn-secondary">Tra cứu đơn hàng khác</a>
            <a href="{{ route('home') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    </div>
@endsection