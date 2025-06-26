@extends('admin.layouts.app')

@section('title', 'Quản lý Phương thức Thanh toán')

@section('content')
<div id="adminPaymentMethodsPage">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="bi bi-credit-card me-2"></i>Quản lý Phương thức Thanh toán</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Hệ thống</li>
                        <li class="breadcrumb-item active">Phương thức Thanh toán</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>Danh sách Phương thức Thanh toán</h2>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPaymentMethodModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Phương thức mới
                    </button>
                </div>
                <div class="card-body">
                    @if ($paymentMethods->isEmpty())
                        <div class="alert alert-info mb-0" role="alert">
                            <i class="bi bi-info-circle me-2"></i>Hiện chưa có phương thức thanh toán nào.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 5%;">STT</th>
                                        <th scope="col" style="width: 10%;" class="text-center">Logo</th>
                                        <th scope="col">Tên Phương thức</th>
                                        <th scope="col">Mã (Code)</th>
                                        <th scope="col" class="text-center" style="width:10%">Trạng thái</th>
                                        <th scope="col" class="text-center" style="width: 20%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paymentMethods as $method)
                                        <tr id="pm-row-{{ $method->id }}" class="{{ !$method->isActive() ? 'row-inactive' : '' }}">
                                            <th scope="row">{{ $paymentMethods->firstItem() + $loop->index }}</th>
                                            <td class="text-center">
                                                <img src="{{ $method->logo_full_url }}"
                                                     alt="{{ $method->name }}" class="img-thumbnail"
                                                     style="max-width: 80px; max-height: 40px; object-fit: contain;">
                                            </td>
                                            <td class="fw-bold">{{ $method->name }}</td>
                                            <td>{{ $method->code }}</td>
                                            <td class="text-center status-cell" id="pm-status-{{ $method->id }}">
                                                <span class="badge {{ $method->status_badge_class }}">{{ $method->status_text }}</span>
                                            </td>
                                            <td class="text-center action-buttons">
    <button type="button"
        class="btn btn-sm toggle-status-btn {{ $method->isActive() ? 'btn-outline-secondary' : 'btn-danger' }}"
        data-id="{{ $method->id }}"
        data-url="{{ route('admin.system.paymentMethods.toggleStatus', $method->id) }}"
        title="{{ $method->isActive() ? 'Ẩn phương thức này' : 'Hiển thị phương thức này' }}">
        <i class="bi bi-power"></i>
    </button>
    <button type="button" class="btn btn-sm btn-primary btn-edit-pm"
        data-bs-toggle="modal" data-bs-target="#updatePaymentMethodModal"
        data-id="{{ $method->id }}"
        data-name="{{ $method->name }}"
        data-code="{{ $method->code }}"
        data-description="{{ $method->description }}"
        data-logo-url="{{ $method->logo_full_url }}"
        data-status="{{ $method->status }}"
        data-update-url="{{ route('admin.system.paymentMethods.update', $method->id) }}"
        title="Chỉnh sửa">
        <i class="bi bi-pencil-square"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-pm"
        data-bs-toggle="modal" data-bs-target="#deletePaymentMethodModal"
        data-id="{{ $method->id }}"
        data-name="{{ $method->name }}"
        data-delete-url="{{ route('admin.system.paymentMethods.destroy', $method->id) }}"
        title="Xóa">
        <i class="bi bi-trash-fill"></i>
    </button>
</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $paymentMethods->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@include('admin.system.partials.modals.modal_create_payment_method')
@include('admin.system.partials.modals.modal_update_payment_method')
@include('admin.system.partials.modals.modal_delete_payment_method')

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/payment_method_manager.js') }}"></script>
@endpush