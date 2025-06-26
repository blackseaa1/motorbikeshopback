<div class="modal fade" id="createPaymentMethodModal" tabindex="-1" aria-labelledby="createPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createPaymentMethodModalLabel"><i class="bi bi-plus-circle me-2"></i>Thêm Phương thức Thanh toán mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createPaymentMethodForm" method="POST" action="{{ route('admin.system.paymentMethods.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="pmNameCreate" class="form-label fw-bold">Tên Phương thức</label>
                                <input type="text" class="form-control" id="pmNameCreate" name="name" required>
                                <div class="invalid-feedback" id="pmNameCreateError"></div>
                            </div>
                            <div class="mb-3">
                                <label for="pmCodeCreate" class="form-label fw-bold">Mã (Code)</label>
                                <input type="text" class="form-control" id="pmCodeCreate" name="code" required placeholder="Ví dụ: cod, vnpay, momo">
                                <div class="invalid-feedback" id="pmCodeCreateError"></div>
                            </div>
                             <div class="mb-3">
                                <label for="pmDescriptionCreate" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="pmDescriptionCreate" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="pmStatusCreate" class="form-label fw-bold">Trạng thái</label>
                                <select class="form-select" id="pmStatusCreate" name="status">
                                    <option value="active" selected>Hoạt động</option>
                                    <option value="inactive">Tạm ẩn</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="pmLogoCreate" class="form-label fw-bold">Logo</label>
                                <input class="form-control" type="file" id="pmLogoCreate" name="logo" accept="image/*">
                                <div class="mt-2">
                                    <img src="https://placehold.co/150x75/EFEFEF/AAAAAA&text=Preview"
                                         id="pmLogoPreviewCreate" class="img-thumbnail" alt="Logo Preview"
                                         style="max-width: 100%; height: auto; object-fit: contain;"
                                         data-default-src="https://placehold.co/150x75/EFEFEF/AAAAAA&text=Preview">
                                </div>
                                <div class="invalid-feedback" id="pmLogoCreateError"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>