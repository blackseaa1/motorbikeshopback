@extends('customer.layouts.app')

@section('title', $product->name)

@push('styles')
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
                    {{-- SỬA ĐỔI: Dùng route 'products.category' và truyền id --}}
                    <li class="breadcrumb-item"><a
                            href="{{ route('products.category', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 50) }}</li>
            </ol>
        </nav>

        <div class="row g-5">
            {{-- ... Phần hiển thị ảnh và thông tin sản phẩm chính giữ nguyên ... --}}
            <div class="col-lg-6">
                <div class="mb-3">
                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-fluid rounded border"
                        id="main-product-image">
                </div>
                @if($product->images && $product->images->count() > 0)
                    <div class="d-flex gap-2 thumbnail-gallery">
                        <img src="{{ $product->thumbnail_url }}" alt="Thumbnail" class="img-thumbnail w-25 active"
                            onclick="changeMainImage('{{ $product->thumbnail_url }}', this)">
                        @foreach($product->images as $image)
                            <img src="{{ $image->image_full_url }}" alt="Thumbnail" class="img-thumbnail w-25"
                                onclick="changeMainImage('{{ $image->image_full_url }}', this)">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-6">
                <h1 class="display-6 fw-bold">{{ $product->name }}</h1>
                {{-- ... Các thông tin khác giữ nguyên --}}
                <p class="fs-3 fw-bold text-primary mb-3">{{ $product->formatted_price }}</p>
            </div>
        </div>

        <div class="row mt-5">
            {{-- ... Phần Tabs và Đánh giá giữ nguyên ... --}}
        </div>

        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="row mt-5">
                <div class="col-12">
                    <h2 class="mb-4">Sản phẩm liên quan</h2>
                    <div class="row g-4">
                        @foreach($relatedProducts as $related)
                            <div class="col-lg-3 col-md-6">
                                <div class="card product-card h-100 border-0 shadow-sm">
                                    {{-- SỬA ĐỔI: Dùng route 'products.show' và truyền id --}}
                                    <a href="{{ route('products.show', ['product' => $related->id]) }}">
                                        <img src="{{ $related->thumbnail_url }}" class="card-img-top" alt="{{ $related->name }}">
                                    </a>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            {{-- SỬA ĐỔI: Dùng route 'products.show' và truyền id --}}
                                            <a href="{{ route('products.show', ['product' => $related->id]) }}"
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
            document.getElementById('main-product-image').src = newSrc;
            const thumbnails = document.querySelectorAll('.thumbnail-gallery img');
            thumbnails.forEach(img => img.classList.remove('active'));
            clickedElement.classList.add('active');
        }
    </script>
@endpush