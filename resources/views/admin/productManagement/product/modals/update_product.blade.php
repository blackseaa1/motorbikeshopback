
<div class="modal fade" id="updateProductModal" tabindex="-1" aria-labelledby="updateProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="updateProductForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProductModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Cập nhật Sản phẩm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productNameUpdate" class="form-label">Tên Sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" id="productNameUpdate" placeholder="Nhập tên sản phẩm" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productDescriptionUpdate" class="form-label">Mô tả chi tiết:</label>
                                <textarea name="description" class="form-control" id="productDescriptionUpdate" rows="4"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productCategoryUpdate" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="selectpicker form-control" id="productCategoryUpdate" data-live-search="true" title="Chọn danh mục..." required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productBrandUpdate" class="form-label">Thương hiệu <span class="text-danger">*</span></label>
                                <select name="brand_id" class="selectpicker form-control" id="productBrandUpdate" data-live-search="true" title="Chọn thương hiệu..." required>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productPriceUpdate" class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" id="productPriceUpdate" min="0" step="0.01" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productStockUpdate" class="form-label">Số lượng tồn kho <span class="text-danger">*</span></label>
                                <input type="number" name="stock_quantity" class="form-control" id="productStockUpdate" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productVehicleModelsUpdate" class="form-label">Tương thích với Dòng xe:</label>
                                <select name="vehicle_model_ids[]" class="selectpicker form-control" id="productVehicleModelsUpdate" multiple data-live-search="true" title="Chọn các dòng xe tương thích">
                                    @foreach ($vehicleBrands as $vehicleBrand)
                                        <optgroup label="{{ $vehicleBrand->name }}">
                                            @foreach ($vehicleBrand->vehicleModels as $model)
                                                <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->year }})</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productMaterialUpdate" class="form-label">Chất liệu:</label>
                                <input type="text" name="material" class="form-control" id="productMaterialUpdate">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productColorUpdate" class="form-label">Màu sắc:</label>
                                <input type="text" name="color" class="form-control" id="productColorUpdate">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productSpecificationsUpdate" class="form-label">Thông số kỹ thuật (khác):</label>
                                <textarea name="specifications" class="form-control" id="productSpecificationsUpdate" rows="4"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="productIsActiveUpdate" checked>
                                <label class="form-check-label" for="productIsActiveUpdate">Hiển thị sản phẩm</label>
                            </div>
                            <div class="mb-3">
                                <label for="productImagesUpdate" class="form-label">Thêm hình ảnh mới:</label>
                                <input type="file" name="product_images[]" class="form-control" id="productImagesUpdate" multiple accept="image/*">
                                <div class="invalid-feedback"></div>
                                <label class="form-label mt-2">Ảnh hiện tại:</label>
                                <div id="productImagesPreviewUpdate" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
