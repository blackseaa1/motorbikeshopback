@extends('customer.layouts.app')

@section('title', $product->name)

@push('styles')
    {{-- Thêm CSS tùy chỉnh cho gallery ảnh nếu cần --}}
    <style>
        .thumbnail-gallery img {
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .thumbnail-gallery img.active,
        .thumbnail-gallery img:hover {
            border-color: var(--bs-primary);
        }
    </style>
@endpush


@section('content')
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                @if($product->category)
                    <li class="breadcrumb-item"><a
                            href="{{ route('products.category', $product->category->slug ?? $product->category->id) }}">{{ $product->category->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 50) }}</li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-6">
                <div class="mb-3">
                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-fluid rounded border"
                        id="main-product-image">
                </div>
                @if($product->images && $product->images->count() > 0)
                    <div class="d-flex gap-2 thumbnail-gallery">
                        {{-- Ảnh thumbnail mặc định của sản phẩm --}}
                        <img src="{{ $product->thumbnail_url }}" alt="Thumbnail" class="img-thumbnail w-25 active"
                            onclick="changeMainImage('{{ $product->thumbnail_url }}', this)">
                        {{-- Các ảnh khác trong gallery --}}
                        @foreach($product->images as $image)
                            <img src="{{ $image->image_full_url }}" alt="Thumbnail" class="img-thumbnail w-25"
                                onclick="changeMainImage('{{ $image->image_full_url }}', this)">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-6">
                <h1 class="display-6 fw-bold">{{ $product->name }}</h1>
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <a href="#reviews" class="text-muted text-decoration-none">({{ $product->reviews->count() }} đánh
                        giá)</a>
                </div>
                <p class="fs-3 fw-bold text-primary mb-3">{{ $product->formatted_price }}</p>

                <p class="text-muted">{{ $product->description }}</p>

                <ul class="list-unstyled mb-4">
                    @if($product->brand)
                        <li><strong>Thương hiệu:</strong> <a href="#"
                                class="text-decoration-none">{{ $product->brand->name }}</a></li>
                    @endif
                    @if($product->category)
                        <li><strong>Danh mục:</strong> <a
                                href="{{ route('products.category', $product->category->slug ?? $product->category->id) }}"
                                class="text-decoration-none">{{ $product->category->name }}</a></li>
                    @endif
                    <li><strong>Tình trạng:</strong> <span
                            class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">{{ $product->stock_quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                    </li>
                </ul>

                <div class="d-flex align-items-center mb-4">
                    <label for="quantity" class="form-label me-3 mb-0">Số lượng:</label>
                    <input type="number" class="form-control" id="quantity" value="1" min="1"
                        max="{{ $product->stock_quantity }}" style="width: 80px;">
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-lg" {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                        <i class="bi bi-cart-plus-fill me-2"></i>Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-outline-secondary btn-lg"><i class="bi bi-heart-fill me-2"></i>Thêm vào yêu
                        thích</button>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs"
                            type="button" role="tab">Thông số kỹ thuật</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                            type="button" role="tab">Đánh giá ({{ $product->reviews->count() }})</button>
                    </li>
                </ul>
                <div class="tab-content border border-top-0 p-4 rounded-bottom" id="productTabContent">
                    <div class="tab-pane fade show active" id="specs" role="tabpanel">
                        {!! nl2br(e($product->specifications)) !!}
                    </div>
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <h4 class="mb-3">Đánh giá của khách hàng</h4>
                        @forelse($product->reviews as $review)
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <img src="https://placehold.co/60/EFEFEF/AAAAAA&text={{ substr($review->customer->name ?? 'A', 0, 1) }}"
                                        class="rounded-circle" alt="avatar">
                                </div>
                                <div class="ms-3">
                                    <h6 class="fw-bold mb-0">{{ $review->customer->name ?? 'Anonymous' }}</h6>
                                    <div class="text-warning mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-1">{{ $review->comment }}</p>
                                    <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        @empty
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="row mt-5">
                <div class="col-12">
                    <h2 class="mb-4">Sản phẩm liên quan</h2>
                    <div class="row g-4">
                        @foreach($relatedProducts as $related)
                            <div class="col-lg-3 col-md-6">
                                <div class="card product-card h-100 border-0 shadow-sm">
                                    <a href="{{ route('products.show', $related->slug ?? $related->id) }}">
                                        <img src="{{ $related->thumbnail_url }}" class="card-img-top" alt="{{ $related->name }}">
                                    </a>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="{{ route('products.show', $related->slug ?? $related->id) }}"
                                                class="text-decoration-none text-dark stretched-link">{{ Str::limit($related->name, 40) }}</a>
                                        </h6>
                                        <p class="fw-bold text-primary">{{ $related->formatted_price }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function changeMainImage(newSrc, clickedElement) {
            // Cập nhật ảnh chính
            document.getElementById('main-product-image').src = newSrc;

            // Cập nhật viền active cho thumbnail
            // Bỏ active ở tất cả thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail-gallery img');
            thumbnails.forEach(img => img.classList.remove('active'));

            // Thêm active cho thumbnail được click
            clickedElement.classList.add('active');
        }
    </script>
@endpush