@extends('customer.layouts.app')

@section('title', 'Tra cứu đơn hàng')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 text-center">Tra cứu đơn hàng của bạn</h4>
                    </div>
                    <div class="card-body">

                        <p class="text-center">Vui lòng nhập mã đơn hàng và email hoặc số điện thoại bạn đã sử dụng khi đặt hàng để xem chi tiết đơn hàng.</p>

                        <form action="{{ route('guest.order.lookup.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="order_id" class="form-label">Mã đơn hàng</label>
                                <input type="text" class="form-control @error('order_id') is-invalid @enderror" id="order_id" name="order_id" value="{{ old('order_id') }}" required>
                                @error('order_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="guest_contact" class="form-label">Email hoặc Số điện thoại</label>
                                <input type="text" class="form-control @error('guest_contact') is-invalid @enderror" id="guest_contact" name="guest_contact" value="{{ old('guest_contact') }}" required>
                                @error('guest_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Tra cứu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection