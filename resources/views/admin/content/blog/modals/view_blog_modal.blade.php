<div class="modal fade" id="viewBlogModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye-fill me-2"></i>Chi tiết bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h3 id="blogTitleView"></h3>
                        <div class="d-flex align-items-center text-muted mb-3">
                            <span class="me-3"><i class="bi bi-person-fill me-1"></i> <strong id="blogAuthorView"></strong></span>
                            <span class="me-3"><i class="bi bi-calendar-check me-1"></i> <span id="blogCreatedAtView"></span></span>
                            <span><i class="bi bi-arrow-repeat me-1"></i> Cập nhật: <span id="blogUpdatedAtView"></span></span>
                        </div>
                        <hr>
                        <div id="blogContentView" style="font-size: 1.1rem; line-height: 1.6;"></div>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Ảnh đại diện:</strong></p>
                        <img src="" id="blogImageView" class="img-fluid rounded" alt="Ảnh đại diện">
                        <p class="mt-3"><strong>Trạng thái:</strong> <span id="blogStatusView" class="badge"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
