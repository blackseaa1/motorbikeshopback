<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Đồ án Web Đồ Chơi Xe</title>

    @stack('laravel-js-vars')

    {{-- SỬA ĐỔI 1: Chỉ nạp CSS tại đây. Phần JS của Vite đã được di chuyển xuống cuối body. --}}
    @vite(['resources/css/app.css'])

    {{-- Các file CSS tùy chỉnh khác --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/library/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/library/bootstrap-select.min.css') }}">

    @yield('styles') {{-- Dành cho CSS riêng của từng trang --}}
</head>

<body>
    {{-- 1. Nhúng loading overlay toàn cục --}}
    @include('admin.layouts.partials.loading')

    <div class="wrapper">
        @include('admin.layouts.partials.sidebar')

        <div class="main-panel">
            @include('admin.layouts.partials.topnav')

            <main class="main-content p-3" id="mainContent">
                {{-- 3. Nhúng hệ thống thông báo toàn cục --}}
                @include('admin.layouts.partials.messages')

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Modal chung cho các thông báo từ session và validation --}}
    <div class="modal fade" id="appInfoModal" tabindex="-1" aria-labelledby="appInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appInfoModalLabel">Thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="appInfoModalBody">
                    {{-- Nội dung thông báo sẽ được JS chèn vào đây --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SỬA ĐỔI 2: KHU VỰC NẠP SCRIPT ================= --}}
    <script src="{{ asset('assets_admin/js/library/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets_admin/js/library/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_admin/js/library/bootstrap-select.min.js') }}"></script>
    {{-- <script src="{{ asset('assets_admin/js/library/chart.js') }}"></script> --}}
    <script src="{{ asset('assets_admin/js/admin_layout.js') }}"></script>
    {{-- 3. Nạp các file JS của từng trang riêng lẻ (ví dụ: product_management.js). --}}
    @stack('scripts')
</body>

</html>