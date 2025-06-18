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

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('categories.index') }}"
                            id="navbarDropdownCategories" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Danh mục
                        </a>
                        <div class="dropdown-menu category-dropdown-menu" aria-labelledby="navbarDropdownCategories">
                            <div class="category-dropdown-columns">
                                @if($sharedCategories->isNotEmpty())
                                    @foreach ($sharedCategories->chunk(5) as $chunk)
                                        <div class="category-column">
                                            @foreach ($chunk as $category)
                                                {{-- SỬA ĐỔI: Dùng route 'products.category' và truyền id --}}
                                                <a class="dropdown-item"
                                                    href="{{ route('products.category', ['category' => $category->id]) }}">
                                                    {{ $category->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @else
                                    <a class="dropdown-item" href="#">Không có danh mục nào</a>
                                @endif
                            </div>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('blog') }}">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">Liên hệ</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-cart-fill"></i>
                            <span class="badge bg-danger">3</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAccount" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownAccount">
                            @guest
                                <li><a class="dropdown-item" href="{{ route('login') }}">Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="{{ route('register') }}">Đăng ký</a></li>
                            @endguest
                            @auth
                                <li><a class="dropdown-item" href="{{ route('account.profile') }}">Tài khoản của tôi</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('account.orders') }}">Đơn hàng của tôi</a></li>
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