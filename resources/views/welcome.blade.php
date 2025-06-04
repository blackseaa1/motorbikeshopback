<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chờ Phân Quyền - Admin Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Bootstrap CSS (nếu bạn không dùng bản từ Vite/npm) --}}
    {{-- Common Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">
    {{-- Chứa CSS cho #loading-overlay --}}
    @if(isset($showPendingMessage) && $showPendingMessage === true)
        <style>
            /* CSS chỉ áp dụng khi hiển thị thông báo chờ phân quyền */
            body.body-pending-auth {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background-color: #34495e;
                /* Màu nền xanh xám đậm */
                margin: 0;
                font-family: 'Figtree', sans-serif;
                overflow: hidden;
                position: relative;
            }

            .pending-auth-container {
                text-align: center;
                background-color: #ffffff;
                /* Nền trắng cho container */
                padding: 35px 45px;
                border-radius: 0.5rem;
                box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
                /* Bóng đổ rõ hơn */
                max-width: 550px;
                /* Giới hạn chiều rộng */
                width: calc(100% - 40px);
                opacity: 0;
                animation: fadeInDown 0.6s ease-out 0.1s forwards;
                z-index: 10;
                position: relative;
            }

            .pending-auth-container h1 {
                font-size: 1.85rem;
                color: #2c3e50;
                /* Màu xanh xám đậm cho tiêu đề */
                margin-bottom: 1rem;
            }

            .pending-auth-container p {
                font-size: 1rem;
                color: #555;
                /* Màu xám cho văn bản */
                line-height: 1.7;
                margin-bottom: 0.75rem;
            }

            .pending-auth-container hr {
                margin-top: 1.25rem;
                margin-bottom: 1.25rem;
                border-color: #ecf0f1;
                /* Màu xám rất nhạt cho đường kẻ */
            }

            .pending-auth-container .btn-logout {
                margin-top: 1.5rem;
                transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, background-color 0.2s ease, border-color 0.2s ease;
                background-color: #3498db;
                /* Màu xanh dương chính cho nút */
                border-color: #3498db;
                color: #ffffff;
                padding: 0.6rem 1.2rem;
                font-size: 0.95rem;
            }

            .pending-auth-container .btn-logout:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(52, 152, 219, 0.4);
                background-color: #2980b9;
                /* Màu xanh dương đậm hơn khi hover */
                border-color: #2980b9;
            }

            /* CSS Animations cho container và text */
            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-25px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(25px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .pending-auth-container h1,
            .pending-auth-container p,
            .pending-auth-container hr,
            .pending-auth-container .btn-logout {
                opacity: 0;
                animation: fadeInUp 0.5s ease-out forwards;
            }

            .pending-auth-container h1 {
                animation-delay: 0.3s;
            }

            .pending-auth-container p:nth-of-type(1) {
                animation-delay: 0.45s;
            }

            .pending-auth-container p:nth-of-type(2) {
                animation-delay: 0.6s;
            }

            .pending-auth-container hr {
                animation-delay: 0.75s;
            }

            .pending-auth-container p:nth-of-type(3) {
                animation-delay: 0.9s;
            }

            .pending-auth-container .btn-logout {
                animation-delay: 1.05s;
            }

            /* Hiệu ứng "đốm sáng" hoặc "bit dữ liệu" rơi */
            .leaf-container {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                overflow: hidden;
                z-index: 1;
            }

            .leaf {
                /* Đổi tên thành 'mote' hoặc 'particle' nếu muốn, nhưng giữ 'leaf' cho tiện */
                position: absolute;
                border-radius: 50%;
                /* Hình tròn */
                opacity: 0.6;
                animation-name: fall, swayMote;
                /* Đổi tên animation sway */
                animation-timing-function: linear, ease-in-out;
                animation-iteration-count: infinite, infinite;
                animation-direction: normal, alternate;
            }

            /* Biến thể màu sắc cho "đốm sáng" */
            /* Sử dụng các màu xanh dương và trắng/xám nhạt */


            @keyframes fall {
                0% {
                    top: -10%;
                    opacity: 0.1;
                }

                /* Bắt đầu mờ hơn */
                70% {
                    opacity: 0.6;
                }

                100% {
                    top: 110%;
                    opacity: 0;
                }

                /* Mờ dần khi rơi xuống */
            }

            @keyframes swayMote {

                /* Animation lắc lư mới */
                0% {
                    transform: translateX(0px) scale(0.8);
                }

                50% {
                    transform: translateX(15px) scale(1);
                }

                100% {
                    transform: translateX(-15px) scale(0.8);
                }
            }
        </style>
    @endif
</head>

<body class="antialiased @if(isset($showPendingMessage) && $showPendingMessage === true) body-pending-auth @endif">

    @if(isset($showPendingMessage) && $showPendingMessage === true)
        <div class="leaf-container" id="leafContainer">
            {{-- Các "đốm sáng" sẽ được thêm vào đây bằng JavaScript --}}
        </div>
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

    @if(isset($showPendingMessage) && $showPendingMessage === true)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const particleContainer = document.getElementById('leafContainer'); // Đổi tên biến cho rõ nghĩa hơn
                if (!particleContainer) return;

                const numberOfParticles = 40; // Số lượng "đốm sáng"
                // Màu sắc cho "đốm sáng" - các tông màu xanh và trắng/xám
                const particleColors = ['#ecf0f1', '#bdc3c7', '#95a5a6', '#7f8c8d', '#3498db', '#2980b9'];

                for (let i = 0; i < numberOfParticles; i++) {
                    createParticle();
                }

                function createParticle() {
                    const particle = document.createElement('div');
                    particle.classList.add('leaf'); // Vẫn dùng class 'leaf' cho CSS, nhưng ý nghĩa là 'particle'

                    const size = Math.random() * 8 + 2; // Kích thước nhỏ hơn, từ 2px đến 10px
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`; // Hình tròn

                    particle.style.left = `${Math.random() * 100}%`;

                    particle.style.backgroundColor = particleColors[Math.floor(Math.random() * particleColors.length)];

                    const fallDuration = Math.random() * 8 + 7; // Thời gian rơi ngẫu nhiên, từ 7s đến 15s
                    particle.style.animationDuration = `${fallDuration}s, ${Math.random() * 3 + 4}s`; // fall, swayMote

                    particle.style.animationDelay = `${Math.random() * 7}s, ${Math.random() * 3}s`; // fall, swayMote

                    // Không cần xoay ban đầu cho các đốm sáng tròn
                    // particle.style.transform = `rotate(${Math.random() * 360}deg)`;

                    particleContainer.appendChild(particle);

                    particle.addEventListener('animationend', function (e) {
                        if (e.animationName === 'fall') {
                            particle.remove();
                            createParticle();
                        }
                    });
                }
            });
        </script>
    @endif
</body>

</html>