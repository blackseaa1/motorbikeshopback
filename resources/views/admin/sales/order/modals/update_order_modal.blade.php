<div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateOrderModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Đơn Hàng: <strong
                        id="updateModalOrderIdStrong">N/A</strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateOrderForm" method="POST">
                    @csrf
                    @method('PUT') {{-- Sử dụng PUT method --}}
                    <input type="hidden" name="_form_identifier" value="update_order_form">

                    {{-- TRƯỜNG STATUS --}}
                    <div class="mb-3">
                        <label for="status_update" class="form-label">Trạng thái đơn hàng</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status_update"
                            name="status">
                            @foreach([
                                    \App\Models\Order::STATUS_PENDING => 'Chờ xử lý',
                                    \App\Models\Order::STATUS_PROCESSING => 'Đang xử lý',
                                    \App\Models\Order::STATUS_APPROVED => 'Đã duyệt',
                                    \App\Models\Order::STATUS_SHIPPED => 'Đã giao vận chuyển',
                                    \App\Models\Order::STATUS_DELIVERED => 'Đã giao hàng',
                                    \App\Models\Order::STATUS_COMPLETED => 'Hoàn thành',
                                    \App\Models\Order::STATUS_CANCELLED => 'Đã hủy',
                                    \App\Models\Order::STATUS_RETURNED => 'Đã trả hàng',
                                    \App\Models\Order::STATUS_FAILED => 'Thất bại',
                                ] as $statusValue => $statusText)
                                    <option value="{{ $statusValue }}">{{ $statusText }}</option>
                            @endforeach

                                                       </select>
                        @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="delivery_service_id_update" class="form-label">Dịch vụ vận chuyển</label>
                        <select class="form-select selectpicker @error('delivery_service_id') is-invalid @enderror" id="delivery_service_id_update" name="delivery_service_id">
                            <option value="">-- Chọn dịch vụ vận chuyển --</option>
                            @foreach($deliveryServices as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} (Phí: {{ number_format($service->shipping_fee) }} ₫)</option>
                            @endforeach
                        </select>
                        @error('delivery_service_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">

                                           <label for="notes_update" class="form-label">Ghi chú cho đơn hàng (tùy chọn)</label>
                        <textarea class="form-control" id="notes_update" name="notes" rows="3"></textarea>
                        @error('notes')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" form="updateOrderForm" id="saveUpdateOrderBtn">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>