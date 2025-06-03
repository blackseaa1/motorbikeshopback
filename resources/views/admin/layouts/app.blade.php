<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Đồ án Web Đồ Chơi Xe</title>

    @stack('laravel-js-vars') {{-- --}}
    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- --}}

    {{-- Các file CSS tùy chỉnh khác --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}"> {{-- --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}"> {{-- --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}"> {{-- --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}"> {{--{{-- --}}


    @yield('styles') {{-- Dành cho CSS riêng của từng trang --}} {{-- --}}
</head>

<body>
    {{-- 1. Nhúng loading overlay toàn cục --}}
    @include('admin.layouts.partials.loading')

    {{-- 2. Xóa bỏ div#loading-overlay cũ đã hardcode ở đây --}}
    {{-- --}}

    <div class="wrapper"> {{-- --}}
        @include('admin.layouts.partials.sidebar') {{-- --}}

        <div class="main-panel"> {{-- --}}
            @include('admin.layouts.partials.topnav') {{-- --}}

            <main class="main-content p-3" id="mainContent"> {{-- --}}
                {{-- 3. Nhúng hệ thống thông báo toàn cục --}}
                @include('admin.layouts.partials.messages')

                @yield('content') {{-- --}}
            </main>
        </div>
    </div>
    {{-- File: app.blade.php --}}
    {{-- ... các nội dung khác ... --}}

    {{-- Modal chung cho các thông báo từ session và validation --}}
    <div class="modal fade" id="appInfoModal" tabindex="-1" aria-labelledby="appInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"> {{-- Class màu nền sẽ được JS thêm vào --}}
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

    {{-- NẠP CÁC FILE JAVASCRIPT CỦA ỨNG DỤNG --}}
    <script src="{{ asset('assets_admin/js/admin_layout.js') }}"></script>
    @stack('scripts')
</body>

</html>