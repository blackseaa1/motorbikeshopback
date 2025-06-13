@extends('customer.layouts.app')

@section('title', 'Tài khoản của tôi')

@section('content')
  <section class="py-5">
    <div class="container">
    <div class="row">
      <div class="col-lg-3 mb-4">
      <div class="card">
        <div class="card-body text-center">
        <div class="mb-3">
          {{-- SỬA: Lấy avatar_url từ accessor của model Customer --}}
          <img src="{{ Auth::user()->avatar_url }}" class="rounded-circle img-thumbnail" alt="User Avatar"
          style="width: 150px; height: 150px; object-fit: cover;">
        </div>
        <h5 class="card-title">{{ Auth::user()->name }}</h5>
        <p class="text-muted">Thành viên từ {{ Auth::user()->created_at->format('m/Y') }}</p>
        </div>
        <div class="list-group list-group-flush">
        <a href="{{ route('account.profile') }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('account.profile') ? 'active' : '' }}">
          <i class="bi bi-person me-2"></i> Hồ sơ của tôi
        </a>
        <a href="{{ route('account.orders') }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('account.orders') ? 'active' : '' }}">
          <i class="bi bi-box me-2"></i> Đơn hàng của tôi
        </a>
        <a href="{{ route('account.addresses') }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('account.addresses') ? 'active' : '' }}">
          <i class="bi bi-geo-alt me-2"></i> Sổ địa chỉ
        </a>
        <a href="#" class="list-group-item list-group-item-action text-danger"
          onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
          <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
        </a>
        <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
      </div>
      </div>

      <div class="col-lg-9">
      <div class="card">
        <div class="card-header bg-white">
        <h4 class="mb-0">Hồ sơ của tôi</h4>
        </div>
        <div class="card-body">
        {{-- THÊM: Hiển thị thông báo thành công hoặc lỗi --}}
        @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
      @endif
        @if($errors->any())
        <div class="alert alert-danger">
        <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
        </ul>
        </div>
      @endif

        {{-- SỬA: Cập nhật action và thêm các trường cần thiết --}}
        <form id="profile-update-form" method="POST" action="{{ route('account.updateProfile') }}">
          @csrf
          @method('PATCH')
          <div class="row mb-3">
          <div class="col-md-6">
            <label for="name" class="form-label">Họ và Tên</label>
            <input type="text" class="form-control" id="name" name="name"
            value="{{ old('name', Auth::user()->name) }}" required>
            <div class="invalid-feedback"></div>
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" name="phone"
            value="{{ old('phone', Auth::user()->phone) }}">
            <div class="invalid-feedback"></div>
          </div>
          </div>
          <div class="mb-3">
          <label for="email" class="form-label">Địa chỉ Email</label>
          <input type="email" class="form-control" id="email" value="{{ Auth::user()->email }}" disabled readonly>
          <div class="form-text">Bạn không thể thay đổi địa chỉ email.</div>
          </div>
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
          <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
          </div>
        </form>

        <hr class="my-4">

        {{-- SỬA: Cập nhật action --}}
        <h5 class="mb-3">Đổi mật khẩu</h5>
        <form id="password-update-form" method="POST" action="{{ route('account.updatePassword') }}">
          @csrf
          @method('PUT')
          <div class="mb-3">
          <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
          <input type="password" class="form-control" name="current_password" id="current_password" required>
          <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
          <label for="password" class="form-label">Mật khẩu mới</label>
          <input type="password" class="form-control" name="password" id="password" required>
          <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
          <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
          <input type="password" class="form-control" name="password_confirmation" id="password_confirmation"
            required>
          </div>
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
          <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
          </div>
        </form>

        </div>
      </div>
      </div>
    </div>
    </div>
  </section>
@endsection
@push('scripts')
  <script src="{{ asset('assets_customer/js/account.js') }}" defer></script>
@endpush  