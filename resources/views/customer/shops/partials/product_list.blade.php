<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="product-list-container">
    @forelse($products as $product)
        <div class="col">
            <div class="card h-100 product-card position-relative">
                <a href="{{ route('products.show', $product->id) }}">
                    <img src="{{ $product->thumbnail_url ?? 'https://via.placeholder.com/300x200?text=MotoToys' }}"
                        class="card-img-top" alt="{{ $product->name }}">
                </a>
                <div class="card-body d-flex flex-column">

                    {{-- SỬA ĐỔI TẠI ĐÂY --}}
                    {{-- 1. Thêm min-height để đảm bảo khu vực tiêu đề luôn có chiều cao cố định --}}
                    {{-- 2. Dùng Str::limit để rút gọn tên sản phẩm --}}
                    {{-- 3. Thêm title="" để xem được tên đầy đủ khi hover --}}
                    <h5 class="card-title" style="min-height: 48px;">
                        <a href="{{ route('products.show', $product->id) }}" title="{{ $product->name }}"
                            class="text-decoration-none text-dark stretched-link-style">
                            {{ Str::limit($product->name, 55) }}
                        </a>
                    </h5>
                    {{-- KẾT THÚC SỬA ĐỔI --}}

                    <div class="mb-2">
                        @if ($product->reviews_count > 0)
                            @php
                                $rating = round($product->reviews_avg_rating);
                            @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $rating ? 'bi-star-fill text-warning' : 'bi-star' }}"></i>
                            @endfor
                            <small class="text-muted ms-1">({{ $product->reviews_count }})</small>
                        @else
                            {{-- Giữ lại một khoảng trống để layout không bị xô lệch khi không có đánh giá --}}
                            <small class="text-muted" style="display: inline-block; min-height: 24px;">Chưa có đánh giá</small>
                        @endif
                    </div>

                    <p class="card-text text-danger fw-bold fs-5 mb-3">
                        {{ $product->formatted_price }}
                    </p>

                    <div class="mb-2">
                        @if ($product->stock_quantity > 0)
                            <span class="badge bg-success">Còn hàng: {{ $product->stock_quantity }}</span>
                        @else
                            <span class="badge bg-danger">Hết hàng</span>
                        @endif
                    </div>

                    <div class="mt-auto d-grid gap-2">
                        <button class="btn btn-sm btn-primary add-to-cart-btn position-relative" style="z-index: 2;"
                            data-product-id="{{ $product->id }}" @if ($product->stock_quantity <= 0) disabled @endif>
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                        <a href="{{ route('products.show', ['product' => $product->id]) }}"
                            class="btn btn-outline-secondary btn-sm">Xem chi tiết</a>
                    </div>

                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">Không tìm thấy sản phẩm nào phù hợp.</div>
        </div>
    @endforelse
</div>

{{-- CSS cho stretched-link, giữ nguyên --}}
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