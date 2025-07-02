{{-- File: resources/views/admin/productManagement/product/modals/force_delete_product_confirm.blade.php --}}
<div class="modal fade" id="bulkForceDeleteProductsModal" tabindex="-1" aria-labelledby="bulkForceDeleteProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkForceDeleteProductsForm" method="POST"> {{-- Action will be set by JS --}}
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="bulkForceDeleteProductsModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận Xóa Vĩnh viễn Sản phẩm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <strong>Cảnh báo!</strong> Bạn có chắc chắn muốn xóa vĩnh viễn <strong id="productNameToForceDelete"></strong>?
                    </div>
                    <p class="text-muted">Hành động này không thể được hoàn tác. Tất cả dữ liệu liên quan đến sản phẩm này sẽ bị xóa khỏi hệ thống.</p>

                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="forceDeleteProductsPassword" class="form-label">Vui lòng nhập mật khẩu của bạn để xác nhận <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="forceDeleteProductsPassword" name="admin_password_confirm_delete" required>
                            <div class="invalid-feedback" id="forceDeleteProductsPasswordError"></div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Xóa Vĩnh viễn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>