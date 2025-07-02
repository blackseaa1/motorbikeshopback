{{-- Modal for Bulk Toggle Status Products --}}
<div class="modal fade" id="bulkToggleStatusProductsModal" tabindex="-1" aria-labelledby="bulkToggleStatusProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkToggleStatusProductsForm" action="{{ route('admin.productManagement.products.bulkToggleStatus') }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkToggleStatusProductsModalLabel"><i class="bi bi-arrow-repeat me-2"></i>Chuyển trạng thái hàng loạt sản phẩm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn muốn chuyển trạng thái của <strong id="selectedProductsCountToggleModal">0</strong> sản phẩm đã chọn thành gì?</p>
                    <div class="mb-3">
                        <label for="bulkStatusSelectProducts" class="form-label">Chọn trạng thái:</label>
                        <select class="form-select" id="bulkStatusSelectProducts" name="status" required>
                            <option value="{{ \App\Models\Product::STATUS_ACTIVE }}">Đang bán (Hiển thị)</option>
                            <option value="{{ \App\Models\Product::STATUS_INACTIVE }}">Ngừng bán (Không hiển thị)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="bulkToggleStatusProductsPassword" class="form-label">Mật khẩu xác nhận <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="bulkToggleStatusProductsPassword" name="admin_password_confirm_delete" required>
                            <div class="invalid-feedback" id="bulkToggleStatusProductsPasswordError"></div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>