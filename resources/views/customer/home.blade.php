@extends('customer.layouts.app')

@section('title', 'Trang chủ - MotoToys Store')

@section('content')
    {{-- Main Carousel --}}
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="banner-slide bg-gradient-primary">
                    <div class="container d-flex align-items-center" style="min-height: 50vh;">
                        <div class="row align-items-center">
                            <div class="col-lg-6 text-white">
                                <h1 class="display-4 fw-bold mb-4">Summer Sale 2025</h1>
                                <p class="lead mb-4">Giảm giá 20% với mã <strong>SUMMER25</strong> - Chỉ trong tuần này!</p>
                                <a href="{{ route('products.index') }}" class="btn btn-warning btn-lg px-4">Mua ngay <i
                                        class="bi bi-arrow-right ms-2"></i></a>
                            </div>
                            <div class="col-lg-6 text-center d-none d-lg-block">
                                <img src="https://placehold.co/500x400/FFFFFF/333333?text=Moto+Parts"
                                    alt="Motorcycle Accessories" class="img-fluid rounded shadow">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="banner-slide bg-gradient-success">
                    <div class="container d-flex align-items-center" style="min-height: 50vh;">
                        <div class="row align-items-center">
                            <div class="col-lg-6 text-white">
                                <h1 class="display-4 fw-bold mb-4">Miễn phí vận chuyển</h1>
                                <p class="lead mb-4">Cho đơn hàng trên 1.000.000đ. Giao hàng nhanh chóng và tin cậy.</p>
                                <a href="#" class="btn btn-light btn-lg px-4">Tìm hiểu thêm <i
                                        class="bi bi-truck ms-2"></i></a>
                            </div>
                            <div class="col-lg-6 text-center d-none d-lg-block">
                                <img src="https://placehold.co/500x400/FFFFFF/333333?text=Free+Shipping" alt="Fast Delivery"
                                    class="img-fluid rounded shadow">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev"><span
                class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next"><span
                class="carousel-control-next-icon"></span></button>
    </div>

    {{-- Featured Categories Section --}}
    <section class="py-5"> {{-- Đã bỏ nền xám bg-light --}}
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Mua sắm theo danh mục</h2>
                <p class="lead text-muted">Tìm kiếm phụ tùng hoàn hảo cho chiếc xe của bạn.</p>
            </div>

            @if(isset($categories) && $categories->isNotEmpty())

                {{-- =============================================================== --}}
                {{-- ================ CAROUSEL CHO MÀN HÌNH LỚN (DESKTOP) ============== --}}
                {{-- =============================================================== --}}
                <div class="carousel-wrapper d-none d-lg-block">
                    <div id="categoryCarouselDesktop" class="carousel slide" data-bs-ride="false">

                        {{-- Nút chỉ báo (Indicators) --}}
                        <div class="carousel-indicators">
                            @foreach($categories->chunk(4) as $index => $chunk)
                                <button type="button" data-bs-target="#categoryCarouselDesktop" data-bs-slide-to="{{ $index }}"
                                    class="{{ $index == 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach($categories->chunk(4) as $index => $chunk)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <div class="row g-4">
                                        @foreach($chunk as $category)
                                            <div class="col-lg-3"> {{-- Luôn là 4 cột --}}
                                                <a href="{{ route('categories.show', $category->id) }}" class="text-decoration-none">
                                                    <div class="category-text-card">
                                                        <h6 class="mb-0 text-truncate">{{ $category->name }}</h6>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Nút điều khiển --}}
                        <button class="carousel-control-desktop prev" type="button" data-bs-target="#categoryCarouselDesktop"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-desktop next" type="button" data-bs-target="#categoryCarouselDesktop"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>

                {{-- =============================================================== --}}
                {{-- ================= CAROUSEL CHO MÀN HÌNH NHỎ (MOBILE) =============== --}}
                {{-- =============================================================== --}}
                <div class="d-block d-lg-none">
                    <div id="categoryCarouselMobile" class="carousel slide" data-bs-ride="false">

                        {{-- Nút chỉ báo (Indicators) --}}
                        <div class="carousel-indicators">
                            @foreach($categories->chunk(2) as $index => $chunk)
                                <button type="button" data-bs-target="#categoryCarouselMobile" data-bs-slide-to="{{ $index }}"
                                    class="{{ $index == 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach($categories->chunk(2) as $index => $chunk)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <div class="row g-3">
                                        @foreach($chunk as $category)
                                            <div class="col-6"> {{-- Luôn là 2 cột --}}
                                                <a href="{{ route('categories.show', $category->id) }}" class="text-decoration-none">
                                                    <div class="category-text-card">
                                                        <h6 class="mb-0 text-truncate">{{ $category->name }}</h6>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Trên mobile, nút điều khiển sẽ đè lên nội dung mặc định --}}
                        <button class="carousel-control-prev" type="button" data-bs-target="#categoryCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#categoryCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

            @else
                <div class="col">
                    <p class="text-center text-muted">Chưa có danh mục nào.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Featured Products Section --}}
    <section id="products" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Sản phẩm nổi bật</h2>
                <p class="lead text-muted">Các mặt hàng bán chạy và được đánh giá cao</p>
            </div>
            <div class="row g-4">
                @forelse ($featuredProducts as $product)
                    <div class="col-lg-3 col-md-6">
                        <div class="card product-card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                {{-- SỬA ĐỔI: Dùng route 'products.show' và truyền id --}}
                                <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                    <img src="{{ $product->thumbnail_url }}" class="card-img-top" alt="{{ $product->name }}">
                                </a>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ $product->brand->name ?? 'N/A' }}</small>
                                </div>
                                <h6 class="card-title">
                                    {{-- SỬA ĐỔI: Dùng route 'products.show' và truyền id --}}
                                    <a href="{{ route('products.show', ['product' => $product->id]) }}"
                                        class="text-decoration-none text-dark">{{ $product->name }}</a>
                                </h6>
                                <p class="card-text text-muted small flex-grow-1">{{ Str::limit($product->description, 50) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div>
                                        <span class="fw-bold text-primary">{{ $product->formatted_price }}</span>
                                    </div>
                                    <span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart-plus me-1"></i>Thêm vào
                                        giỏ</button>
                                    {{-- SỬA ĐỔI: Dùng route 'products.show' và truyền id --}}
                                    <a href="{{ route('products.show', ['product' => $product->id]) }}"
                                        class="btn btn-outline-secondary btn-sm">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">Chưa có sản phẩm nổi bật nào.</p>
                    </div>
                @endforelse
            </div>
            <div class="text-center mt-5">
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg">Xem tất cả sản phẩm <i
                        class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
    </section>
    {{-- Featured Brands Section --}}
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Thương hiệu nổi bật</h2>
                <p class="lead text-muted">Những đối tác chất lượng hàng đầu của chúng tôi.</p>
            </div>
            <div class="row g-4 justify-content-center align-items-center">
                @if(isset($brands) && $brands->isNotEmpty())
                    @foreach($brands as $brand)
                        {{-- SỬA LỖI: Đổi col-4 thành col-6 để hiển thị 2 item trên mobile --}}
                        <div class="col-lg-2 col-md-3 col-6">
                            <a href="#" title="{{ $brand->name }}" class="text-decoration-none text-dark">
                                <div class="brand-card text-center p-3">
                                    <img src="{{ $brand->logo_full_url }}" alt="{{ $brand->name }}" class="img-fluid mb-2"
                                        style="height: 60px; object-fit: contain;">

                                    <h6 class="mb-0 text-truncate">{{ $brand->name }}</h6>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-muted">Chưa có thương hiệu nào.</p>
                @endif
            </div>
        </div>
    </section>
    {{-- Newsletter Subscription Section --}}
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold mb-3">Luôn được cập nhật</h2>
                    <p class="lead mb-4">Nhận các ưu đãi mới nhất, hàng mới về và các mẹo hay về xe máy qua email của bạn
                    </p>
                    <form class="row g-3 justify-content-center">
                        <div class="col-md-6">
                            <input type="email" class="form-control form-control-lg"
                                placeholder="Nhập địa chỉ email của bạn" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-warning btn-lg w-100">Đăng ký</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection