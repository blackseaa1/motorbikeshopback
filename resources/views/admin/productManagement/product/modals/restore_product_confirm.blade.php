{{-- File: resources/views/admin/productManagement/product/modals/restore_product_confirm.blade.php --}}
<div class="modal fade" id="bulkRestoreProductsModal" tabindex="-1" aria-labelledby="bulkRestoreProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkRestoreProductsForm" method="POST"> {{-- Action will be set by JS --}}
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="bulkRestoreProductsModalLabel"><i class="bi bi-arrow-counterclockwise me-2"></i>Xác nhận Khôi phục Sản phẩm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn khôi phục <strong id="productNameToRestore"></strong>?</p>
                    <small class="text-muted">Sản phẩm sẽ được đưa trở lại danh sách đang bán.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận Khôi phục</button>
                </div>
            </form>
        </div>
    </div>
</div>