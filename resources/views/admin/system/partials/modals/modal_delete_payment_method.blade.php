<div class="modal fade" id="deletePaymentMethodModal" tabindex="-1" aria-labelledby="deletePaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePaymentMethodModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deletePaymentMethodForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa phương thức thanh toán <strong id="pmNameToDelete" class="text-danger"></strong> không?</p>
                    <p class="text-warning"><small>Hành động này không thể được hoàn tác.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>