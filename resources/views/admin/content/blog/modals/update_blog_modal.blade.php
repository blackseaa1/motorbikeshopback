<div class="modal fade" id="updateBlogModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="updateBlogForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Chỉnh sửa bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="blogTitleUpdate" class="form-label">Tiêu đề <span
                                class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" id="blogTitleUpdate" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="blogContentUpdate" class="form-label">Nội dung <span
                                class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" id="blogContentUpdate" rows="10"
                            required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blogImageUpdate" class="form-label">Thay đổi ảnh đại diện</label>
                                <input type="file" name="image" class="form-control" id="blogImageUpdate"
                                    accept="image/*">
                                <div class="invalid-feedback"></div>
                            </div>
                            <img src="https://placehold.co/400x250/EFEFEF/AAAAAA&text=Preview"
                                id="blogImagePreviewUpdate" class="img-fluid rounded mt-2" alt="Preview">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blogStatusUpdate" class="form-label">Trạng thái</label>
                                <select name="status" id="blogStatusUpdate" class="form-select">
                                    <option value="draft">Bản nháp</option>
                                    {{-- SỬA ĐỔI: Đổi 'changeStatus' thành 'toggleStatus' để khớp với Policy --}}
                                    @can('toggleStatus', App\Models\BlogPost::class)
                                        <option value="published">Xuất bản</option>
                                        <option value="pending">Chờ duyệt</option>
                                        <option value="archived">Ẩn bài viết</option>
                                    @else
                                        <option value="pending">Gửi duyệt</option>
                                    @endcan
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </div>
</div>