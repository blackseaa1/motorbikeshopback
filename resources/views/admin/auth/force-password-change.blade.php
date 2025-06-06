<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo Mật Khẩu Mới - Đồ án Web Đồ Chơi Xe</title>

    {{-- CSS (Sử dụng chung các file CSS với trang đăng ký) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">
</head>

<body class="bg-light">

    @include('admin.layouts.partials.loading') {{-- Loading overlay --}}

    <div class="container-fluid d-flex align-items-center justify-content-center p-3 min-vh-100">
        <div class="card register-card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Tạo Mật Khẩu Mới</h4>
            </div>
            <div class="card-body p-4 p-md-5">

                {{-- Thêm phần hiển thị lỗi từ session nếu có --}}
                @include('admin.layouts.partials.messages')

                <p class="text-center text-muted mb-4">
                    Vì lý do bảo mật, bạn cần tạo một mật khẩu mới để tiếp tục.
                    <br>
                    Mật khẩu tạm thời của bạn là <strong>12345</strong>.
                </p>

                <form method="POST" action="{{ route('admin.auth.forcePasswordChange') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu tạm thời <span
                                class="text-danger">*</span></label>
                        <input id="current_password" type="password"
                            class="form-control @error('current_password') is-invalid @enderror" name="current_password"
                            placeholder="Nhập mật khẩu được cấp" required autofocus>
                        @error('current_password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Nhập mật khẩu mới" required
                            aria-describedby="passwordRequirements">

                        {{-- Hiển thị các yêu cầu mật khẩu --}}
                        <div id="passwordRequirements" class="mt-2">
                            <small class="d-block text-muted">Mật khẩu mới phải bao gồm:</small>
                            <small class="d-block requirement-item ps-3">&bull; Ít nhất 8 ký tự</small>
                            <small class="d-block requirement-item ps-3">&bull; Ít nhất 1 chữ hoa (A-Z) và 1 chữ thường
                                (a-z)</small>
                            <small class="d-block requirement-item ps-3">&bull; Ít nhất 1 chữ số (0-9)</small>
                            <small class="d-block requirement-item ps-3">&bull; Ít nhất 1 ký tự đặc biệt (!, @, #, $,
                                ...)</small>
                        </div>

                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Xác nhận Mật khẩu mới <span
                                class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" placeholder="Nhập lại mật khẩu mới" required>
                        {{-- Thêm dòng này để JS có thể chèn thông báo lỗi --}}
                        <div class="invalid-feedback">Mật khẩu xác nhận không khớp.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Xác nhận và Tiếp tục</button>
                </form>

                <div class="text-center mt-4">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="text-decoration-none fw-medium">Đăng xuất</a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf
                    </form>
                </div>

            </div>
            <div class="card-footer text-center text-muted py-3">
                <small>&copy; {{ date('Y') }} Thành Đô Shop - Web Đồ Chơi Xe</small>
            </div>
        </div>
    </div>

    {{-- NẠP SCRIPT CỦA TRANG NÀY --}}
    <script src="{{ asset('assets_admin/js/force_password_change.js') }}"></script>
</body>

</html>