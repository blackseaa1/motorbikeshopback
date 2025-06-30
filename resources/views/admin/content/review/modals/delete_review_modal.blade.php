<div class="modal fade" id="deleteReviewModal" tabindex="-1" role="dialog" aria-labelledby="deleteReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReviewModalLabel">Xác nhận Xóa Đánh giá</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa đánh giá của khách hàng
                "<strong id="customerNameForDelete"></strong>"
                cho sản phẩm "<strong id="productNameForDelete"></strong>" vĩnh viễn không?
                Thao tác này không thể hoàn tác.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteReviewForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    {{-- Các input ẩn này sẽ được điền bởi JavaScript (review_manager.js) --}}
                    <input type="hidden" name="customer_id" id="deleteCustomerId">
                    <input type="hidden" name="product_id" id="deleteProductId">
                    <button type="submit" class="btn btn-danger">Xóa Vĩnh Viễn</button>
                </form>
            </div>
        </div>
    </div>
</div>
