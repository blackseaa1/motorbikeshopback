@extends('customer.layouts.app')

@section('title', 'Kết quả tìm kiếm cho: "' . $query . '"')

@section('content')
    <div class="container py-5">
        <h2 class="mb-4 text-center">Kết quả tìm kiếm cho: "<span class="text-primary">{{ $query }}</span>"</h2>

        @if (empty($results) || ($results['products']->isEmpty() && $results['brands']->isEmpty() && $results['blogPosts']->isEmpty()))
            <div class="alert alert-info text-center" role="alert">
                Không tìm thấy kết quả nào phù hợp với từ khóa "{{ $query }}".
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="btn btn-primary">Quay về trang chủ</a>
            </div>
        @else
            {{-- Kết quả Sản phẩm --}}
            <div class="mb-5">
                <h3 class="mb-3">Sản phẩm ({{ $results['products']->count() }})</h3>
                @if($results['products']->isNotEmpty())
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($results['products'] as $product)
                            <div class="col">
                                <div class="card h-100 shadow-sm product-card">
                                    <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                                        @if($product->thumbnail_url)
                                            <img src="{{ $product->thumbnail_url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <img src="https://placehold.co/200x200/EFEFEF/AAAAAA&text=No+Image" class="card-img-top" alt="No Image" style="height: 200px; object-fit: cover;">
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $product->name }}</h5>
                                            <p class="card-text text-muted mb-1">{{ number_format($product->price) }} ₫</p>
                                            @if($product->reviews_count > 0)
                                                <div class="d-flex align-items-center">
                                                    <div class="text-warning me-1">
                                                        @for ($i = 0; $i < round($product->reviews_avg_rating); $i++)
                                                            <i class="bi bi-star-fill small"></i>
                                                        @endfor
                                                        @for ($i = round($product->reviews_avg_rating); $i < 5; $i++)
                                                            <i class="bi bi-star small"></i>
                                                        @endfor
                                                    </div>
                                                    <small class="text-muted">({{ $product->reviews_count }} đánh giá)</small>
                                                </div>
                                            @else
                                                <small class="text-muted">Chưa có đánh giá</small>
                                            @endif
                                        </div>
                                    </a>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary btn-sm w-100">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-end mt-3">
                        <a href="{{ route('products.index', ['search' => $query]) }}" class="btn btn-outline-primary">Xem tất cả sản phẩm</a>
                    </div>
                @else
                    <p class="text-muted">Không tìm thấy sản phẩm nào.</p>
                @endif
            </div>

            {{-- Kết quả Thương hiệu --}}
            <div class="mb-5">
                <h3 class="mb-3">Thương hiệu ({{ $results['brands']->count() }})</h3>
                @if($results['brands']->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($results['brands'] as $brand)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('products.index', ['brands[]' => $brand->id]) }}" class="text-decoration-none text-primary fw-bold">
                                    {{ $brand->name }}
                                </a>
                                <span class="badge bg-primary rounded-pill">{{ $brand->products_count ?? 'N/A' }} sản phẩm</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="text-end mt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Xem tất cả thương hiệu</a>
                    </div>
                @else
                    <p class="text-muted">Không tìm thấy thương hiệu nào.</p>
                @endif
            </div>

            {{-- Kết quả Bài Blog --}}
            <div class="mb-5">
                <h3 class="mb-3">Bài Blog ({{ $results['blogPosts']->count() }})</h3>
                @if($results['blogPosts']->isNotEmpty())
                    <div class="list-group">
                        @foreach ($results['blogPosts'] as $blogPost)
                            <a href="{{ route('blog.show', $blogPost->id) }}" class="list-group-item list-group-item-action d-flex flex-column flex-md-row align-items-md-center">
                                @if($blogPost->thumbnail_url)
                                    <img src="{{ $blogPost->thumbnail_url }}" alt="{{ $blogPost->title }}" class="img-thumbnail me-md-3 mb-2 mb-md-0" style="width: 120px; height: 90px; object-fit: cover;">
                                @else
                                    <img src="https://placehold.co/120x90/EFEFEF/AAAAAA&text=No+Image" alt="No Image" class="img-thumbnail me-md-3 mb-2 mb-md-0" style="width: 120px; height: 90px; object-fit: cover;">
                                @endif
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $blogPost->title }}</h5>
                                    <p class="mb-1 text-muted small">{{ Str::limit(strip_tags($blogPost->content), 100) }}</p>
                                    <small class="text-muted">Ngày đăng: {{ $blogPost->created_at->format('d/m/Y') }}</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="text-end mt-3">
                        <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">Xem tất cả bài blog</a>
                    </div>
                @else
                    <p class="text-muted">Không tìm thấy bài blog nào.</p>
                @endif
            </div>

        @endif
    </div>
@endsection
