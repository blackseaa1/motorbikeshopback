
<div class="modal fade" id="confirmForceDeleteModal" tabindex="-1" aria-labelledby="confirmForceDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmForceDeleteModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận Xóa Vĩnh viễn</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- SỬA ĐỔI: Chuyển form ra ngoài để bao toàn bộ modal-body và modal-footer --}}
            <form id="forceDeleteProductForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <strong>Cảnh báo!</strong> Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm "<strong id="productNameToForceDelete"></strong>"?
                    </div>
                    <p class="text-muted">Hành động này không thể được hoàn tác. Tất cả dữ liệu liên quan đến sản phẩm này sẽ bị xóa khỏi hệ thống.</p>
                    
                    {{-- THÊM MỚI: Thêm trường nhập mật khẩu để xác nhận --}}
                    <div class="mb-3">
                        <label for="admin_password_confirm_delete" class="form-label">Vui lòng nhập mật khẩu của bạn để xác nhận</label>
                        <input type="password" class="form-control" id="admin_password_confirm_delete" name="admin_password_confirm_delete" required>
                        <div class="invalid-feedback" id="admin_password_confirm_delete_error"></div>
                    </div>
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