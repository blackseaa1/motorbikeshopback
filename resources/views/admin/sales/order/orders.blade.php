{{-- resources/views/admin/sales/order/orders.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('header', 'Danh sách Đơn hàng')

@section('content')
    <div id="adminOrdersPage" data-products="{{ json_encode($allProductsForJs) }}"
        data-promotions="{{ json_encode($promotions) }}" data-customers="{{ json_encode($customers) }}"
        data-provinces="{{ json_encode($provinces) }}" data-delivery-services="{{ json_encode($deliveryServices) }}"
        data-errors="{{ $errors->any() ? 'true' : 'false' }}" data-form-marker="{{ session('form_identifier') }}"
        @if(session('form_identifier') === 'update_order_form' && old('id')) data-original-order-id="{{ old('id') }}" @endif>

        {{-- Header của trang --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-receipt-cutoff me-2"></i>Quản lý Đơn hàng</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Bán hàng</li>
                            <li class="breadcrumb-item active">Đơn hàng</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nội dung chính --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0 text-primary"><i class="bi bi-box-seam-fill me-2"></i>Danh sách Đơn hàng</h2>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Đơn Hàng Mới
                        </button>
                    </div>
                    <div class="card-body">
                        {{-- Form tìm kiếm và lọc --}}
                        <div class="d-flex justify-content-end mb-3">
                            <form action="{{ route('admin.sales.orders.index') }}" method="GET"
                                class="d-flex align-items-center">
                                {{-- SỬA ĐỔI: Đảo vị trí select và input, và thêm lớp -sm cho kích thước nhỏ hơn --}}
                                <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                                    <option value="all" @if(request('status') === 'all') selected @endif>Tất cả trạng thái
                                    </option>
                                    @foreach($orderStatuses as $key => $value)
                                        <option value="{{ $key }}" @if(request('status') === $key) selected @endif>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" class="form-control form-control-sm me-2"
                                    placeholder="Tìm kiếm đơn hàng, khách hàng..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Dịch vụ V/C</th>
                                        <th>Ngày tạo</th>
                                        <th class="text-center" style="width: 180px;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>
                                                @if($order->customer)
                                                    <a href="#">{{ $order->customer->name }}</a> <br>
                                                    <small class="text-muted">{{ $order->customer->email }}</small>
                                                @else
                                                    {{ $order->guest_name }} <br>
                                                    <small class="text-muted">{{ $order->guest_email }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $order->formatted_total_price }}</td>
                                            <td><span
                                                    class="badge {{ $order->status_badge_class }}">{{ $order->status_text }}</span>
                                            </td>
                                            <td>{{ $order->deliveryService->name ?? 'N/A' }}</td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-1">
                                                    {{-- Nút Duyệt Đơn Hàng --}}
                                                    @if($order->status === \App\Models\Order::STATUS_PENDING || $order->status === \App\Models\Order::STATUS_PROCESSING)
                                                        <button class="btn btn-sm btn-success approve-order-btn btn-action"
                                                            data-id="{{ $order->id }}" data-name="Đơn hàng #{{ $order->id }}"
                                                            data-approve-url="{{ route('admin.sales.orders.approve', $order->id) }}"
                                                            title="Duyệt Đơn Hàng">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-info view-order-btn btn-action"
                                                        data-id="{{ $order->id }}"
                                                        data-view-url="{{ route('admin.sales.orders.show', $order->id) }}"
                                                        data-print-url="{{ route('admin.sales.orders.show', $order->id) }}?print=true"
                                                        title="Xem/In">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary update-order-btn btn-action"
                                                        data-id="{{ $order->id }}"
                                                        data-update-url="{{ route('admin.sales.orders.update', $order->id) }}"
                                                        data-fetch-url="{{ route('admin.sales.orders.show', $order->id) }}"
                                                        title="Chỉnh sửa">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-order-btn btn-action"
                                                        data-bs-toggle="modal" data-bs-target="#deleteOrderModal"
                                                        data-id="{{ $order->id }}" data-name="Đơn hàng #{{ $order->id }}"
                                                        data-delete-url="{{ route('admin.sales.orders.destroy', $order->id) }}"
                                                        title="Xóa">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
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
        </section>

        @include('admin.sales.order.modals.create_order_modal')
        @include('admin.sales.order.modals.update_order_modal')
        @include('admin.sales.order.modals.view_order_modal')
        @include('admin.sales.order.modals.delete_order_modal')
    </div>
@endsection

@push('scripts')
    <script>
        window.LaravelOldInputForUpdate = @json(old());
        window.LaravelErrors = {
            create_order_form: @json($errors->getBag('create_order_form')->toArray()),
            update_order_form: @json($errors->getBag('update_order_form')->toArray())
        };
        window.LaravelOldInput = @json(old());
    </script>
    <script src="{{ asset('assets_admin/js/order_manager.js') }}"></script>
@endpush