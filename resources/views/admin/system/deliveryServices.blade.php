@extends('admin.layouts.app')

@section('title', 'Quản lý Đơn vị Giao hàng')

@section('content')
<div id="adminDeliveryServicesPage"> {{-- ID cho trang để JS có thể scope nếu cần --}}
    {{-- Content Header --}}
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

    {{-- Main Content --}}
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
                                        <th scope="col" class="text-end">Phí Giao hàng (VNĐ)</th>
                                        <th scope="col" class="text-center" style="width:10%">Trạng thái</th>
                                        <th scope="col" class="text-center" style="width: 25%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deliveryServices as $service)
                                        <tr id="ds-row-{{ $service->id }}" class="{{ !$service->isActive() ? 'row-inactive' : '' }}">
                                            <td>{{ $deliveryServices->firstItem() + $loop->index }}</td>
                                            <td class="text-center">
                                                <img src="{{ $service->logo_url ? Storage::url($service->logo_url) : 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' }}"
                                                    alt="{{ $service->name }}" class="img-thumbnail"
                                                    style="max-width: 80px; max-height: 40px; object-fit: contain;">
                                            </td>
                                            <td>{{ $service->name }}</td>
                                            <td class="text-end">{{ number_format($service->shipping_fee, 0, ',', '.') }}</td>
                                            <td class="text-center status-cell" id="ds-status-{{ $service->id }}">
                                                @if ($service->isActive())
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Đã ẩn</span>
                                                @endif
                                            </td>
                                            <td class="text-center action-buttons">
                                                <button type="button" class="btn btn-sm btn-success btn-view-ds"
                                                    data-bs-toggle="modal" data-bs-target="#viewDeliveryServiceModal"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-logo-url="{{ $service->logo_url ? Storage::url($service->logo_url) : 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' }}"
                                                    data-shipping-fee="{{ $service->shipping_fee }}"
                                                    data-status="{{ $service->status }}"
                                                    data-created-at="{{ $service->created_at->format('H:i:s d/m/Y') }}"
                                                    data-updated-at="{{ $service->updated_at->format('H:i:s d/m/Y') }}"
                                                    data-update-url="{{ route('admin.system.deliveryServices.update', $service->id) }}"
                                                    title="Xem chi tiết">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary toggle-status-btn"
                                                    data-id="{{ $service->id }}"
                                                    data-url="{{ route('admin.system.deliveryServices.toggleStatus', $service->id) }}"
                                                    title="{{ $service->isActive() ? 'Ẩn đơn vị này' : 'Hiển thị đơn vị này' }}">
                                                    <i class="bi {{ $service->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info btn-edit-ds" data-bs-toggle="modal"
                                                    data-bs-target="#updateDeliveryServiceModal"
                                                    data-id="{{ $service->id }}"
                                                    data-name="{{ $service->name }}"
                                                    data-logo-url="{{ $service->logo_url ? Storage::url($service->logo_url) : 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' }}"
                                                    data-shipping-fee="{{ $service->shipping_fee }}"
                                                    data-status="{{ $service->status }}"
                                                    data-update-url="{{ route('admin.system.deliveryServices.update', $service->id) }}"
                                                    title="Cập nhật">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete-ds" data-bs-toggle="modal"
                                                    data-bs-target="#deleteDeliveryServiceModal"
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
                        {{-- Phân trang --}}
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $deliveryServices->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

{{-- MODALS EMBEDDED DIRECTLY --}}

{{-- Create DeliveryService Modal --}}
<div class="modal fade" id="createDeliveryServiceModal" tabindex="-1" aria-labelledby="createDeliveryServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createDeliveryServiceForm" action="{{ route('admin.system.deliveryServices.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_form_marker" value="create_delivery_service"> {{-- Để JS mở lại modal nếu có lỗi validation server khi submit truyền thống --}}
                <div class="modal-header">
                    <h5 class="modal-title" id="createDeliveryServiceModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Thêm Đơn vị Giao hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dsNameCreate" class="form-label">Tên Đơn vị Giao hàng:<span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name','_form_marker') is-invalid @enderror" id="dsNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name','_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="invalid-feedback" id="dsNameCreateError"></div> {{-- For AJAX validation --}}
                    </div>
                    <div class="mb-3">
                        <label for="dsShippingFeeCreate" class="form-label">Phí Giao hàng (VNĐ):<span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('shipping_fee','_form_marker') is-invalid @enderror" id="dsShippingFeeCreate" name="shipping_fee" value="{{ old('shipping_fee') }}" step="1000" min="0" required placeholder="VD: 30000">
                        @error('shipping_fee','_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="invalid-feedback" id="dsShipping_feeCreateError"></div> {{-- For AJAX validation --}}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dsLogoCreate" class="form-label">Logo Đơn vị Giao hàng:</label>
                                <input type="file" class="form-control @error('logo_url','_form_marker') is-invalid @enderror" id="dsLogoCreate" name="logo_url" accept="image/*">
                                @error('logo_url','_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="invalid-feedback" id="dsLogo_urlCreateError"></div> {{-- For AJAX validation --}}
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Preview" alt="Xem trước logo" id="dsLogoPreviewCreate" class="img-thumbnail mt-2" style="max-width: 100px; max-height: 50px; object-fit: contain;" data-default-src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Preview">
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="dsStatusCreate" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status','_form_marker') is-invalid @enderror" id="dsStatusCreate" name="status" required>
                            <option value="{{ \App\Models\DeliveryService::STATUS_ACTIVE }}" {{ old('status', \App\Models\DeliveryService::STATUS_ACTIVE) == \App\Models\DeliveryService::STATUS_ACTIVE ? 'selected' : '' }}>Hoạt động</option>
                            <option value="{{ \App\Models\DeliveryService::STATUS_INACTIVE }}" {{ old('status') == \App\Models\DeliveryService::STATUS_INACTIVE ? 'selected' : '' }}>Đã ẩn</option>
                        </select>
                        @error('status','_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="invalid-feedback" id="dsStatusCreateError"></div> {{-- For AJAX validation --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Đơn vị</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Update DeliveryService Modal --}}
<div class="modal fade" id="updateDeliveryServiceModal" tabindex="-1" aria-labelledby="updateDeliveryServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="updateDeliveryServiceForm" method="POST" enctype="multipart/form-data"> {{-- action sẽ được JS đặt --}}
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateDeliveryServiceModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Đơn vị Giao hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dsNameUpdate" class="form-label">Tên Đơn vị Giao hàng:<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dsNameUpdate" name="name" required>
                        <div class="invalid-feedback" id="dsNameUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="dsShippingFeeUpdate" class="form-label">Phí Giao hàng (VNĐ):<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="dsShippingFeeUpdate" name="shipping_fee" step="1000" min="0" required>
                        <div class="invalid-feedback" id="dsShipping_feeUpdateError"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label for="dsLogoUpdate" class="form-label">Logo mới (để trống nếu không đổi):</label>
                                <input type="file" class="form-control" id="dsLogoUpdate" name="logo_url" accept="image/*">
                                <div class="invalid-feedback" id="dsLogo_urlUpdateError"></div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current" alt="Logo hiện tại" id="dsLogoPreviewUpdate" class="img-thumbnail mt-2" style="max-width: 100px; max-height: 50px; object-fit: contain;" data-default-src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current">
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="dsStatusUpdate" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select" id="dsStatusUpdate" name="status" required>
                            <option value="{{ \App\Models\DeliveryService::STATUS_ACTIVE }}">Hoạt động</option>
                            <option value="{{ \App\Models\DeliveryService::STATUS_INACTIVE }}">Đã ẩn</option>
                        </select>
                        <div class="invalid-feedback" id="dsStatusUpdateError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete DeliveryService Modal --}}
<div class="modal fade" id="deleteDeliveryServiceModal" tabindex="-1" aria-labelledby="deleteDeliveryServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteDeliveryServiceForm" method="POST"> {{-- action sẽ được JS đặt --}}
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteDeliveryServiceModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa Đơn vị Giao hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa đơn vị "<strong id="dsNameToDelete"></strong>"?</p>
                    @if(Config::get('admin.deletion_password')) {{-- Sử dụng key config bạn đã đặt --}}
                    <div class="mb-3 mt-3">
                        <label for="dsDeletionPassword" class="form-label">Nhập Mật khẩu Xóa Chung:<span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="dsDeletionPassword" name="deletion_password" required autocomplete="new-password">
                        <div class="invalid-feedback" id="dsDeletionPasswordError"></div>
                    </div>
                    @endif
                    <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Đơn vị</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View DeliveryService Modal --}}
<div class="modal fade" id="viewDeliveryServiceModal" tabindex="-1" aria-labelledby="viewDeliveryServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDeliveryServiceModalLabel"><i class="bi bi-truck me-2"></i>Chi tiết Đơn vị Giao hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="dsLogoView" src="https://placehold.co/150x75/EFEFEF/AAAAAA&text=LOGO" alt="Logo Đơn vị" class="img-thumbnail mb-3" style="max-height: 75px; max-width: 150px; object-fit: contain;">
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-4">ID:</dt>
                            <dd class="col-sm-8" id="dsIdView">-</dd>
                            <dt class="col-sm-4">Tên Đơn vị:</dt>
                            <dd class="col-sm-8" id="dsNameView">-</dd>
                            <dt class="col-sm-4">Phí Giao hàng:</dt>
                            <dd class="col-sm-8" id="dsShippingFeeView">-</dd>
                            <dt class="col-sm-4">Trạng thái:</dt>
                            <dd class="col-sm-8" id="dsStatusViewText">-</dd>
                            <dt class="col-sm-4">Ngày tạo:</dt>
                            <dd class="col-sm-8" id="dsCreatedAtView">-</dd>
                            <dt class="col-sm-4">Cập nhật lần cuối:</dt>
                            <dd class="col-sm-8" id="dsUpdatedAtView">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="editDeliveryServiceFromViewButton">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>
{{-- END MODALS --}}

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/delivery_service_manager.js') }}"></script>
    <script>
        // Script để mở lại modal create nếu có lỗi validation từ server (khi submit truyền thống)
        @if ($errors->any() && old('_form_marker') === 'create_delivery_service')
            document.addEventListener('DOMContentLoaded', function () {
                const createModal = document.getElementById('createDeliveryServiceModal');
                if (createModal) {
                    new bootstrap.Modal(createModal).show();
                }
            });
        @endif
    </script>
@endpush