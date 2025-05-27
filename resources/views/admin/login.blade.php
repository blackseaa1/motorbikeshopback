<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Quan trọng: Thêm CSRF Token để JavaScript có thể sử dụng cho các request AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Đăng nhập</title>

    {{-- Các file CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/login.css') }}">
</head>

<body class="bg-light">
    {{-- THÊM VÀO ĐÂY --}}
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div class="container-fluid d-flex align-items-center justify-content-center p-3 min-vh-100">
        <div class="card login-card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Đăng nhập hệ thống Quản trị</h4>
            </div>
            <div class="card-body p-4 p-md-5">

                {{-- Nơi hiển thị thông báo lỗi chung từ AJAX --}}
                <div id="loginErrorAlert" class="alert alert-danger d-none" role="alert">
                    Email hoặc mật khẩu không chính xác. Vui lòng thử lại.
                </div>

                {{-- Nơi hiển thị lỗi validation nếu không dùng AJAX --}}
                @if ($errors->has('email') && !request()->expectsJson())
                    <div class="alert alert-danger" role="alert">
                        {{ $errors->first('email') }}
                    </div>
                @endif

                {{-- Vòng xoay chờ, sẽ được hiển thị bởi JavaScript --}}
                {{-- <div id="loadingSpinner" class="d-none text-center mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div> --}}

                {{-- Form đăng nhập --}}
                <form id="loginForm" method="POST" action="{{ route('admin.login') }}"
                    data-action="{{ route('admin.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Địa chỉ Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="emailInput"
                            name="email" placeholder="nhapemail@example.com" required value="{{ old('email') }}">
                    </div>

                    <div class="mb-4">
                        <label for="passwordInput" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="passwordInput" name="password" placeholder="Nhập mật khẩu" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMeCheck" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="rememberMeCheck">Ghi nhớ tôi</label>
                        </div>
                        <a href="#" class="text-decoration-none">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">Đăng nhập</button>
                </form>
            </div>
            <div class="card-footer text-center text-muted py-3">
                <small>&copy; {{ date('Y') }} Thành Đô Shop</small>
            </div>
        </div>
    </div>

    {{-- Các file JavaScript --}}
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_admin/js/login.js') }}"></script>

    {{-- Không còn khối
    <script>...</script> nội tuyến ở đây nữa --}}
</body>

</html>