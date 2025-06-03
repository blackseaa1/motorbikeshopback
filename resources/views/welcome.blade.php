<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(isset($showPendingMessage) && $showPendingMessage === true)
        <title>Chờ Phân Quyền - Admin Panel</title>
    @else
        <title>Laravel với Bootstrap</title>
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    @if(isset($showPendingMessage) && $showPendingMessage === true)
        <style>
            /* CSS chỉ áp dụng khi hiển thị thông báo chờ phân quyền */
            body.body-pending-auth {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background-color: #f8f9fa;
                /* Một màu nền nhẹ nhàng */
                margin: 0;
                font-family: 'Figtree', sans-serif;
            }

            .pending-auth-container {
                text-align: center;
                background-color: #ffffff;
                padding: 35px 45px;
                border-radius: 0.5rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
                max-width: 550px;
                width: calc(100% - 30px);
                /* Responsive với padding */
            }

            .pending-auth-container h1 {
                font-size: 1.75rem;
                /* Kích thước tiêu đề */
                color: #343a40;
                /* Màu tối hơn một chút */
                margin-bottom: 0.75rem;
            }

            .pending-auth-container p {
                font-size: 0.95rem;
                /* Kích thước chữ nội dung */
                color: #495057;
                /* Màu xám đậm */
                line-height: 1.65;
                margin-bottom: 0.5rem;
            }

            .pending-auth-container hr {
                margin-top: 1rem;
                margin-bottom: 1rem;
            }

            .pending-auth-container .btn-logout {
                margin-top: 1.25rem;
            }
        </style>
    @endif
</head>
{{-- Thêm class vào body nếu đang hiển thị thông báo chờ --}}

<body class="antialiased @if(isset($showPendingMessage) && $showPendingMessage === true) body-pending-auth @endif">

    @if(isset($showPendingMessage) && $showPendingMessage === true)
        {{-- NỘI DUNG THÔNG BÁO CHỜ PHÂN QUYỀN --}}
        <div class="pending-auth-container">
            <h1>Chào mừng bạn, {{ $adminUserName ?? 'Quản trị viên' }}!</h1>
            <p>Hiện tại, tài khoản của bạn chưa được phân quyền hạn truy cập các chức năng cụ thể.</p>
            <p>Vui lòng liên hệ với Quản trị viên (Super Admin) để được cấp quyền phù hợp.</p>
            <hr>
            <p><small>Sau khi được cấp quyền, bạn có thể cần phải đăng xuất và đăng nhập lại.</small></p>

            @if(Auth::guard('admin')->check() && Route::has('admin.logout'))
                <a href="{{ route('admin.logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form-pending-auth').submit();"
                    class="btn btn-primary btn-logout">
                    Đăng xuất
                </a>
                <form id="logout-form-pending-auth" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endif
        </div>
    @else
        {{-- NỘI DUNG WELCOME MẶC ĐỊNH --}}
        <div class="container mt-5">
            <h1>Xin chào, Bootstrap đã được cài đặt!</h1>
            <p>Đây là một ví dụ sử dụng các lớp của Bootstrap.</p>

            <div class="alert alert-success" role="alert">
                Một thông báo thành công đơn giản—hãy thử xem!
            </div>

            <button type="button" class="btn btn-primary">Nút Primary</button>
            <button type="button" class="btn btn-secondary">Nút Secondary</button>

            <div class="btn-group">
                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Dropdown Menu
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="#">Separated link</a></li>
                </ul>
            </div>
        </div>
    @endif

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>