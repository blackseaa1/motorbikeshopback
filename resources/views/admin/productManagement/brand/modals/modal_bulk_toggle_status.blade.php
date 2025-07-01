{{-- Modal for Bulk Toggle Status Brands --}}
<div class="modal fade" id="bulkToggleStatusModal" tabindex="-1" aria-labelledby="bulkToggleStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkToggleStatusForm" action="{{ route('admin.productManagement.brands.bulkToggleStatus') }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkToggleStatusModalLabel"><i class="bi bi-arrow-repeat me-2"></i>Chuyển trạng thái hàng loạt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn muốn chuyển trạng thái của <strong id="selectedCountToggleModal">0</strong> thương hiệu đã chọn thành gì?</p>
                    <div class="mb-3">
                        <label for="bulkStatusSelect" class="form-label">Chọn trạng thái:</label>
                        <select class="form-select" id="bulkStatusSelect" name="status" required>
                            <option value="{{ \App\Models\Brand::STATUS_ACTIVE }}">Hoạt động (Hiển thị)</option>
                            <option value="{{ \App\Models\Brand::STATUS_INACTIVE }}">Ẩn (Không hiển thị)</option>
                        </select>
                        <div class="invalid-feedback"></div> {{-- For AJAX validation errors --}}
                    </div>
                     {{-- Div để JS hiển thị lỗi validation cho mật khẩu --}}
                    @if(Config::get('admin.deletion_password'))
                        <div class="mb-3">
                            <label for="bulkToggleStatusPassword" class="form-label">Mật khẩu xác nhận <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="bulkToggleStatusPassword" name="deletion_password" required>
                            <div class="invalid-feedback" id="bulkToggleStatusPasswordError"></div>
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