@extends('customer.layouts.app')

@section('title', 'Tất cả sản phẩm')

@section('content')
<div class="container py-5">
    <div class="row">
        <aside class="col-lg-3">
            <h3><i class="bi bi-filter"></i> Bộ lọc</h3>
            <hr>

            <div class="mb-4">
                <h5>Danh mục</h5>
                <ul class="list-unstyled">
                    @foreach($sharedCategories as $category)
                        <li>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{ $category->id }}" id="cat-{{ $category->id }}">
                                <label class="form-check-label" for="cat-{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mb-4">
                <h5>Thương hiệu</h5>
                {{-- Giả sử bạn có biến $brands được truyền từ Controller --}}
                {{-- @foreach($brands as $brand) --}}
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="brand_id" id="brand-1">
                    <label class="form-check-label" for="brand-1">Honda</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="brand_id" id="brand-2">
                    <label class="form-check-label" for="brand-2">Yamaha</label>
                </div>
                {{-- @endforeach --}}
            </div>
            
            <div class="mb-4">
                <h5>Tương thích xe</h5>
                 {{-- Tương tự, bạn cần có danh sách các loại xe --}}
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="vehicle_id" id="vehicle-1">
                    <label class="form-check-label" for="vehicle-1">Wave Alpha</label>
                </div>
                 <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="vehicle_id" id="vehicle-2">
                    <label class="form-check-label" for="vehicle-2">Exciter 150</label>
                </div>
            </div>

            <button class="btn btn-primary w-100">Áp dụng</button>
        </aside>

        <main class="col-lg-9">
            <h1 class="mb-4">Tất cả sản phẩm</h1>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                {{-- Vòng lặp để hiển thị sản phẩm --}}
                {{-- @foreach($products as $product) --}}
                <div class="col">
                    <div class="card h-100">
                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Tên sản phẩm</h5>
                            <p class="card-text text-danger fw-bold">1.200.000₫</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                             <a href="#" class="btn btn-primary w-100">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                {{-- @endforeach --}}

                <div class="col">
                    <div class="card h-100">
                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title">Tên sản phẩm 2</h5>
                            <p class="card-text text-danger fw-bold">950.000₫</p>
                        </div>
                         <div class="card-footer bg-transparent border-top-0">
                             <a href="#" class="btn btn-primary w-100">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Phân trang --}}
            <div class="mt-5 d-flex justify-content-center">
                {{-- {{ $products->links() }} --}}
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Thêm javascript để xử lý logic lọc ở đây
</script>
@endpush