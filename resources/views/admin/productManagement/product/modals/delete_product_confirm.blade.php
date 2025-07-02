{{-- File: resources/views/admin/productManagement/product/modals/delete_product_confirm.blade.php --}}
<div class="modal fade" id="bulkDeleteProductsModal" tabindex="-1" aria-labelledby="bulkDeleteProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkDeleteProductsForm" method="POST"> {{-- Action will be set by JS --}}
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="bulkDeleteProductsModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa mềm Sản phẩm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn chuyển <strong id="productNameToDelete"></strong> vào thùng rác?</p>
                    <p class="text-warning"><strong>Lưu ý:</strong> Hành động này có thể khôi phục được.</p>
                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="deleteProductsPassword" class="form-label">Mật khẩu xác nhận <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="deleteProductsPassword" name="admin_password_confirm_delete" required>
                            <div class="invalid-feedback" id="deleteProductsPasswordError"></div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận Xóa mềm</button>
                </div>
            </form>
        </div>
    </div>
</div>