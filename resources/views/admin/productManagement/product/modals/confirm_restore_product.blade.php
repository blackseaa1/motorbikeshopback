{{-- File: resources/views/admin/productManagement/product/modals/confirm_restore_product.blade.php --}}
<div class="modal fade" id="confirmRestoreModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="restoreProductForm" method="POST">
            @csrf
            @method('POST') {{-- Phương thức POST cho restore --}}
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Xác nhận Khôi phục</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn khôi phục sản phẩm <strong id="productNameToRestore"></strong>?</p>
                    <small class="text-muted">Sản phẩm sẽ được đưa trở lại danh sách đang bán.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận Khôi phục</button>
                </div>
            </div>
        </form>
    </div>
</div>
