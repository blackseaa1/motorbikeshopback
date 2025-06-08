<div class="modal fade" id="deletePromotionModal" tabindex="-1" aria-labelledby="deletePromotionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePromotionModalLabel"><i
                        class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận Xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa vĩnh viễn mã khuyến mãi <strong id="deletePromotionCode"></strong> không?
                    Hành động này không thể hoàn tác.</p>
                <p class="text-danger">Lưu ý: Bạn chỉ có thể xóa các mã khuyến mãi chưa từng được sử dụng.</p>

                <form id="deletePromotionForm" method="POST">
                    @csrf
                    @method('DELETE')
                    {{-- Trường ẩn để xác định form khi có lỗi validation --}}
                    <input type="hidden" name="_form_identifier" value="delete_promotion_form">

                    @if(config('admin.deletion_password'))
                        <div class="mt-3">
                            <label for="adminPasswordDeletePromotion" class="form-label">Mật khẩu xác nhận</label>
                            <input type="password" class="form-control" id="adminPasswordDeletePromotion"
                                name="admin_password_delete_promotion" required>
                            @error('admin_password_delete_promotion', 'delete_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger" form="deletePromotionForm">Tôi chắc chắn, Xóa!</button>
            </div>
        </div>
    </div>
</div>