<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteOrderModalLabel"><i class="bi bi-trash me-2"></i>Xác nhận Xóa Đơn Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deleteOrderForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="delete_order_id" name="id">
                    <p>Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng #<span id="delete-order-id"></span>? Hành động này không thể hoàn tác.</p>
                    <p class="text-muted">Lưu ý: Chỉ nên xóa các đơn hàng sai sót hoặc đã bị hủy. Xóa đơn hàng Đã Duyệt sẽ hoàn trả tồn kho và lượt sử dụng mã khuyến mãi.</p>
                    @if (config('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="delete_password" class="form-label">Mật khẩu xác nhận</label>
                            <input type="password" class="form-control" id="delete_password" name="password" autocomplete="new-password">
                            <div class="text-danger mt-1" data-field="password"></div>
                        </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" form="deleteOrderForm" class="btn btn-danger">Tôi chắc chắn, Xóa!</button>
            </div>
        </div>
    </div>
</div>
