{{-- resources/views/admin/sales/promotion/modals/modal_view_promotion.blade.php --}}
<div class="modal fade" id="viewPromotionModal" tabindex="-1" aria-labelledby="viewPromotionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPromotionModalLabel"><i class="bi bi-eye-fill me-2"></i>Chi tiết Mã
                    Khuyến mãi: <strong id="viewModalPromoCodeStrong">N/A</strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 30%;">Mã Code</th>
                            <td id="viewDetailPromoCode"></td>
                        </tr>
                        <tr>
                            <th>Mô tả</th>
                            <td id="viewDetailPromoDescription"></td>
                        </tr>
                        <tr>
                            <th>Loại giảm giá</th> {{-- Thêm hàng mới --}}
                            <td id="viewDetailPromoDiscountType"></td>
                        </tr>
                        <tr>
                            <th>Giá trị giảm giá</th> {{-- Thay đổi tiêu đề --}}
                            <td id="viewDetailPromoDiscount" class="text-danger fw-bold"></td>
                        </tr>
                        <tr>
                            <th>Số tiền giảm tối đa</th> {{-- Thêm hàng mới --}}
                            <td id="viewDetailPromoMaxDiscountAmount"></td>
                        </tr>
                        <tr>
                            <th>Ngày giờ bắt đầu</th>
                            <td id="viewDetailPromoStartDate"></td>
                        </tr>
                        <tr>
                            <th>Ngày giờ kết thúc</th>
                            <td id="viewDetailPromoEndDate"></td>
                        </tr>
                        <tr>
                            <th>Số lượt sử dụng tối đa</th>
                            <td id="viewDetailPromoMaxUses"></td>
                        </tr>
                        <tr>
                            <th>Số lượt đã sử dụng</th>
                            <td id="viewDetailPromoUsesCount"></td>
                        </tr>
                        <tr>
                            <th>Giá trị đơn hàng tối thiểu</th> {{-- Thêm hàng mới --}}
                            <td id="viewDetailPromoMinOrderAmount"></td>
                        </tr>
                        <tr>
                            <th>Trạng thái Cài đặt</th>
                            <td id="viewDetailPromoStatusConfigText"></td>
                        </tr>
                        <tr>
                            <th>Trạng thái Hiện tại (Hiệu lực)</th>
                            <td id="viewDetailPromoStatusDisplayBadge"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary me-auto" id="editFromViewBtn">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>