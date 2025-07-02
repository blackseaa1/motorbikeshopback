{{-- File: resources/views/admin/productManagement/product/modals/modal_bulk_delete_product.blade.php --}}
<div class="modal fade" id="bulkDeleteProductModal" tabindex="-1" aria-labelledby="bulkDeleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDeleteProductModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận Xóa Hàng Loạt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkDeleteProductForm" action="{{ route('admin.productManagement.products.bulkDestroy') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn chuyển <strong id="bulkDeleteCount">0</strong> sản phẩm đã chọn vào thùng rác không?</p>
                    <p class="text-danger">Lưu ý: Các sản phẩm đã tồn tại trong đơn hàng sẽ không thể bị xóa.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>
