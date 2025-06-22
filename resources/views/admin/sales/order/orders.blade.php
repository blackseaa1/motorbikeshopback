@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn Hàng')

@section('content')
    <div id="adminOrdersPage">
        <header class="content-header">
            <h1><i class="bi bi-tags-fill me-2"></i>Quản lý Đơn Hàng</h1>
            <div class="content-header-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                    <i class="bi bi-plus-circle me-2"></i>Tạo Đơn Hàng Mới
                </button>
            </div>
        </header>

        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0 text-primary"><i class="bi bi-receipt-cutoff me-2"></i>Danh sách Đơn hàng</h2>
            </div>
            <div class="card-body">
                <!-- Form lọc và tìm kiếm -->
                <form action="{{ route('admin.sales.orders.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="Tìm kiếm theo ID, Tên khách hàng, Email, SĐT..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="all">Tất cả trạng thái</option>
                                @foreach ($orderStatuses as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-secondary">
                                <i class="bi bi-funnel me-2"></i>Lọc
                            </button>
                        </div>
                        @if (request('search') || request('status') != 'all')
                            <div class="col-md-auto">
                                <a href="{{ route('admin.sales.orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Xóa lọc
                                </a>
                            </div>
                        @endif
                    </div>
                </form>

                @if ($orders->isEmpty())
                    <div class="alert alert-info mb-0" role="alert">
                        <i class="bi bi-info-circle me-2"></i>Hiện chưa có đơn hàng nào.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 5%">ID</th>
                                    <th scope="col">Khách hàng</th>
                                    <th scope="col">Tổng phụ</th>
                                    <th scope="col">Phí ship</th>
                                    <th scope="col">Giảm giá</th>
                                    <th scope="col">Tổng tiền</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Ngày tạo</th>
                                    <th scope="col" style="width: 15%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                             <td>
                                            @if($order->customer)
                                                <i class="bi bi-person-fill me-1"></i>{{ $order->customer->name }} (KH có tài khoản)
                                            @else
                                                <i class="bi bi-person-circle me-1"></i>{{ $order->guest_name }} (Khách vãng lai)
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ $order->guest_email ?? ($order->customer ? $order->customer->email : 'N/A') }}</small>
                                        </td>
                                        <td>{{ number_format($order->subtotal) }} ₫</td>
                                        <td>{{ number_format($order->shipping_fee) }} ₫</td>
                                        <td>-{{ number_format($order->discount_amount) }} ₫</td>
                                        <td><strong class="text-danger">{{ number_format($order->total_price) }} ₫</strong></td>
                                        <td>
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status_text }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm view-order-btn" data-bs-toggle="modal"
                                                data-bs-target="#viewOrderModal" data-id="{{ $order->id }}" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm update-order-btn"
                                                data-bs-toggle="modal" data-bs-target="#updateOrderModal" data-id="{{ $order->id }}"
                                                title="Cập nhật">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-order-btn"
                                                data-bs-toggle="modal" data-bs-target="#deleteOrderModal" data-id="{{ $order->id }}"
                                                title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $orders->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Include các modal -->
    @include('admin.sales.order.modals.create_order_modal')
    @include('admin.sales.order.modals.view_order_modal')
    @include('admin.sales.order.modals.update_order_modal')
    @include('admin.sales.order.modals.delete_order_modal')

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/order_manager.js') }}"></script>
@endpush