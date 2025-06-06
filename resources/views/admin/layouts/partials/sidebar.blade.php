{{-- resources/views/admin/layouts/sidebar.blade.php --}}
@php
    $currentUser = Auth::guard('admin')->user();
@endphp

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="bi bi-shield-lock-fill"></i> Admin Panel
        </div>
        <button class="btn btn-sm sidebar-close-button d-lg-none" type="button" id="sidebarCloseButton"
            aria-label="Close sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    @if ($currentUser) {{-- Chỉ hiển thị menu nếu người dùng đã đăng nhập và có thông tin --}}
        <ul class="nav flex-column">
            {{-- =================== MAIN =================== --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            {{-- Quản lý Bán Hàng --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                @php
                    $isSalesMenuActive = request()->routeIs('admin.sales.orders') ||
                        request()->routeIs('admin.sales.promotions.index');
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isSalesMenuActive ? 'active' : '' }}" href="#salesSubmenu" data-bs-toggle="collapse"
                        role="button" aria-expanded="{{ $isSalesMenuActive ? 'true' : 'false' }}" aria-controls="salesSubmenu">
                        <i class="bi bi-receipt-cutoff"></i> Quản lý Bán Hàng <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isSalesMenuActive ? 'show' : '' }}" id="salesSubmenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sales.orders') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.sales.orders') }}">Đơn hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sales.promotions.index') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.sales.promotions.index') }}">Mã Khuyến mãi</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Quản lý Sản phẩm --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_STAFF || $currentUser->role === \App\Models\Admin::ROLE_WAREHOUSE_STAFF)
                @php
                    $isProductMenuActive = request()->routeIs('admin.productManagement.products') ||
                        request()->routeIs('admin.productManagement.categories.index') ||
                        request()->routeIs('admin.productManagement.brands.index') ||
                        request()->routeIs('admin.productManagement.vehicle.index') ||
                        request()->routeIs('admin.productManagement.inventory');
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isProductMenuActive ? 'active' : '' }}" href="#productSubmenu"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $isProductMenuActive ? 'true' : 'false' }}"
                        aria-controls="productSubmenu">
                        <i class="bi bi-box-seam-fill"></i> Quản lý Sản phẩm <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isProductMenuActive ? 'show' : '' }}" id="productSubmenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.productManagement.products') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.productManagement.products') }}">Danh sách Sản phẩm</a>
                        </li>
                        @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.productManagement.categories.index') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.productManagement.categories.index') }}">Danh mục</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.productManagement.brands.index') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.productManagement.brands.index') }}">Thương hiệu</a>
                            </li>
                        @endif
                        {{-- Cả Staff và Warehouse Staff có thể cần xem Hãng xe & Dòng xe, Tồn kho --}}
                        @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_STAFF || $currentUser->role === \App\Models\Admin::ROLE_WAREHOUSE_STAFF)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.productManagement.vehicle.index') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.productManagement.vehicle.index') }}">Hãng xe & Dòng xe</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.productManagement.inventory') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.productManagement.inventory') }}">Tồn kho</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- Quản lý Nội dung --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                @php
                    $isContentMenuActive = request()->routeIs('admin.content.posts') ||
                        request()->routeIs('admin.content.reviews');
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isContentMenuActive ? 'active' : '' }}" href="#contentSubmenu"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $isContentMenuActive ? 'true' : 'false' }}"
                        aria-controls="contentSubmenu">
                        <i class="bi bi-file-text-fill"></i> Quản lý Nội dung <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isContentMenuActive ? 'show' : '' }}" id="contentSubmenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.content.posts') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.content.posts') }}">Blog/Bài viết</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.content.reviews') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.content.reviews') }}">Đánh giá sản phẩm</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Thống Kê & Báo Cáo --}}
            @if ($currentUser->isSuperAdmin()) {{-- Chỉ Super Admin được xem Thống kê & Báo cáo --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
                        href="{{ route('admin.reports') }}">
                        <i class="bi bi-bar-chart-line-fill"></i> Thống Kê & Báo Cáo
                    </a>
                </li>
            @endif

            {{-- =================== HỆ THỐNG (Thường chỉ Super Admin) =================== --}}
            @if ($currentUser->isSuperAdmin())
                <li class="nav-item mt-3 pt-2 border-top border-secondary">
                    <span class="nav-link disabled text-uppercase"
                        style="color: #6c757d; font-size: 0.9rem; letter-spacing: 0.05em;">
                        Hệ Thống
                    </span>
                </li>

                {{-- Quản lý Người dùng --}}
                @php
                    // Điều chỉnh $isUserMenuActive để chỉ bao gồm route của khách hàng nếu nhân viên không phải là Super Admin
                    // Super Admin sẽ thấy cả Nhân viên và Khách hàng
                    // Staff sẽ chỉ thấy Khách hàng (theo ví dụ)
                    $userManagementRoutes = ['admin.userManagement.customers.index'];
                    if ($currentUser->isSuperAdmin()) {
                        $userManagementRoutes[] = 'admin.userManagement.staff.index';
                    }
                    $isUserMenuActive = request()->routeIs($userManagementRoutes);
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isUserMenuActive ? 'active' : '' }}" href="#userManagementSubmenu"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $isUserMenuActive ? 'true' : 'false' }}"
                        aria-controls="userManagementSubmenu">
                        <i class="bi bi-people-fill"></i> Quản lý Người dùng <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isUserMenuActive ? 'show' : '' }}" id="userManagementSubmenu">
                        @if ($currentUser->isSuperAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.userManagement.staff.index') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.userManagement.staff.index') }}">
                                    <i class="bi bi-shield-lock-fill me-2"></i>Nhân viên
                                </a>
                            </li>
                        @endif
                        {{-- Nhân viên Hỗ trợ cũng có thể quản lý Khách hàng --}}
                        @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.userManagement.customers.index') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.userManagement.customers.index') }}">
                                    <i class="bi bi-person-lines-fill me-2"></i>Khách hàng
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                {{-- Cấu hình Hệ thống (Chỉ Super Admin) --}}
                @if ($currentUser->isSuperAdmin())
                    @php
                        $isSystemMenuActive = request()->routeIs('admin.system.deliveryServices.index') ||
                            request()->routeIs('admin.system.geography.*') || // Sử dụng wildcard cho route địa lý
                            request()->routeIs('admin.system.settings');
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $isSystemMenuActive ? 'active' : '' }}" href="#configSubmenu"
                            data-bs-toggle="collapse" role="button" aria-expanded="{{ $isSystemMenuActive ? 'true' : 'false' }}"
                            aria-controls="configSubmenu">
                            <i class="bi bi-gear-fill"></i> Cấu hình Hệ thống <i class="bi bi-caret-down-fill ms-auto"></i>
                        </a>
                        <ul class="collapse list-unstyled ps-4 {{ $isSystemMenuActive ? 'show' : '' }}" id="configSubmenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.system.deliveryServices.index') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.system.deliveryServices.index') }}">Đơn vị Giao hàng</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.system.geography.index') || request()->routeIs('admin.system.geography.*') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.system.geography.index') }}">
                                    <i class="bi bi-globe-americas me-2"></i> Quản lý Địa lý
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.system.settings') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.system.settings') }}">Cài đặt chung</a>
                            </li>
                        </ul>
                    </li>
                @endif {{-- Kết thúc Cấu hình Hệ thống (Chỉ Super Admin) --}}
            @endif {{-- Kết thúc Mục Hệ Thống (Chỉ Super Admin) --}}
        </ul>
    @endif {{-- Kết thúc if ($currentUser) --}}


    <div class="sidebar-footer">
        @if ($currentUser)
            <div class="user-info">
                <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }} Avatar"
                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                <div>
                    <strong>{{ $currentUser->name }}</strong><br>
                    <small>{{ $currentUser->role_name }}</small> {{-- Sử dụng accessor đã tạo --}}
                </div>
            </div>
        @else
            <div class="user-info">
                <img src="https://placehold.co/40x40/001529/FFF?text=N/A" alt="Not Logged In Avatar"
                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                <div>
                    <strong>Khách</strong><br>
                    <small>Chưa đăng nhập</small>
                </div>
            </div>
        @endif
    </div>
</nav>