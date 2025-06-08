<div class="modal fade" id="createPromotionModal" tabindex="-1" aria-labelledby="createPromotionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPromotionModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Mã
                    Khuyến Mãi Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createPromotionForm" action="{{ route('admin.sales.promotions.store') }}" method="POST">
                    @csrf
                    {{-- Trường ẩn để xác định form khi có lỗi validation --}}
                    <input type="hidden" name="_form_identifier" value="create_promotion_form">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoCodeCreate" class="form-label">Mã Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="promoCodeCreate" name="code"
                                value="{{ old('code') }}" required style="text-transform: uppercase;">
                            @error('code', 'create_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoDiscountCreate" class="form-label">Phần trăm giảm giá (%) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="promoDiscountCreate"
                                name="discount_percentage" value="{{ old('discount_percentage') }}" required step="0.01"
                                min="0.01" max="100">
                            @error('discount_percentage', 'create_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="promoDescriptionCreate" class="form-label">Mô tả ngắn</label>
                        <textarea class="form-control" id="promoDescriptionCreate" name="description"
                            rows="2">{{ old('description') }}</textarea>
                        @error('description', 'create_promotion_form')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoStartDateCreate" class="form-label">Ngày bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoStartDateCreate"
                                name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date', 'create_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoEndDateCreate" class="form-label">Ngày kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoEndDateCreate" name="end_date"
                                value="{{ old('end_date') }}" required>
                            @error('end_date', 'create_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="promoMaxUsesCreate" class="form-label">Số lượt sử dụng tối đa</label>
                            <input type="number" class="form-control" id="promoMaxUsesCreate" name="max_uses"
                                value="{{ old('max_uses') }}" min="1" placeholder="Bỏ trống nếu không giới hạn">
                            @error('max_uses', 'create_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="promoStatusCreate" class="form-label">Trạng thái cài đặt <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="promoStatusCreate" name="status" required>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_ACTIVE }}" selected>Bật (Active)
                                </option>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_INACTIVE }}">Tắt (Inactive)
                                </option>
                            </select>
                            @error('status', 'create_promotion_form')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="createPromotionForm">Lưu và Tạo mới</button>
            </div>
        </div>
    </div>
</div>