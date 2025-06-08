{{-- Delete Category Modal --}}
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- action sẽ được JS đặt động --}}
            <form id="deleteCategoryForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa Danh mục</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa vĩnh viễn danh mục "<strong id="categoryNameToDelete"></strong>"?</p>

                    {{-- Chỉ hiển thị ô nhập mật khẩu nếu được cấu hình trong file .env hoặc config --}}
                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="categoryDeletionPassword" class="form-label">Mật khẩu xác nhận xóa <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="categoryDeletionPassword" name="deletion_password" required>
                            {{-- Div để JS hiển thị lỗi validation cho mật khẩu --}}
                            <div class="invalid-feedback" id="categoryDeletionPasswordError"></div>
                        </div>
                    @endif

                    <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác và sẽ xóa tất cả sản phẩm liên quan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Vĩnh Viễn</button>
                </div>
            </form>
        </div>
    </div>
</div>
