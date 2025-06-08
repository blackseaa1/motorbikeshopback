{{-- View Brand Modal --}}
<div class="modal fade" id="viewBrandModal" tabindex="-1" aria-labelledby="viewBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBrandModalLabel"><i class="bi bi-bookmark-star-fill me-2"></i>Chi tiết Thương hiệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="brandLogoView" src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO" alt="Logo thương hiệu" class="img-thumbnail mb-3" style="max-height: 150px; max-width: 150px; object-fit: contain;">
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-4">ID Thương hiệu:</dt>
                            <dd class="col-sm-8" id="brandIdView">-</dd>

                            <dt class="col-sm-4">Tên Thương hiệu:</dt>
                            <dd class="col-sm-8" id="brandNameView">-</dd>

                            <dt class="col-sm-4">Mô tả:</dt>
                            <dd class="col-sm-8" id="brandDescriptionView" style="white-space: pre-wrap; word-break: break-word;">-</dd>

                            <dt class="col-sm-4">Trạng thái:</dt>
                            <dd class="col-sm-8" id="brandStatusView">-</dd>

                            <dt class="col-sm-4">Ngày tạo:</dt>
                            <dd class="col-sm-8" id="brandCreatedAtView">-</dd>

                            <dt class="col-sm-4">Cập nhật lần cuối:</dt>
                            <dd class="col-sm-8" id="brandUpdatedAtView">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="editBrandFromViewButton">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>