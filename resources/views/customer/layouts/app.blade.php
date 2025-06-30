<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MotoToys Store')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets_admin/images/logo.ico') }}">
    {{-- Meta tags from new index.html for SEO --}}
    <meta name="description"
        content="Discover premium motorcycle accessories, toys, and parts. Free shipping on orders over $50. Shop Honda, Yamaha, Kawasaki parts with 20% off using code SUMMER25.">
    <meta name="keywords" content="motorcycle toys, motorcycle accessories, bike parts, Honda, Yamaha, Kawasaki">

    @vite(['resources/css/app.css'])
    {{-- CSS cũ có thể giữ lại hoặc loại bỏ tùy theo nhu cầu --}}
    <link rel="stylesheet" href="{{ asset('vendor/common/base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/common/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/common/reset.css') }}">

    {{-- Bootstrap 5 CSS (đã có trong file cũ) --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap-select.min.css') }}">


    {{-- SỬA ĐỔI: Thêm Bootstrap Icons từ file index.html --}}

    {{-- CSS tùy chỉnh mới (nội dung sẽ được cung cấp ở dưới) --}}
    <link rel="stylesheet" href="{{ asset('assets_customer/css/style.css') }}">

    @stack('styles')
</head>

<body>
    @include('customer.layouts.partials.loading')

    {{-- Header đã được cập nhật --}}
    @include('customer.layouts.partials._header')

    <div class="wrapper">
        <main class="main-content" id="mainContent">
            @include('customer.layouts.partials.messages')
            @yield('content')
        </main>
    </div>

    {{-- Footer đã được cập nhật --}}
    @include('customer.layouts.partials._footer')

    {{-- SỬA ĐỔI: Nút Scroll to Top từ index.html --}}
    <button id="scrollToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle d-none"
        style="z-index: 1000;">
        <i class="bi bi-arrow-up"></i>
    </button>

    <script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('assets_customer/js/customer_layout.js') }}"></script>
    <script src="{{ asset('assets_customer/js/cart.js') }}"></script>
    <script src="{{ asset('assets_customer/js/search_autocomplete.js') }}"></script>

    {{-- SỬA ĐỔI: Thêm file script mới (nội dung sẽ được cung cấp ở dưới) --}}

    @stack('scripts')

    {{-- Modal chung (giữ nguyên) --}}
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
    <div class="modal fade" id="appConfirmModal" tabindex="-1" aria-labelledby="appConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark"> {{-- Có thể điều chỉnh màu sắc --}}
                    <h5 class="modal-title" id="appConfirmModalLabel">Xác nhận</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn thực hiện hành động này?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="appConfirmModalConfirmBtn">Xác nhận</button> {{--
                    Nút này cần ID --}}
                </div>
            </div>
        </div>
    </div>
</body>

</html>