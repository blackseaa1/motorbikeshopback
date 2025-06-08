{{-- Update Category Modal --}}
<div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{-- action sẽ được JS đặt động --}}
            <form id="updateCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryNameUpdate" class="form-label">Tên Danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryNameUpdate" name="name" required>
                        {{-- Div để JS hiển thị lỗi validation cho trường 'name' --}}
                        <div class="invalid-feedback" id="categoryNameUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescriptionUpdate" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="categoryDescriptionUpdate" name="description" rows="3"></textarea>
                         {{-- Div để JS hiển thị lỗi validation cho trường 'description' --}}
                        <div class="invalid-feedback" id="categoryDescriptionUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="categoryStatusUpdate" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select" id="categoryStatusUpdate" name="status" required>
                            <option value="{{ \App\Models\Category::STATUS_ACTIVE }}">Hoạt động (Hiển thị)</option>
                            <option value="{{ \App\Models\Category::STATUS_INACTIVE }}">Ẩn (Không hiển thị)</option>
                        </select>
                         {{-- Div để JS hiển thị lỗi validation cho trường 'status' --}}
                        <div class="invalid-feedback" id="categoryStatusUpdateError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
