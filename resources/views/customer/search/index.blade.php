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
                <h3 class="mb-3">Sản phẩm ({{ $results['products']->total() }})</h3> {{-- Dùng .total() thay vì .count() cho paginator --}}
                @if($results['products']->isNotEmpty())
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($results['products'] as $product)
                            <div class="col">
                                <div class="card h-100 shadow-sm product-card">
                                    <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                                        @if($product->images->isNotEmpty())
                                            <img  class="card-img-top" src="{{ $product->firstImage->image_full_url ?? asset('path/to/default-image.jpg') }}"
                                                alt="{{ $product->name }}">
                                        @else
                                            <img src="https://placehold.co/400x300/EFEFEF/AAAAAA&text=No+Image" class="card-img-top" alt="No Image">
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $product->name }}</h5>
                                            <p class="card-text text-danger">{{ number_format($product->price) }} ₫</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Thêm phân trang cho sản phẩm --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $results['products']->links('customer.vendor.pagination', ['pageName' => 'products_page']) }}
                    </div>
                @else
                    <p class="text-muted">Không tìm thấy sản phẩm nào.</p>
                @endif
            </div>

            {{-- Kết quả Thương hiệu --}}
            <div class="mb-5">
                <h3 class="mb-3">Thương hiệu ({{ $results['brands']->total() }})</h3> {{-- Dùng .total() cho paginator --}}
                @if($results['brands']->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($results['brands'] as $brand)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('products.index', ['brand_id' => $brand->id]) }}" class="text-decoration-none">{{ $brand->name }}</a>
                                <span class="badge bg-primary rounded-pill">{{ $brand->products_count ?? 0 }}</span>
                            </li>
                        @endforeach
                    </ul>
                    {{-- Thêm phân trang cho thương hiệu --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $results['brands']->links('customer.vendor.pagination', ['pageName' => 'brands_page']) }}
                    </div>
                @else
                    <p class="text-muted">Không tìm thấy thương hiệu nào.</p>
                @endif
            </div>

            {{-- Kết quả Blog --}}
            <div class="mb-5">
                <h3 class="mb-3">Bài Blog ({{ $results['blogPosts']->total() }})</h3> {{-- Dùng .total() cho paginator --}}
                @if($results['blogPosts']->isNotEmpty())
                    <div class="list-group">
                        @foreach ($results['blogPosts'] as $blogPost)
                            <a href="{{ route('blog.show', $blogPost->id) }}" class="list-group-item list-group-item-action d-flex align-items-center mb-2">
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
                    {{-- Thêm phân trang cho blog posts --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $results['blogPosts']->links('customer.vendor.pagination', ['pageName' => 'blog_posts_page']) }}
                    </div>
                @else
                    <p class="text-muted">Không tìm thấy bài blog nào.</p>
                @endif
            </div>
        @endif
    </div>
@endsection