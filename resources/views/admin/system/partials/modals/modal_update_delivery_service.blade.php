<div class="modal fade" id="updateDeliveryServiceModal" tabindex="-1" aria-labelledby="updateDeliveryServiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateDeliveryServiceForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateDeliveryServiceModalLabel">Chỉnh sửa Đơn vị Giao hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dsNameUpdate" class="form-label">Tên đơn vị *</label>
                        <input type="text" class="form-control" id="dsNameUpdate" name="name" required>
                        <div id="dsNameUpdateError" class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="dsShippingFeeUpdate" class="form-label">Phí giao hàng (VNĐ) *</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="dsShippingFeeUpdate"
                            name="shipping_fee" required>
                        <div id="dsShippingFeeUpdateError" class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="dsLogoUpdate" class="form-label">Tải lên logo mới (nếu muốn thay đổi)</label>
                        <input type="file" class="form-control" id="dsLogoUpdate" name="logo_url" accept="image/*">
                        <div id="dsLogoUpdateError" class="invalid-feedback"></div>
                        <img id="dsLogoPreviewUpdate" src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current"
                            alt="Logo Preview" class="img-fluid mt-2 rounded"
                            style="max-height: 50px; object-fit: contain;"
                            data-default-src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current">
                        <input type="hidden" id="dsExistingLogoUrl" name="existing_logo_url" value="">
                    </div>
                    <div class="mb-3">
                        <label for="dsStatusUpdate" class="form-label">Trạng thái</label>
                        <select class="form-select" id="dsStatusUpdate" name="status">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tạm ẩn</option>
                        </select>
                        <div id="dsStatusUpdateError" class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>