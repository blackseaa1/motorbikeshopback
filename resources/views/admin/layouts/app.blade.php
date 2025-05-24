<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Trang Quản Trị') - Admin Panel</title>

    {{-- LƯU Ý: Tôi sử dụng đúng đường dẫn asset mà bạn đã cung cấp --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
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

    {{-- LƯU Ý: Tôi sử dụng đúng đường dẫn asset mà bạn đã cung cấp --}}
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets_admin/js/admin_layout.js') }}"></script>


    @yield('scripts')
</body>

</html>