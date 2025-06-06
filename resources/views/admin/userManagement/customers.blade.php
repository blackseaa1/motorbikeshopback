@extends('admin.layouts.app')

@section('title', 'Quản lý Khách hàng')

@push('styles')
    <style>
        .profile-avatar-modal {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
        #customerDetailModal .table th {
            width: 180px;
            background-color: #f8f9fa;
        }
        .action-buttons .btn {
            margin-right: 4px;
        }
        .password-required-icon {
            color: #fd7e14;
            vertical-align: middle;
        }
        .row-inactive {
            background-color: #f8f9fa;
            opacity: 0.7;
        }
        .row-trashed {
            background-color: #ffebee !important;
        }
        .row-trashed td {
            color: #6c757d;
            text-decoration: line-through;
        }
    </style>
@endpush

@section('content')
    <div id="adminCustomersPage">
        {{-- Header --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-person-lines-fill me-2"></i>Quản lý Khách hàng</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý Người dùng</li>
                            <li class="breadcrumb-item active">Khách hàng</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0"><i class="bi bi-people-fill me-2"></i>Danh sách Khách hàng</h2>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal"><i class="bi bi-person-plus-fill me-1"></i> Tạo mới</button>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('admin.userManagement.customers.index') }}" class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-dark' }} me-2"><i class="bi bi-list-ul me-1"></i> Tất cả</a>
                            <a href="{{ route('admin.userManagement.customers.index', ['status' => 'trashed']) }}" class="btn btn-sm {{ $status === 'trashed' ? 'btn-dark' : 'btn-outline-dark' }}"><i class="bi bi-trash me-1"></i> Thùng rác</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th class="text-center">Ảnh</th>
                                        <th>Tên</th>
                                        <th>Email & SĐT</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-center" style="width: 22%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($customers as $customer)
                                        <tr id="customer-row-{{ $customer->id }}" class="{{ $customer->trashed() ? 'row-trashed' : (!$customer->isActive() ? 'row-inactive' : '') }}">
                                            <td>{{ $loop->iteration + $customers->firstItem() - 1 }}</td>
                                            <td class="text-center"><img src="{{ $customer->avatar_url }}" alt="Avatar" class="rounded-circle" width="40" height="40" style="object-fit: cover;"></td>
                                            <td><span class="customer-name">{{ $customer->name }}</span></td>
                                            <td>
                                                <div>{{ $customer->email }}</div>
                                                <small class="text-muted">{{ $customer->phone ?? 'N/A' }}</small>
                                            </td>
                                            <td class="text-center status-cell">
                                                @if($customer->trashed())
                                                    <span class="badge bg-danger">Trong thùng rác</span>
                                                @else
                                                    <span class="badge {{ $customer->status_badge_class }}">{{ $customer->status_text }}</span>
                                                    @if($customer->password_change_required)
                                                        <i class="bi bi-key-fill password-required-icon" title="Buộc đổi mật khẩu"></i>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center action-buttons">
                                                @if ($customer->trashed())
                                                    <button class="btn btn-success btn-sm btn-action btn-restore-customer" data-url="{{ route('admin.userManagement.customers.restore', $customer->id) }}" data-name="{{ $customer->name }}" title="Khôi phục"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                    <button class="btn btn-danger btn-sm btn-action btn-force-delete-customer" data-bs-toggle="modal" data-bs-target="#confirmForceDeleteModal" data-name="{{ $customer->name }}" data-delete-url="{{ route('admin.userManagement.customers.forceDelete', $customer->id) }}" title="Xóa vĩnh viễn"><i class="bi bi-trash-fill"></i></button>
                                                @else
                                                    <button class="btn btn-info btn-sm btn-action btn-view-customer" data-bs-toggle="modal" data-bs-target="#customerDetailModal" data-customer='{{ json_encode($customer) }}' title="Xem chi tiết"><i class="bi bi-eye-fill"></i></button>
                                                    <button class="btn btn-warning btn-sm btn-action btn-edit-customer" data-bs-toggle="modal" data-bs-target="#updateCustomerModal" data-customer='{{ json_encode($customer) }}' data-update-url="{{ route('admin.userManagement.customers.update', $customer->id) }}" title="Sửa"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-sm btn-action toggle-status-customer-btn {{ $customer->isActive() ? 'btn-secondary' : 'btn-success' }}" data-url="{{ route('admin.userManagement.customers.toggleStatus', $customer) }}" title="{{ $customer->isActive() ? 'Khóa' : 'Mở khóa' }}"><i class="bi {{ $customer->isActive() ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i></button>
                                                    <button class="btn btn-danger btn-sm btn-action btn-delete-customer" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-name="{{ $customer->name }}" data-delete-url="{{ route('admin.userManagement.customers.destroy', $customer->id) }}" title="Xóa"><i class="bi bi-trash"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center">Chưa có dữ liệu.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($customers->hasPages())
                            <div class="mt-3 d-flex justify-content-center">{{ $customers->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- =================================== MODALS =================================== --}}

    <div class="modal fade" id="customerDetailModal" tabindex="-1" aria-labelledby="customerDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="customerDetailModalLabel"><i class="bi bi-person-badge me-2"></i>Chi tiết Khách hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 text-center mb-3 mb-md-0">
                            <img src="" alt="Avatar" id="detailAvatar" class="img-fluid rounded-circle profile-avatar-modal mb-2 shadow-sm">
                            <h4 id="detailNameDisplay" class="mb-1"></h4><p id="detailEmailDisplay" class="text-muted small"></p>
                        </div>
                        <div class="col-lg-8 col-md-8">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr><th><i class="bi bi-key-fill me-2 text-secondary"></i>ID Khách hàng</th><td id="detailId"></td></tr>
                                    <tr><th><i class="bi bi-person-fill me-2 text-secondary"></i>Họ và Tên</th><td id="detailName"></td></tr>
                                    <tr><th><i class="bi bi-envelope-fill me-2 text-secondary"></i>Email</th><td id="detailEmail"></td></tr>
                                    <tr><th><i class="bi bi-telephone-fill me-2 text-secondary"></i>Số điện thoại</th><td id="detailPhone"></td></tr>
                                    <tr><th><i class="bi bi-check-circle-fill me-2 text-secondary"></i>Trạng thái</th><td><span id="detailStatusBadge" class="badge"></span></td></tr>
                                    <tr><th><i class="bi bi-shield-key-fill me-2 text-secondary"></i>Bắt buộc đổi MK</th><td id="detailPasswordRequired"></td></tr>
                                    <tr><th><i class="bi bi-calendar-plus-fill me-2 text-secondary"></i>Ngày tạo</th><td id="detailCreatedAt"></td></tr>
                                    <tr><th><i class="bi bi-calendar-check-fill me-2 text-secondary"></i>Cập nhật cuối</th><td id="detailUpdatedAt"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-warning" id="editFromDetailBtn" style="display: none;"> {{-- Ẩn mặc định --}}
                        <i class="bi bi-pencil-square"></i> Chỉnh sửa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <form id="createCustomerForm" action="{{ route('admin.userManagement.customers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="createCustomerModalLabel">Tạo Khách hàng mới</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3"><i class="bi bi-key-fill me-2"></i>Mật khẩu mặc định sẽ là <strong>12345</strong>. Khách hàng sẽ được yêu cầu đổi mật khẩu sau đó.</div>
                        <div class="text-center mb-3"><img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH" class="img-thumbnail rounded-circle" id="customerAvatarPreviewCreate" style="width:100px; height: 100px; object-fit: cover;"></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Tên <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" required><div class="invalid-feedback"></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" required><div class="invalid-feedback"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Điện thoại</label><input type="tel" class="form-control" name="phone"><div class="invalid-feedback"></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Trạng thái <span class="text-danger">*</span></label><select class="form-select" name="status" required><option value="active" selected>Hoạt động</option><option value="suspended">Bị khóa</option></select><div class="invalid-feedback"></div></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Ảnh đại diện</label><input type="file" class="form-control" name="img" accept="image/*" data-preview="customerAvatarPreviewCreate"><div class="invalid-feedback"></div></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Tạo Khách hàng</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="updateCustomerModal" tabindex="-1" aria-labelledby="updateCustomerModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <form id="updateCustomerForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="updateCustomerModalLabel">Cập nhật Khách hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="text-center mb-3"><img src="" class="img-thumbnail rounded-circle" id="customerAvatarPreviewUpdate" style="width:100px; height: 100px; object-fit: cover;"></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Tên <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" required><div class="invalid-feedback"></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email (Không đổi)</label><input type="email" class="form-control" name="email_display" readonly disabled></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Điện thoại</label><input type="tel" class="form-control" name="phone"><div class="invalid-feedback"></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Trạng thái <span class="text-danger">*</span></label><select class="form-select" name="status" required><option value="active">Hoạt động</option><option value="suspended">Bị khóa</option></select><div class="invalid-feedback"></div></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Ảnh đại diện mới</label><input type="file" class="form-control" name="img" accept="image/*" data-preview="customerAvatarPreviewUpdate"><div class="invalid-feedback"></div></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-warning btn-reset-password" data-url="" data-name=""><i class="bi bi-key-fill"></i> Reset Mật khẩu</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <form id="deleteCustomerForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="confirmDeleteModalLabel"><i class="bi bi-trash text-danger me-2"></i>Xác nhận Xóa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <p>Bạn có chắc muốn chuyển khách hàng <strong id="customerNameToDelete"></strong> vào thùng rác?</p>
                        <small class="text-muted">Hành động này có thể khôi phục được.</small>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-danger">Xác nhận</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="confirmForceDeleteModal" tabindex="-1" aria-labelledby="confirmForceDeleteModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <form id="forceDeleteCustomerForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white"><h5 class="modal-title" id="confirmForceDeleteModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận Xóa Vĩnh Viễn</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <p>Bạn có chắc muốn <strong class="text-danger">XÓA VĨNH VIỄN</strong> khách hàng <strong id="customerNameToForceDelete"></strong>?</p>
                        <p class="fw-bold text-danger">Hành động này không thể hoàn tác!</p>
                        <div class="mb-3">
                            <label for="adminPasswordConfirmForceDelete" class="form-label">Nhập mật khẩu của bạn để xác nhận:<span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="adminPasswordConfirmForceDelete" name="admin_password_confirm_delete" required autocomplete="current-password">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-danger">Tôi hiểu, Xóa vĩnh viễn</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="confirmActionModalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p id="confirmActionMessage"></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="button" class="btn btn-primary" id="confirmActionButton"></button></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/customer_manager.js') }}"></script>
@endpush