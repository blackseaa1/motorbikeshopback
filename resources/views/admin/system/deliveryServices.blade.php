@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn vị Giao hàng')

@section('content')
<div id="adminDeliveryServicesPage">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="bi bi-truck me-2"></i>Quản lý Đơn vị Giao hàng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Hệ thống</li>
                        <li class="breadcrumb-item active">Đơn vị Giao hàng</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>Danh sách Đơn vị Giao hàng</h2>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDeliveryServiceModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Đơn vị mới
                    </button>
                </div>
                <div class="card-body">
                    @if ($deliveryServices->isEmpty())
                        <div class="alert alert-info mb-0" role="alert">
                            <i class="bi bi-info-circle me-2"></i>Hiện chưa có đơn vị giao hàng nào.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 5%;">STT</th>
                                        <th scope="col" style="width: 10%;" class="text-center">Logo</th>
                                        <th scope="col">Tên Đơn vị</th>
                                        <th scope="col" class="text-end">Phí Giao hàng</th>
                                        <th scope="col" class="text-center" style="width:10%">Trạng thái</th>
                                        <th scope="col" class="text-center" style="width: 20%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deliveryServices as $service)
                                        <tr id="ds-row-{{ $service->id }}" class="{{ !$service->isActive() ? 'row-inactive' : '' }}">
                                            <th scope="row">{{ $deliveryServices->firstItem() + $loop->index }}</th>
                                            <td class="text-center">
                                                <img src="{{ $service->logo_full_url }}"
                                                     alt="{{ $service->name }}" class="img-thumbnail"
                                                     style="max-width: 80px; max-height: 40px; object-fit: contain;">
                                            </td>
                                            <td class="fw-bold">{{ $service->name }}</td>
                                            <td class="text-end">{{ $service->shipping_fee ? $service->formatted_shipping_fee : '-' }}</td>
                                            <td class="text-center status-cell" id="ds-status-{{ $service->id }}">
                                                <span class="badge {{ $service->status_badge_class }}">{{ $service->status_text }}</span>
                                            </td>
                                            <td class="text-center action-buttons">
                                                <button type="button" class="btn btn-sm btn-info btn-view-ds"
                                                    data-bs-toggle="modal" data-bs-target="#viewDeliveryServiceModal"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-shipping-fee="{{ $service->shipping_fee }}"
                                                    data-logo-url="{{ $service->logo_full_url }}"
                                                    data-status="{{ $service->status }}"
                                                    data-created-at="{{ $service->created_at->format('d/m/Y H:i:s') }}"
                                                    data-updated-at="{{ $service->updated_at->format('d/m/Y H:i:s') }}"
                                                    data-service-json="{{ json_encode($service->toArray(), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) }}"
                                                    data-update-url="{{ route('admin.system.deliveryServices.update', $service->id) }}" {{-- THÊM DÒNG NÀY --}}
                                                    title="Xem chi tiết">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm toggle-status-btn {{ $service->isActive() ? 'btn-outline-secondary' : 'btn-danger' }}"
                                                    data-id="{{ $service->id }}"
                                                    data-url="{{ route('admin.system.deliveryServices.toggleStatus', $service->id) }}"
                                                    title="{{ $service->isActive() ? 'Ẩn đơn vị này' : 'Hiển thị đơn vị này' }}">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary btn-edit-ds"
                                                    data-bs-toggle="modal" data-bs-target="#updateDeliveryServiceModal"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-shipping-fee="{{ $service->shipping_fee }}"
                                                    data-logo-url="{{ $service->logo_full_url }}"
                                                    data-status="{{ $service->status }}"
                                                    data-update-url="{{ route('admin.system.deliveryServices.update', $service->id) }}"
                                                    data-service-json="{{ json_encode($service->toArray(), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) }}"
                                                    title="Chỉnh sửa">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-ds"
                                                    data-bs-toggle="modal" data-bs-target="#deleteDeliveryServiceModal"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-delete-url="{{ route('admin.system.deliveryServices.destroy', $service->id) }}"
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
                            {{ $deliveryServices->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@include('admin.system.partials.modals.modal_create_delivery_service')
@include('admin.system.partials.modals.modal_update_delivery_service')
@include('admin.system.partials.modals.modal_delete_delivery_service')
@include('admin.system.partials.modals.modal_view_delivery_service')

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/delivery_service_manager.js') }}"></script>
@endpush