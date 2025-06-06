@extends('admin.layouts.app')

@section('title', 'Quản lý Tài khoản Nhân viên')

@push('styles')
    {{-- CSS tùy chỉnh cho trang này --}}
    <style>
        .profile-avatar-modal {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
        #staffDetailModal .table th {
            width: 180px;
            background-color: #f8f9fa;
        }
        .action-buttons .btn {
            margin-right: 4px;
        }
    </style>
@endpush

@section('content')
    <div id="adminStaffsPage"
        data-is-super-admin="{{ Auth::guard('admin')->user()->isSuperAdmin() ? 'true' : 'false' }}"
        data-logged-in-user-id="{{ Auth::guard('admin')->id() }}">

        {{-- Bắt đầu: Khối Header và Breadcrumb mới --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-people-fill me-2"></i>Quản lý Nhân viên</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý Người dùng</li>
                            <li class="breadcrumb-item active">Nhân viên</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        {{-- Kết thúc: Khối Header và Breadcrumb mới --}}

        <section class="content">
            <div class="container-fluid">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Danh sách Nhân viên</h2>
                        @if(Auth::guard('admin')->user()->isSuperAdmin() || Auth::guard('admin')->user()->role == \App\Models\Admin::ROLE_ADMIN)
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                                <i class="bi bi-plus-circle-fill me-1"></i> Tạo Nhân viên mới
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($staffs->isEmpty())
                            <div class="alert alert-info" role="alert">
                                <i class="bi bi-info-circle me-2"></i>Hiện chưa có tài khoản nhân viên nào.
                            </div>
                        @else
                            <div class="table-responsive">
                                {{-- Bỏ class 'table-hover' và 'table-striped' để nền tbody màu trắng --}}
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width:5%">STT</th>
                                            <th scope="col" style="width:8%" class="text-center">Ảnh</th>
                                            <th scope="col" style="width:20%">Tên</th>
                                            <th scope="col" style="width:22%">Email</th>
                                            <th scope="col" style="width:15%">Vai trò</th>
                                            <th scope="col" class="text-center" style="width:10%">Trạng thái</th>
                                            <th scope="col" class="text-center" style="width:20%">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($staffs as $staff)
                                            <tr id="staff-row-{{ $staff->id }}" class="{{ !$staff->isActive() && $staff->role !== null ? 'row-inactive' : '' }} {{ $staff->role === null ? 'row-pending-role' : '' }}">
                                                <td>{{ ($staffs->currentPage() - 1) * $staffs->perPage() + $loop->iteration }}</td>
                                                <td class="text-center">
                                                    <img src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" class="img-thumbnail rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                </td>
                                                <td>{{ $staff->name }}</td>
                                                <td>{{ $staff->email }}</td>
                                                <td><span class="badge {{ $staff->role_badge_class }}">{{ $staff->role_name }}</span></td>
                                                <td class="text-center status-cell" id="staff-status-{{ $staff->id }}">
                                                    @if($staff->role === null)
                                                        <span class="badge bg-light text-dark">N/A</span>
                                                    @else
                                                        <span class="badge {{ $staff->status_badge_class }}">{{ $staff->status_text }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center action-buttons">
                                                    {{-- Nút Xem chi tiết (mở modal) --}}
                                                    <button type="button" class="btn btn-info btn-sm btn-action btn-view-staff"
                                                        data-bs-toggle="modal" data-bs-target="#staffDetailModal"
                                                        data-id="{{ $staff->id }}"
                                                        data-name="{{ $staff->name }}"
                                                        data-email="{{ $staff->email }}"
                                                        data-phone="{{ $staff->phone ?? 'Chưa cập nhật' }}"
                                                        data-role="{{ $staff->role ?? '' }}"
                                                        data-status="{{ $staff->status }}"
                                                        data-is-super-admin="{{ $staff->isSuperAdmin() ? 'true' : 'false' }}"
                                                        data-role-name="{{ $staff->role_name }}"
                                                        data-role-badge-class="{{ $staff->role_badge_class }}"
                                                        data-status-text="{{ $staff->role === null ? 'Chờ cấp quyền' : $staff->status_text }}"
                                                        data-status-badge-class="{{ $staff->role === null ? 'bg-light text-dark' : $staff->status_badge_class }}"
                                                        data-avatar-url="{{ $staff->avatar_url }}"
                                                        data-update-url="{{ route($routeName . '.update', $staff->id) }}"
                                                        data-reset-password-url="{{ route($routeName . '.resetPassword', $staff->id) }}"
                                                        data-created-at="{{ $staff->created_at ? $staff->created_at->format('H:i:s d/m/Y') : 'N/A' }}"
                                                        data-updated-at="{{ $staff->updated_at ? $staff->updated_at->format('H:i:s d/m/Y') : 'N/A' }}"
                                                        title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>

                                                    {{-- Nút Cập nhật --}}
                                                    @if(
                                                        Auth::guard('admin')->user()->isSuperAdmin() ||
                                                        (Auth::guard('admin')->user()->role === \App\Models\Admin::ROLE_ADMIN && !in_array($staff->role, [\App\Models\Admin::ROLE_SUPER_ADMIN, \App\Models\Admin::ROLE_ADMIN])) ||
                                                        Auth::guard('admin')->id() == $staff->id
                                                    )
                                                        <button type="button" class="btn btn-warning btn-sm btn-action btn-edit-staff"
                                                            data-bs-toggle="modal" data-bs-target="#updateAdminModal"
                                                            data-id="{{ $staff->id }}"
                                                            data-name="{{ $staff->name }}"
                                                            data-email="{{ $staff->email }}"
                                                            data-phone="{{ $staff->phone ?? '' }}"
                                                            data-role="{{ $staff->role ?? '' }}"
                                                            data-status="{{ $staff->status }}"
                                                            data-is-super-admin="{{ $staff->isSuperAdmin() ? 'true' : 'false' }}"
                                                            data-avatar-url="{{ $staff->avatar_url }}"
                                                            data-update-url="{{ route($routeName . '.update', $staff->id) }}"
                                                            data-reset-password-url="{{ route($routeName . '.resetPassword', $staff->id) }}"
                                                            title="Cập nhật">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                    @endif

                                                    {{-- Nút Đặt lại Mật khẩu (ngoài bảng) --}}
                                                    @if(Auth::guard('admin')->user()->isSuperAdmin() && !$staff->isSuperAdmin() && Auth::guard('admin')->id() != $staff->id)
                                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-action btn-reset-password"
                                                                data-url="{{ route($routeName . '.resetPassword', $staff->id) }}"
                                                                data-name="{{ $staff->name }}"
                                                                title="Đặt lại mật khẩu về mặc định (12345)">
                                                            <i class="bi bi-key-fill"></i>
                                                        </button>
                                                    @endif

                                                    {{-- Nút Khóa / Mở khóa --}}
                                                    @if($staff->role !== null && !$staff->isSuperAdmin() && Auth::guard('admin')->user()->isSuperAdmin() && Auth::guard('admin')->id() != $staff->id)
                                                        @if($staff->isActive())
                                                            <button type="button" class="btn btn-secondary btn-sm btn-action action-lock toggle-status-staff-btn"
                                                                data-id="{{ $staff->id }}" data-name="{{ $staff->name }}"
                                                                data-url="{{ route($routeName . '.toggleStatus', $staff->id) }}"
                                                                title="Khóa tài khoản này">
                                                                <i class="bi bi-lock-fill"></i>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-success btn-sm btn-action action-unlock toggle-status-staff-btn"
                                                                data-id="{{ $staff->id }}" data-name="{{ $staff->name }}"
                                                                data-url="{{ route($routeName . '.toggleStatus', $staff->id) }}"
                                                                title="Mở khóa tài khoản này">
                                                                <i class="bi bi-unlock-fill"></i>
                                                            </button>
                                                        @endif
                                                    @endif

                                                    {{-- Nút Xóa --}}
                                                    @if(Auth::guard('admin')->user()->isSuperAdmin() && !$staff->isSuperAdmin() && Auth::guard('admin')->id() != $staff->id)
                                                        <button type="button" class="btn btn-danger btn-sm btn-action btn-delete-staff"
                                                                data-bs-toggle="modal" data-bs-target="#confirmDeleteStaffModal"
                                                                data-id="{{ $staff->id }}" data-name="{{ $staff->name }}"
                                                                data-delete-url="{{ route($routeName . '.destroy', $staff->id) }}"
                                                                title="Xóa nhân viên">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($staffs instanceof \Illuminate\Pagination\LengthAwarePaginator && $staffs->hasPages())
                                <div class="mt-3 d-flex justify-content-center">{{ $staffs->links('pagination::bootstrap-5') }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ========================================================= --}}
    {{-- PHẦN CÁC MODALS --}}
    {{-- ========================================================= --}}

    {{-- Modal Chi tiết Nhân viên --}}
    <div class="modal fade" id="staffDetailModal" tabindex="-1" aria-labelledby="staffDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staffDetailModalLabel"><i class="bi bi-person-badge me-2"></i>Chi tiết Nhân viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 text-center mb-3 mb-md-0">
                            <img src="" alt="Avatar" id="detailAvatar" class="img-fluid rounded-circle profile-avatar-modal mb-2 shadow-sm">
                            <h4 id="detailNameDisplay" class="mb-1"></h4>
                            <p id="detailEmailDisplay" class="text-muted small"></p>
                            <span id="detailRoleNameDisplay" class="badge fs-6"></span>
                        </div>
                        <div class="col-lg-8 col-md-8">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr><th><i class="bi bi-key-fill me-2 text-secondary"></i>ID Tài khoản</th><td id="detailId"></td></tr>
                                    <tr><th><i class="bi bi-person-fill me-2 text-secondary"></i>Họ và Tên</th><td id="detailName"></td></tr>
                                    <tr><th><i class="bi bi-envelope-fill me-2 text-secondary"></i>Email</th><td id="detailEmail"></td></tr>
                                    <tr><th><i class="bi bi-telephone-fill me-2 text-secondary"></i>Số điện thoại</th><td id="detailPhone"></td></tr>
                                    <tr><th><i class="bi bi-shield-lock-fill me-2 text-secondary"></i>Vai trò</th><td><span id="detailRoleBadge" class="badge"></span></td></tr>
                                    <tr><th><i class="bi bi-check-circle-fill me-2 text-secondary"></i>Trạng thái</th><td><span id="detailStatusBadge" class="badge"></span></td></tr>
                                    <tr><th><i class="bi bi-calendar-plus-fill me-2 text-secondary"></i>Ngày tạo</th><td id="detailCreatedAt"></td></tr>
                                    <tr><th><i class="bi bi-calendar-check-fill me-2 text-secondary"></i>Cập nhật cuối</th><td id="detailUpdatedAt"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-warning" id="editFromDetailBtn">
                        <i class="bi bi-pencil-square"></i> Chỉnh sửa
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Admin Modal --}}
    @if(Auth::guard('admin')->user()->isSuperAdmin() || Auth::guard('admin')->user()->role == \App\Models\Admin::ROLE_ADMIN)
        <div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="createStaffForm" action="{{ route($routeName . '.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_form_identifier" value="create_staff_form">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createAdminModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Tài khoản Nhân viên mới</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- START: MODIFICATION --}}
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-key-fill me-2"></i>
                                Mật khẩu mặc định sẽ được đặt là <strong>12345</strong>. Nhân viên sẽ được yêu cầu đổi mật khẩu này trong lần đăng nhập đầu tiên.
                            </div>
                            {{-- END: MODIFICATION --}}
                            <div class="row">
                                <div class="col-md-6 mb-3"><label for="staffNameCreate" class="form-label">Họ và Tên:<span class="text-danger">*</span></label><input type="text" class="form-control @error('name', 'create_staff_form') is-invalid @enderror" id="staffNameCreate" name="name" value="{{ old('name') }}" required>@error('name', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                                <div class="col-md-6 mb-3"><label for="staffEmailCreate" class="form-label">Email:<span class="text-danger">*</span></label><input type="email" class="form-control @error('email', 'create_staff_form') is-invalid @enderror" id="staffEmailCreate" name="email" value="{{ old('email') }}" required>@error('email', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label for="staffPhoneCreate" class="form-label">Số điện thoại:</label><input type="tel" class="form-control @error('phone', 'create_staff_form') is-invalid @enderror" id="staffPhoneCreate" name="phone" value="{{ old('phone') }}">@error('phone', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                                <div class="col-md-6 mb-3"><label for="staffRoleCreate" class="form-label">Vai trò:<span class="text-danger">*</span></label><select class="form-select @error('role', 'create_staff_form') is-invalid @enderror" id="staffRoleCreate" name="role" required><option value="" disabled {{ old('role') ? '' : 'selected' }}>Chọn vai trò...</option>@foreach($availableRoles as $roleKey => $roleName)<option value="{{ $roleKey }}" {{ old('role') == $roleKey ? 'selected' : '' }}>{{ $roleName }}</option>@endforeach</select>@error('role', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                            </div>
                            {{-- START: MODIFICATION - Remove password fields --}}
                            {{--
                            <div class="row">
                                <div class="col-md-6 mb-3"><label for="staffPasswordCreate" class="form-label">Mật khẩu:<span class="text-danger">*</span></label><input type="password" class="form-control @error('password', 'create_staff_form') is-invalid @enderror" id="staffPasswordCreate" name="password" required autocomplete="new-password">@error('password', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                                <div class="col-md-6 mb-3"><label for="staffPasswordConfirmationCreate" class="form-label">Xác nhận Mật khẩu:<span class="text-danger">*</span></label><input type="password" class="form-control @error('password_confirmation', 'create_staff_form') is-invalid @enderror" id="staffPasswordConfirmationCreate" name="password_confirmation" required autocomplete="new-password">@error('password_confirmation', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                            </div>
                            --}}
                            {{-- END: MODIFICATION --}}
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3"><label for="staffAvatarCreate" class="form-label">Ảnh đại diện:</label><input type="file" class="form-control @error('img', 'create_staff_form') is-invalid @enderror" id="staffAvatarCreate" name="img" accept="image/*">@error('img', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                                <div class="col-md-6 mb-3 text-center"><img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Avatar" alt="Xem trước Avatar" id="staffAvatarPreviewCreate" class="img-thumbnail rounded-circle" style="width:100px; height: 100px; object-fit: cover;" data-default-src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Avatar"></div>
                            </div>
                            <div class="mb-3"><label for="staffStatusCreate" class="form-label">Trạng thái:<span class="text-danger">*</span></label><select class="form-select @error('status', 'create_staff_form') is-invalid @enderror" id="staffStatusCreate" name="status" required><option value="{{ App\Models\Admin::STATUS_ACTIVE }}" {{ old('status', App\Models\Admin::STATUS_ACTIVE) == App\Models\Admin::STATUS_ACTIVE ? 'selected' : '' }}>Hoạt động</option><option value="{{ App\Models\Admin::STATUS_SUSPENDED }}" {{ old('status') == App\Models\Admin::STATUS_SUSPENDED ? 'selected' : '' }}>Khóa (Tạm ngưng)</option></select>@error('status', 'create_staff_form') <div class="invalid-feedback">{{$message}}</div> @enderror</div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button><button type="submit" class="btn btn-primary">Tạo Nhân viên</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Update Admin Modal --}}
    <div class="modal fade" id="updateAdminModal" tabindex="-1" aria-labelledby="updateAdminModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateStaffForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form_identifier" value="update_staff_form">
                    <input type="hidden" name="staff_id_for_update_modal" id="staffIdForUpdateModalInput">

                    <div class="modal-header">
                        <h5 class="modal-title" id="updateAdminModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Tài khoản Nhân viên</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=AD" alt="Ảnh đại diện Admin" id="staffAvatarPreviewUpdate" class="img-thumbnail rounded-circle" style="width:100px; height: 100px; object-fit: cover;" data-default-src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=AD">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="staffNameUpdate" class="form-label">Họ và Tên:<span class="text-danger">*</span></label><input type="text" class="form-control" id="staffNameUpdate" name="name" required><div class="invalid-feedback" id="staffNameUpdateError"></div></div>
                            <div class="col-md-6 mb-3"><label for="staffEmailUpdate" class="form-label">Email: (Không thể thay đổi)</label><input type="email" class="form-control" id="staffEmailUpdate" name="email_display" readonly disabled></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="staffPhoneUpdate" class="form-label">Số điện thoại:</label><input type="tel" class="form-control" id="staffPhoneUpdate" name="phone"><div class="invalid-feedback" id="staffPhoneUpdateError"></div></div>
                            <div class="col-md-6 mb-3"><label for="staffRoleUpdate" class="form-label">Vai trò:<span class="text-danger">*</span></label><select class="form-select" id="staffRoleUpdate" name="role" required><option value="" disabled>Chọn vai trò...</option>@foreach($availableRoles as $roleKey => $roleName)<option value="{{ $roleKey }}">{{ $roleName }}</option>@endforeach</select><div class="invalid-feedback" id="staffRoleUpdateError"></div></div>
                        </div>
                        <div class="mb-3">
                            <label for="staffAvatarUpdate" class="form-label">Ảnh đại diện mới (để trống nếu không đổi):</label>
                            <input type="file" class="form-control" id="staffAvatarUpdate" name="img" accept="image/*">
                            <div class="invalid-feedback" id="staffImgUpdateError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="staffStatusUpdate" class="form-label">Trạng thái:<span class="text-danger">*</span></label>
                            <select class="form-select" id="staffStatusUpdate" name="status" required><option value="{{ \App\Models\Admin::STATUS_ACTIVE }}">Hoạt động</option><option value="{{ \App\Models\Admin::STATUS_SUSPENDED }}">Khóa (Tạm ngưng)</option></select>
                            <div class="invalid-feedback" id="staffStatusUpdateError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-warning btn-reset-password" id="resetPasswordFromModalBtn">
                            <i class="bi bi-key-fill"></i> Đặt lại mật khẩu
                        </button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Confirm Delete Staff Modal --}}
    <div class="modal fade" id="confirmDeleteStaffModal" tabindex="-1" aria-labelledby="confirmDeleteStaffModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <form id="deleteStaffForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="confirmDeleteStaffModalLabel"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Xác nhận Xóa Nhân viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa vĩnh viễn nhân viên <strong id="staffNameToDeleteInModal"></strong>?</p>
                        <p class="text-danger fw-bold">Hành động này không thể hoàn tác.</p>
                        <div class="mb-3"><label for="adminPasswordConfirmDelete" class="form-label">Nhập mật khẩu của bạn để xác nhận:<span class="text-danger">*</span></label><input type="password" class="form-control" id="adminPasswordConfirmDelete" name="admin_password_confirm_delete" required autocomplete="current-password"><div class="invalid-feedback" id="adminPasswordConfirmDeleteError"></div></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-danger">Xác nhận Xóa</button></div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Xác nhận Hành động Chung --}}
    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="confirmActionModalLabel">Xác nhận Hành động</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><p id="confirmActionMessage"></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="button" class="btn btn-primary" id="confirmActionButton">Đồng ý</button></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/staff_manager.js') }}"></script>
@endpush