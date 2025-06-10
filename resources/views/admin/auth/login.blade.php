<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập Admin - Đồ án Web Đồ Chơi Xe</title>

    {{-- CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Bootstrap CSS (nếu bạn không dùng bản từ Vite/npm) --}}
    {{-- Common Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/library/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/library/bootstrap-select.min.css') }}">
    {{-- Chứa CSS cho #loading-overlay
    --}}

</head>

<body class="bg-light">

    {{-- 1. NHÚNG LOADING OVERLAY TOÀN CỤC --}}
    {{-- Giả định 'admin.layouts.partials.loading' là đường dẫn đúng --}}
    @include('admin.layouts.partials.loading')

    <div class="container-fluid d-flex align-items-center justify-content-center p-3 min-vh-100">
        <div class="card login-card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Đăng nhập hệ thống Quản trị</h4>
            </div>
            <div class="card-body p-4 p-md-5">

                {{-- 2. NHÚNG HỆ THỐNG THÔNG BÁO (CHO LỖI VALIDATION SERVER-SIDE VÀ SESSION MESSAGES) --}}
                {{-- Giả định 'admin.layouts.partials.messages' là đường dẫn đúng và đã được cấu hình để hiển thị
                session('success'), session('error'), $errors->all() --}}
                @include('admin.layouts.partials.messages')

                {{-- Alert fallback cho lỗi AJAX (nếu Modal không hoạt động hoặc là ưu tiên hiển thị lỗi AJAX tại đây)
                --}}
                <div id="loginErrorAlert" class="alert alert-danger d-none mt-3" role="alert">
                    {{-- Nội dung lỗi AJAX sẽ được JS chèn vào đây --}}
                </div>

                {{-- Form đăng nhập --}}
                <form id="loginForm" method="POST" action="{{ route('admin.auth.login') }}"
                    data-action="{{ route('admin.auth.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Địa chỉ Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="emailInput"
                            name="email" placeholder="nhapemail@example.com" required value="{{ old('email') }}"
                            autofocus>
                        {{-- Thông báo lỗi inline từ validation server-side (nếu không dùng @include('messages') ở trên
                        hoặc muốn hiển thị cụ thể) --}}
                        {{-- @error('email')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror --}}
                    </div>

                    <div class="mb-3"> {{-- Giảm margin bottom để gần hơn với checkbox --}}
                        <label for="passwordInput" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="passwordInput" name="password" placeholder="Nhập mật khẩu" required>
                        {{-- @error('password')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror --}}
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMeCheck" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="rememberMeCheck">Ghi nhớ tôi</label>
                        </div>
                        {{-- Tạm thời ẩn hoặc xóa link Quên mật khẩu nếu chưa làm --}}
                        {{-- <a href="#" class="text-decoration-none">Quên mật khẩu?</a> --}}
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Đăng nhập</button>
                </form>

                {{-- Liên kết đến trang đăng ký --}}
                <div class="text-center mt-4">
                    <p class="mb-0">Chưa có tài khoản? <a href="{{ route('admin.auth.register') }}"
                            class="text-decoration-none fw-medium">Đăng ký tại đây</a></p>
                </div>

            </div>
            <div class="card-footer text-center text-muted py-3">
                <small>&copy; {{ date('Y') }} Thành Đô Shop - Web Đồ Chơi Xe</small>
            </div>
        </div>
    </div>

    {{-- HTML CHO MODAL HIỂN THỊ LỖI AJAX TỪ login.js --}}
    <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="loginErrorModalLabel"><i
                            class="bi bi-exclamation-triangle-fill me-2"></i>Lỗi Đăng Nhập</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loginErrorModalBody">
                    {{-- Nội dung lỗi AJAX từ login.js sẽ được chèn vào đây --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets_admin/js/library/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_admin/js/library/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('assets_admin/js/admin_layout.js') }}"></script>
    <script src="{{ asset('assets_admin/js/login.js') }}"></script>

</body>

</html>