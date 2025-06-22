{{-- resources/views/admin/sales/order/modals/update_order_modal.blade.php --}}
<div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="updateOrderModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập Nhật Đơn
                    Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateOrderForm" method="POST">
                    @csrf
                    @method('PUT') {{-- Sử dụng PUT method cho cập nhật --}}
                    <input type="hidden" id="update_order_id" name="id">

                    <div class="mb-3">
                        <label for="update_customer_info" class="form-label">Thông tin khách hàng</label>
                        <p id="update_customer_info" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label for="update_shipping_address" class="form-label">Địa chỉ giao hàng</label>
                        <p id="update_shipping_address" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label for="update_order_status" class="form-label">Trạng Thái Đơn Hàng <span
                                class="text-danger">*</span></label>
                        <select class="form-select selectpicker" id="update_order_status" name="status"
                            title="Chọn trạng thái...">
                            {{-- SỬA ĐỔI: Sử dụng orderStatuses để hiển thị tất cả các trạng thái có thể cập nhật --}}
                            @foreach($orderStatuses as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="status"></div>
                    </div>

                    <div class="mb-3">
                        <label for="update_delivery_service_id" class="form-label">Dịch Vụ Vận Chuyển <span
                                class="text-danger">*</span></label>
                        <select class="form-select selectpicker" id="update_delivery_service_id"
                            name="delivery_service_id" title="Chọn dịch vụ...">
                            @foreach($deliveryServices as $service)
                                <option value="{{ $service->id }}" data-shipping-fee="{{ $service->shipping_fee }}">
                                    {{ $service->name }} ({{ number_format($service->shipping_fee) }} ₫)
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="delivery_service_id"></div>
                    </div>

                    <div class="mb-3">
                        <label for="update_notes" class="form-label">Ghi Chú</label>
                        <textarea class="form-control" id="update_notes" name="notes" rows="3"></textarea>
                        <div class="text-danger mt-1" data-field="notes"></div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Chi tiết tài chính</h5>
                    <div class="order-summary-details">
                        <p class="mb-1"><span class="strong-label">Tổng phụ:</span> <span
                                id="update-order-subtotal"></span></p>
                        <p class="mb-1"><span class="strong-label">Phí vận chuyển:</span> <span
                                id="update-order-shipping-fee"></span></p>
                        <p class="mb-1 text-success"><span class="strong-label">Giảm giá:</span> <span
                                id="update-order-discount"></span></p>
                        <h4 class="mt-2"><span class="strong-label">Tổng cộng:</span> <span
                                id="update-order-grand-total" class="text-danger"></span></h4>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-warning">Lưu Thay Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>