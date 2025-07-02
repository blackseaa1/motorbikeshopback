{{-- resources/views/admin/productManagement/product/modals/modal_bulk_force_delete_product.blade.php --}}
<div class="modal fade" id="bulkForceDeleteProductModal" tabindex="-1" aria-labelledby="bulkForceDeleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkForceDeleteProductModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận Xóa Vĩnh Viễn Hàng Loạt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkForceDeleteProductForm" action="{{ route('admin.productManagement.products.bulkForceDelete') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <strong>Cảnh báo!</strong> Bạn có chắc chắn muốn xóa vĩnh viễn <strong id="bulkForceDeleteCount">0</strong> sản phẩm đã chọn?
                    </div>
                    <p class="text-muted">Hành động này không thể được hoàn tác. Tất cả dữ liệu liên quan đến các sản phẩm này sẽ bị xóa khỏi hệ thống.</p>

                    @if(config('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="admin_password_bulk_force_delete" class="form-label">Vui lòng nhập mật khẩu của bạn để xác nhận</label>
                            <input type="password" class="form-control" id="admin_password_bulk_force_delete" name="admin_password_bulk_force_delete" required>
                            <div class="invalid-feedback" id="admin_password_bulk_force_delete_error"></div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Vĩnh viễn</button>
                </div>
            </form>
        </div>
    </div>
</div>
