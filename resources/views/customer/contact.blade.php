@extends('customer.layouts.app')

@section('title', 'Liên hệ - MotoToys')
<style>
    .custom-link:hover {
        color: var(--bs-primary) !important;
        /* Màu primary khi hover */
    }
</style>
@section('content')
    <main class="py-5">
        <div class="container">
            <h1 class="text-center mb-4">Liên hệ với chúng tôi</h1>
            <p class="text-center lead mb-5">Chúng tôi muốn nghe từ bạn! Vui lòng điền vào biểu mẫu bên dưới hoặc sử dụng
                các chi tiết liên hệ được cung cấp..</p>

            <div class="row">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Gửi cho chúng tôi một tin nhắn</h4>
                            {{-- THAY ĐỔI QUAN TRỌNG: Thêm action, method và @csrf --}}
                            <form action="{{ route('contact.submit') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="contactName" class="form-label">Tên đầy đủ</label>
                                    <input type="text" class="form-control" id="contactName" name="name"
                                        value="{{ old('name') }}" required>
                                    @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Địa chỉ Email</label>
                                    <input type="email" class="form-control" id="contactEmail" name="email"
                                        value="{{ old('email') }}" required>
                                    @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                                {{-- THÊM TRƯỜNG Số điện thoại (phone) --}}
                                <div class="mb-3">
                                    <label for="contactPhone" class="form-label">Số điệnthoại (Tùy chọn)</label>
                                    <input type="tel" class="form-control" id="contactPhone" name="phone"
                                        value="{{ old('phone') }}">
                                    @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Lời nhắn</label>
                                    <textarea class="form-control" id="contactMessage" name="message" rows="5"
                                        required>{{ old('message') }}</textarea>
                                    @error('message')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Gửi Lời Nhắn</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Thông Tin Liên Hệ</h4>
                            <ul class="list-unstyled">
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-geo-alt-fill fs-4 me-3"></i>
                                    <span>62 - Châu Văn Liêm - Phú Đô -Nam Từ Liêm- Hà Nội</span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-telephone-fill fs-4 me-3"></i>
                                    <span>
                                        <a href="tel:0394831886"
                                            class="text-decoration-none text-black custom-link">0394831886</a> -
                                        <a href="tel:0973634129"
                                            class="text-decoration-none text-black custom-link">0973634129</a>
                                    </span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-envelope-fill fs-4 me-3"></i>
                                    <span>
                                        <a href="mailto:thanhdoshop@gmail.com"
                                            class="text-decoration-none text-black custom-link">thanhdoshop@gmail.com</a>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Vị trí của chúng tôi</h4>
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2209.080175439823!2d105.7669097!3d21.0070583!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3134535baecb401b%3A0xf7137c21adbd660b!2zNjIgxJAuIENow6J1IFbEg24gTGnDqm0sIE3hu4cgVHLDrCwgTmFtIFThu6sgTGnDqm0sIEjDoCBO4buZaSAxMDAwMDA!5e1!3m2!1svi!2s!4v1750324781649!5m2!1svi!2s"
                                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection