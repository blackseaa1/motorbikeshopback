@extends('customer.layouts.app')

@section('title', 'Cửa hàng sản phẩm')

{{-- Thêm CSS cho bootstrap-select vào section head (xem Bước 4) --}}
@push('styles')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <style>
        /* Tùy chỉnh nhỏ cho bộ lọc */
        .filter-card .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
@endpush


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
                    <span class="text-muted">Tìm thấy {{ $products->total() }} sản phẩm</span>
                </div>

                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @forelse($products as $product)
                        <div class="col">
                            <div class="card h-100 product-card">
                                <a href="{{ route('products.show', $product->id) }}">
                                    <img src="{{ $product->thumbnail_url ?? 'https://via.placeholder.com/300x200?text=MotoToys' }}"
                                        class="card-img-top" alt="{{ $product->name }}">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title flex-grow-1">
                                        <a href="{{ route('products.show', $product->id) }}"
                                            class="text-decoration-none text-dark stretched-link">{{ $product->name }}</a>
                                    </h5>
                                    <p class="card-text text-danger fw-bold fs-5 mt-auto mb-0">
                                        {{ number_format($product->price, 0, ',', '.') }}₫</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">Không tìm thấy sản phẩm nào phù hợp với tiêu chí
                                của bạn.</div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection

{{-- Thêm JS cho bootstrap-select và logic dropdown phụ thuộc (xem Bước 4) --}}
@push('scripts')

    <script>
        $(function() {
            // Khởi tạo bootstrap-select
            $('.selectpicker').selectpicker();

            // Dữ liệu các mẫu xe theo từng loại xe (truyền từ Controller)
            const vehicleData = @json($vehicleBrands->keyBy('id'));
            const selectedVehicleBrandId = '{{ $request->input('vehicle_brand_id') }}';
            const selectedVehicleModelId = '{{ $request->input('vehicle_model_id') }}';

            function populateVehicleModels(brandId) {
                const modelSelect = $('#vehicle-model-select');
                modelSelect.empty(); // Xóa các option cũ

                if (brandId && vehicleData[brandId] && vehicleData[brandId].vehicle_models.length > 0) {
                    const models = vehicleData[brandId].vehicle_models;
                    models.forEach(function(model) {
                        modelSelect.append($('<option>', {
                            value: model.id,
                            text: model.name
                        }));
                    });
                    modelSelect.prop('disabled', false);
                } else {
                    modelSelect.prop('disabled', true);
                }
                
                // Refresh lại selectpicker để cập nhật UI
                modelSelect.selectpicker('refresh');
            }

            // Xử lý khi trang tải xong
            if (selectedVehicleBrandId) {
                populateVehicleModels(selectedVehicleBrandId);
                // Chọn lại mẫu xe đã lọc trước đó
                if (selectedVehicleModelId) {
                    $('#vehicle-model-select').selectpicker('val', selectedVehicleModelId);
                }
            }


            // Xử lý khi thay đổi Loại xe
            $('#vehicle-brand-select').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
                const selectedBrandId = $(this).val();
                populateVehicleModels(selectedBrandId);
            });
        });
    </script>
@endpush