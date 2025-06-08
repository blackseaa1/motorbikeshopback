<div class="modal fade" id="viewDeliveryServiceModal" tabindex="-1" aria-labelledby="viewDeliveryServiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDeliveryServiceModalLabel">Chi tiết Đơn vị Giao hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="dsLogoView" src="https://placehold.co/150x75/EFEFEF/AAAAAA&text=LOGO" alt="Logo"
                            class="img-fluid rounded mb-3" style="max-height: 100px; object-fit: contain;">
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 30%;">ID Đơn vị:</th>
                                <td id="dsIdView">-</td>
                            </tr>
                            <tr>
                                <th>Tên Đơn vị:</th>
                                <td id="dsNameView">-</td>
                            </tr>
                            <tr>
                                <th>Phí Giao hàng:</th>
                                <td id="dsShippingFeeView">-</td>
                            </tr>
                            <tr>
                                <th>Trạng thái:</th>
                                <td id="dsStatusViewText">-</td>
                            </tr>
                            <tr>
                                <th>Ngày tạo:</th>
                                <td id="dsCreatedAtView">-</td>
                            </tr>
                            <tr>
                                <th>Cập nhật lần cuối:</th>
                                <td id="dsUpdatedAtView">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" id="editDeliveryServiceFromViewButton" class="btn btn-primary"
                    data-bs-toggle="modal" data-bs-target="#updateDeliveryServiceModal"><i
                        class="bi bi-pencil-square me-1"></i>Chỉnh sửa</button>
            </div>
        </div>
    </div>
</div>