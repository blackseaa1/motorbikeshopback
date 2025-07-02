{{-- resources/views/admin/productManagement/product/modals/modal_bulk_restore_product.blade.php --}}
<div class="modal fade" id="bulkRestoreProductModal" tabindex="-1" aria-labelledby="modalBulkRestoreProductLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- SỬA ĐỔI ID FORM Ở ĐÂY --}}
            <form id="bulkRestoreProductForm" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalBulkRestoreProductLabel"><i
                            class="bi bi-arrow-counterclockwise me-2"></i>Xác nhận Khôi phục hàng loạt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- ĐỔI TÊN ID CHO SPAN NÀY ĐỂ ĐỒNG BỘ --}}
                    <p>Bạn có chắc chắn muốn khôi phục <strong id="bulkRestoreCount">0</strong> sản phẩm đã chọn?</p>
                    <small class="text-muted">Các sản phẩm sẽ được đưa trở lại danh sách đang bán.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận Khôi phục</button>
                </div>
            </form>
        </div>
    </div>
</div>