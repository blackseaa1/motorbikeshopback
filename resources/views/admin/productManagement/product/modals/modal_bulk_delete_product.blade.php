{{-- resources/views/admin/productManagement/product/modals/modal_bulk_delete_product.blade.php --}}
<div class="modal fade" id="modalBulkDeleteProduct" tabindex="-1" aria-labelledby="modalBulkDeleteProductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formBulkDeleteProduct" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalBulkDeleteProductLabel"><i class="bi bi-trash text-white me-2"></i>Xác nhận Xóa mềm hàng loạt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn chuyển <strong id="bulkDeleteProductCount">0</strong> sản phẩm đã chọn vào thùng rác?</p>
                    <small class="text-muted">Hành động này có thể khôi phục được.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>