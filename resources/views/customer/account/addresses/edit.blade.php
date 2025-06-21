@extends('customer.account.layouts.app')

@section('title', 'Chỉnh sửa địa chỉ')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Chỉnh sửa địa chỉ</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('account.addresses.update', $address->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Gọi partial chứa các trường của form --}}
                @include('customer.account.addresses.partials._form', ['address' => $address])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('account.addresses.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- Thêm vào cuối file --}}
{{-- Thêm vào cuối file --}}
@push('scripts')
    {{-- Truyền ID cũ sang JS để tự động chọn lại --}}
    <script>
        window.oldDistrictId = '{{ old('district_id', $address->district_id) }}';
        window.oldWardId = '{{ old('ward_id', $address->ward_id) }}';
    </script>
    <script src="{{ asset('assets_customer/js/address_manager.js') }}"></script>
@endpush