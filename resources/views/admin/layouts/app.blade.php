<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Trang Quản Trị') - Admin Panel</title>

    {{-- LƯU Ý: Tôi sử dụng đúng đường dẫn asset mà bạn đã cung cấp --}}
    <!-- App CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_admin/css/common/style.css') }}">

    @yield('styles')
</head>

<body>

    {{-- Gọi file sidebar.blade.php vào đây --}}
    @include('admin.layouts.partials.sidebar')

    {{-- Gọi file topnav.blade.php vào đây --}}
    @include('admin.layouts.partials.topnav')



    {{-- Đây là khu vực chứa nội dung chính của từng trang --}}
    <div class="main-content" id="mainContent">
        @yield('content')
    </div>

    @yield('scripts')
    <script defer src="{{ asset('assets_admin/js/admin_layout.js') }}"></script>

</body>

</html>