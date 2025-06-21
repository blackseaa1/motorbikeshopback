<header class="sticky-top bg-light shadow-sm">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('assets_admin/images/thanhdo_shop_logo.png') }}" alt="Thành Đô Shop" height="40"
                    class="d-inline-block align-top">
                Thành Đô Shop
            </a>
            <div class="d-none d-md-flex flex-grow-1 mx-4">
                <form class="input-group" action="#">
                    <input type="text" class="form-control" placeholder="Tìm kiếm sản phẩm, thương hiệu...">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Trang Chủ</a>
                    </li>

                    {{-- === MODIFIED CATEGORY DROPDOWN START === --}}
                    <li class="nav-item dropdown category-nav-item">
                        {{-- Link này sẽ điều hướng đến trang lọc sản phẩm --}}
                        <a class="nav-link" href="{{ route('products.index') }}">Cửa hàng</a>
                    </li>
                    {{-- === MODIFIED CATEGORY DROPDOWN END === --}}

                    {{-- === NEW LINKS START === --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('blog') }}">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">Liên hệ</a>
                    </li>
                    {{-- === NEW LINKS END === --}}



                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="cartDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-cart3"></i>
                            <span id="header-cart-count"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: 0.6em;">
                                {{-- Số lượng sẽ được JS chèn vào đây --}}
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 shadow" aria-labelledby="cartDropdown"
                            style="width: 380px;">
                            <div class="p-3">
                                <h6 class="mb-0">Giỏ hàng</h6>
                            </div>
                            <div class="dropdown-divider m-0"></div>

                            {{-- Danh sách sản phẩm sẽ được JS chèn vào đây --}}
                            <div id="header-cart-items-container" style="max-height: 300px; overflow-y: auto;">
                                <div class="p-4 text-center text-muted">Giỏ hàng của bạn đang trống</div>
                            </div>

                            {{-- Phần tổng tiền và các nút hành động --}}
                            <div id="header-cart-footer" class="p-3 border-top d-none">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fw-bold">Tổng cộng:</span>
                                    <span class="fw-bold text-danger" id="header-cart-total">0 ₫</span>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('cart.index') }}" class="btn btn-primary">Xem giỏ hàng chi
                                        tiết</a>
                                    <a href="#" class="btn btn-warning">Tiến hành thanh toán</a>
                                </div>
                            </div>
                        </div>
                    </li>









                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> Tài khoản
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark"
                            aria-labelledby="navbarDropdownUser">
                            @guest('customer')
                                <li><a class="dropdown-item" href="{{ route('login') }}">Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="{{ route('register') }}">Đăng ký</a></li>
                            @endguest
                            @auth('customer')
                                <li><a class="dropdown-item" href="{{ route('account.profile') }}">Tài khoản của tôi</a>
                                </li>
                                <li><a class="dropdown-item" href="#">Đơn hàng của tôi</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Đăng xuất
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf
                                    </form>
                                </li>
                            @endauth
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-md-none bg-light p-3">
        <form class="input-group" action="#">
            <input type="text" class="form-control" placeholder="Tìm kiếm sản phẩm...">
            <button class="btn btn-primary" type="button">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</header>