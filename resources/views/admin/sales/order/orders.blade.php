@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn Hàng')

@section('content')
    {{-- Identifier để JS biết đây là trang quản lý đơn hàng --}}
    <div id="adminOrdersPage">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><i class="bi bi-receipt-cutoff me-2"></i>Quản lý Đơn Hàng</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Bán hàng</li>
                            <li class="breadcrumb-item active">Quản lý Đơn Hàng</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
      

            <div class="content-header-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                    <i class="bi bi-plus-circle me-2"></i>Tạo Đơn Hàng Mới
                </button>
            </div>

        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0 text-primary"><i class="bi bi-funnel me-2"></i>Bộ lọc và Tìm kiếm</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sales.orders.index') }}" method="GET" class="form-search">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="ID, Tên khách hàng, Email, SĐT..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select id="status" name="status" class="form-select">
                                <option value="all">Tất cả trạng thái</option>
                                {{-- Vòng lặp này sử dụng biến $orderStatuses được truyền từ OrderController --}}
                                @foreach ($orderStatuses as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">Sắp xếp theo</label>
                            <select id="sort_by" name="sort_by" class="form-select">
                                <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>Mới nhất
                                </option>
                                <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                                <option value="priority" {{ request('sort_by') == 'priority' ? 'selected' : '' }}>Ưu tiên
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-secondary me-2">
                                <i class="bi bi-search me-1"></i>Lọc
                            </button>
                            {{-- Nút xóa lọc chỉ hiển thị khi có bộ lọc được áp dụng --}}
                            @if (request('search') || (request()->filled('status') && request('status') != 'all') || request()->filled('sort_by'))
                                <a href="{{ route('admin.sales.orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4 shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách Đơn hàng</h2>
            </div>
            <div class="card-body">
                @if ($orders->isEmpty())
                    <div class="alert alert-info mb-0" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>Không tìm thấy đơn hàng nào phù hợp.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Khách hàng</th>
                                    <th scope="col" class="text-end">Tổng tiền</th>
                                    <th scope="col" class="text-center">Thanh toán</th>
                                    <th scope="col" class="text-center">Trạng thái</th>
                                    <th scope="col">Ngày tạo</th>
                                    <th scope="col" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        // *** LOGIC NGHIỆP VỤ: Xác định các trạng thái cuối cùng ***
                                        $isFinalStatus = in_array($order->status, [
                                            \App\Models\Order::STATUS_COMPLETED,
                                            \App\Models\Order::STATUS_CANCELLED,
                                            \App\Models\Order::STATUS_FAILED,
                                            \App\Models\Order::STATUS_RETURNED
                                        ]);
                                        $isCompleted = $order->status === \App\Models\Order::STATUS_COMPLETED;
                                    @endphp
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>
                                            @if($order->customer_id)
                                                <i class="bi bi-person-check-fill text-success me-1"
                                                    title="Khách hàng có tài khoản"></i>
                                            @else
                                                <i class="bi bi-person-circle text-muted me-1" title="Khách vãng lai"></i>
                                            @endif
                                            <span>{{ $order->guest_name }}</span>
                                            <br>
                                            <small class="text-muted">{{ $order->guest_phone ?? 'N/A' }}</small>
                                            <br>
                                            <small class="text-muted">{{ $order->guest_email ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-end"><strong class="text-danger">{{ number_format($order->total_price) }}
                                                ₫</strong></td>
                                        <td class="text-center"><span
                                                class="badge bg-secondary">{{ $order->paymentMethod->name ?? 'N/A' }}</span></td>
                                        <td class="text-center">
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status_text }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-sm view-order-btn" data-bs-toggle="modal"
                                                data-bs-target="#viewOrderModal" data-id="{{ $order->id }}" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm update-order-btn"
                                                data-bs-toggle="modal" data-bs-target="#updateOrderModal" data-id="{{ $order->id }}"
                                                title="Cập nhật trạng thái" {{ $isFinalStatus ? 'disabled' : '' }}>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-order-btn"
                                                data-bs-toggle="modal" data-bs-target="#deleteOrderModal" data-id="{{ $order->id }}"
                                                title="Xóa vĩnh viễn" {{ $isCompleted ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Links --}}
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- BAO GỒM CÁC MODAL --}}
    @include('admin.sales.order.modals.create_order_modal', [
        'customers' => $customers,
        'provinces' => $provinces,
        'deliveryServices' => $deliveryServices,
        'promotions' => $promotions,
        'orderStatuses' => $orderStatuses,
        'paymentMethods' => $paymentMethods
    ])
    @include('admin.sales.order.modals.view_order_modal')
    @include('admin.sales.order.modals.update_order_modal', ['orderStatuses' => $orderStatuses, 'deliveryServices' => $deliveryServices])
                    @include('admin.sales.order.modals.delete_order_modal')

@endsection

@push('scripts')
    {{-- Tải script quản lý đơn hàng --}}
    <script src="{{ asset('assets_admin/js/order_manager.js') }}"></script>
@endpush
