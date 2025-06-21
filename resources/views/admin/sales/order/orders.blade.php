@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn Hàng')

@section('content')
    <div id="adminOrdersPage">
        <header class="content-header">
            <h1><i class="bi bi-tags-fill me-2"></i>Đơn Hàng</h1>
            <div class="content-header-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                    <i class="bi bi-plus-circle me-2"></i>Tạo Đơn Hàng Mới
                </button>
            </div>
        </header>

        @include('admin.layouts.partials.messages')

        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0 text-primary"><i class="bi bi-receipt-cutoff me-2"></i>Danh sách Đơn hàng</h2>
            </div>
            <div class="card-body">
                @if ($orders->isEmpty())
                    <div class="alert alert-info mb-0" role="alert">
                        <i class="bi bi-info-circle me-2"></i>Hiện chưa có đơn hàng nào.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:5%">ID</th>
                                    <th scope="col">Khách hàng</th>
                                    <th scope="col">Tổng tiền</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Ngày đặt</th>
                                    <th scope="col" class="text-center" style="width: 15%;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="orders-table-body">
                                @forelse($orders as $order)
                                    <tr id="order-row-{{ $order->id }}">
                                        <th scope="row">#{{ $order->id }}</th>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->formatted_total_price }}</td>
                                        <td id="order-status-{{ $order->id }}">
                                            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                {{-- Nút Xem --}}
                                                <button class="btn btn-sm btn-outline-info me-2 view-order-btn"
                                                    data-bs-toggle="modal" data-bs-target="#viewOrderModal"
                                                    data-id="{{ $order->id }}"
                                                    data-url="{{ route('admin.sales.orders.show', $order->id) }}"
                                                    title="Xem chi tiết">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>

                                                {{-- Nút Sửa --}}
                                                <button class="btn btn-sm btn-outline-primary me-2 edit-order-btn"
                                                    data-bs-toggle="modal" data-bs-target="#updateOrderModal"
                                                    data-id="{{ $order->id }}"
                                                    data-url="{{ route('admin.sales.orders.show', $order->id) }}"
                                                    data-update-url="{{ route('admin.sales.orders.update', $order->id) }}"
                                                    title="Chỉnh sửa">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                {{-- Nút Xóa --}}
                                                <button class="btn btn-sm btn-outline-danger delete-order-btn"
                                                    data-bs-toggle="modal" data-bs-target="#deleteOrderModal"
                                                    data-id="{{ $order->id }}"
                                                    data-name="{{ $order->customer_name }} (Order #{{ $order->id }})"
                                                    data-delete-url="{{ route('admin.sales.orders.destroy', $order->id) }}"
                                                    title="Xóa">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Không có đơn hàng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Include tất cả các modals cần thiết cho trang --}}
    @include('admin.sales.order.modals.create_order_modal')
    @include('admin.sales.order.modals.view_order_modal')
    @include('admin.sales.order.modals.update_order_modal')
    @include('admin.sales.order.modals.delete_order_modal')

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap-select.min.css') }}">
    <style>
        .product-item-row-modal {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .product-item-row-modal select,
        .product-item-row-modal input[type="number"] {
            flex-grow: 1;
        }

        .product-item-row-modal button {
            flex-shrink: 0;
        }

        .row-inactive {
            opacity: 0.6;
            filter: grayscale(80%);
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets_admin/js/order_manager.js') }}"></script>
@endpush