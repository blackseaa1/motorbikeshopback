{{-- resources/views/admin/sales/order/modals/delete_order_modal.blade.php --}}
<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteOrderModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận Xóa Đơn Hàng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng **<strong id="deleteOrderName"></strong>** không? Hành
                    động này không thể hoàn tác.</p>
                <p class="text-danger">Lưu ý: Chỉ nên xóa các đơn hàng sai sót hoặc đã bị hủy. Xóa đơn hàng Đã Duyệt sẽ
                    hoàn trả tồn kho và lượt sử dụng mã khuyến mãi.</p>

                <form id="deleteOrderForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="_form_identifier" value="delete_order_form">

                    @if(config('admin.deletion_password')) {{-- Kiểm tra nếu bạn yêu cầu mật khẩu xóa admin --}}
                        <div class="mt-3">
                            <label for="adminPasswordDeleteOrder" class="form-label">Mật khẩu xác nhận</label>
                            <input type="password"
                                class="form-control @error('admin_password_delete_order', 'delete_order_form') is-invalid @enderror"
                                id="adminPasswordDeleteOrder" name="admin_password_delete_order" required>
                            @error('admin_password_delete_order', 'delete_order_form')
                                <div class="text-danger mt-1">{{$message}}</div>
                            @enderror
                        </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger" form="deleteOrderForm">Tôi chắc chắn, Xóa!</button>
            </div>
        </div>
    </div>
</div>