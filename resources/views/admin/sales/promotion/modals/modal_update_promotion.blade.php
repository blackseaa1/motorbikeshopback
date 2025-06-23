{{-- resources/views/admin/sales/promotion/modals/modal_update_promotion.blade.php --}}
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
                <form id="updatePromotionForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoCodeUpdate" class="form-label">Mã Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="promoCodeUpdate" name="code" required
                                style="text-transform: uppercase;">
                            <div id="promoCodeUpdateError" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoDiscountTypeUpdate" class="form-label">Loại giảm giá <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="promoDiscountTypeUpdate" name="discount_type" required>
                                <option value="{{ \App\Models\Promotion::DISCOUNT_TYPE_PERCENTAGE }}">Phần trăm (%)
                                </option>
                                <option value="{{ \App\Models\Promotion::DISCOUNT_TYPE_FIXED }}">Số tiền cố định (VNĐ)
                                </option>
                            </select>
                            <div id="promoDiscountTypeUpdateError" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="promoDiscountPercentageGroupUpdate">
                            <label for="promoDiscountUpdate" class="form-label">Phần trăm giảm giá (%) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="promoDiscountUpdate"
                                name="discount_percentage" step="0.01" min="0.01" max="100">
                            <div id="promoDiscount_percentageUpdateError" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3" id="promoFixedDiscountAmountGroupUpdate" style="display: none;">
                            <label for="promoFixedDiscountAmountUpdate" class="form-label">Số tiền giảm giá (VNĐ) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="promoFixedDiscountAmountUpdate"
                                name="fixed_discount_amount" min="1000">
                            <div id="promoFixedDiscountAmountUpdateError" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3" id="promoMaxDiscountAmountGroupUpdate">
                            <label for="promoMaxDiscountAmountUpdate" class="form-label">Giảm tối đa (VNĐ)</label>
                            <input type="text" class="form-control" id="promoMaxDiscountAmountUpdate"
                                name="max_discount_amount" min="0">
                            <div id="promoMax_discount_amountUpdateError" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="promoDescriptionUpdate" class="form-label">Mô tả ngắn</label>
                        <textarea class="form-control" id="promoDescriptionUpdate" name="description"
                            rows="2"></textarea>
                        <div id="promoDescriptionUpdateError" class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoStartDateUpdate" class="form-label">Ngày bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoStartDateUpdate"
                                name="start_date" required>
                            <div id="promoStart_dateUpdateError" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoEndDateUpdate" class="form-label">Ngày kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoEndDateUpdate" name="end_date"
                                required>
                            <div id="promoEnd_dateUpdateError" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoMaxUsesUpdate" class="form-label">Số lượt sử dụng tối đa</label>
                            <input type="text" class="form-control" id="promoMaxUsesUpdate" name="max_uses" min="1">
                            <div id="promoMax_usesUpdateError" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoMinOrderAmountUpdate" class="form-label">Giá trị đơn hàng tối thiểu
                                (VNĐ)</label>
                            <input type="text" class="form-control" id="promoMinOrderAmountUpdate"
                                name="min_order_amount" min="0">
                            <div id="promoMin_order_amountUpdateError" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoStatusUpdate" class="form-label">Trạng thái cài đặt <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="promoStatusUpdate" name="status" required>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_ACTIVE }}">Bật (Active)</option>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_INACTIVE }}">Tắt (Inactive)
                                </option>
                            </select>
                            <div id="promoStatusUpdateError" class="invalid-feedback"></div>
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