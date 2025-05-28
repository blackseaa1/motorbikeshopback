@extends('admin.layouts.app')

@section('title', 'Quản lý Địa lý')

{{-- 1. "NHÉT" SCRIPT TRUYỀN BIẾN VÀO KHE 'laravel-js-vars' CỦA LAYOUT --}}
@push('laravel-js-vars')
    <script>
        window.laravelErrors = @json($errors->getBags());
        window.errorUpdateProvinceId = "{{ session('error_update_province_id') }}";
        window.errorUpdateDistrictId = "{{ session('error_update_district_id') }}";
        window.errorUpdateWardId = "{{ session('error_update_ward_id') }}";
    </script>
@endpush


{{-- 2. NỘI DUNG HTML CỦA TRANG --}}
@section('content')
    <div class="content-header mb-4">
        <h1><i class="bi bi-globe-americas me-2"></i>Quản lý Đơn vị Hành chính</h1>
    </div>

    <div class="container-fluid">
        {{-- Hiển thị thông báo thành công/thất bại --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ================================================= --}}
        {{-- BỔ SUNG LẠI CARD IMPORT ĐÃ THIẾU --}}
        {{-- ================================================= --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-upload me-1"></i>Import Dữ liệu Excel</h2>
                <div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importGeographyModal">
                        <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Chọn File & Import
                    </button>
                </div>
            </div>
        </div>

        {{-- Card chứa các Tab Tỉnh/Huyện/Xã --}}
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-map-fill me-2"></i>Bản đồ Hành chính</h2>
            </div>
            <div class="card-body">
                {{-- Nav tabs --}}
                <ul class="nav nav-tabs mb-3" id="geographyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !request('tab') || request('tab') == 'provinces' ? 'active' : '' }}"
                            data-bs-toggle="tab" data-bs-target="#provinces-tab-pane" type="button">Tỉnh/Thành phố</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'districts' ? 'active' : '' }}" data-bs-toggle="tab"
                            data-bs-target="#districts-tab-pane" type="button">Quận/Huyện</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'wards' ? 'active' : '' }}" data-bs-toggle="tab"
                            data-bs-target="#wards-tab-pane" type="button">Phường/Xã</button>
                    </li>
                </ul>

                {{-- Tab content --}}
                <div class="tab-content" id="geographyTabsContent">
                    <div class="tab-pane fade {{ !request('tab') || request('tab') == 'provinces' ? 'show active' : '' }}"
                        id="provinces-tab-pane" role="tabpanel">
                        @include('admin.system.partials.tabs.provinces_tab')
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'districts' ? 'show active' : '' }}"
                        id="districts-tab-pane" role="tabpanel">
                        @include('admin.system.partials.tabs.districts_tab')
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'wards' ? 'show active' : '' }}" id="wards-tab-pane"
                        role="tabpanel">
                        @include('admin.system.partials.tabs.wards_tab')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- BỔ SUNG LẠI MODAL IMPORT ĐÃ THIẾU --}}
    {{-- ================================================= --}}
    @include('admin.system.partials.modals.import')
    @include('admin.system.partials.modals.province')
    @include('admin.system.partials.modals.district', ['allProvinces' => $allProvinces ?? []])
    @include('admin.system.partials.modals.ward', ['allProvinces' => $allProvinces ?? []])

@endsection


{{-- 3. NẠP FILE JAVASCRIPT RIÊNG CHO TRANG NÀY --}}
@section('scripts')
    <script src="{{ asset('assets_admin/js/geography.js') }}"></script>
@endsection