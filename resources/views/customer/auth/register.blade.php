@extends('customer.layouts.app')

@section('title', 'Đăng ký tài khoản')

@section('content')
  <section class="py-5 my-3">
    <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
      <div class="card shadow">
        <div class="card-body p-4 p-md-5">
        <h2 class="text-center mb-4">Tạo tài khoản</h2>

        <form id="register-form" method="POST" action="{{ route('register') }}">
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
          <label for="name" class="form-label">Họ và Tên</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Nhập họ và tên của bạn"
            value="{{ old('name') }}" required>
          </div>

          <div class="mb-3">
          <label for="email" class="form-label">Địa chỉ Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email của bạn"
            value="{{ old('email') }}" required>
          </div>

          <div class="mb-3">
          <label for="password" class="form-label">Mật khẩu</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Tạo mật khẩu"
            required>
          </div>

          <div class="mb-3">
          <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
          <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
            placeholder="Nhập lại mật khẩu" required>
          </div>

          <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
          {{-- Modified link to trigger modal --}}
          <label class="form-check-label" for="terms">Tôi đồng ý với <a href="#" data-bs-toggle="modal"
            data-bs-target="#termsModal">Điều khoản dịch vụ</a></label>
          </div>

          <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Đăng ký</button>
          </div>
        </form>

        <div class="text-center mt-4">
          <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
        </div>
        </div>
      </div>
      </div>
    </div>
    </div>
  </section>

  {{-- Terms and Conditions Modal --}}
  <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
      <h5 class="modal-title" id="termsModalLabel">Điều khoản dịch vụ</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      {{-- Include the content of _terms_content.blade.php here --}}
      @include('customer.auth.partials._terms_content')
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script src="{{ asset('assets_customer/js/auth.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
    // Kiểm tra xem hàm đã tồn tại trên window object chưa trước khi gọi
    if (typeof window.initializeAuthPages === 'function') {
      window.initializeAuthPages();
    }
    });
  </script>
@endpush