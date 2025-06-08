{{-- Update Brand Modal --}}
<div class="modal fade" id="updateBrandModal" tabindex="-1" aria-labelledby="updateBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="updateBrandForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateBrandModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                        Thương hiệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="brandNameUpdate" class="form-label">Tên Thương hiệu <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="brandNameUpdate" name="name" required>
                        <div class="invalid-feedback" id="brandNameUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="brandDescriptionUpdate" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="brandDescriptionUpdate" name="description"
                            rows="3"></textarea>
                        <div class="invalid-feedback" id="brandDescriptionUpdateError"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="brandLogoUpdate" class="form-label">Logo mới (để trống nếu không
                                    đổi)</label>
                                <input type="file" class="form-control" id="brandLogoUpdate" name="logo_url"
                                    accept="image/*">
                                <small class="form-text text-muted">Định dạng: JPG, PNG, GIF, SVG, WEBP. Tối đa
                                    2MB.</small>
                                <div class="invalid-feedback" id="brandLogoUrlUpdateError"></div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <img id="brandLogoPreviewUpdate"
                                src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=Current"
                                alt="Xem trước logo hiện tại" class="img-thumbnail mt-2"
                                style="max-height: 150px; max-width: 150px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="brandStatusUpdate" class="form-label">Trạng thái <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="brandStatusUpdate" name="status" required>
                            <option value="{{ \App\Models\Brand::STATUS_ACTIVE }}">Hoạt động (Hiển thị)</option>
                            <option value="{{ \App\Models\Brand::STATUS_INACTIVE }}">Ẩn (Không hiển thị)</option>
                        </select>
                        <div class="invalid-feedback" id="brandStatusUpdateError"></div>
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