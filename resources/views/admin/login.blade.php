<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="{{ asset('assets_admin/css/login.css') }}">
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid d-flex align-items-center justify-content-center p-3">
        <div class="card login-card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Đăng nhập hệ thống Quản trị</h4>
            </div>
            <div class="card-body p-4 p-md-5">
                <div id="loginErrorAlert" class="alert alert-danger d-none" role="alert">
                    Email hoặc mật khẩu không chính xác. Vui lòng thử lại.
                </div>

                <form id="loginForm" method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Địa chỉ Email</label>
                        <input type="email" class="form-control" id="emailInput" placeholder="nhapemail@example.com"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="passwordInput" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="passwordInput" placeholder="Nhập mật khẩu"
                            required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMeCheck">
                            <label class="form-check-label" for="rememberMeCheck">Ghi nhớ tôi</label>
                        </div>
                        <a href="#" class="text-decoration-none">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">Đăng nhập</button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted">Chưa có tài khoản? <a href="#" class="text-decoration-none">Đăng ký ngay</a>
                    </p>
                </div>
            </div>
            <div class="card-footer text-center text-muted py-3">
                <small>&copy; 2025 Thành Đô Shop</small>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_admin/js/login.js') }}"></script>
</body>

</html>