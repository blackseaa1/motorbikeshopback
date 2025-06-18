@extends('customer.layouts.app')

@section('title', 'Tất cả danh mục')

@section('content')
<main>
    {{-- Phần header của trang (giữ nguyên) --}}
    <section class="bg-light py-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Danh mục sản phẩm</li>
                </ol>
            </nav>
            <h1 class="mt-2">Khám phá các danh mục</h1>
            <p class="lead">Tìm kiếm sản phẩm theo các danh mục đã được phân loại.</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                {{-- Sidebar lọc (giữ nguyên) --}}
                <aside class="col-lg-3">
                    {{-- ... Nội dung sidebar ... --}}
                </aside>

                {{-- Khu vực hiển thị danh mục --}}
                <div class="col-lg-9">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @forelse ($categories as $category)
                            <div class="col">
                                <div class="card h-100 shadow-sm category-card-item">
                                    {{-- SỬA Ở ĐÂY --}}
                                    <a href="{{ route('products.category', ['slug' => $category->slug]) }}">
                                        <img src="{{ $category->image_url ?? 'https://placehold.co/600x400/EFEFEF/AAAAAA&text=No+Image' }}"
                                             alt="{{ $category->name }}" class="card-img-top">
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            {{-- SỬA Ở ĐÂY --}}
                                            <a href="{{ route('products.category', ['slug' => $category->slug]) }}"
                                               class="text-dark text-decoration-none">{{ $category->name }}</a>
                                        </h5>
                                        <p class="card-text text-muted small">{{ $category->description ?? 'Chưa có mô tả' }}</p>
                                    </div>
                                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                        <span class="text-muted">{{ $category->products_count }} sản phẩm</span>
                                        {{-- SỬA Ở ĐÂY --}}
                                        <a href="{{ route('products.category', ['slug' => $category->slug]) }}"
                                           class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <p class="mb-0">Hiện chưa có danh mục nào được hiển thị.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Phân trang (giữ nguyên) --}}
                    <nav class="mt-5 d-flex justify-content-center" aria-label="Category pagination">
                       @if(isset($categories))
                           {{ $categories->links('vendor.pagination.bootstrap-5') }}
                       @endif
                    </nav>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    .category-card-item a {
        text-decoration: none;
    }
    .category-card-item .card-title a:hover {
        color: var(--bs-primary) !important;
    }
    .category-card-item img {
        aspect-ratio: 3/2;
        object-fit: cover;
    }
</style>
@endpush