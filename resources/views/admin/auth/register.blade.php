<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng ký tài khoản Admin - Đồ án Web Đồ Chơi Xe</title>

    {{-- CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/style.css') }}">

    {{--
    <link rel="stylesheet" href="{{ asset('assets_admin/css/register.css') }}"> --}}
    <style>

    </style>
</head>

<body class="bg-light">

    @include('admin.layouts.partials.loading') {{-- Loading overlay --}}

    <div class="container-fluid d-flex align-items-center justify-content-center p-3 min-vh-100">
        <div class="card register-card shadow-sm">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0">Đăng ký tài khoản Quản trị</h4>
            </div>
            <div class="card-body p-4 p-md-5">

                <div id="registerErrorAlert" class="alert alert-danger d-none mt-3" role="alert">
                    [Lỗi AJAX sẽ hiển thị ở đây]
                </div>

                <form id="registerForm" method="POST" action="{{ route('admin.auth.register') }}"
                    data-action="{{ route('admin.auth.register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput"
                            name="name" placeholder="Ví dụ: Văn A" required value="{{ old('name') }}" autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Địa chỉ Email <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="emailInput"
                            name="email" placeholder="email@example.com" required value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phoneInput" class="form-label">Số điện thoại (Tùy chọn)</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phoneInput"
                            name="phone" placeholder="Ví dụ: 09XXXXXXXX" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Mật khẩu <span
                                class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="passwordInput" name="password" placeholder="Nhập mật khẩu" required
                            aria-describedby="passwordRequirements">
                        {{-- Thay thế passwordHelpBlock bằng danh sách các yêu cầu --}}
                        <div id="passwordRequirements" class="mt-2">
                            <small id="lengthReq" class="d-block requirement-item">Ít nhất 8 ký tự</small>
                            <small id="uppercaseReq" class="d-block requirement-item">Ít nhất 1 chữ hoa (A-Z)</small>
                            <small id="lowercaseReq" class="d-block requirement-item">Ít nhất 1 chữ thường (a-z)</small>
                            <small id="numberReq" class="d-block requirement-item">Ít nhất 1 chữ số (0-9)</small>
                            <small id="symbolReq" class="d-block requirement-item">Ít nhất 1 ký tự đặc biệt (ví dụ:
                                !@#$%...)</small>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{-- d-block để không bị ẩn bởi passwordRequirements --}}
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="passwordConfirmationInput" class="form-label">Xác nhận Mật khẩu <span
                                class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="passwordConfirmationInput"
                            name="password_confirmation" placeholder="Nhập lại mật khẩu" required
                            aria-describedby="passwordConfirmationHelp">
                        <div id="passwordConfirmationHelp" class="form-text mt-1"></div> {{-- Hiển thị thông báo
                        khớp/không khớp --}}
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">Đăng ký</button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Đã có tài khoản? <a href="{{ route('admin.auth.login') }}"
                            class="text-decoration-none fw-medium">Đăng nhập tại đây</a></p>
                </div>

            </div>
            <div class="card-footer text-center text-muted py-3">
                <small>&copy; {{ date('Y') }} Thành Đô Shop - Web Đồ Chơi Xe</small>
            </div>
        </div>
    </div>

    {{-- Modal hiển thị lỗi AJAX --}}
    <div class="modal fade" id="registerErrorModal" tabindex="-1" aria-labelledby="registerErrorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="registerErrorModalLabel"><i
                            class="bi bi-exclamation-triangle-fill me-2"></i>Lỗi Đăng Ký</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="registerErrorModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets_admin/js/admin_layout.js') }}"></script> {{-- Thêm dòng này --}}
    <script src="{{ asset('assets_admin/js/register.js') }}"></script>
</body>

</html>