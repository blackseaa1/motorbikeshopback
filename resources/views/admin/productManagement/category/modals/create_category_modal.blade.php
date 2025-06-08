{{-- Create Category Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createCategoryForm" action="{{ route('admin.productManagement.categories.store') }}" method="POST">
                @csrf
                {{-- Trường ẩn để xác định form nào được submit khi có lỗi validation --}}
                <input type="hidden" name="_form_marker" value="create_category">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryNameCreate" class="form-label">Tên Danh mục <span class="text-danger">*</span></label>
                        {{-- Hiển thị lỗi validation từ phía server --}}
                        <input type="text" class="form-control @error('name', '_form_marker') is-invalid @enderror" id="categoryNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name', '_form_marker')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescriptionCreate" class="form-label">Mô tả</label>
                        <textarea class="form-control @error('description', '_form_marker') is-invalid @enderror" id="categoryDescriptionCreate" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description', '_form_marker')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="categoryStatusCreate" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status', '_form_marker') is-invalid @enderror" id="categoryStatusCreate" name="status" required>
                            {{-- Mặc định chọn Hoạt động --}}
                            <option value="{{ \App\Models\Category::STATUS_ACTIVE }}" {{ old('status', \App\Models\Category::STATUS_ACTIVE) == \App\Models\Category::STATUS_ACTIVE ? 'selected' : '' }}>
                                Hoạt động (Hiển thị)
                            </option>
                            <option value="{{ \App\Models\Category::STATUS_INACTIVE }}" {{ old('status') == \App\Models\Category::STATUS_INACTIVE ? 'selected' : '' }}>
                                Ẩn (Không hiển thị)
                            </option>
                        </select>
                        @error('status', '_form_marker')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Danh mục</button>
                </div>
            </form>
        </div>
    </div>
</div>
