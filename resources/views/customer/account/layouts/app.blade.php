{{-- resources/views/customer/account/layouts/app.blade.php --}}
@extends('customer.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        {{-- Cột Sidebar --}}
        <div class="col-lg-3">
            @include('customer.account.partials._sidebar')
        </div>

        {{-- Cột Nội dung chính --}}
        <div class="col-lg-9">
            @yield('account_content')
        </div>
    </div>
</div>
@endsection