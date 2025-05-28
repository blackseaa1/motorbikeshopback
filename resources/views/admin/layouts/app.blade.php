<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Đồ án Web Đồ Chơi Xe</title>

    {{-- =================================================================== --}}
    {{-- 1. TẠO "KHE CẮM" ĐỂ TRANG CON TRUYỀN BIẾN SANG JAVASCRIPT --}}
    {{-- Chỉ những trang con nào dùng @push('laravel-js-vars') thì mới có script ở đây. --}}
    {{-- Các trang khác sẽ không tải script này, giúp tối ưu tốc độ. --}}
    {{-- =================================================================== --}}
    @stack('laravel-js-vars')

    {{-- 2. NẠP CSS VÀ JS THƯ VIỆN GỐC QUA VITE --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Các file CSS tùy chỉnh khác --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">

    @yield('styles') {{-- Dành cho CSS riêng của từng trang --}}
</head>

<body>
    {{-- Lớp phủ tải trang (loading overlay) --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <span class="loading-text">Đang xử lý...</span>
    </div>

    <div class="wrapper">
        @include('admin.layouts.partials.sidebar')

        <div class="main-panel">
            @include('admin.layouts.partials.topnav')

            <main class="main-content p-3" id="mainContent">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- 3. NẠP CÁC FILE JAVASCRIPT CỦA ỨNG DỤNG --}}
    {{-- =================================================================== --}}


    {{-- 3.1 "Khe cắm" cho các file JS của trang con --}}
    {{-- Tất cả các file như brand_manager.js, category_manager.js, dashboard_chart.js sẽ được nạp ở đây --}}
    @stack('scripts')
    {{-- 3.2. Nạp file layout chung (Orchestrator) CUỐI CÙNG --}}
    <script src="{{ asset('assets_admin/js/admin_layout.js') }}"></script>

    {{-- Nơi để các trang con chèn vào các file JS riêng của mình --}}
    @yield('scripts')
</body>

</html>