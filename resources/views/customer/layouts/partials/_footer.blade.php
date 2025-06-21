<style>
    .custom-link:hover {
        color: var(--bs-primary) !important;
    }
</style>
<footer id="contact" class="bg-dark text-white py-5 ">
    <div class="container">
        <div class="row g-4">
            {{-- Cột thông tin shop --}}
            <div class="col-lg-3 col-md-12 mb-4 mb-lg-0">
                <h5 class="fw-bold mb-3">
                    <a href="{{ route('home') }}" class="text-white text-decoration-none">
                        <img src="{{ asset('assets_admin/images/thanhdo_shop_logo.png') }}" alt="ThanhDoShop"
                            height="40" class="d-inline-block align-middle me-2">
                        Thành Đô Shop
                    </a>
                </h5>
                <p class="text-white-50">Đối tác tin cậy của bạn cho các phụ kiện, phụ tùng và đồ chơi xe máy sưu tầm
                    cao cấp. Chất lượng đảm bảo.</p>
                <div class="d-flex gap-3">
                    <a href="https://www.facebook.com/nhan.sieu.779" class="text-white-50 fs-4"><i
                            class="bi bi-facebook"></i></a>
                    <a href="https://www.youtube.com/channel/UCxmqaFN7BKiRhkQtj3LogqQ" class="text-white-50 fs-4"><i
                            class="bi bi-youtube"></i></a>
                </div>
            </div>

            {{-- Cột điều hướng chính --}}
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Điều hướng</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('home') }}"
                            class="text-white-50 text-decoration-none custom-link">Trang Chủ</a></li>
                    <li class="mb-2"><a href="{{ route('products.index') }}"
                            class="text-white-50 text-decoration-none custom-link">Cửa Hàng</a></li>
                    <li class="mb-2"><a href="{{ route('blog.index') }}"
                            class="text-white-50 text-decoration-none custom-link">Blog</a></li>
                    <li class="mb-2"><a href="{{ route('contact.index') }}"
                            class="text-white-50 text-decoration-none custom-link">Liên hệ</a></li>
                </ul>
            </div>

            {{-- ============================================= --}}
            {{-- == THÔNG TIN TÀI KHOẢN KHÁCH HÀNG (MỚI) == --}}
            {{-- ============================================= --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Tài khoản của tôi</h6>
                <ul class="list-unstyled">
                    {{-- Hiển thị khi khách chưa đăng nhập --}}
                    @guest('customer')
                        <li class="mb-2"><a href="{{ route('login') }}"
                                class="text-white-50 text-decoration-none custom-link">Đăng nhập</a></li>
                        <li class="mb-2"><a href="{{ route('register') }}"
                                class="text-white-50 text-decoration-none custom-link">Đăng ký</a></li>
                    @endguest

                    {{-- Hiển thị khi khách đã đăng nhập --}}
                    @auth('customer')
                        <li class="mb-2"><a href="{{ route('account.profile') }}"
                                class="text-white-50 text-decoration-none custom-link">Hồ sơ của tôi</a></li>
                        <li class="mb-2"><a href="{{ route('account.orders') }}"
                                class="text-white-50 text-decoration-none custom-link">Đơn hàng của tôi</a></li>
                        <li class="mb-2"><a href="" class="text-white-50 text-decoration-none custom-link">Bài viết của
                                tôi</a></li>
                        <li class="mb-2">
                            <a href="#" class="text-white-50 text-decoration-none custom-link"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Đăng xuất
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>

            {{-- Cột thông tin liên hệ --}}
            <div class="col-lg-4">
                <h6 class="fw-bold mb-3">Thông tin liên hệ</h6>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>62 - Châu Văn Liêm - Phú Đô - Nam Từ Liêm - Hà
                        Nội</li>
                    <li class="mb-2 d-flex">
                        <i class="bi bi-telephone me-2"></i>
                        <span>
                            <a href="tel:0394831886"
                                class="text-decoration-none text-white-50 custom-link">0394831886</a> -
                            <a href="tel:0973634129"
                                class="text-decoration-none text-white-50 custom-link">0973634129</a>
                        </span>
                    </li>
                    <li class="mb-2"><i class="bi bi-envelope me-2"></i>
                        <a href="mailto:thanhdoshop@gmail.com"
                            class="text-decoration-none text-white-50 custom-link">thanhdoshop@gmail.com</a>
                    </li>
                    <li><i class="bi bi-clock me-2"></i>Thứ 2 - 6: 9h-18h, Thứ 7: 10h-16h</li>
                </ul>
            </div>
        </div>
        <hr class="my-4 text-white-50">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-white-50 mb-md-0 mb-2">© {{ date('Y') }} ThanhDoShop. Đã đăng ký bản quyền.</p>
            </div>
            <div class="col-md-6 text-md-end">
                {{-- Có thể thêm thông tin thanh toán hoặc các logo khác ở đây --}}
            </div>
        </div>
    </div>
</footer>