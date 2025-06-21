@extends('customer.account.layouts.app')

@section('title', 'Thêm địa chỉ mới')

@section('account_content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thêm địa chỉ mới</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('account.addresses.store') }}" method="POST">
            @csrf
            {{-- Gọi partial chứa các trường của form --}}
            @include('customer.account.addresses.partials._form', ['address' => new \App\Models\CustomerAddress()])
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Lưu địa chỉ</button>
                <a href="{{ route('account.addresses.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

{{-- Thêm vào cuối file --}}
@push('scripts')
    <script src="{{ asset('assets_customer/js/address_manager.js') }}"></script>
@endpush