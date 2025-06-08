<div class="modal fade" tabindex="-1" id="deleteDeliveryServiceModal">
    <div class="modal-dialog" role="dialog">
        <div class="modal-content">
            <form id="deleteDeliveryServiceForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Xác nhận Xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa đơn vị giao hàng <strong id="dsNameToDelete">N/A</strong> không? Hành
                        động này không thể hoàn tác.</p>

                    @if(config('admin.deletion_password'))
                        <div class="form-group mb-3">
                            <label for="dsDeletionPassword" class="form-label">Mật khẩu xác nhận <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="dsDeletionPassword" name="deletion_password"
                                placeholder="Nhập mật khẩu để xác nhận" required>
                            <div class="invalid-feedback" id="dsDeletionPasswordError"></div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" id="btn-delete-submit" class="btn btn-danger">Tôi chắc chắn, Xóa!</button>
                </div>
            </form>
        </div>
    </div>
</div>