{{-- resources/views/admin/productManagement/product/modals/view_product.blade.php --}}
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewProductModalLabel">
                    <i class="bi bi-eye-fill me-2"></i>Chi tiết Sản phẩm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- Cột bên trái --}}
                    <div class="col-md-7">
                        <h4><strong id="productNameView"></strong></h4>
                        <p><strong>Mô tả:</strong></p>
                        <p id="productDescriptionView" class="text-muted" style="white-space: pre-wrap;"></p>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <strong>Giá bán:</strong> <span id="productPriceView" class="text-danger fw-bold"></span>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong>Tồn kho:</strong> <span id="productStockView" class="badge bg-info"></span>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong>Danh mục:</strong> <span id="productCategoryView"></span>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong>Thương hiệu:</strong> <span id="productBrandView"></span>
                            </div>
                             <div class="col-sm-6 mb-2">
                                <strong>Trạng thái:</strong> <span id="productStatusView"></span>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong>Chất liệu:</strong> <span id="productMaterialView"></span>
                            </div>
                             <div class="col-sm-6 mb-2">
                                <strong>Màu sắc:</strong> <span id="productColorView"></span>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Thông số kỹ thuật khác:</strong></p>
                        <p id="productSpecificationsView" class="text-muted" style="white-space: pre-wrap;"></p>
                        <p><strong>Tương thích dòng xe:</strong></p>
                        <div id="productVehicleModelsView"></div>
                    </div>
                    {{-- Cột bên phải cho hình ảnh --}}
                    <div class="col-md-5">
                        <strong>Hình ảnh:</strong>
                        <div id="productImagesView" class="mt-2 d-flex flex-column gap-2">
                            {{-- Hình ảnh sẽ được chèn bằng JS --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Đóng
                </button>
                <button type="button" class="btn btn-warning" id="editProductFromViewBtn" data-product-id="">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>