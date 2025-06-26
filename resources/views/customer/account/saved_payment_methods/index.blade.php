@extends('customer.account.layouts.app')

@section('title', 'Phương thức thanh toán đã lưu')

@section('content')
    <h3 class="mb-4">Phương thức thanh toán của tôi</h3>

    <div class="card mb-4">
        <div class="card-header">
            Thêm phương thức thanh toán mới
        </div>
        <div class="card-body">
            <form action="{{ route('customer.account.saved_payment_methods.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="payment_method_id">Chọn phương thức</label>
                    <select name="payment_method_id" id="payment_method_id" class="form-control">
                        @foreach ($availableMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Lưu lại</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Danh sách đã lưu
        </div>
        <div class="card-body">
            <ul class="list-group">
                @forelse ($savedMethods as $method)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $method->name }}
                        <form action="{{ route('customer.account.saved_payment_methods.destroy', $method->id) }}" method="POST"
                            onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item">Bạn chưa lưu phương thức thanh toán nào.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection