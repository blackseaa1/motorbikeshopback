@extends('customer.layouts.app')

@section('title', 'Tất cả Danh mục')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4 text-center">Tất cả danh mục</h1>
        @if(isset($categories) && $categories->isNotEmpty())
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                @foreach($categories as $category)
                    <div class="col">
                        <div class="card h-100 category-card text-center">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h5 class="card-title">{{ $category->name }}</h5>
                                @if($category->products_count > 0)
                                    <p class="card-text text-muted">({{ $category->products_count }} sản phẩm)</p>
                                @endif
                                {{-- Sửa lỗi: Tên route đúng là 'categories.show' --}}
                                <a href="{{ route('categories.show', $category->id) }}"
                                    class="btn btn-sm btn-outline-primary mt-auto">Xem sản phẩm</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 d-flex justify-content-center">
                {{ $categories->links() }}
            </div>
        @else
            <div class="alert alert-warning text-center">Chưa có danh mục nào.</div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .category-card {
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }
    </style>
@endpush