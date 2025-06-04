@extends('admin.layouts.app')

@section('title', 'Hồ Sơ Của Tôi')

@push('styles')
    {{-- NẾU BẠN ĐÃ GỘP CSS VÀO FILE STYLE.CSS CHUNG, HÃY XÓA HOẶC BÌNH LUẬN DÒNG NÀY --}}
    {{--
    <link rel="stylesheet" href="{{ asset('assets_admin/css/profile.css') }}"> --}}
@endpush

@section('content')
    {{-- THÊM THẺ DIV BAO BỌC VỚI ID MỚI --}}
    <div id="adminProfilePage">

        {{-- Element để truyền dữ liệu từ Blade sang JS --}}
        <div id="profilePageData"
            data-has-password-errors="{{ $errors->getBag('changePassword')->any() ? 'true' : 'false' }}"
            data-active-tab-hash="{{ session('active_tab_hash', '') }}">
        </div>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Hồ Sơ Của Tôi</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Hồ sơ</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                {{-- @include('admin.partials.messages') ĐÃ CÓ Ở LAYOUT CHÍNH app.blade.php --}}
                {{-- Nếu bạn vẫn muốn include ở đây thì cần xem xét lại cấu trúc messages --}}

                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-primary card-outline card-profile-avatar">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <form id="avatarUpdateForm" action="{{ route('admin.profile.updateAvatar') }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="profile-avatar-container">
                                            <label for="adminAvatarInput" class="cursor-pointer">
                                                <img class="profile-avatar-preview" id="adminAvatarPreview"
                                                    src="{{ $admin->img ? asset($admin->img) : 'https://placehold.co/150x150/001529/FFF?text=' . mb_substr(Auth::guard('admin')->user()->name, 0, 1) }}"
                                                    alt="{{ Auth::guard('admin')->user()->name }} profile picture">
                                                <span class="profile-avatar-edit-icon" title="Đổi ảnh đại diện"><i
                                                        class="bi bi-pencil-fill"></i></span>
                                            </label>
                                            <input type="file" name="avatar" id="adminAvatarInput" accept="image/*">
                                        </div>
                                        <button type="submit" id="submitAvatarButton" class="btn btn-primary btn-sm mt-3">
                                            <i class="bi bi-check-circle"></i>Lưu ảnh
                                        </button>
                                    </form>
                                </div>

                                <h3 class="profile-username text-center mt-3 mb-1">{{ $admin->name }}</h3>
                                <p class="text-muted text-center small mb-2">{{ $admin->email }}</p>
                                <p class="text-center mb-3">
                                    <span
                                        class="admin-role-badge role-{{ strtolower(str_replace([' ', '_'], '-', $admin->role ?: 'administrator')) }}">
                                        {{ $admin->role ? ucfirst(str_replace('_', ' ', $admin->role)) : 'Administrator' }}
                                    </span>
                                </p>

                                <ul class="list-group list-group-unbordered mt-3">
                                    <li class="list-group-item">
                                        <b>Điện thoại</b> <span
                                            class="float-end detail-value">{{ $admin->phone ?: 'Chưa cập nhật' }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Ngày tham gia</b> <span
                                            class="float-end detail-value">{{ $admin->created_at->format('d/m/Y') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card card-profile-settings">
                            <div class="card-header p-2">
                                <ul class="nav nav-tabs profile-tabs" id="profileTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="settings-tab-link" data-bs-toggle="tab"
                                            href="#settings" role="tab" aria-controls="settings" aria-selected="true">Cập
                                            nhật Thông tin</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="changePassword-tab-link" data-bs-toggle="tab"
                                            href="#changePassword" role="tab" aria-controls="changePassword"
                                            aria-selected="false">Đổi Mật khẩu</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content pt-3" id="profileTabContent">
                                    <div class="tab-pane fade show active" id="settings" role="tabpanel"
                                        aria-labelledby="settings-tab-link">
                                        <form class="form-horizontal" action="{{ route('admin.profile.updateInfo') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="_form_id" value="updateInfo">
                                            <div class="mb-3 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Họ và tên <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                        class="form-control @error('name', 'updateInfo') is-invalid @enderror"
                                                        id="inputName" name="name" placeholder="Họ và tên"
                                                        value="{{ old('name', $admin->name) }}" required>
                                                    @error('name', 'updateInfo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="inputEmail" class="col-sm-3 col-form-label">Email <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="email"
                                                        class="form-control @error('email', 'updateInfo') is-invalid @enderror"
                                                        id="inputEmail" name="email" placeholder="Email"
                                                        value="{{ old('email', $admin->email) }}" required>
                                                    @error('email', 'updateInfo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="inputPhone" class="col-sm-3 col-form-label">Số điện
                                                    thoại</label>
                                                <div class="col-sm-9">
                                                    <input type="text"
                                                        class="form-control @error('phone', 'updateInfo') is-invalid @enderror"
                                                        id="inputPhone" name="phone" placeholder="Số điện thoại"
                                                        value="{{ old('phone', $admin->phone) }}">
                                                    @error('phone', 'updateInfo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <div class="offset-sm-3 col-sm-9">
                                                    <button type="submit" class="btn btn-success"><i
                                                            class="bi bi-save"></i>Lưu
                                                        thay đổi</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="tab-pane fade" id="changePassword" role="tabpanel"
                                        aria-labelledby="changePassword-tab-link">
                                        <form class="form-horizontal" action="{{ route('admin.profile.changePassword') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="_form_id" value="changePassword">
                                            <div class="mb-3 row">
                                                <label for="current_password" class="col-sm-4 col-form-label">Mật khẩu hiện
                                                    tại
                                                    <span class="text-danger">*</span></label>
                                                <div class="col-sm-8">
                                                    <input type="password"
                                                        class="form-control @error('current_password', 'changePassword') is-invalid @enderror"
                                                        id="current_password" name="current_password" required>
                                                    @error('current_password', 'changePassword')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="new_password" class="col-sm-4 col-form-label">Mật khẩu mới <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-8">
                                                    <input type="password"
                                                        class="form-control @error('new_password', 'changePassword') is-invalid @enderror"
                                                        id="new_password" name="new_password" required>
                                                    @error('new_password', 'changePassword')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <ul class="password-requirements mt-1" id="passwordRequirements">
                                                        <li data-regex=".{8,}">Ít nhất 8 ký tự</li>
                                                        <li data-regex="[A-Z]">Ít nhất 1 chữ hoa</li>
                                                        <li data-regex="[a-z]">Ít nhất 1 chữ thường</li>
                                                        <li data-regex="[0-9]">Ít nhất 1 chữ số</li>
                                                        <li data-regex="[\W_]">Ít nhất 1 ký tự đặc biệt</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="new_password_confirmation" class="col-sm-4 col-form-label">Xác
                                                    nhận
                                                    mật khẩu mới <span class="text-danger">*</span></label>
                                                <div class="col-sm-8">
                                                    <input type="password" class="form-control"
                                                        id="new_password_confirmation" name="new_password_confirmation"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <div class="offset-sm-4 col-sm-8">
                                                    <button type="submit" class="btn btn-danger"><i
                                                            class="bi bi-key-fill"></i>Đổi mật khẩu</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div> {{-- KẾT THÚC THẺ DIV BAO BỌC --}}
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/profile.js') }}"></script>
@endpush