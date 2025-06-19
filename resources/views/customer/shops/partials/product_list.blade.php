<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="product-list-container">
    @forelse($products as $product)
        <div class="col">
            <div class="card h-100 product-card">
                <a href="{{ route('products.show', $product->id) }}">
                    <img src="{{ $product->thumbnail_url ?? 'https://via.placeholder.com/300x200?text=MotoToys' }}"
                        class="card-img-top" alt="{{ $product->name }}">
                </a>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <a href="{{ route('products.show', $product->id) }}"
                            class="text-decoration-none text-dark stretched-link-style">{{ $product->name }}</a>
                    </h5>

                    {{-- PHẦN HIỂN THỊ ĐÁNH GIÁ MỚI --}}
                    <div class="mb-2">
                        @if ($product->reviews_count > 0)
                            @php
                                $rating = round($product->reviews_avg_rating * 2) / 2; // Làm tròn đến 0.5
                                $fullStars = floor($rating);
                                $halfStar = ($rating - $fullStars) > 0;
                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                            @endphp
                            <small class="text-warning">
                                @for ($i = 0; $i < $fullStars; $i++)<i class="bi bi-star-fill"></i>@endfor
                                @if ($halfStar)<i class="bi bi-star-half"></i>@endif
                                @for ($i = 0; $i < $emptyStars; $i++)<i class="bi bi-star"></i>@endfor
                            </small>
                            <small class="text-muted ms-1">({{ $product->reviews_count }})</small>
                        @else
                            <small class="text-muted">Chưa có đánh giá</small>
                        @endif
                    </div>

                    <p class="card-text text-danger fw-bold fs-5 mb-3">
                        {{ $product->formatted_price }}
                    </p>

                    {{-- NÚT THÊM VÀO GIỎ HÀNG MỚI --}}
                    <div class="mt-auto d-grid">
                        <button class="btn btn-sm btn-primary add-to-cart-btn" data-product-id="{{ $product->id }}">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">Không tìm thấy sản phẩm nào phù hợp với tiêu chí
                của bạn.</div>
        </div>
    @endforelse
</div>

{{-- Loại bỏ class stretched-link cũ và dùng class mới để tránh xung đột --}}
<style>
    .stretched-link-style::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: "";
    }
</style>