@extends('customer.layouts.app')

@section('title', 'Danh mục: ' . $category->name)

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold">{{ $category->name }}</h1>
            @if($category->description)
                <p class="lead text-muted">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    <div class="row">
        <aside class="col-lg-3">
            <h4><i class="bi bi-funnel-fill me-2"></i>Bộ lọc</h4>
            <hr>
            <div class="mb-4">
                <h5>Thương hiệu</h5>
                {{-- Logic lọc theo thương hiệu sẽ được thêm ở đây --}}
                <p class="text-muted small">Tính năng sắp ra mắt.</p>
            </div>
            <div class="mb-4">
                <h5>Khoảng giá</h5>
                 {{-- Logic lọc theo giá sẽ được thêm ở đây --}}
                <p class="text-muted small">Tính năng sắp ra mắt.</p>
            </div>
        </aside>

        <main class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Hiển thị {{ $products->firstItem() }}-{{ $products->lastItem() }} trên tổng số {{ $products->total() }} kết quả</span>
                <div class="d-flex align-items-center">
                    <label for="sort-by" class="form-label me-2 mb-0">Sắp xếp:</label>
                    <select class="form-select form-select-sm" id="sort-by" style="width: auto;">
                        <option selected>Mới nhất</option>
                        <option value="price-asc">Giá: Thấp đến Cao</option>
                        <option value="price-desc">Giá: Cao đến Thấp</option>
                        <option value="name-asc">Tên: A-Z</option>
                        <option value="name-desc">Tên: Z-A</option>
                    </select>
                </div>
            </div>

            <div class="row g-4">
                @forelse ($products as $product)
                    <div class="col-lg-4 col-md-6">
                        {{-- Sử dụng một partial view cho card sản phẩm để dễ tái sử dụng --}}
                        <div class="card product-card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                <a href="{{ route('products.show', $product->slug ?? $product->id) }}">
                                    <img src="{{ $product->thumbnail_url }}" class="card-img-top" alt="{{ $product->name }}">
                                </a>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ $product->brand->name ?? 'N/A' }}</small>
                                </div>
                                <h6 class="card-title">
                                    <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="text-decoration-none text-dark stretched-link">{{ Str::limit($product->name, 50) }}</a>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                    <div>
                                        <span class="fw-bold text-primary">{{ $product->formatted_price }}</span>
                                    </div>
                                    <span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <div class="d-grid">
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart-plus me-1"></i>Thêm vào giỏ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Không tìm thấy sản phẩm nào phù hợp trong danh mục này.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-5">
                {{-- Hiển thị các link phân trang, và giữ lại các tham số query (ví dụ: ?sort=price-asc) --}}
                {{ $products->appends(request()->query())->links() }}
            </div>
        </main>
    </div>
</div>
@endsection