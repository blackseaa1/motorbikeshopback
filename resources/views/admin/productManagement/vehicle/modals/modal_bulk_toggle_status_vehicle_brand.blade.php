{{-- Modal for Bulk Toggle Status Vehicle Brands --}}
<div class="modal fade" id="bulkToggleStatusVehicleBrandModal" tabindex="-1" aria-labelledby="bulkToggleStatusVehicleBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkToggleStatusBrandForm" action="{{ route('admin.productManagement.vehicleBrands.bulkToggleStatus') }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkToggleStatusVehicleBrandModalLabel"><i class="bi bi-arrow-repeat me-2"></i>Chuyển trạng thái hàng loạt Hãng xe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn muốn chuyển trạng thái của <strong id="selectedBrandCountToggleModal">0</strong> hãng xe đã chọn thành gì?</p>
                    <div class="mb-3">
                        <label for="bulkBrandStatusSelect" class="form-label">Chọn trạng thái:</label>
                        <select class="form-select" id="bulkBrandStatusSelect" name="status" required>
                            <option value="{{ \App\Models\VehicleBrand::STATUS_ACTIVE }}">Hoạt động (Hiển thị)</option>
                            <option value="{{ \App\Models\VehicleBrand::STATUS_INACTIVE }}">Ẩn (Không hiển thị)</option>
                        </select>
                        <div class="invalid-feedback"></div> {{-- For AJAX validation errors --}}
                    </div>
                     {{-- Div để JS hiển thị lỗi validation cho mật khẩu --}}
                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="bulkToggleStatusBrandPassword" class="form-label">Mật khẩu xác nhận <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="bulkToggleStatusBrandPassword" name="deletion_password" required>
                            <div class="invalid-feedback" id="bulkToggleStatusBrandPasswordError"></div>
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