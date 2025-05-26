@extends('admin.layouts.app')

@section('title', 'Products') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-tags-fill me-2"></i>Sản phẩm</h1>
    </header>

    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-box-seam-fill me-2"></i>Danh sách Sản phẩm</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Sản phẩm mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Ảnh</th>
                                <th scope="col">Tên Sản phẩm</th>
                                <th scope="col">Danh mục</th>
                                <th scope="col">Thương hiệu</th>
                                <th scope="col">Giá</th>
                                <th scope="col">Số lượng tồn</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua $products từ controller --}}
                            <tr>
                                <td>1</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=SP" alt="Ảnh SP"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Dầu nhớt ABC</td>
                                <td>Phụ tùng</td>
                                <td>Castrol</td>
                                <td>150,000 đ</td>
                                <td>100</td>
                                <td><span class="badge bg-success">Đang hoạt động</span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm btn-action" title="Xem chi tiết">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateProductModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteProductModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Kết thúc lặp --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals for Product Management --}}

    <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl"> {{-- modal-xl cho form dài hơn --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProductModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Sản
                        phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createProductForm" enctype="multipart/form-data">
                        {{-- @csrf --}}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="productNameCreate" class="form-label">Tên Sản phẩm:</label>
                                    <input type="text" class="form-control" id="productNameCreate" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="productDescriptionCreate" class="form-label">Mô tả chi tiết:</label>
                                    <textarea class="form-control" id="productDescriptionCreate" name="description"
                                        rows="5"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="productCategoryCreate" class="form-label">Danh mục:</label>
                                        <select class="form-select" id="productCategoryCreate" name="category_id" required>
                                            <option value="">Chọn danh mục...</option>
                                            {{-- Lặp qua $categories từ controller --}}
                                            {{-- @foreach($categories as $category) --}}
                                            {{-- <option value="{{ $category->id }}">{{ $category->name }}</option> --}}
                                            {{-- @endforeach --}}
                                            <option value="1">Phụ tùng</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="productBrandCreate" class="form-label">Thương hiệu (sản phẩm):</label>
                                        <select class="form-select" id="productBrandCreate" name="brand_id" required>
                                            <option value="">Chọn thương hiệu...</option>
                                            {{-- Lặp qua $brands từ controller --}}
                                            {{-- @foreach($brands as $brand) --}}
                                            {{-- <option value="{{ $brand->id }}">{{ $brand->name }}</option> --}}
                                            {{-- @endforeach --}}
                                            <option value="1">Castrol</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="productPriceCreate" class="form-label">Giá bán (VNĐ):</label>
                                        <input type="number" class="form-control" id="productPriceCreate" name="price"
                                            step="1000" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="productStockCreate" class="form-label">Số lượng tồn kho:</label>
                                        <input type="number" class="form-control" id="productStockCreate"
                                            name="stock_quantity" value="0" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="productVehicleModelsCreate" class="form-label">Tương thích với Dòng
                                        xe:</label>
                                    <select class="form-select select2-multiple" id="productVehicleModelsCreate"
                                        name="vehicle_model_ids[]" multiple="multiple" style="width: 100%;">
                                        {{-- Lặp qua $vehicleModels từ controller, có thể nhóm theo hãng xe --}}
                                        {{-- @foreach($vehicleBrands as $vehicleBrand) --}}
                                        {{-- <optgroup label="{{ $vehicleBrand->name }}"> --}}
                                            {{-- @foreach($vehicleBrand->vehicleModels as $model) --}}
                                            {{-- <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->year }})
                                            </option> --}}
                                            {{-- @endforeach --}}
                                            {{-- </optgroup> --}}
                                        {{-- @endforeach --}}
                                        <optgroup label="Honda">
                                            <option value="1">Wave Alpha (2023)</option>
                                            <option value="2">Air Blade (2022)</option>
                                        </optgroup>
                                        <optgroup label="Yamaha">
                                            <option value="3">Sirius (2023)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="productMaterialCreate" class="form-label">Chất liệu:</label>
                                        <input type="text" class="form-control" id="productMaterialCreate" name="material">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="productColorCreate" class="form-label">Màu sắc:</label>
                                        <input type="text" class="form-control" id="productColorCreate" name="color">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="productSpecificationsCreate" class="form-label">Thông số kỹ thuật
                                        (khác):</label>
                                    <textarea class="form-control" id="productSpecificationsCreate" name="specifications"
                                        rows="3"></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="productIsActiveCreate"
                                        name="is_active" value="1" checked>
                                    <label class="form-check-label" for="productIsActiveCreate">Hiển thị sản phẩm</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="productImagesCreate" class="form-label">Hình ảnh sản phẩm:</label>
                                    <input type="file" class="form-control" id="productImagesCreate" name="product_images[]"
                                        multiple>
                                    <div id="productImagesPreviewCreate" class="mt-2 d-flex flex-wrap gap-2">
                                        {{-- Xem trước ảnh sẽ hiển thị ở đây --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" form="createProductForm">Lưu Sản phẩm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Product Modal (Tương tự Create, nhưng có sẵn dữ liệu và @method('PUT')) --}}
    {{-- Delete Product Modal (Tương tự các modal xóa khác) --}}


@endsection

@push('styles')
@endpush

@push('scripts')
@endpush