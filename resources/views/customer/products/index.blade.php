@extends('customer.layouts.app')

@section('title', 'Tất cả sản phẩm')

@section('content')
<main class="container py-5">
    <div class="row">
        <aside class="col-lg-3">
            <h4 class="mb-4">Bộ lọc</h4>
            <form action="{{ route('products.index') }}" method="GET">
                @if(isset($categories) && $categories->isNotEmpty())
                <div class="mb-4">
                    <h5>Danh mục</h5>
                    @foreach($categories as $category)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat-{{ $category->id }}">
                        <label class="form-check-label" for="cat-{{ $category->id }}">{{ $category->name }}</label>
                    </div>
                    @endforeach
                </div>
                <hr>
                @endif
                
                @if(isset($brands) && $brands->isNotEmpty())
                <div class="mb-4">
                    <h5>Thương hiệu</h5>
                     @foreach($brands as $brand)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="brands[]" value="{{ $brand->id }}" id="brand-{{ $brand->id }}">
                        <label class="form-check-label" for="brand-{{ $brand->id }}">{{ $brand->name }}</label>
                    </div>
                    @endforeach
                </div>
                <hr>
                @endif
                <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
            </form>
        </aside>

        <div class="col-lg-9">
            <h1 class="mb-4">Tất cả sản phẩm</h1>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                @forelse($products as $product)
                <div class="col">
                    <div class="card h-100 product-card">
                        <a href="{{ route('products.show', $product->id) }}">
                            <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x200?text=MotoToys' }}" class="card-img-top" alt="{{ $product->name }}">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                            </h5>
                            <p class="card-text text-danger fw-bold fs-5 mt-auto">{{ number_format($product->price, 0, ',', '.') }}₫</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-warning text-center">Không tìm thấy sản phẩm nào.</div>
                @endforelse
            </div>
            
            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</main>
@endsection