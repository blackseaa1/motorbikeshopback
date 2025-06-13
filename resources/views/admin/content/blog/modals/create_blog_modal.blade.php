<div class="modal fade" id="createBlogModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="createBlogForm" action="{{ route('admin.content.blogs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Viết bài mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="blogTitleCreate" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" id="blogTitleCreate" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="blogContentCreate" class="form-label">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" id="blogContentCreate" rows="10" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label for="blogImageCreate" class="form-label">Ảnh đại diện</label>
                                <input type="file" name="image" class="form-control" id="blogImageCreate" accept="image/*">
                                <div class="invalid-feedback"></div>
                            </div>
                            <img src="https://placehold.co/400x250/EFEFEF/AAAAAA&text=Preview" id="blogImagePreviewCreate" class="img-fluid rounded mt-2" alt="Preview">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blogStatusCreate" class="form-label">Trạng thái</label>
                                <select name="status" id="blogStatusCreate" class="form-select">
                                    <option value="draft" selected>Bản nháp</option>
                                    {{-- SỬA ĐỔI: Đổi 'changeStatus' thành 'toggleStatus' để khớp với Policy --}}
                                    @can('toggleStatus', App\Models\BlogPost::class)
                                        <option value="published">Xuất bản</option>
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
                    <button type="submit" class="btn btn-primary">Lưu bài viết</button>
                </div>
            </div>
        </form>
    </div>
</div>
