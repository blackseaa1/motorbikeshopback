@auth('admin')
    @php
        // Lấy thông tin người dùng từ guard 'admin'.
        // Dòng này hoạt động cho TẤT CẢ các vai trò (super admin, admin, staff, etc.)
        $currentUser = Auth::guard('admin')->user();
    @endphp
    <nav class="top-nav d-flex">
        <button class="sidebar-toggler" type="button" id="sidebarToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        {{-- <div class="input-group input-group-sm me-auto" style="max-width: 320px;">
            <span class="input-group-text bg-transparent border-end-0" id="searchAddon"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control form-control-sm border-start-0"
                placeholder="Tìm kiếm sản phẩm, đơn hàng..." aria-label="Search" aria-describedby="searchAddon">
        </div> --}}
        <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
            {{-- Phần thông báo --}}
            {{-- <li class="nav-item dropdown me-3">
                <a class="nav-link" href="#" id="navbarDropdownNotif" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-bell-fill"></i>
                    <span id="notification-badge-count"
                        class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" ...>3</span>
                </a>
                Dropdown content
            </li> --}}

            {{-- Phần thông tin người dùng --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownUser" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">

                    {{-- Sử dụng biến $currentUser. Hoạt động cho mọi vai trò. --}}
                    {{ $currentUser->name }}

                    {{-- Sử dụng accessor avatar_url từ $currentUser. Hoạt động cho mọi vai trò. --}}
                    <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }}" class="user-avatar">

                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                    <li><a class="dropdown-item" href="{{ route('admin.profile.show') }}"><i
                                class="bi bi-person-circle me-2"></i>Hồ sơ</a></li>
                    {{-- <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Cài đặt</a></li> --}}
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                        </a>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
@endauth