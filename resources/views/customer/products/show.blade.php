@extends('customer.layouts.app')

@section('title', $product->name)

@section('content')
    <main class="container py-5">
        <div class="row">
            <div class="col-lg-6">
                <img src="{{ $product->image_url ?? 'https://via.placeholder.com/600x600?text=MotoToys' }}"
                    class="img-fluid rounded mb-3" alt="{{ $product->name }}">
                {{-- TODO: Thêm carousel cho các ảnh phụ nếu có --}}
            </div>

            <div class="col-lg-6">
                <h1>{{ $product->name }}</h1>
                <h2 class="text-danger fw-bold mb-3">{{ number_format($product->price, 0, ',', '.') }}₫</h2>
                <p class="mb-4">{{ $product->short_description ?? '' }}</p>

                <div class="d-flex mb-4">
                    <input type="number" class="form-control me-3" value="1" min="1" style="max-width: 80px;">
                    <button class="btn btn-primary flex-shrink-0" type="button">
                        <i class="bi-cart-fill me-1"></i>
                        Thêm vào giỏ
                    </button>
                </div>

                <ul class="list-unstyled">
                    @if($product->category)
                        <li><strong>Danh mục:</strong> <a
                                href="{{ route('categories.show', $product->category->id) }}">{{ $product->category->name }}</a>
                        </li>
                    @endif
                    @if($product->brand)
                        <li><strong>Thương hiệu:</strong> {{ $product->brand->name }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description-pane">Mô tả chi
                            tiết</button>
                    </li>
                </ul>
                <div class="tab-content pt-4" id="productTabContent">
                    <div class="tab-pane fade show active" id="description-pane">
                        {!! $product->description ?? 'Chưa có mô tả chi tiết cho sản phẩm này.' !!}
                    </div>
                </div>
            </div>
        </div>
        {{-- ... sau phần mô tả sản phẩm ... --}}
        {{-- <div class="row mt-5">
            <div class="col-12">
                <h3>Đánh giá sản phẩm</h3>
                @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">{{ $review->customer->name }}</h5>
                                <p class="card-text">{{ $review->comment }}</p>
                                <small class="text-muted">Đánh giá: {{ $review->rating }} sao - Ngày:
                                    {{ $review->created_at->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                @endif
            </div>
        </div> --}}
    </main>
@endsection