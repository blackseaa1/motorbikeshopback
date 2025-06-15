{{-- resources/views/admin/layouts/partials/sidebar.blade.php --}}
@php
    // Lấy thông tin người dùng hiện tại một lần để tái sử dụng
    $currentUser = Auth::guard('admin')->user();
@endphp

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <a href="dashboard" style="color:#fff;font-size:20px"><img src="{{ asset('assets_admin/images/thanhdo_shop_logo.png') }}"
                    alt="Thành Đô Shop Logo" style="width:26%; height: auto;"> Thành Đô Shop</a>
        </div>
        <button class="btn btn-sm sidebar-close-button d-lg-none" type="button" id="sidebarCloseButton"
            aria-label="Close sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    @if ($currentUser)
        <ul class="nav flex-column">
            {{-- =================== MAIN =================== --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            {{-- Quản lý Bán Hàng: Dành cho Super Admin, Admin và Staff --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_ADMIN || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                @php $isSalesMenuActive = request()->routeIs('admin.sales.*'); @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isSalesMenuActive ? 'active' : '' }}" href="#salesSubmenu" data-bs-toggle="collapse"
                        role="button" aria-expanded="{{ $isSalesMenuActive ? 'true' : 'false' }}">
                        <i class="bi bi-receipt-cutoff"></i> Quản lý Bán Hàng <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isSalesMenuActive ? 'show' : '' }}" id="salesSubmenu">
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.sales.orders.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.sales.orders.index') }}">Đơn hàng</a></li>
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.sales.promotions.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.sales.promotions.index') }}">Mã Khuyến mãi</a></li>
                    </ul>
                </li>
            @endif

            {{-- Quản lý Sản phẩm: Dành cho tất cả các vai trò liên quan --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_ADMIN || $currentUser->role === \App\Models\Admin::ROLE_STAFF || $currentUser->role === \App\Models\Admin::ROLE_WAREHOUSE_STAFF)
                @php $isProductMenuActive = request()->routeIs('admin.productManagement.*'); @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isProductMenuActive ? 'active' : '' }}" href="#productSubmenu"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $isProductMenuActive ? 'true' : 'false' }}">
                        <i class="bi bi-box-seam-fill"></i> Quản lý Sản phẩm <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isProductMenuActive ? 'show' : '' }}" id="productSubmenu">
                        {{-- Warehouse Staff thấy tất cả mục sản phẩm --}}
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.productManagement.products.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.productManagement.products.index') }}">Danh sách Sản phẩm</a></li>
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.productManagement.categories.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.productManagement.categories.index') }}">Danh mục</a></li>
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.productManagement.brands.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.productManagement.brands.index') }}">Thương hiệu</a></li>
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.productManagement.vehicle.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.productManagement.vehicle.index') }}">Hãng xe & Dòng xe</a></li>
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.productManagement.inventory.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.productManagement.inventory.index') }}">Tồn kho</a></li>
                    </ul>
                </li>
            @endif

            {{-- Quản lý Nội dung: Tất cả các vai trò đều có thể truy cập để quản lý bài viết của mình --}}
            @php $isContentMenuActive = request()->routeIs('admin.content.*'); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $isContentMenuActive ? 'active' : '' }}" href="#contentSubmenu"
                    data-bs-toggle="collapse" role="button" aria-expanded="{{ $isContentMenuActive ? 'true' : 'false' }}">
                    <i class="bi bi-file-text-fill"></i> Quản lý Nội dung <i class="bi bi-caret-down-fill ms-auto"></i>
                </a>
                <ul class="collapse list-unstyled ps-4 {{ $isContentMenuActive ? 'show' : '' }}" id="contentSubmenu">
                    <li class="nav-item"><a
                            class="nav-link {{ request()->routeIs('admin.content.blogs.*') ? 'active-submenu' : '' }}"
                            href="{{ route('admin.content.blogs.index') }}">Blog/Bài viết</a></li>
                    {{-- Chỉ Super Admin và Admin mới quản lý được Đánh giá --}}
                    @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_ADMIN)
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.content.reviews.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.content.reviews.index') }}">Đánh giá sản phẩm</a></li>
                    @endif
                </ul>
            </li>

            {{-- Thống Kê & Báo Cáo: Super Admin, Admin và Staff --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_ADMIN || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
                        href="{{ route('admin.reports') }}">
                        <i class="bi bi-bar-chart-line-fill"></i> Thống Kê & Báo Cáo
                    </a>
                </li>
            @endif

            {{-- =================== HỆ THỐNG =================== --}}
            {{-- Nhóm Hệ Thống chỉ hiển thị cho các vai trò có quyền truy cập --}}
            @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_ADMIN || $currentUser->role === \App\Models\Admin::ROLE_STAFF)
                <li class="nav-item mt-3 pt-2 border-top border-secondary">
                    <span class="nav-link disabled text-uppercase" style="color: #6c757d; font-size: 0.9rem;">
                        Hệ Thống
                    </span>
                </li>

                {{-- Quản lý Người dùng: Hiển thị cho Super Admin, Admin và Staff --}}
                @php $isUserMenuActive = request()->routeIs('admin.userManagement.*'); @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $isUserMenuActive ? 'active' : '' }}" href="#userManagementSubmenu"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $isUserMenuActive ? 'true' : 'false' }}">
                        <i class="bi bi-people-fill"></i> Quản lý Người dùng <i class="bi bi-caret-down-fill ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 {{ $isUserMenuActive ? 'show' : '' }}" id="userManagementSubmenu">
                        {{-- Chỉ Super Admin và Admin mới quản lý được Nhân viên --}}
                        @if ($currentUser->isSuperAdmin() || $currentUser->role === \App\Models\Admin::ROLE_ADMIN)
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('admin.userManagement.staff.*') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.userManagement.staff.index') }}">Nhân viên</a></li>
                        @endif
                        {{-- Super Admin, Admin và Staff đều có thể quản lý Khách hàng --}}
                        <li class="nav-item"><a
                                class="nav-link {{ request()->routeIs('admin.userManagement.customers.*') ? 'active-submenu' : '' }}"
                                href="{{ route('admin.userManagement.customers.index') }}">Khách hàng</a></li>
                    </ul>
                </li>

                {{-- Cấu hình Hệ thống: Chỉ Super Admin --}}
                @if ($currentUser->isSuperAdmin())
                    @php $isSystemMenuActive = request()->routeIs('admin.system.*'); @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $isSystemMenuActive ? 'active' : '' }}" href="#configSubmenu"
                            data-bs-toggle="collapse" role="button" aria-expanded="{{ $isSystemMenuActive ? 'true' : 'false' }}">
                            <i class="bi bi-gear-fill"></i> Cấu hình Hệ thống <i class="bi bi-caret-down-fill ms-auto"></i>
                        </a>
                        <ul class="collapse list-unstyled ps-4 {{ $isSystemMenuActive ? 'show' : '' }}" id="configSubmenu">
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('admin.system.deliveryServices.*') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.system.deliveryServices.index') }}">Đơn vị Giao hàng</a></li>
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('admin.system.geography.*') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.system.geography.index') }}">Quản lý Địa lý</a></li>
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('admin.system.settings') ? 'active-submenu' : '' }}"
                                    href="{{ route('admin.system.settings') }}">Cài đặt chung</a></li>
                        </ul>
                    </li>
                @endif
            @endif
        </ul>
    @endif
    {{--
    <div class="sidebar-footer">
        @if ($currentUser)
        <div class="user-info">
            <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }} Avatar"
                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
            <div>
                <strong>{{ $currentUser->name }}</strong><br>
                <small>{{ $currentUser->role_name }}</small>
            </div>
        </div>
        @endif
    </div> --}}
</nav>