@extends('customer.account.layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('account_content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Đơn hàng của tôi</h5>
        </div>
        <div class="card-body">
            {{-- Form tìm kiếm, lọc, sắp xếp --}}
            <form action="{{ route('account.orders.index') }}" method="GET" class="mb-4">
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
                                <option value="{{ $key }}"
                                    {{ $selectedFilters['status_filter'] == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label visually-hidden">Sắp xếp theo</label>
                        <select class="form-select" id="sort_by" name="sort_by">
                            <option value="created_at_desc" {{ $selectedFilters['sort_by'] == 'created_at_desc' ? 'selected' : '' }}>Ngày đặt (Mới nhất)</option>
                            <option value="created_at_asc" {{ $selectedFilters['sort_by'] == 'created_at_asc' ? 'selected' : '' }}>Ngày đặt (Cũ nhất)</option>
                            <option value="total_price_desc" {{ $selectedFilters['sort_by'] == 'total_price_desc' ? 'selected' : '' }}>Tổng tiền (Giảm dần)</option>
                            <option value="total_price_asc" {{ $selectedFilters['sort_by'] == 'total_price_asc' ? 'selected' : '' }}>Tổng tiền (Tăng dần)</option>
                            <option value="status_asc" {{ $selectedFilters['sort_by'] == 'status_asc' ? 'selected' : '' }}>Trạng thái (A-Z)</option>
                            <option value="status_desc" {{ $selectedFilters['sort_by'] == 'status_desc' ? 'selected' : '' }}>Trạng thái (Z-A)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                    </div>
                </div>
            </form>

            @if ($orders->isEmpty())
                <div class="alert alert-info text-center" role="alert">
                    Bạn chưa có đơn hàng nào hoặc không tìm thấy đơn hàng phù hợp.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                                        <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-sm btn-info text-white">Xem chi tiết</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $orders->links('customer.vendor.pagination') }} {{-- Sử dụng phân trang Bootstrap 5 --}}
                </div>
            @endif
        </div>
    </div>
@endsection
