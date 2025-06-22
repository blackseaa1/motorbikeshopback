{{-- resources/views/admin/sales/order/modals/view_order_modal.blade.php --}}
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
                {{-- Giao diện xem chi tiết thông thường --}}
                <div id="order-view-content">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-person-circle me-2"></i>Thông tin khách hàng</h6>
                            <hr class="mt-0">
                            <p class="mb-1"><strong>Mã đơn hàng:</strong> <span id="viewDetailOrderId"></span></p>
                            <p class="mb-1"><strong>Ngày tạo:</strong> <span id="viewDetailOrderCreatedAt"></span></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> <span id="viewDetailOrderStatusBadge"></span>
                            </p>
                            <p class="mb-1"><strong>Loại khách hàng:</strong> <span
                                    id="viewDetailOrderCustomerType"></span>
                            </p>
                            <p class="mb-1"><strong>Tên khách hàng:</strong> <span
                                    id="viewDetailOrderCustomerName"></span></p>
                            <p class="mb-1"><strong>Email:</strong> <span id="viewDetailOrderCustomerEmail"></span></p>
                            <p class="mb-1"><strong>Số điện thoại:</strong> <span
                                    id="viewDetailOrderCustomerPhone"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-truck me-2"></i>Thông tin giao hàng</h6>
                            <hr class="mt-0">
                            <p class="mb-1"><strong>Địa chỉ giao hàng:</strong> <span
                                    id="viewDetailOrderShippingAddress"></span></p>
                            <p class="mb-1"><strong>Phương thức thanh toán:</strong> <span
                                    id="viewDetailOrderPaymentMethod"></span></p>
                            <p class="mb-1"><strong>Dịch vụ vận chuyển:</strong> <span
                                    id="viewDetailOrderDeliveryService"></span></p>
                            <p class="mb-1"><strong>Ghi chú:</strong> <span id="viewDetailOrderNotes"></span></p>
                            <p class="mb-1"><strong>Người tạo:</strong> <span id="viewDetailOrderCreatedBy"></span></p>
                        </div>
                    </div>

                    <h6 class="mt-4"><i class="bi bi-box-seam me-2"></i>Sản phẩm trong đơn hàng</h6>
                    <hr class="mt-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="viewDetailOrderItems">
                                {{-- Order items will be dynamically loaded here --}}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">Tổng phụ:</td>
                                    <td class="text-end" id="viewDetailOrderSubtotal"></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Phí vận chuyển:</td>
                                    <td class="text-end" id="viewDetailOrderShippingFee"></td>
                                </tr>
                                <tr id="view-detail-discount-row" class="d-none">
                                    <td colspan="3" class="text-end">Giảm giá: <span
                                            id="viewDetailOrderPromotionCode"></span></td>
                                    <td class="text-end text-danger" id="viewDetailOrderDiscount"></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td class="text-end"><strong><span id="viewDetailOrderGrandTotal"></span></strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- MẪU HÓA ĐƠN ĐỂ IN (ẨN THEO MẶC ĐỊNH, CHỈ HIỆN KHI IN) --}}
                <div id="invoice-print-area" class="d-none" style="font-family: 'Times New Roman', Times, serif;">
                    <div class="invoice-box">
                        <table cellpadding="0" cellspacing="0"
                            style="width: 100%; line-height: inherit; text-align: left; border-collapse: collapse;">
                            <tr class="top">
                                <td colspan="4">
                                    <table
                                        style="width: 100%; line-height: inherit; text-align: left; border-collapse: collapse;">
                                        <tr>
                                            <td class="title"
                                                style="padding-bottom: 20px; font-size: 45px; line-height: 45px; color: #333;">
                                                {{-- <img src="{{ asset('path/to/your/logo.png') }}"
                                                    style="width:100%; max-width:150px; display: block; margin-bottom: 10px;"
                                                    alt="Company Logo"> --}}
                                                MOTORBIKE SHOP
                                            </td>
                                            <td style="text-align: right; vertical-align: top;">
                                                Hóa đơn #<span id="print-order-id"></span><br>
                                                Ngày tạo: <span id="print-created-at"></span><br>
                                                Trạng thái: <span id="print-status"></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="information">
                                <td colspan="4">
                                    <table
                                        style="width: 100%; line-height: inherit; text-align: left; border-collapse: collapse;">
                                        <tr>
                                            <td style="padding-bottom: 20px; vertical-align: top; width: 50%;">
                                                <strong>Thông tin khách hàng:</strong><br>
                                                Tên: <span id="print-customer-name"></span><br>
                                                Email: <span id="print-customer-email"></span><br>
                                                SĐT: <span id="print-customer-phone"></span>
                                            </td>
                                            <td
                                                style="padding-bottom: 20px; text-align: right; vertical-align: top; width: 50%;">
                                                <strong>Địa chỉ giao hàng:</strong><br>
                                                <span id="print-shipping-address-full"></span><br>
                                                Phương thức TT: <span id="print-payment-method"></span><br>
                                                Dịch vụ V/C: <span id="print-delivery-service"></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="heading">
                                <td
                                    style="background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; padding: 8px;">
                                    Sản phẩm</td>
                                <td
                                    style="background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; text-align: center; padding: 8px;">
                                    SL</td>
                                <td
                                    style="background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; text-align: right; padding: 8px;">
                                    Đơn giá</td>
                                <td
                                    style="background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; text-align: right; padding: 8px;">
                                    Thành tiền</td>
                            </tr>
                            <tbody id="print-order-items">
                                {{-- Print items will be dynamically loaded here --}}
                            </tbody>
                            <tr class="total">
                                <td colspan="3" class="text-right"
                                    style="border-top: 2px solid #eee; font-weight: bold; padding: 8px;">Tổng phụ</td>
                                <td class="text-right" id="print-subtotal"
                                    style="border-top: 2px solid #eee; font-weight: bold; padding: 8px;"></td>
                            </tr>
                            <tr class="total">
                                <td colspan="3" class="text-right" style="font-weight: bold; padding: 8px;">Phí vận
                                    chuyển</td>
                                <td class="text-right" id="print-shipping" style="font-weight: bold; padding: 8px;">
                                </td>
                            </tr>
                            <tr class="total" id="print-discount-row" class="d-none">
                                <td colspan="3" class="text-right" style="font-weight: bold; padding: 8px;">Giảm giá
                                    (<span id="print-promotion-code"></span>)</td>
                                <td class="text-right" id="print-discount" style="font-weight: bold; padding: 8px;">
                                </td>
                            </tr>
                            <tr class="total" style="background: #eee;">
                                <td colspan="3" class="text-right" style="font-weight: bold; padding: 8px;"><strong>Tổng
                                        cộng</strong></td>
                                <td class="text-right" style="font-weight: bold; padding: 8px;"><strong><span
                                            id="print-grand-total"></span></strong></td>
                            </tr>
                        </table>
                        <div class="invoice-footer"
                            style="text-align: center; margin-top: 20px; font-size: 14px; color: #555;">
                            Cảm ơn quý khách đã tin tưởng và mua sắm tại Motorbike Shop!<br>
                            Ghi chú: <span id="print-notes"></span>
                        </div>
                    </div>
                </div>
                {{-- KẾT THÚC MẪU HÓA ĐƠN --}}
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