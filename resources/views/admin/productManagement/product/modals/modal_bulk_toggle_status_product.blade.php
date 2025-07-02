{{-- resources/views/admin/productManagement/product/modals/modal_bulk_toggle_status_product.blade.php --}}
<div class="modal fade" id="modalBulkToggleStatusProduct" tabindex="-1" aria-labelledby="modalBulkToggleStatusProductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formBulkToggleStatusProduct" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalBulkToggleStatusProductLabel"><i class="bi bi-arrow-repeat me-2"></i>Chuyển trạng thái hàng loạt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn muốn chuyển trạng thái của <strong id="bulkToggleStatusProductCount">0</strong> sản phẩm đã chọn thành gì?</p>
                    <div class="mb-3">
                        <label for="bulkToggleStatusProductSelect" class="form-label">Chọn trạng thái:</label>
                        <select class="form-select" id="bulkToggleStatusProductSelect" name="status" required>
                            <option value="active">Đang bán</option>
                            <option value="inactive">Ngừng bán</option>
                        </select>
                        <div class="invalid-feedback"></div> {{-- For AJAX validation errors --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>