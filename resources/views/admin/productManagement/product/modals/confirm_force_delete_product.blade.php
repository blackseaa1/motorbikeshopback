<div class="modal fade" id="confirmForceDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="forceDeleteProductForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận Xóa Vĩnh Viễn</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn <strong class="text-danger">XÓA VĨNH VIỄN</strong> sản phẩm <strong id="customerNameToForceDelete"></strong>?</p>
                    <p class="fw-bold text-danger">Hành động này không thể hoàn tác!</p>
                    <div class="mb-3">
                        <label for="adminPasswordConfirmForceDelete" class="form-label">Nhập mật khẩu của bạn để xác nhận:<span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="adminPasswordConfirmForceDelete" name="admin_password_confirm_delete" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Tôi hiểu, Xóa vĩnh viễn</button>
                </div>
            </div>
        </form>
    </div>
</div>