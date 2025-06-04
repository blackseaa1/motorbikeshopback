{{-- Bọc trong @auth('admin') để đảm bảo chỉ hiển thị khi admin đã đăng nhập --}}
@auth('admin')
    <nav class="top-nav d-flex">
        <button class="sidebar-toggler" type="button" id="sidebarToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div class="input-group input-group-sm me-auto" style="max-width: 320px;">
            <span class="input-group-text bg-transparent border-end-0" id="searchAddon"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control form-control-sm border-start-0"
                placeholder="Tìm kiếm sản phẩm, đơn hàng..." aria-label="Search" aria-describedby="searchAddon">
        </div>
        <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
            {{-- Phần thông báo (giữ nguyên hoặc tùy chỉnh sau) --}}
            <li class="nav-item dropdown me-3">
                <a class="nav-link" href="#" id="navbarDropdownNotif" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-bell-fill"></i>
                    <span id="notification-badge-count"
                        class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" ...>3</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="navbarDropdownNotif"
                    style="min-width: 280px;">
                    <li>
                        <h6 class="dropdown-header">Thông báo</h6>
                    </li>
                    <li><a class="dropdown-item d-flex align-items-start" href="#">
                            <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
                            <div>Đơn hàng mới #1234 <br><small class="text-muted">5 phút trước</small></div>
                        </a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-center" href="#">Xem tất cả</a></li>
                </ul>
            </li>

            {{-- Phần thông tin người dùng --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownUser" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">

                    {{-- THAY ĐỔI: Hiển thị tên admin đang đăng nhập --}}
                    {{ Auth::user()->name }}

                    {{-- THAY ĐỔI: Hiển thị avatar của admin hoặc ảnh mặc định --}}
                    @if(Auth::user()->img)
                        <img src="{{ asset(Auth::user()->img) }}" alt="{{ Auth::user()->name }}" class="user-avatar">
                    @else
                        {{-- Ảnh mặc định với chữ cái đầu của tên --}}
                        <img src="https://placehold.co/32x32/001529/FFF?text={{ mb_substr(Auth::user()->name, 0, 1) }}"
                            alt="{{ Auth::user()->name }}" class="user-avatar">
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                    <li><a class="dropdown-item" href="{{ route('admin.profile.show') }}"><i
                                class="bi bi-person-circle me-2"></i>Hồ sơ</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Cài đặt</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    {{-- THAY ĐỔI: Chức năng Đăng xuất --}}
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                        </a>
                        {{-- Form này sẽ được submit bằng Javascript khi click vào link trên --}}
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
@endauth