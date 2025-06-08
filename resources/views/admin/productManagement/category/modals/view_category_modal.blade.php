{{-- View Category Modal --}}
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCategoryModalLabel"><i class="bi bi-tags-fill me-2"></i>Chi tiết Danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">ID Danh mục:</dt>
                    <dd class="col-sm-9" id="categoryIdView">-</dd>

                    <dt class="col-sm-3">Tên Danh mục:</dt>
                    <dd class="col-sm-9" id="categoryNameView">-</dd>

                    <dt class="col-sm-3">Mô tả:</dt>
                    <dd class="col-sm-9" id="categoryDescriptionView" style="white-space: pre-wrap; word-break: break-word;">-</dd>

                    <dt class="col-sm-3">Trạng thái:</dt>
                    <dd class="col-sm-9" id="categoryStatusView">-</dd>

                    <dt class="col-sm-3">Ngày tạo:</dt>
                    <dd class="col-sm-9" id="categoryCreatedAtView">-</dd>

                    <dt class="col-sm-3">Cập nhật lần cuối:</dt>
                    <dd class="col-sm-9" id="categoryUpdatedAtView">-</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="editCategoryFromViewButton">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>
