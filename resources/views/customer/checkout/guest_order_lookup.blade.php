@extends('customer.layouts.app')

@section('title', 'Tra cứu đơn hàng của khách vãng lai')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Tra cứu đơn hàng của bạn</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-center">Vui lòng nhập số điện thoại hoặc email bạn đã dùng khi đặt hàng để tra cứu
                            đơn hàng của mình.</p>
                        <form action="{{ route('guest.order.lookup') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="guest_phone" class="form-label">Số điện thoại hoặc Email:</label>
                                <input type="text" class="form-control @error('guest_phone') is-invalid @enderror"
                                    id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}" required
                                    placeholder="Ví dụ: 0987654321 hoặc email@example.com">
                                @error('guest_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Tra cứu đơn hàng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection