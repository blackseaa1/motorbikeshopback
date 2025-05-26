@extends('admin.layouts.app')

@section('title', 'User Management') @section('content')
<header class="content-header">
    <h1><i class="bi bi-people-fill me-2"></i>Quản lý Tài khoản Quản trị viên</h1>
</header>

    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Quản lý Tài khoản Quản trị viên</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Admin mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tên</th>
                                <th scope="col">Email</th>
                                <th scope="col">Vai trò</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua dữ liệu từ controller ở đây --}}
                            <tr>
                                <td>1</td>
                                <td>Nguyễn Văn A</td>
                                <td>admin.a@example.com</td>
                                <td><span class="badge bg-success">Super Admin</span></td>
                                <td><span class="badge bg-success">Hoạt động</span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateAdminModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#lockAdminModal" title="Khóa tài khoản">
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Trần Thị B</td>
                                <td>admin.b@example.com</td>
                                <td><span class="badge bg-info text-dark">Editor</span></td>
                                <td><span class="badge bg-danger">Bị khóa</span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateAdminModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#unlockAdminModal" title="Mở khóa tài khoản">
                                        <i class="bi bi-unlock-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Kết thúc lặp --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals for Admin Management --}}
    <div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAdminModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Tài
                        khoản Admin mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- CSRF Token --}}
                        {{-- @csrf --}}
                        <div class="mb-3">
                            <label for="adminNameCreate" class="form-label">Tên Admin:</label>
                            <input type="text" class="form-control" id="adminNameCreate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminEmailCreate" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="adminEmailCreate" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminPhoneCreate" class="form-label">Số điện thoại:</label>
                            <input type="tel" class="form-control" id="adminPhoneCreate" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="adminPasswordCreate" class="form-label">Mật khẩu:</label>
                            <input type="password" class="form-control" id="adminPasswordCreate" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="adminRoleCreate" class="form-label">Vai trò:</label>
                            <select class="form-select" id="adminRoleCreate" name="role" required>
                                <option selected disabled value="">Chọn vai trò...</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button> {{-- Thay đổi type thành submit nếu
                    form được xử lý qua HTTP request --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateAdminModal" tabindex="-1" aria-labelledby="updateAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateAdminModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Tài
                        khoản Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- CSRF Token and Method Spoofing for PUT request --}}
                        {{-- @csrf --}}
                        {{-- @method('PUT') --}}
                        <div class="text-center mb-3">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=AD" alt="Ảnh đại diện Admin"
                                class="avatar-preview" id="adminAvatarPreviewUpdate">
                        </div>
                        <div class="mb-3">
                            <label for="adminNameUpdate" class="form-label">Tên Admin:</label>
                            <input type="text" class="form-control" id="adminNameUpdate" name="name" value="Nguyễn Văn A"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="adminEmailUpdate" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="adminEmailUpdate" name="email"
                                value="admin.a@example.com" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="adminPhoneUpdate" class="form-label">Số điện thoại:</label>
                            <input type="tel" class="form-control" id="adminPhoneUpdate" name="phone" value="0987654321">
                        </div>
                        <div class="mb-3">
                            <label for="adminAvatarUpdate" class="form-label">Ảnh đại diện:</label>
                            <input type="file" class="form-control" id="adminAvatarUpdate" name="img">
                        </div>
                        <div class="mb-3">
                            <label for="adminPasswordUpdate" class="form-label">Mật khẩu mới (để trống nếu không
                                đổi):</label>
                            <input type="password" class="form-control" id="adminPasswordUpdate" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="adminRoleUpdate" class="form-label">Vai trò:</label>
                            <select class="form-select" id="adminRoleUpdate" name="role" required>
                                <option value="super_admin" selected>Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="lockAdminModal" tabindex="-1" aria-labelledby="lockAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="lockAdminModalLabel"><i class="bi bi-lock-fill me-2"></i>Xác nhận Khóa Tài
                        khoản Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn khóa tài khoản admin <strong>Nguyễn Văn A</strong> không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger">Khóa tài khoản</button> {{-- Cần form và logic để xử lý
                    --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="unlockAdminModal" tabindex="-1" aria-labelledby="unlockAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="unlockAdminModalLabel"><i class="bi bi-unlock-fill me-2"></i>Xác nhận Mở
                        Khóa Tài khoản Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn mở khóa tài khoản admin <strong>Trần Thị B</strong> không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success">Mở khóa</button> {{-- Cần form và logic để xử lý --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
@endpush