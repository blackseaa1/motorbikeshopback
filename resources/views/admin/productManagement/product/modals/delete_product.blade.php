<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteProductForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteProductModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận
                        Xóa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa sản phẩm <strong id="productNameToDelete"></strong>?
                    <p class="text-danger mt-2">Hành động này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>