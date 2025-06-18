<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-motorcycle me-2"></i>MotoToys
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
                        <a class="nav-link" href="{{ route('products.index') }}">Sản phẩm</a>
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

                    <li class="nav-item">
                        {{-- Logic giỏ hàng --}}
                        <a class="nav-link position-relative" href="#">
                            <i class="bi bi-cart3"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3 {{-- Số lượng sản phẩm trong giỏ hàng sẽ được cập nhật động sau --}}
                            </span>
                        </a>
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
                                <li><a class="dropdown-item" href="#">Tài khoản của tôi</a>
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