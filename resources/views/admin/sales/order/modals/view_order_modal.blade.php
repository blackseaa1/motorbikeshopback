<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">
                    <i class="bi bi-eye-fill me-2"></i>Chi tiết Đơn hàng: <strong id="viewModalOrderIdStrong">N/A</strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Thông tin chung:</strong></h6>
                        <table class="table table-bordered table-sm mb-3">
                            <tbody>
                                <tr><th>Mã Đơn hàng</th><td id="viewDetailOrderId"></td></tr>
                                <tr><th>Ngày đặt</th><td id="viewDetailOrderCreatedAt"></td></tr>
                                <tr><th>Trạng thái</th><td id="viewDetailOrderStatusBadge"></td></tr>
                                <tr><th>Tổng cộng</th><td id="viewDetailOrderTotalPrice" class="text-danger fw-bold"></td></tr>
                                <tr><th>Phương thức TT</th><td id="viewDetailOrderPaymentMethod"></td></tr>
                                <tr><th>Dịch vụ VC</th><td id="viewDetailOrderDeliveryService"></td></tr>
                                <tr><th>Ghi chú</th><td id="viewDetailOrderNotes"></td></tr>
                                <tr><th>Tạo bởi Admin</th><td id="viewDetailOrderCreatedByAdmin"></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Thông tin khách hàng:</strong></h6>
                        <table class="table table-bordered table-sm mb-3">
                            <tbody>
                                <tr><th>Loại khách hàng</th><td id="viewDetailCustomerType"></td></tr>
                                <tr><th>Tên khách hàng</th><td id="viewDetailCustomerName"></td></tr>
                                <tr><th>Email</th><td id="viewDetailCustomerEmail"></td></tr>
                                <tr><th>Số điện thoại</th><td id="viewDetailCustomerPhone"></td></tr>
                                <tr><th>Địa chỉ</th><td id="viewDetailOrderFullAddress"></td></tr>
                                {{-- Nếu bạn có cột `address_line` trong DB Order, có thể thêm vào đây --}}
                                {{-- <tr><th>Địa chỉ chi tiết</th><td id="viewDetailOrderAddressLine"></td></tr> --}}
                                @if(config('admin.show_promotion_info')) {{-- Chỉ hiện nếu bạn muốn --}}
                                    <tr><th>Mã KM đã dùng</th><td id="viewDetailOrderPromotionCode"></td></tr>
                                    <tr><th>Giảm giá</th><td id="viewDetailOrderDiscountAmount"></td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <h6><strong>Sản phẩm trong đơn hàng:</strong></h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá mua</th>
                                <th>Tổng</th>
                            </tr>
                        </thead>
                        <tbody id="viewOrderItemsBody">
                            {{-- Dữ liệu sẽ được điền bởi JS --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Tạm tính:</th>
                                <td id="viewOrderSubtotal"></td>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Phí vận chuyển:</th>
                                <td id="viewOrderShippingFee"></td>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Giảm giá:</th>
                                <td id="viewOrderDiscount"></td>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Tổng cộng:</th>
                                <td id="viewOrderGrandTotal" class="fw-bold"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary me-auto" id="editOrderFromViewBtn">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>