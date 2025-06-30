@extends('customer.layouts.app')

@section('title', 'Tạo Mật Khẩu Mới')

@push('styles')
    {{-- CSS cho chức năng kiểm tra mật khẩu --}}
    <style>
        #password-strength-criteria {
            list-style-type: none;
            padding-left: 0;
            font-size: 0.9em;
        }

        #password-strength-criteria li {
            color: #dc3545;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }

        #password-strength-criteria li.valid {
            color: #198754;
        }

        #password-strength-criteria li .icon::before {
            content: "✗";
            font-family: 'bootstrap-icons';
            margin-right: 8px;
            vertical-align: middle;
        }

        #password-strength-criteria li.valid .icon::before {
            content: "✓";
        }
    </style>
@endpush

@section('content')
    <section class="py-5 my-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="text-center mb-2">Tạo Mật Khẩu Mới</h2>
                            <p class="text-center text-muted mb-4">
                                Mật khẩu của bạn đã được reset. Vui lòng tạo một mật khẩu mới để bảo mật tài khoản.
                            </p>

                            <form id="force-password-change-form" method="POST"
                                action="{{ route('customer.password.handle_force_change') }}" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="invalid-feedback" data-field="password"></div>
                                </div>

                                <ul id="password-strength-criteria" class="mt-2">
                                    <li data-regex=".{8,}"><span class="icon"></span>Ít nhất 8 ký tự</li>
                                    <li data-regex="[A-Z]"><span class="icon"></span>Ít nhất 1 chữ hoa</li>
                                    <li data-regex="[a-z]"><span class="icon"></span>Ít nhất 1 chữ thường</li>
                                    <li data-regex="[0-9]"><span class="icon"></span>Ít nhất 1 chữ số</li>
                                    <li data-regex="[^A-Za-z0-9]"><span class="icon"></span>Ít nhất 1 ký tự đặc biệt</li>
                                </ul>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                    <div class="invalid-feedback" data-field="password_confirmation"></div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Xác nhận và Tiếp tục</button>
                                </div>
                            </form>

                            {{-- === THÊM MỚI: NÚT ĐĂNG XUẤT === --}}
                            <div class="text-center mt-4">
                                <a href="#"
                                    onclick="event.preventDefault(); document.getElementById('force-logout-form').submit();"
                                    class="text-muted text-decoration-none">
                                    Đăng xuất
                                </a>
                                <form id="force-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                            {{-- ============================== --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Nhúng tệp JavaScript xử lý logic --}}
    <script src="{{ asset('assets_customer/js/auth.js') }}" defer></script>

    {{-- SỬA ĐỔI: Thêm đoạn script này để gọi hàm khởi tạo --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Kiểm tra xem hàm đã tồn tại trên window object chưa trước khi gọi
            if (typeof window.initializeAuthPages === 'function') {
                window.initializeAuthPages();
            }
        });
    </script>
@endpush