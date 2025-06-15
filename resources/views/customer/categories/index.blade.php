@extends('customer.layouts.app')

@section('title', 'Tất cả danh mục sản phẩm')

@push('styles')
    {{-- CSS tùy chỉnh cho trang danh mục --}}
    <style>
        .category-showcase-card {
            display: block;
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
            /* Bo góc card */
            aspect-ratio: 4 / 3;
            /* Giữ tỷ lệ khung hình */
            background-size: cover;
            background-position: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .category-showcase-card:hover {
            transform: scale(1.05);
            /* Hiệu ứng phóng to khi hover */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .category-showcase-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Lớp phủ gradient để làm nổi bật chữ */
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0) 60%);
        }

        .card-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.25rem;
            z-index: 2;
            /* Đảm bảo nội dung nằm trên lớp phủ */
        }

        .card-content .category-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .card-content .product-count {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
@endpush


@section('content')
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold">Khám phá Danh Mục</h1>
                <p class="lead text-muted">Tìm kiếm phụ tùng và đồ chơi hoàn hảo cho chiếc xe của bạn.</p>
            </div>
        </div>

        <div class="row g-4">
            @forelse ($categories as $category)
                <div class="col-lg-4 col-md-6">
                    {{-- Toàn bộ card là một liên kết --}}
                    <a href="{{ route('products.category', ['slug' => $category->slug ?? $category->id]) }}"
                        class="category-showcase-card"
                        style="background-image: url('{{ $category->image_url ?? 'https://placehold.co/400x300/333333/FFFFFF?text=' . urlencode($category->name) }}')">

                        <div class="card-content">
                            <h3 class="category-title">{{ $category->name }}</h3>
                            <p class="product-count">{{ $category->products_count }} sản phẩm</p>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Hiện chưa có danh mục nào để hiển thị.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $categories->links() }}
        </div>
    </div>
@endsection