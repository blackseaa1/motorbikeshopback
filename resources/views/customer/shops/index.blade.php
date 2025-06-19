@extends('customer.layouts.app')

@section('title', 'Cửa hàng sản phẩm')

@section('content')
    <main class="container py-5">
        <div class="row">
            {{-- BỘ LỌC (ASIDE) --}}
            <aside class="col-lg-3">
                <form action="{{ route('products.index') }}" method="GET" id="filter-form">
                    <div class="card filter-card">
                        <div class="card-header">
                            <i class="bi bi-funnel-fill me-2"></i>Bộ lọc
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h5>Danh mục</h5>
                                <select class="selectpicker form-control" name="categories[]" multiple
                                    data-live-search="true" title="Chọn danh mục...">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ in_array($category->id, $request->input('categories', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <h5>Thương hiệu</h5>
                                <select class="selectpicker form-control" name="brands[]" multiple data-live-search="true"
                                    title="Chọn thương hiệu...">
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ in_array($brand->id, $request->input('brands', [])) ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <h5>Loại xe</h5>
                                <select class="selectpicker form-control" name="vehicle_brand_id" id="vehicle-brand-select"
                                    data-live-search="true" title="Chọn loại xe...">
                                    @foreach ($vehicleBrands as $vBrand)
                                        <option value="{{ $vBrand->id }}"
                                            {{ $request->input('vehicle_brand_id') == $vBrand->id ? 'selected' : '' }}>
                                            {{ $vBrand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <h5>Mẫu xe</h5>
                                <select class="selectpicker form-control" name="vehicle_model_id" id="vehicle-model-select"
                                    data-live-search="true" title="Chọn mẫu xe..." disabled>
                                    {{-- Options sẽ được thêm bằng JavaScript --}}
                                </select>
                            </div>

                            <hr>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Áp dụng</button>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                            </div>
                        </div>
                    </div>
                </form>
            </aside>

            {{-- DANH SÁCH SẢN PHẨM --}}
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Tất cả sản phẩm</h1>
                    <span class="text-muted" id="product-count">Tìm thấy {{ $products->total() }} sản phẩm</span>
                </div>

                {{-- Vùng chứa danh sách sản phẩm sẽ được cập nhật bởi JS --}}
                <div id="product-list-wrapper">
                    @include('customer.shops.partials.product_list', ['products' => $products])
                </div>

                {{-- Vùng chứa phân trang sẽ được cập nhật bởi JS --}}
                <div class="mt-4 d-flex justify-content-center" id="pagination-links">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')

    {{-- Dữ liệu ban đầu cho JavaScript --}}
    <script>
        // Truyền dữ liệu từ PHP Controller sang JavaScript
        window.vehicleDataForFilter = @json($vehicleBrands->keyBy('id'));
        window.selectedFilters = {
            brandId: '{{ $request->input('vehicle_brand_id') }}',
            modelId: '{{ $request->input('vehicle_model_id') }}'
        };
        // URL cho API
        window.productsApiUrl = "{{ route('api.customer.products.index') }}";
        // URL trang sản phẩm (để cập nhật thanh địa chỉ)
        window.productsPageUrl = "{{ route('products.index') }}";
    </script>

    {{-- TẢI FILE SHOP.JS --}}
    <script src="{{ asset('assets_customer/js/shop.js') }}" defer></script>
@endpush