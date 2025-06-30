<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">
                    <i class="bi bi-receipt-cutoff me-2"></i>Chi Tiết Đơn Hàng: <strong
                        id="viewModalOrderIdStrong">N/A</strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Normal view interface --}}
                <div id="order-view-content">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-person-circle me-2"></i>Thông tin khách hàng</h6>
                            <hr class="mt-0">
                            <p class="mb-1"><strong>Mã đơn hàng:</strong> <span id="viewDetailOrderId"></span></p>
                            <p class="mb-1"><strong>Ngày tạo:</strong> <span id="viewDetailOrderCreatedAt"></span></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> <span id="viewDetailOrderStatusBadge"></span>
                            </p>
                            <p class="mb-1"><strong>Loại khách hàng:</strong> <span id="viewDetailCustomerType"></span>
                            </p>
                            {{-- Use guest_name, guest_phone, guest_email from Order model --}}
                            <p class="mb-1"><strong>Tên người nhận:</strong> <span id="viewDetailCustomerName"></span>
                            </p>
                            <p class="mb-1"><strong>Điện thoại:</strong> <span id="viewDetailCustomerPhone"></span></p>
                            <p class="mb-1"><strong>Email:</strong> <span id="viewDetailCustomerEmail"></span></p>
                            <p class="mb-1"><strong>Địa chỉ giao hàng:</strong> <span
                                    id="viewDetailOrderFullAddress"></span></p>
                        </div>

                        <div class="col-md-6">
                            <h6><i class="bi bi-box-seam me-2"></i>Thông tin đơn hàng</h6>
                            <hr class="mt-0">
                            <p class="mb-1"><strong>Phương thức thanh toán:</strong> <span
                                    id="viewDetailOrderPaymentMethod"></span></p>
                            <p class="mb-1"><strong>Dịch vụ vận chuyển:</strong> <span
                                    id="viewDetailOrderDeliveryService"></span></p>
                            <p class="mb-1"><strong>Mã khuyến mãi:</strong> <span
                                    id="viewDetailOrderPromotionCode"></span></p>
                            <p class="mb-1"><strong>Ghi chú:</strong> <span id="viewDetailOrderNotes"></span></p>
                            <p class="mb-1"><strong>Người tạo đơn:</strong> <span
                                    id="viewDetailOrderCreatedByAdmin">N/A</span></p>
                        </div>
                    </div>

                    <h6 class="mt-4"><i class="bi bi-card-list me-2"></i>Chi tiết sản phẩm</h6>
                    <hr class="mt-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="viewOrderItemsBody">
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Tạm tính:</td>
                                    <td class="text-end" id="viewOrderSubtotal">0 ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Phí vận chuyển:</td>
                                    <td class="text-end" id="viewOrderShippingFee">0 ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Giảm giá:</td>
                                    <td class="text-end text-danger" id="viewOrderDiscount">-0 ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fs-5">Tổng cộng:</td>
                                    <td class="text-end fs-5" id="viewOrderGrandTotal">0 ₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- INVOICE TEMPLATE FOR PRINTING (HIDDEN BY DEFAULT) --}}
                <div id="invoice-print-template" style="display: none;">
                    {{-- CSS and HTML for the printable invoice --}}
                    <div class="invoice-box">
                        <table>
                            <tr class="top">
                                <td colspan="3">
                                    <table>
                                        <tr>
                                            <td class="title">
                                                <img src="{{ asset('assets_admin/images/hoadon.png') }}"
                                                    alt="Company logo" class="invoice-company-logo"
                                                    style="max-width: 150px;" />
                                            </td>
                                            <td class="text-right">
                                                <strong>HÓA ĐƠN</strong><br>
                                                Mã: #<span id="print-invoice-id"></span><br>
                                                Ngày tạo: <span id="print-invoice-date"></span><br>
                                                Trạng thái: <span id="print-invoice-status"></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="information">
                                <td colspan="3">
                                    <table>
                                        <tr>
                                            <td>
                                                <strong>TỪ CỬA HÀNG:</strong><br>
                                                Thanhdo Shop<br>
                                                62 Châu Văn Liêm, Phú Đô, Nam Từ Liêm, Hà Nội<br>
                                                Email: thanhdoshop@gmail.com<br>
                                                SĐT: 0394831886 - 0973634129
                                            </td>
                                            <td class="text-right">
                                                <strong>GIAO ĐẾN:</strong><br>
                                                <strong id="print-customer-name"></strong><br>
                                                <span id="print-customer-phone"></span><br>
                                                <span id="print-customer-email"></span><br>
                                                <span id="print-customer-address"></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="heading">
                                <td style="width: 50%;">Sản phẩm</td>
                                <td style="width: 25%; text-align: center;">Số lượng</td>
                                <td style="width: 25%;" class="text-right">Giá</td>
                            </tr>
                            <tbody id="print-items-body">
                                {{-- Product rows will be inserted here by JS --}}
                            </tbody>
                            <tr class="heading">
                                <td colspan="3">Tổng kết</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-right">Tạm tính</td>
                                <td class="text-right" id="print-subtotal"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-right">Phí vận chuyển</td>
                                <td class="text-right" id="print-shipping"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-right">Giảm giá</td>
                                <td class="text-right" id="print-discount"></td>
                            </tr>
                            <tr class="total">
                                <td colspan="2" class="text-right"><strong>Tổng cộng</strong></td>
                                <td class="text-right"><strong><span id="print-grand-total"></span></strong></td>
                            </tr>
                        </table>
                        <div class="invoice-footer">
                            Cảm ơn quý khách đã tin tưởng và mua sắm tại Motorbike Shop!
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="printOrderBtn">
                    <i class="bi bi-printer me-1"></i> In hóa đơn
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="editOrderFromViewBtn">
                    <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>
