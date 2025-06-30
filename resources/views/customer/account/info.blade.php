@extends('customer.account.layouts.app')

@push('styles')
    {{-- CSS cho chức năng kiểm tra mật khẩu --}}
    <style>
        #password-strength-criteria {
            list-style-type: none;
            padding-left: 0;
            font-size: 0.9em;
            display: none;
            /* Ẩn mặc định */
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
            content: '✗';
            /* Icon cross của Bootstrap Icons */
            font-family: 'bootstrap-icons';
            margin-right: 8px;
            vertical-align: middle;
        }

        #password-strength-criteria li.valid .icon::before {
            content: '✓';
            /* Icon check của Bootstrap Icons */
        }
    </style>
@endpush

@section('account_content')
    {{-- Card Cập nhật thông tin cá nhân & Avatar --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Thông tin cá nhân</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    {{-- Form cập nhật Avatar --}}
                    <form id="avatar-update-form" action="{{ route('account.updateAvatar') }}" method="POST"
                        enctype="multipart/form-data" novalidate>
                        @csrf
                        {{-- Hiển thị avatar hiện tại của người dùng --}}
                        <img id="avatar-preview" src="{{ Auth::user()->avatar_url }}" alt="Avatar"
                            class="rounded-circle img-thumbnail mb-3"
                            style="width: 150px; height: 150px; object-fit: cover;">
                        <div class="mb-3">
                            {{-- Nút chọn ảnh, thực chất kích hoạt input type="file" ẩn --}}
                            <label for="avatar-input" class="btn btn-sm btn-secondary">Chọn ảnh</label>
                            <input type="file" id="avatar-input" name="avatar" class="d-none"
                                accept="image/png, image/jpeg">
                        </div>
                        {{-- Nút Lưu ảnh, ban đầu ẩn, sẽ hiện khi có ảnh được chọn qua JS --}}
                        <button type="submit" class="btn btn-sm btn-primary" id="avatar-save-btn" style="display: none;">Lưu
                            ảnh</button>
                        {{-- Nơi hiển thị lỗi validate cho trường 'avatar' từ phản hồi AJAX --}}
                        <div class="invalid-feedback mt-2" data-field="avatar"></div>
                    </form>
                </div>
                <div class="col-md-8">
                    {{-- Form cập nhật thông tin cá nhân (Họ và tên, Số điện thoại) --}}
                    <form id="profile-update-form" action="{{ route('account.updateProfile') }}" method="POST" novalidate>
                        @csrf
                        {{--
                        Route 'account.updateProfile' trong web.php được định nghĩa là POST,
                        nên chỉ cần @csrf, không cần @method('PUT')
                        --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $customer->name) }}">
                            {{-- Nơi hiển thị lỗi validate cho trường 'name' từ phản hồi AJAX --}}
                            <div class="invalid-feedback" data-field="name"></div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            {{-- Email thường là trường không đổi, hiển thị chỉ đọc --}}
                            <input type="email" class="form-control" id="email" value="{{ $customer->email }}" readonly
                                disabled>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="{{ old('phone', $customer->phone) }}">
                            {{-- Nơi hiển thị lỗi validate cho trường 'phone' từ phản hồi AJAX --}}
                            <div class="invalid-feedback" data-field="phone"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Đổi mật khẩu (dạng collapse) --}}
    <div class="card">
        <div class="card-header" id="headingPassword">
            <h5 class="mb-0">
                <button class="btn btn-link text-decoration-none p-0" data-bs-toggle="collapse"
                    data-bs-target="#collapsePassword" aria-expanded="false" aria-controls="collapsePassword">
                    Đổi mật khẩu
                </button>
            </h5>
        </div>
        <div id="collapsePassword" class="collapse" aria-labelledby="headingPassword">
            <div class="card-body">
                {{-- Form đổi mật khẩu --}}
                <form id="password-update-form" method="POST" action="{{ route('account.updatePassword') }}" novalidate>
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        {{-- Nơi hiển thị lỗi validate cho trường 'current_password' từ phản hồi AJAX --}}
                        <div class="invalid-feedback" data-field="current_password"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="password" name="password">
                        {{-- Nơi hiển thị lỗi validate cho trường 'password' từ phản hồi AJAX --}}
                        <div class="invalid-feedback" data-field="password"></div>
                    </div>
                    {{-- Giao diện các tiêu chí mật khẩu (được điều khiển bởi account.js) --}}
                    <ul id="password-strength-criteria" class="mt-2">
                        <li data-regex=".{8,}"><span class="icon"></span>Ít nhất 8 ký tự</li>
                        <li data-regex="[A-Z]"><span class="icon"></span>Ít nhất 1 chữ hoa</li>
                        <li data-regex="[a-z]"><span class="icon"></span>Ít nhất 1 chữ thường</li>
                        <li data-regex="[0-9]"><span class="icon"></span>Ít nhất 1 chữ số</li>
                        <li data-regex="[^A-Za-z0-9]"><span class="icon"></span>Ít nhất 1 ký tự đặc biệt</li>
                    </ul>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        {{-- Nơi hiển thị lỗi validate cho trường 'password_confirmation' từ phản hồi AJAX --}}
                        <div class="invalid-feedback" data-field="password_confirmation"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Xác nhận đổi</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Bao gồm file JavaScript xử lý logic cho các form --}}
    <script src="{{ asset('assets_customer/js/account.js') }}"></script>
@endpush