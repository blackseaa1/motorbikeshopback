<div class="modal fade" id="updatePaymentMethodModal" tabindex="-1" aria-labelledby="updatePaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="updatePaymentMethodModalLabel"><i class="bi bi-pencil-square me-2"></i>Chỉnh sửa Phương thức Thanh toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updatePaymentMethodForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="existing_logo_url" id="pmExistingLogoUrl">
                     <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="pmNameUpdate" class="form-label fw-bold">Tên Phương thức</label>
                                <input type="text" class="form-control" id="pmNameUpdate" name="name" required>
                                <div class="invalid-feedback" id="pmNameUpdateError"></div>
                            </div>
                            <div class="mb-3">
                                <label for="pmCodeUpdate" class="form-label fw-bold">Mã (Code)</label>
                                <input type="text" class="form-control" id="pmCodeUpdate" name="code" required>
                                <div class="invalid-feedback" id="pmCodeUpdateError"></div>
                            </div>
                             <div class="mb-3">
                                <label for="pmDescriptionUpdate" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="pmDescriptionUpdate" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="pmStatusUpdate" class="form-label fw-bold">Trạng thái</label>
                                <select class="form-select" id="pmStatusUpdate" name="status">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Tạm ẩn</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="pmLogoUpdate" class="form-label fw-bold">Logo mới (tùy chọn)</label>
                                <input class="form-control" type="file" id="pmLogoUpdate" name="logo" accept="image/*">
                                <div class="mt-2">
                                     <p class="form-text text-muted small">Logo hiện tại:</p>
                                    <img src="" id="pmLogoPreviewUpdate" class="img-thumbnail" alt="Current Logo"
                                         style="max-width: 100%; height: auto; object-fit: contain;"
                                         data-default-src="https://placehold.co/150x75/EFEFEF/AAAAAA&text=Current">
                                </div>
                                <div class="invalid-feedback" id="pmLogoUpdateError"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>