@extends('customer.layouts.app')

@section('title', 'Sản phẩm ' . $category->name)

@section('content')
<div class="container py-5">
    <div class="row">
        <aside class="col-lg-3">
            <h4><i class="bi bi-tags-fill"></i> {{ $category->name }}</h4>
            <p>{{ $category->description }}</p>
            <hr>
            <a href="{{ route('categories.index') }}" class="btn btn-light w-100 mb-4">&larr; Quay lại tất cả danh mục</a>
            
            {{-- TODO: Thêm các bộ lọc khác nếu cần --}}
        </aside>

        <main class="col-lg-9">
            <h1 class="mb-4">Sản phẩm trong danh mục: {{ $category->name }}</h1>
            
            @if($products->isNotEmpty())
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 product-card">
                                {{-- Giả sử Product có route 'products.show' và có image_url --}}
                                <a href="{{ route('products.show', $product->slug) }}">
                                    <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x200' }}" class="card-img-top" alt="{{ $product->name }}">
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                    </h5>
                                    <p class="card-text text-danger fw-bold fs-5">
                                        {{ number_format($product->price, 0, ',', '.') }}₫
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 p-3">
                                     <a href="#" class="btn btn-primary w-100">Thêm vào giỏ</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                {{-- Phân trang --}}
                <div class="mt-5 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="alert alert-warning">
                    Chưa có sản phẩm nào trong danh mục này.
                </div>
            @endif
        </main>
    </div>
</div>
@endsection

@push('styles')
<style>
    .product-card a {
        color: inherit;
        text-decoration: none;
    }
    .product-card .card-title {
        height: 3em;
        overflow: hidden;
    }
</style>
@endpush