{{-- resources/views/admin/productManagement/product/modals/modal_bulk_toggle_status_product.blade.php --}}
<div class="modal fade" id="bulkToggleStatusProductModal" tabindex="-1" aria-labelledby="bulkToggleStatusProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="bulkToggleStatusProductModalLabel">Chuyển trạng thái hàng loạt Sản phẩm</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkToggleStatusProductForm" action="{{ route('admin.productManagement.products.bulkToggleStatus') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn chuyển trạng thái cài đặt của <strong id="bulkToggleStatusCount">0</strong> sản phẩm đã chọn không?</p>
                    <div class="mb-3">
                        <label for="bulkStatusSelect" class="form-label">Chọn trạng thái:</label>
                        <select class="form-select" id="bulkStatusSelect" name="status" required>
                            <option value="active">Đang bán</option>
                            <option value="inactive">Ngừng bán</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info">Xác nhận chuyển trạng thái</button>
                </div>
            </form>
        </div>
    </div>
</div>