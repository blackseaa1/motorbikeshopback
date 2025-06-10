{{-- resources/views/admin/sales/promotion/modals/modal_create_promotion.blade.php --}}
<div class="modal fade" id="createPromotionModal" tabindex="-1" aria-labelledby="createPromotionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPromotionModalLabel">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tạo Mã Khuyến Mãi Mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Thêm 'novalidate' để tắt validation mặc định của trình duyệt, ưu tiên xử lý lỗi từ server --}}
                <form id="createPromotionForm" action="{{ route('admin.sales.promotions.store') }}" method="POST"
                    novalidate>
                    @csrf
                    <div class="row">
                        {{-- Mã Code --}}
                        <div class="col-md-6 mb-3">
                            <label for="promoCodeCreate" class="form-label">Mã Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="promoCodeCreate" name="code" required
                                style="text-transform: uppercase;" placeholder="VD: TET2025">
                            {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'code' --}}
                            <div class="invalid-feedback"></div>
                        </div>

                        {{-- Phần trăm giảm giá --}}
                        <div class="col-md-6 mb-3">
                            <label for="promoDiscountCreate" class="form-label">Phần trăm giảm giá (%) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="promoDiscountCreate"
                                name="discount_percentage" required step="0.01" min="0.01" max="100"
                                placeholder="VD: 15.5">
                            {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'discount_percentage' --}}
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- Mô tả --}}
                    <div class="mb-3">
                        <label for="promoDescriptionCreate" class="form-label">Mô tả ngắn</label>
                        <textarea class="form-control" id="promoDescriptionCreate" name="description" rows="2"
                            placeholder="VD: Giảm giá mừng Tết Nguyên Đán"></textarea>
                        {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'description' --}}
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        {{-- Ngày bắt đầu --}}
                        <div class="col-md-6 mb-3">
                            <label for="promoStartDateCreate" class="form-label">Ngày bắt đầu <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoStartDateCreate"
                                name="start_date" required>
                            {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'start_date' --}}
                            <div class="invalid-feedback"></div>
                        </div>

                        {{-- Ngày kết thúc --}}
                        <div class="col-md-6 mb-3">
                            <label for="promoEndDateCreate" class="form-label">Ngày kết thúc <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="promoEndDateCreate" name="end_date"
                                required>
                            {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'end_date' --}}
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Số lượt sử dụng --}}
                        <div class="col-md-6 mb-3">
                            <label for="promoMaxUsesCreate" class="form-label">Số lượt sử dụng tối đa</label>
                            <input type="number" class="form-control" id="promoMaxUsesCreate" name="max_uses" min="1"
                                placeholder="Bỏ trống nếu không giới hạn">
                            {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'max_uses' --}}
                            <div class="invalid-feedback"></div>
                        </div>

                        {{-- Trạng thái cài đặt --}}
                        <div class="col-md-6 mb-3">
                            <label for="promoStatusCreate" class="form-label">Trạng thái cài đặt <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="promoStatusCreate" name="status" required>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_ACTIVE }}" selected>Bật (Active)
                                </option>
                                <option value="{{ \App\Models\Promotion::STATUS_MANUAL_INACTIVE }}">Tắt (Inactive)
                                </option>
                            </select>
                            {{-- Div này là nơi JS sẽ hiển thị lỗi validation cho trường 'status' --}}
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                {{-- Nút submit nằm ngoài form nhưng được liên kết qua thuộc tính 'form' --}}
                <button type="submit" class="btn btn-primary" form="createPromotionForm">
                    <i class="bi bi-floppy-fill me-1"></i> Lưu và Tạo mới
                </button>
            </div>
        </div>
    </div>
</div>