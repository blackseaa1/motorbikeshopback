<div class="modal fade" id="updatePromotionModal" tabindex="-1" aria-labelledby="updatePromotionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePromotionModalLabel"><i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa
                    Mã Khuyến Mãi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Form sẽ được set action động bằng JS --}}
                <form id="updatePromotionForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoCodeUpdate" class="form-label">Mã Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="promoCodeUpdate" name="code" required
                                style="text-transform: uppercase;">
                            @error('code', 'update_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoDiscountUpdate" class="form-label">Phần trăm giảm giá (%) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="promoDiscountUpdate"
                                name="discount_percentage" required step="0.01" min="0.01" max="100">
                            @error('discount_percentage', 'update_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="promoDescriptionUpdate" class="form-label">Mô tả ngắn</label>
                        <textarea class="form-control" id="promoDescriptionUpdate" name="description"
                            rows="2"></textarea>
                        @error('description', 'update_promotion_form')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoStartDateUpdate" class="form-label">Ngày bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoStartDateUpdate"
                                name="start_date" required>
                            @error('start_date', 'update_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoEndDateUpdate" class="form-label">Ngày kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoEndDateUpdate" name="end_date"
                                required>
                            @error('end_date', 'update_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoMaxUsesUpdate" class="form-label">Số lượt sử dụng tối đa</label>
                            <input type="number" class="form-control" id="promoMaxUsesUpdate" name="max_uses" min="1"
                                placeholder="Bỏ trống nếu không giới hạn">
                            @error('max_uses', 'update_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoStatusUpdate" class="form-label">Trạng thái cài đặt <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="promoStatusUpdate" name="status" required>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_ACTIVE }}">Bật (Active)</option>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_INACTIVE }}">Tắt (Inactive)
                                </option>
                            </select>
                            @error('status', 'update_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="updatePromotionForm">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>