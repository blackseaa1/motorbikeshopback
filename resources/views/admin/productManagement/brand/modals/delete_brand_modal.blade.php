{{-- Delete Brand Modal --}}
<div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- CHỈNH SỬA: Đảm bảo method là POST và loại bỏ @method('DELETE') --}}
            <form id="deleteBrandForm" method="POST">
                @csrf
                {{-- ĐÃ XÓA: @method('DELETE') --}} {{-- Dòng này là nguyên nhân gây lỗi --}}
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteBrandModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa
                        Thương hiệu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa vĩnh viễn thương hiệu "<strong id="brandNameToDelete"></strong>"?</p>
                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="brandDeletionPassword" class="form-label">Mật khẩu xác nhận xóa <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="brandDeletionPassword" name="deletion_password"
                                required>
                            <div class="invalid-feedback" id="brandDeletionPasswordError"></div>
                        </div>
                    @endif
                    <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Vĩnh Viễn</button>
                </div>
            </form>
        </div>
    </div>
</div>