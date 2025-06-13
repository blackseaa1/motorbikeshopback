<div class="modal fade" id="confirmRestoreBlogModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="restoreBlogForm" method="POST">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Xác nhận Khôi phục</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn khôi phục bài viết <strong id="blogNameToRestore"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận Khôi phục</button>
                </div>
            </div>
        </form>
    </div>
</div>
