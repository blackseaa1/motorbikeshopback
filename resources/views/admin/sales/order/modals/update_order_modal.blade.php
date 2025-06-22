<div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="updateOrderModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Đơn
                    Hàng #<span id="update-order-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateOrderForm" method="POST" action="">
                    @csrf
                    {{-- SỬA LỖI QUAN TRỌNG: Phải là PUT hoặc PATCH cho route resource update --}}
                    @method('PUT')
                    <input type="hidden" id="update_order_id" name="id">

                    <div class="mb-3">
                        <label class="form-label">Thông tin khách hàng</label>
                        <p id="update-customer-info" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Địa chỉ giao hàng</label>
                        <p id="update-shipping-address" class="form-control-plaintext"></p>
                    </div>

                    <div class="mb-3">
                        <label for="update_order_status" class="form-label">Trạng thái đơn hàng <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="update_order_status" name="status" title="Chọn trạng thái...">
                            @foreach ($orderStatuses as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="status"></div>
                    </div>

                    <div class="mb-3">
                        <label for="update_delivery_service_id" class="form-label">Dịch vụ vận chuyển</label>
                        <select class="form-select" id="update_delivery_service_id" name="delivery_service_id"
                            title="Chọn dịch vụ...">
                            @foreach ($deliveryServices as $service)
                                <option value="{{ $service->id }}" data-shipping-fee="{{ $service->shipping_fee }}">
                                    {{ $service->name }} ({{ number_format($service->shipping_fee) }} ₫)
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger mt-1" data-field="delivery_service_id"></div>
                    </div>

                    <div class="mb-3">
                        <label for="update_notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="update_notes" name="notes" rows="3"></textarea>
                        <div class="text-danger mt-1" data-field="notes"></div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Chi tiết tài chính</h5>
                    <div class="order-summary-details text-end">
                        <p class="mb-1"><strong>Tổng phụ:</strong> <span id="update-order-subtotal">0 ₫</span></p>
                        <p class="mb-1"><strong>Phí vận chuyển:</strong> <span id="update-order-shipping-fee">0 ₫</span>
                        </p>
                        <p class="mb-1 text-success"><strong>Giảm giá:</strong> <span id="update-order-discount">-0
                                ₫</span></p>
                        <h4 class="mt-2"><strong>Tổng cộng:</strong> <span id="update-order-grand-total"
                                class="text-danger">0 ₫</span></h4>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" form="updateOrderForm" class="btn btn-warning">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>