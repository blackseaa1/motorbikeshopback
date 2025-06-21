@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('header', 'Danh sách Đơn hàng')

@section('content')
    <div id="adminOrdersPage" data-products="{{ json_encode($allProductsForJs) }}"
        data-promotions="{{ json_encode($promotions) }}" data-errors="{{ $errors->any() ? 'true' : 'false' }}"
        data-form-marker="{{ session('form_identifier') }}">

        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                                <i class="bi bi-plus-circle me-1"></i> Tạo Đơn Hàng Mới
                            </button>
                        </div>
                        <div class="d-flex">
                            <form action="{{ route('admin.sales.orders.index') }}" method="GET" class="d-flex">
                                <select name="status" class="form-select me-2" onchange="this.form.submit()">
                                    <option value="all" @if(request('status') === 'all') selected @endif>Tất cả trạng thái
                                    </option>
                                    @foreach($orderStatuses as $key => $value)
                                        <option value="{{ $key }}" @if(request('status') === $key) selected @endif>{{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <input class="form-control me-2" type="search" name="search"
                                    placeholder="Tìm ID, tên khách..." value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit">Tìm</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Khách hàng</th>
                                    <th scope="col">Tổng tiền</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Phương thức TT</th>
                                    <th scope="col">Ngày tạo</th>
                                    <th scope="col" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <th scope="row">#{{ $order->id }}</th>
                                        <td>
                                            <div>{{ $order->getCustomerNameAttribute() }}</div>
                                            <small
                                                class="text-muted">{{ $order->guest_phone ?: $order->customer?->phone }}</small>
                                        </td>
                                        <td>{{ number_format($order->final_amount, 0, ',', '.') }}đ</td>
                                        <td>
                                            <span
                                                class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                                        </td>
                                        <td>{{ $order->payment_method_text }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info view-order-btn" data-id="{{ $order->id }}"
                                                title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary edit-order-btn" data-id="{{ $order->id }}"
                                                title="Chỉnh sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-order-btn" data-id="{{ $order->id }}"
                                                title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Không tìm thấy đơn hàng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $orders->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        @include('admin.sales.order.modals.create_order_modal')
        @include('admin.sales.order.modals.update_order_modal')
        @include('admin.sales.order.modals.view_order_modal')
        @include('admin.sales.order.modals.delete_order_modal')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/order_manager.js') }}"></script>
@endpush