@extends('customer.layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <section class="py-5 my-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="text-center mb-4">Đăng nhập</h2>

                            {{-- Form đăng nhập đã được sửa đổi cho Laravel --}}
                            <form id="login-form" method="POST" action="{{ route('login') }}">
                                {{-- Token chống tấn công CSRF, bắt buộc phải có --}}
                                @csrf

                                {{-- Hiển thị lỗi nếu có --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="email" class="form-label">Địa chỉ Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        {{-- Thuộc tính 'name' là bắt buộc để gửi dữ liệu, 'value' để giữ lại giá trị cũ khi
                                        có lỗi --}}
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Nhập email của bạn" value="{{ old('email') }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Nhập mật khẩu của bạn" required>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
                                    <label class="form-check-label" for="remember-me">Ghi nhớ đăng nhập</label>
                                    <a href="#" class="float-end">Quên mật khẩu?</a>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                {{-- Sửa link tĩnh bằng hàm route() --}}
                                <p>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script src="{{ asset('assets_customer/js/auth.js') }}" defer></script>
@endpush