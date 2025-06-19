@extends('customer.layouts.app')

@section('title', $product->name)

@section('content')
    <main class="container py-5">
        <div class="row">
            {{-- ============================================= --}}
            {{-- == CỘT THƯ VIỆN ẢNH (ĐÃ SỬA LỖI VỠ GIAO DIỆN) == --}}
            {{-- ============================================= --}}
            <div class="col-lg-6">
                <div class="main-image-container mb-3"
                    style="aspect-ratio: 1 / 1; border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden;">
                    <img id="main-product-image" src="{{ $product->thumbnail_url }}" class="w-100 h-100"
                        alt="{{ $product->name }}" style="object-fit: cover;">
                </div>

                @if($product->images && $product->images->count() > 1)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product->images as $image)
                            <a href="javascript:void(0)"
                                onclick="document.getElementById('main-product-image').src='{{ $image->image_full_url }}'">
                                <img src="{{ $image->image_full_url }}" class="img-thumbnail" alt="{{ $product->name }} thumbnail"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Cột thông tin sản phẩm --}}
            <div class="col-lg-6">
                <h1>{{ $product->name }}</h1>
                <h2 class="text-danger fw-bold mb-3">{{ $product->formatted_price }}</h2>

                <p class="text-muted">{!! nl2br(e($product->description)) !!}</p>

                <div class="d-flex align-items-center mb-4">
                    <label for="quantity" class="me-2">Số lượng:</label>
                    <input type="number" id="quantity" class="form-control me-3" value="1" min="1" style="max-width: 80px;">
                    <button class="btn btn-primary flex-shrink-0" type="button">
                        <i class="bi-cart-fill me-1"></i>
                        Thêm vào giỏ
                    </button>
                </div>

                <hr>

                <ul class="list-unstyled">
                    @if($product->category)
                        <li><strong>Danh mục:</strong> <a
                                href="{{ route('categories.show', $product->category->id) }}">{{ $product->category->name }}</a>
                        </li>
                    @endif
                    @if($product->brand)
                        <li><strong>Thương hiệu:</strong> {{ $product->brand->name }}</li>
                    @endif
                    <li><strong>Tình trạng:</strong> <span
                            class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span></li>
                </ul>
            </div>
        </div>

        {{-- Phần tab mô tả chi tiết, thông số và đánh giá --}}
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specifications-tab" data-bs-toggle="tab"
                            data-bs-target="#specifications-pane" type="button" role="tab"
                            aria-controls="specifications-pane" aria-selected="true">Thông số kỹ thuật</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane"
                            type="button" role="tab" aria-controls="reviews-pane" aria-selected="false">Đánh giá</button>
                    </li>
                </ul>
                <div class="tab-content pt-4" id="productTabContent">
                    {{-- Gọi file partial cho tab thông số --}}
                    @include('customer.products.partials._specifications_tab', ['product' => $product])

                    {{-- Gọi file partial cho tab đánh giá --}}
                    @include('customer.products.partials._reviews_tab')
                </div>
            </div>
        </div>
    </main>
@endsection
@push('scripts')
    <script src="{{ asset('assets_customer/js/products.js') }}"></script>
@endpush