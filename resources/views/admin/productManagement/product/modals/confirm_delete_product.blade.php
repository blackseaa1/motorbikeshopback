{{-- File: resources/views/admin/productManagement/product/modals/confirm_delete_product.blade.php --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="deleteProductForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash text-danger me-2"></i>Xác nhận Xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn chuyển sản phẩm <strong id="productNameToDelete"></strong> vào thùng rác?</p>
                    <small class="text-muted">Hành động này có thể khôi phục được.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
