{{-- resources/views/admin/productManagement/product/modals/modal_bulk_force_delete_product.blade.php --}}
<div class="modal fade" id="modalBulkForceDeleteProduct" tabindex="-1" aria-labelledby="modalBulkForceDeleteProductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white"> {{-- Màu sắc khác để phân biệt --}}
                <h5 class="modal-title" id="modalBulkForceDeleteProductLabel"><i class="bi bi-x-circle me-2"></i>Xác nhận Xóa vĩnh viễn hàng loạt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBulkForceDeleteProduct" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <strong>Cảnh báo!</strong> Bạn có chắc chắn muốn xóa vĩnh viễn <strong id="bulkForceDeleteProductCount">0</strong> sản phẩm đã chọn?
                    </div>
                    <p class="text-muted">Hành động này không thể được hoàn tác. Tất cả dữ liệu liên quan sẽ bị xóa khỏi hệ thống.</p>

                    <div class="mb-3">
                        <label for="bulkForceDeletePassword" class="form-label">Vui lòng nhập mật khẩu của bạn để xác nhận</label>
                        <input type="password" class="form-control" id="bulkForceDeletePassword" name="admin_password_confirm_delete" required>
                        <div class="invalid-feedback" id="bulkForceDeletePasswordError"></div>
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