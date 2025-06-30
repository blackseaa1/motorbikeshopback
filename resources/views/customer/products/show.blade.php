@extends('customer.layouts.app')

@section('title', $product->name)

@section('content')
    <main class="container py-5">
        <h2 class="mb-4">Chi tiết sản phẩm</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                @if($product->category)
                    <li class="breadcrumb-item"><a
                            href="{{ route('categories.show', $product->category->id) }}">{{ $product->category->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
            </ol>
        </nav>
        <div class="row">
            {{-- Cột thư viện ảnh --}}
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

            {{-- Cột thông tin sản phẩm và hành động --}}
            <div class="col-lg-6">
                <h1>{{ $product->name }}</h1>

                @if($product->brand)
                    <p class="text-muted">Thương hiệu: <a href="#">{{ $product->brand->name }}</a></p>
                @endif

                <p class="display-5 text-danger fw-bold">{{ $product->formatted_price }}</p>

                <p class="lead">{!! nl2br(e($product->description)) !!}</p>

                <hr>

                {{-- Form Thêm vào giỏ hàng --}}
                <div class="d-flex align-items-center mb-4 gap-3">
                    <label for="quantity" class="form-label fw-bold me-2">Số lượng:</label>
                    <input type="number" class="form-control w-25" id="quantity" name="quantity" value="1" min="1"
                        max="{{ $product->stock_quantity }}" style="max-width: 100px;">
                    <button class="btn btn-primary flex-shrink-0 add-to-cart-btn" data-product-id="{{ $product->id }}">
                        <i class="bi bi-cart-fill me-2"></i>Thêm vào giỏ
                    </button>
                </div>

                <p><strong>Tình trạng:</strong>
                    @if($product->stock_quantity > 0)
                        <span class="text-success">Còn hàng ({{$product->stock_quantity}} sản phẩm)</span>
                    @else
                        <span class="text-danger">Hết hàng</span>
                    @endif
                </p>

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
            <h3 class="mb-3">Thông số kỹ thuật</h3>
            @include('customer.products.partials._specifications_tab', ['product' => $product])
        </div>
        <div class="row mt-5">
            <h3 class="mb-3">Đánh giá</h3>
            @include('customer.products.partials._reviews_tab')
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('assets_customer/js/products.js') }}"></script>
@endpush