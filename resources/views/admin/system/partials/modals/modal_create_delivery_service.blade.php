<div class="modal fade" id="createDeliveryServiceModal" tabindex="-1" aria-labelledby="createDeliveryServiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createDeliveryServiceForm" action="{{ route('admin.system.deliveryServices.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createDeliveryServiceModalLabel">Thêm Đơn vị Giao hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dsNameCreate" class="form-label">Tên đơn vị *</label>
                        <input type="text" class="form-control" id="dsNameCreate" name="name" required>
                        <div class="invalid-feedback" id="dsNameCreateError"></div>
                    </div>
                    {{-- Trường Phí giao hàng đã được loại bỏ --}}
                    <div class="mb-3">
                        <label for="dsLogoCreate" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="dsLogoCreate" name="logo_url" accept="image/*">
                        <div class="invalid-feedback" id="dsLogoUrlCreateError"></div>
                        <img id="dsLogoPreviewCreate" src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Preview"
                            class="img-fluid mt-2" style="max-width: 150px;"
                            data-default-src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Preview">
                    </div>
                    <div class="mb-3">
                        <label for="dsStatusCreate" class="form-label">Trạng thái</label>
                        <select class="form-select" id="dsStatusCreate" name="status" required>
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tạm ẩn</option>
                        </select>
                        <div class="invalid-feedback" id="dsStatusCreateError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>