<nav class="sidebar" id="sidebar">
    <div class="logo">
        <i class="bi bi-shield-lock-fill"></i> Admin Panel
    </div>
    <ul class="nav flex-column">
        {{-- =================== MAIN =================== --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
        </li>

        {{-- Quản lý Bán Hàng (Collapsible) --}}
        @php
            $isSalesMenuActive = request()->routeIs('admin.sales.orders') ||
                                 request()->routeIs('admin.sales.promotions');
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
                    <a class="nav-link {{ request()->routeIs('admin.sales.promotions') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.sales.promotions') }}">Mã Khuyến mãi</a>
                </li>
            </ul>
        </li>

        {{-- Quản lý Sản phẩm (Collapsible) --}}
        @php
            $isProductMenuActive = request()->routeIs('admin.productManagement.products') ||
                                   request()->routeIs('admin.productManagement.categories') ||
                                   request()->routeIs('admin.productManagement.brands') ||
                                   request()->routeIs('admin.productManagement.vehicle') ||
                                   request()->routeIs('admin.productManagement.inventory');
        @endphp
        <li class="nav-item">
            <a class="nav-link {{ $isProductMenuActive ? 'active' : '' }}" href="#productSubmenu"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $isProductMenuActive ? 'true' : 'false' }}" aria-controls="productSubmenu">
                <i class="bi bi-box-seam-fill"></i> Quản lý Sản phẩm <i class="bi bi-caret-down-fill ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled ps-4 {{ $isProductMenuActive ? 'show' : '' }}" id="productSubmenu">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.productManagement.products') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.productManagement.products') }}">Danh sách Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.productManagement.categories') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.productManagement.categories') }}">Danh mục</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.productManagement.brands') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.productManagement.brands') }}">Thương hiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.productManagement.vehicle') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.productManagement.vehicle') }}">Hãng xe</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.productManagement.inventory') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.productManagement.inventory') }}">Tồn kho</a>
                </li>
            </ul>
        </li>

        {{-- Quản lý Nội dung (Collapsible) --}}
        @php
            $isContentMenuActive = request()->routeIs('admin.content.posts') ||
                                   request()->routeIs('admin.content.reviews');
        @endphp
        <li class="nav-item">
            <a class="nav-link {{ $isContentMenuActive ? 'active' : '' }}" href="#contentSubmenu"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $isContentMenuActive ? 'true' : 'false' }}" aria-controls="contentSubmenu">
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

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
                href="{{ route('admin.reports') }}">
                <i class="bi bi-bar-chart-line-fill"></i> Thống Kê & Báo Cáo
            </a>
        </li>


        {{-- =================== HỆ THỐNG =================== --}}
        <li class="nav-item mt-3 pt-2 border-top border-secondary">
            <span class="nav-link disabled text-uppercase"
                style="color: #6c757d; font-size: 0.9rem; letter-spacing: 0.05em;">
                Hệ Thống
            </span>
        </li>

        {{-- Quản lý Người dùng (Collapsible) --}}
        @php
            $isUserMenuActive = request()->routeIs('admin.userManagement.admins') ||
                                request()->routeIs('admin.userManagement.customers');
        @endphp
        <li class="nav-item">
            <a class="nav-link {{ $isUserMenuActive ? 'active' : '' }}" href="#userManagementSubmenu"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $isUserMenuActive ? 'true' : 'false' }}" aria-controls="userManagementSubmenu">
                <i class="bi bi-people-fill"></i> Quản lý Người dùng <i class="bi bi-caret-down-fill ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled ps-4 {{ $isUserMenuActive ? 'show' : '' }}" id="userManagementSubmenu">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.userManagement.admins') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.userManagement.admins') }}">
                        <i class="bi bi-shield-lock-fill me-2"></i>Quản trị viên
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.userManagement.customers') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.userManagement.customers') }}">
                        <i class="bi bi-person-lines-fill me-2"></i>Khách hàng
                    </a>
                </li>
            </ul>
        </li>

        {{-- Cấu hình Hệ thống (Collapsible) --}}
        @php
            // Mục cha "Cấu hình Hệ thống" sẽ active nếu bạn ở một trong các trang con của nó
            $isSystemMenuActive = request()->routeIs('admin.system.delivery') ||
                                  request()->routeIs('admin.system.geography.*') || // Sử dụng wildcard * để bao gồm tất cả các route con của geography
                                  request()->routeIs('admin.system.settings');
        @endphp
        <li class="nav-item">
            <a class="nav-link {{ $isSystemMenuActive ? 'active' : '' }}" href="#configSubmenu"
                data-bs-toggle="collapse" role="button" aria-expanded="{{ $isSystemMenuActive ? 'true' : 'false' }}" aria-controls="configSubmenu">
                <i class="bi bi-gear-fill"></i> Cấu hình Hệ thống <i class="bi bi-caret-down-fill ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled ps-4 {{ $isSystemMenuActive ? 'show' : '' }}" id="configSubmenu">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.system.delivery') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.system.delivery') }}">Đơn vị Giao hàng</a>
                </li>
                {{-- Mục Quản lý Địa lý --}}
                <li class="nav-item">
                    {{-- SỬA Ở ĐÂY: Liên kết đến trang tổng quan/import của địa lý --}}
                    <a class="nav-link {{ request()->routeIs('admin.system.geography.index') ? 'active-submenu' : '' }}"
                       href="{{ route('admin.system.geography.index') }}">
                       <i class="bi bi-globe-americas me-2"></i> Quản lý Địa lý
                    </a>
                </li>
                {{-- Bạn có thể bỏ các link con riêng cho Tỉnh/Thành nếu trang geography.index đã hiển thị tất cả qua tabs --}}
                {{--
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.system.geography.provinces.*') ? 'active-submenu' : '' }}"
                       href="{{ route('admin.system.geography.provinces.index') }}">
                       <i class="bi bi-geo-alt-fill me-2"></i> Tỉnh/Thành phố
                    </a>
                </li>
                --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.system.settings') ? 'active-submenu' : '' }}"
                        href="{{ route('admin.system.settings') }}">Cài đặt chung</a>
                </li>
            </ul>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <img src="https://placehold.co/40x40/001529/FFF?text=AD" alt="Admin Avatar">
            <div>
                <strong>Admin Name</strong><br>
                <small>Quản trị viên</small>
            </div>
        </div>
    </div>
</nav>
