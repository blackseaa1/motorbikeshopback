@extends('admin.layouts.app')

@section('title', 'Quản lý Địa lý')

@section('content')
    <div class="content-header mb-4">
        <h1><i class="bi bi-globe-americas me-2"></i>Quản lý Đơn vị Hành chính</h1>
    </div>

    <div class="container-fluid">

        {{-- Hiển thị thông báo --}}
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
        {{-- Bạn có thể thêm một section để hiển thị lỗi chung từ validation nếu không dùng error bag cụ thể cho modal --}}
        @if ($errors->any() && 
            !$errors->hasBag('storeProvince') && !session()->has('error_update_province_id') &&
            !$errors->hasBag('storeDistrict') && !session()->has('error_update_district_id') &&
            !$errors->hasBag('storeWard') && !session()->has('error_update_ward_id') &&
            !$errors->hasBag('importGeography')
            )
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


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

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-map-fill me-2"></i>Bản đồ Hành chính</h2>
            </div>
            <div class="card-body">
                {{-- Nav tabs (Blade sẽ quyết định tab nào active) --}}
                <ul class="nav nav-tabs mb-3" id="geographyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !request('tab') || request('tab') == 'provinces' ? 'active' : '' }}"
                            id="provinces-tab-btn" data-bs-toggle="tab" data-bs-target="#provinces-tab-pane" type="button"
                            role="tab" aria-controls="provinces-tab-pane"
                            aria-selected="{{ !request('tab') || request('tab') == 'provinces' ? 'true' : 'false' }}">
                            <i class="bi bi-geo-alt-fill me-1"></i> Tỉnh/Thành phố
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'districts' ? 'active' : '' }}" id="districts-tab-btn"
                            data-bs-toggle="tab" data-bs-target="#districts-tab-pane" type="button" role="tab"
                            aria-controls="districts-tab-pane"
                            aria-selected="{{ request('tab') == 'districts' ? 'true' : 'false' }}">
                            <i class="bi bi-pin-map-fill me-1"></i> Quận/Huyện
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'wards' ? 'active' : '' }}" id="wards-tab-btn"
                            data-bs-toggle="tab" data-bs-target="#wards-tab-pane" type="button" role="tab"
                            aria-controls="wards-tab-pane"
                            aria-selected="{{ request('tab') == 'wards' ? 'true' : 'false' }}">
                            <i class="bi bi-pin-angle-fill me-1"></i> Phường/Xã
                        </button>
                    </li>
                </ul>

                {{-- Tab content (Blade sẽ quyết định tab nào active) --}}
                <div class="tab-content" id="geographyTabsContent">
                    <div class="tab-pane fade {{ !request('tab') || request('tab') == 'provinces' ? 'show active' : '' }}"
                        id="provinces-tab-pane" role="tabpanel" aria-labelledby="provinces-tab-btn" tabindex="0">
                        @include('admin.system.partials.tabs.provinces_tab')
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'districts' ? 'show active' : '' }}"
                        id="districts-tab-pane" role="tabpanel" aria-labelledby="districts-tab-btn" tabindex="0">
                        @include('admin.system.partials.tabs.districts_tab')
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'wards' ? 'show active' : '' }}" id="wards-tab-pane"
                        role="tabpanel" aria-labelledby="wards-tab-btn" tabindex="0">
                        @include('admin.system.partials.tabs.wards_tab')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Các modal --}}
    {{-- Bạn có thể cần tạo một file riêng cho modal import nếu nó phức tạp --}}
    {{-- @include('admin.system.partials.modals.import') --}}
    @include('admin.system.partials.modals.province')
    @include('admin.system.partials.modals.district', ['allProvinces' => $allProvinces])
    @include('admin.system.partials.modals.ward', ['allProvinces' => $allProvinces])

@endsection

{{-- TOÀN BỘ PHẦN @push('scripts') ĐÃ ĐƯỢC XÓA KHỎI ĐÂY --}}