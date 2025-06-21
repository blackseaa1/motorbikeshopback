{{-- VIẾT LẠI FILE NÀY --}}

<div class="card">
    <div class="card-body text-center">
        <div class="mb-3">
            <img id="sidebar-avatar-img" src="{{ Auth::user()->avatar_url }}" class="rounded-circle img-thumbnail"
                alt="User Avatar" style="width: 150px; height: 150px; object-fit: cover;">
        </div>
        {{-- THÊM ID VÀO ĐÂY --}}
        <h5 class="card-title" id="sidebar-user-name">{{ Auth::user()->name }}</h5>
        <p class="text-muted">Thành viên từ {{ Auth::user()->created_at->format('m/Y') }}</p>
    </div>
    <div class="list-group list-group-flush">
        <a href="{{ route('account.profile') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('account.profile') ? 'active' : '' }}">
            <i class="bi bi-person me-2"></i> Hồ sơ của tôi
        </a>
        <a href="{{ route('account.orders.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('account.orders*') ? 'active' : '' }}">
            <i class="bi bi-box me-2"></i> Đơn hàng của tôi
        </a>
        {{-- Sửa lại route và logic active cho Sổ địa chỉ --}}
        <a href="{{ route('account.addresses.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('account.addresses*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt me-2"></i> Sổ địa chỉ
        </a>
        {{-- Nút đăng xuất giữ nguyên --}}
        <a href="#" class="list-group-item list-group-item-action text-danger"
            onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
        </a>
        <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
</div>
</div>