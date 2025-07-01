{{-- resources/views/admin/sales/promotion/modals/modal_bulk_toggle_status.blade.php --}}
<div class="modal fade" id="bulkToggleStatusModal" tabindex="-1" aria-labelledby="bulkToggleStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="bulkToggleStatusModalLabel">Chuyển trạng thái hàng loạt Mã Khuyến Mãi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkToggleStatusForm" action="{{ route('admin.sales.promotions.bulkToggleStatus') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn chuyển trạng thái cài đặt của <strong id="bulkToggleStatusCount">0</strong> mã khuyến mãi đã chọn không?</p>
                    <div class="mb-3">
                        <label for="bulkStatusSelect" class="form-label">Chọn trạng thái:</label>
                        <select class="form-select" id="bulkStatusSelect" name="status" required>
                            <option value="active">Kích hoạt</option>
                            <option value="inactive">Vô hiệu hóa</option>
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