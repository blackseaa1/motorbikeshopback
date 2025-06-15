@extends('customer.layouts.app')

{{-- Lấy tên danh mục để làm tiêu đề trang --}}
@section('title', $category->name ?? 'Sản phẩm theo danh mục')
@section('content')
    <main>
        <section class="bg-light py-4">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $category->name ?? 'Danh mục' }}
                                </li>
                            </ol>
                        </nav>
                        <h1 class="mt-2">{{ $category->name ?? 'Tất cả sản phẩm' }}</h1>
                        <p class="lead">{{ $category->description ?? 'Các sản phẩm chất lượng cao, đa dạng.' }}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <img src="{{ $category->image_url ?? 'https://placehold.co/400x150' }}"
                            alt="{{ $category->name ?? '' }}" class="img-fluid rounded" style="max-height: 150px;">
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Bộ lọc</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6 class="mb-3">Khoảng giá</h6>
                                    <input type="range" class="form-range" min="0" max="10000000" step="100000"
                                        id="price-range">
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <label for="min-price" class="form-label">Tối thiểu</label>
                                            <input type="number" class="form-control" id="min-price" value="0">
                                        </div>
                                        <div class="col-6">
                                            <label for="max-price" class="form-label">Tối đa</label>
                                            <input type="number" class="form-control" id="max-price" value="10000000">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="mb-3">Thương hiệu</h6>
                                    @forelse ($brands as $brand)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $brand->slug }}"
                                                id="brand-{{ $brand->id }}">
                                            <label class="form-check-label" for="brand-{{ $brand->id }}">
                                                {{ $brand->name }}
                                            </label>
                                        </div>
                                    @empty
                                        <p class="small text-muted">Chưa có thương hiệu.</p>
                                    @endforelse
                                </div>

                                <div class="mb-4">
                                    <h6 class="mb-3">Tương thích xe</h6>
                                    <select class="form-select mb-2 selectpicker" data-live-search="true"
                                        title="Chọn hãng xe">
                                        @foreach ($vehicleBrands as $vBrand)
                                            <option value="{{ $vBrand->id }}">{{ $vBrand->name }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select mb-2 selectpicker" data-live-search="true"
                                        title="Chọn dòng xe" disabled>
                                    </select>
                                    <select class="form-select selectpicker" title="Chọn đời xe" disabled>
                                    </select>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary" type="button">Áp dụng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                @if(isset($products))
                                    <span class="me-2">{{ $products->total() }} sản phẩm</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="me-2" for="sort-by">Sắp xếp:</label>
                                <select class="form-select form-select-sm me-3" id="sort-by" style="width: auto;">
                                    <option value="popularity">Phổ biến</option>
                                    <option value="price-low">Giá: Thấp đến Cao</option>
                                    <option value="price-high">Giá: Cao đến Thấp</option>
                                    <option value="rating">Đánh giá</option>
                                    <option value="newest">Mới nhất</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4">
                            @if(isset($products))
                                @forelse ($products as $product)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100">
                                            @if($product->sale_price && $product->sale_price < $product->price)
                                                <div class="badge bg-danger position-absolute top-0 end-0 m-2">Giảm giá</div>
                                            @endif
                                            <a href="{{ route('product.show', ['slug' => $product->slug]) }}">
                                                <img src="{{ $product->image_url ?? 'https://placehold.co/300x200' }}"
                                                    class="card-img-top" alt="{{ $product->name }}">
                                            </a>
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title">{{ $product->name }}</h5>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted">Hãng: {{ $product->brand->name ?? 'N/A' }}</span>
                                                    <div>
                                                        <i class="bi bi-star-fill text-warning"></i>
                                                        <small
                                                            class="text-muted">{{ number_format($product->rating, 1) ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                                <p class="card-text small">{{ Str::limit($product->description, 50) }}</p>
                                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                                    <div>
                                                        @if($product->sale_price && $product->sale_price < $product->price)
                                                            <span
                                                                class="text-muted text-decoration-line-through">{{ number_format($product->price) }}
                                                                ₫</span>
                                                            <span
                                                                class="fs-5 fw-bold text-primary">{{ number_format($product->sale_price) }}
                                                                ₫</span>
                                                        @else
                                                            <span class="fs-5 fw-bold text-primary">{{ number_format($product->price) }}
                                                                ₫</span>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('product.show', ['slug' => $product->slug]) }}"
                                                        class="btn btn-outline-primary btn-sm">Chi tiết</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-warning">Chưa có sản phẩm nào trong danh mục này.</div>
                                    </div>
                                @endforelse
                            @endif
                        </div>

                        <nav class="mt-5" aria-label="Product pagination">
                            @if(isset($products))
                                {{ $products->links('vendor.pagination.bootstrap-5') }}
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
@endpush