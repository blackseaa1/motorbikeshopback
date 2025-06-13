{{-- SỬA ĐỔI: Header mới từ index.html, tích hợp logic Laravel --}}
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-motorcycle me-2"></i>MotoToys
            </a>

            <div class="d-none d-md-flex flex-grow-1 mx-4">
                <form class="input-group" action="#"> {{-- Thay action="#" bằng route tìm kiếm của bạn --}}
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
                        <a class="nav-link" href="#categories">Danh mục</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#blog">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Liên hệ</a>
                    </li>
                    <li class="nav-item">
                        {{-- Giữ lại logic giỏ hàng từ file cũ --}}
                        <a class="nav-link position-relative" href="#">
                            <i class="bi bi-cart3"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                            </span>
                        </a>
                    </li>
                    {{-- Giữ lại dropdown tài khoản từ file cũ và tích hợp vào --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> Tài khoản
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            @guest
                                <li><a class="dropdown-item" href="{{ route('login') }}">Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="{{ route('register') }}">Đăng ký</a></li>
                            @endguest
                            @auth
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
        <form class="input-group" action="#"> {{-- Thay action="#" bằng route tìm kiếm của bạn --}}
            <input type="text" class="form-control" placeholder="Search products...">
            <button class="btn btn-primary" type="button">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</header>