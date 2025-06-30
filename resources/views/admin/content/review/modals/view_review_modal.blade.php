<div class="modal fade" id="viewReviewModal" tabindex="-1" role="dialog" aria-labelledby="viewReviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document"> {{-- Thêm modal-dialog-centered để căn giữa --}}
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"> {{-- Đổi màu header sang xanh cho view modal --}}
                <h5 class="modal-title" id="viewReviewModalLabel"><i class="bi bi-info-circle-fill me-2"></i>Chi tiết
                    Đánh giá</h5> {{-- Thêm icon --}}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button> {{-- Nút đóng màu trắng --}}
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-4 text-dark">ID Khách hàng:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8" id="viewReviewCustomerId"></dd> {{-- Thêm để hiển thị ID khách hàng --}}

                    <dt class="col-sm-4 text-dark">ID Sản phẩm:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8" id="viewReviewProductId"></dd> {{-- Thêm để hiển thị ID sản phẩm --}}

                    <dt class="col-sm-4 text-dark">Khách hàng:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8 text-primary" id="viewReviewCustomer"></dd> {{-- Thêm text-primary --}}

                    <dt class="col-sm-4 text-dark">Sản phẩm:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8"><a href="#" id="viewReviewProductLink" target="_blank"
                            class="text-primary fw-bold"></a></dd> {{-- Thêm text-primary fw-bold --}}

                    <dt class="col-sm-4 text-dark">Đánh giá:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8" id="viewReviewRating"></dd>

                    <dt class="col-sm-4 text-dark">Bình luận:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8" id="viewReviewComment"></dd>

                    <dt class="col-sm-4 text-dark">Trạng thái:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8" id="viewReviewStatus"></dd>

                    <dt class="col-sm-4 text-dark">Ngày tạo:</dt> {{-- Thêm text-dark --}}
                    <dd class="col-sm-8" id="viewReviewCreatedAt"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>