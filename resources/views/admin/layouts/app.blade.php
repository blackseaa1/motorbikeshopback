<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Admin Panel</title>

    {{-- TRUYỀN BIẾN LỖI VÀ SESSION TỪ LARAVEL SANG JAVASCRIPT --}}
    <script>
        window.laravelErrors = @json($errors->getBags());
        window.errorUpdateProvinceId = "{{ session('error_update_province_id') }}";
        window.errorUpdateDistrictId = "{{ session('error_update_district_id') }}";
        window.errorUpdateWardId = "{{ session('error_update_ward_id') }}";
        // Bạn có thể thêm các session flash khác nếu cần
    </script>

    {{-- Nạp CSS và JS chính qua Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Các file CSS tùy chỉnh khác của bạn (nếu có và không được quản lý bởi Vite) --}}
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">

    @yield('styles') {{-- Dành cho CSS riêng của từng trang (nếu có) --}}
</head>

<body>
    {{-- Lớp phủ tải trang --}}
    <div id="loading-overlay">
        <div class="spinner"></div>
        <span class="loading-text">Đang xử lý...</span>
    </div>

    <div class="wrapper"> {{-- Hoặc class layout chính của bạn --}}
        {{-- Sidebar --}}
        @include('admin.layouts.partials.sidebar') {{-- --}}

        <div class="main-panel"> {{-- Hoặc class main content của bạn --}}
            {{-- Topnav/Header --}}
            @include('admin.layouts.partials.topnav') {{-- --}}

            {{-- Khu vực chứa nội dung chính của từng trang --}}
            <main class="main-content p-3" id="mainContent"> {{-- --}}
                @yield('content')
            </main>

            {{-- Footer (nếu có) --}}
            {{-- @include('admin.layouts.partials.footer') --}}
        </div>
    </div>

    {{-- KHÔNG CÒN @stack('scripts') ở đây nếu tất cả JS đã vào app.js --}}
    {{-- Nếu bạn có script inline ở đâu đó mà không muốn chuyển vào app.js, có thể giữ lại @stack('scripts') --}}
    @yield('scripts') {{-- Vẫn giữ @yield('scripts') nếu một số trang đặc biệt cần script inline --}}
</body>

</html>