<div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="createProductForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createProductModalLabel">
                        <i class="bi bi-plus-circle-fill me-2"></i>Tạo Sản phẩm mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productNameCreate" class="form-label">Tên Sản phẩm <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" id="productNameCreate"
                                    placeholder="Nhập tên sản phẩm" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productDescriptionCreate" class="form-label">Mô tả chi tiết:</label>
                                <textarea name="description" class="form-control" id="productDescriptionCreate" rows="4"
                                    placeholder="Nhập mô tả chi tiết"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productCategoryCreate" class="form-label">Danh mục <span
                                        class="text-danger">*</span></label>
                                <select name="category_id" class="selectpicker form-control" id="productCategoryCreate"
                                    data-live-search="true" title="Chọn danh mục..." required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productBrandCreate" class="form-label">Thương hiệu <span
                                        class="text-danger">*</span></label>
                                <select name="brand_id" class="selectpicker form-control" id="productBrandCreate"
                                    data-live-search="true" title="Chọn thương hiệu..." required>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productPriceCreate" class="form-label">Giá bán (VNĐ) <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="price" class="form-control" id="productPriceCreate" min="0"
                                    step="0.01" placeholder="Nhập giá bán" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productStockCreate" class="form-label">Số lượng tồn kho <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="stock_quantity" class="form-control" id="productStockCreate"
                                    min="0" placeholder="Nhập số lượng" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productVehicleModelsCreate" class="form-label">Tương thích với Dòng
                                    xe:</label>
                                <select name="vehicle_model_ids[]" class="selectpicker form-control"
                                    id="productVehicleModelsCreate" multiple data-live-search="true"
                                    title="Chọn các dòng xe tương thích">
                                    @foreach ($vehicleBrands as $vehicleBrand)
                                        @if($vehicleBrand->vehicleModels->count() > 0)
                                            <optgroup label="{{ $vehicleBrand->name }}">
                                                @foreach ($vehicleBrand->vehicleModels as $model)
                                                    <option value="{{ $model->id }}">{{ $model->name }} {{ $model->year }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productMaterialCreate" class="form-label">Chất liệu:</label>
                                <input type="text" name="material" class="form-control" id="productMaterialCreate"
                                    placeholder="Nhập chất liệu">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productColorCreate" class="form-label">Màu sắc:</label>
                                <input type="text" name="color" class="form-control" id="productColorCreate"
                                    placeholder="Nhập màu sắc">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="productSpecificationsCreate" class="form-label">Thông số kỹ thuật
                                    (khác):</label>
                                <textarea name="specifications" class="form-control" id="productSpecificationsCreate"
                                    rows="4" placeholder="Nhập thông số kỹ thuật"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" class="form-check-input"
                                    id="productIsActiveCreate" checked>
                                <label class="form-check-label" for="productIsActiveCreate">Hiển thị sản phẩm</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh sản phẩm:</label>
                                <div class="image-upload-container">
                                    <input type="file" name="product_images[]" id="productImagesCreate" multiple
                                        accept="image/*" class="d-none">

                                    <label for="productImagesCreate" class="image-drop-zone">
                                        <i class="bi bi-cloud-arrow-up-fill"></i>
                                        <p>Kéo và thả file vào đây, hoặc nhấn để chọn ảnh</p>
                                        <small>(Có thể chọn nhiều ảnh)</small>
                                    </label>

                                    <div id="productImagesPreviewCreate" class="mt-2 d-flex flex-wrap gap-2">
                                        {{-- Các ảnh preview sẽ được hiển thị ở đây bằng JavaScript --}}
                                    </div>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>