@extends('customer.layouts.app')

@section('title', 'Trang chủ - MotoToys Store')

@section('content')
    {{-- Main Carousel --}}
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
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

    {{-- Categories Section --}}
    <section id="categories" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Mua sắm theo danh mục</h2>
                <p class="lead text-muted">Khám phá các loại phụ tùng và đồ chơi xe máy</p>
            </div>
            <div class="row g-4">
                @forelse ($categories as $category)
                    <div class="col-lg-4 col-md-6">
                        <div class="card category-card h-100 border-0 shadow-sm">
                            {{-- SỬA ĐỔI: Cả 2 link đều dùng 'products.category' và truyền id --}}
                            <a href="{{ route('products.category', ['category' => $category->id]) }}">
                                <img src="{{ $category->image_url ?? 'https://placehold.co/400x250/EEEEEE/333333?text=' . urlencode($category->name) }}"
                                    class="card-img-top" alt="{{ $category->name }}">
                            </a>
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold">{{ $category->name }}</h5>
                                <p class="card-text text-muted">{{ Str::limit($category->description, 50) }}</p>
                                <a href="{{ route('products.category', ['category' => $category->id]) }}"
                                    class="btn btn-outline-primary">Xem ngay</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">Chưa có danh mục nào để hiển thị.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Featured Products Section --}}
    <section id="products" class="py-5">
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