@extends('admin.layouts.app')

@section('title', 'User Management') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-people-fill me-2"></i>Quản lý Khách hàng</h1>
    </header>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-people-fill me-2"></i>Quản lý Khách hàng</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Tạo Khách hàng mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Ảnh đại diện</th>
                                <th scope="col">Tên</th>
                                <th scope="col">Email</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua dữ liệu từ controller ở đây --}}
                            <tr>
                                <td>101</td>
                                <td><img src="https://placehold.co/40x40/EFEFEF/AAAAAA&text=KH" alt="Ảnh đại diện"
                                        class="rounded-circle" width="40" height="40"></td>
                                <td>Phạm Văn C</td>
                                <td>customer.c@example.com</td>
                                <td>0901234567</td>
                                <td><span class="badge bg-success">Hoạt động</span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateCustomerModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#lockCustomerModal" title="Khóa tài khoản">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>102</td>
                                <td><img src="https://placehold.co/40x40/EFEFEF/AAAAAA&text=KH" alt="Ảnh đại diện"
                                        class="rounded-circle" width="40" height="40"></td>
                                <td>Lê Thị D</td>
                                <td>customer.d@example.com</td>
                                <td>0987654321</td>
                                <td><span class="badge bg-danger">Bị khóa</span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateCustomerModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#unlockCustomerModal" title="Mở khóa tài khoản">
                                        <i class="bi bi-unlock"></i>
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

    {{-- Modals for Customer Management --}}
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCustomerModalLabel"><i class="bi bi-person-plus-fill me-2"></i>Tạo Tài
                        khoản Khách hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- @csrf --}}
                        <div class="text-center mb-3">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH" alt="Ảnh đại diện Khách hàng"
                                class="avatar-preview" id="customerAvatarPreviewCreate">
                        </div>
                        <div class="mb-3">
                            <label for="customerNameCreate" class="form-label">Tên Khách hàng:</label>
                            <input type="text" class="form-control" id="customerNameCreate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerEmailCreate" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="customerEmailCreate" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerPhoneCreate" class="form-label">Số điện thoại:</label>
                            <input type="tel" class="form-control" id="customerPhoneCreate" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="customerAvatarCreate" class="form-label">Ảnh đại diện:</label>
                            <input type="file" class="form-control" id="customerAvatarCreate" name="img">
                        </div>
                        <div class="mb-3">
                            <label for="customerPasswordCreate" class="form-label">Mật khẩu:</label>
                            <input type="password" class="form-control" id="customerPasswordCreate" name="password"
                                required>
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

    <div class="modal fade" id="updateCustomerModal" tabindex="-1" aria-labelledby="updateCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCustomerModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                        Tài khoản Khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- @csrf --}}
                        {{-- @method('PUT') --}}
                        <div class="text-center mb-3">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH" alt="Ảnh đại diện Khách hàng"
                                class="avatar-preview" id="customerAvatarPreviewUpdate">
                        </div>
                        <div class="mb-3">
                            <label for="customerNameUpdate" class="form-label">Tên Khách hàng:</label>
                            <input type="text" class="form-control" id="customerNameUpdate" name="name" value="Phạm Văn C"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="customerEmailUpdate" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="customerEmailUpdate" name="email"
                                value="customer.c@example.com" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="customerPhoneUpdate" class="form-label">Số điện thoại:</label>
                            <input type="tel" class="form-control" id="customerPhoneUpdate" name="phone" value="0901234567">
                        </div>
                        <div class="mb-3">
                            <label for="customerAvatarUpdate" class="form-label">Ảnh đại diện mới:</label>
                            <input type="file" class="form-control" id="customerAvatarUpdate" name="img">
                        </div>
                        <div class="mb-3">
                            <label for="customerPasswordUpdate" class="form-label">Mật khẩu mới (để trống nếu không
                                đổi):</label>
                            <input type="password" class="form-control" id="customerPasswordUpdate" name="password">
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

    <div class="modal fade" id="lockCustomerModal" tabindex="-1" aria-labelledby="lockCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="lockCustomerModalLabel"><i class="bi bi-person-fill-lock me-2"></i>Xác nhận
                        Khóa Tài khoản Khách hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn khóa tài khoản khách hàng <strong>Phạm Văn C</strong> không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger">Khóa tài khoản</button> {{-- Cần form và logic để xử lý
                    --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="unlockCustomerModal" tabindex="-1" aria-labelledby="unlockCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="unlockCustomerModalLabel"><i class="bi bi-person-fill-unlock me-2"></i>Xác
                        nhận Mở Khóa Tài khoản Khách hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn mở khóa tài khoản khách hàng <strong>Lê Thị D</strong> không?
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