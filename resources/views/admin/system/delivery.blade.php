@extends('admin.layouts.app')
 
@section('title', 'Delivery Service') @section('content')
<header class="content-header">
    <h1><i class="bi bi-truck me-2"></i>Quản lý Đơn vị Giao hàng</h1>
</header>

    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-truck me-2"></i>Quản lý Đơn vị Giao hàng</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDeliveryServiceModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Thêm Đơn vị mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Logo</th>
                                <th scope="col">Tên Đơn vị Giao hàng</th>
                                <th scope="col" class="text-end">Phí Giao hàng (VNĐ)</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua $deliveryServices từ controller --}}
                            <tr>
                                <td>1</td>
                                <td><img src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=GHN" alt="Logo ĐVGH"
                                        class="img-thumbnail" style="width: 100px; height: 50px; object-fit: contain;"></td>
                                <td>Giao Hàng Nhanh</td>
                                <td class="text-end">30,000</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateDeliveryServiceModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteDeliveryServiceModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><img src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=GHTK" alt="Logo ĐVGH"
                                        class="img-thumbnail" style="width: 100px; height: 50px; object-fit: contain;"></td>
                                <td>Giao Hàng Tiết Kiệm</td>
                                <td class="text-end">25,000</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateDeliveryServiceModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteDeliveryServiceModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Kết thúc lặp --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals for Delivery Service Management --}}

    <div class="modal fade" id="createDeliveryServiceModal" tabindex="-1" aria-labelledby="createDeliveryServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDeliveryServiceModalLabel"><i
                            class="bi bi-plus-circle-fill me-2"></i>Thêm Đơn vị Giao hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createDeliveryServiceForm">
                        <div class="mb-3">
                            <label for="dsNameCreate" class="form-label">Tên Đơn vị Giao hàng:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dsNameCreate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="dsShippingFeeCreate" class="form-label">Phí Giao hàng cố định (VNĐ):<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="dsShippingFeeCreate" name="shipping_fee"
                                step="1000" min="0" required placeholder="VD: 30000">
                        </div>
                        <div class="mb-3">
                            <label for="dsLogoCreate" class="form-label">Logo Đơn vị Giao hàng:</label>
                            <input type="file" class="form-control" id="dsLogoCreate" name="logo_url">
                            <img src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Logo" alt="Xem trước logo"
                                id="dsLogoPreviewCreate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 50px; object-fit: contain;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="createDeliveryServiceForm">Lưu Đơn vị</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateDeliveryServiceModal" tabindex="-1" aria-labelledby="updateDeliveryServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateDeliveryServiceModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập
                        nhật Đơn vị Giao hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateDeliveryServiceForm">
                        <input type="hidden" id="dsIdUpdate" name="delivery_service_id">
                        <div class="mb-3">
                            <label for="dsNameUpdate" class="form-label">Tên Đơn vị Giao hàng:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dsNameUpdate" name="name" value="Giao Hàng Nhanh"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="dsShippingFeeUpdate" class="form-label">Phí Giao hàng cố định (VNĐ):<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="dsShippingFeeUpdate" name="shipping_fee"
                                step="1000" min="0" value="30000" required>
                        </div>
                        <div class="mb-3">
                            <label for="dsLogoUpdate" class="form-label">Logo Đơn vị Giao hàng mới (để trống nếu không
                                đổi):</label>
                            <input type="file" class="form-control" id="dsLogoUpdate" name="logo_url">
                            <img src="https://placehold.co/100x50/EFEFEF/AAAAAA&text=Logo+Cũ" alt="Xem trước logo"
                                id="dsLogoPreviewUpdate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 50px; object-fit: contain;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="updateDeliveryServiceForm">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteDeliveryServiceModal" tabindex="-1" aria-labelledby="deleteDeliveryServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteDeliveryServiceModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác
                        nhận Xóa Đơn vị Giao hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa đơn vị giao hàng "<strong>Giao Hàng Nhanh</strong>" không?</p>
                    <p class="text-danger">Lưu ý: Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteDeliveryServiceForm">
                        <button type="button" class="btn btn-danger">Xóa Đơn vị</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Đảm bảo hàm previewImage đã được định nghĩa trong file JS chung của bạn.
        if (typeof previewImage === 'function') {
            previewImage('dsLogoCreate', 'dsLogoPreviewCreate');
            previewImage('dsLogoUpdate', 'dsLogoPreviewUpdate');
        } else {
            console.warn('Hàm previewImage chưa được định nghĩa. Chức năng xem trước ảnh sẽ không hoạt động.');
        }

        // JavaScript để điền dữ liệu vào modal Sửa/Xóa khi bạn sẵn sàng thêm tính năng.
        // Ví dụ cho modal Update:
        // document.addEventListener('DOMContentLoaded', function () {
        //     const updateDeliveryServiceModal = document.getElementById('updateDeliveryServiceModal');
        //     if (updateDeliveryServiceModal) {
        //         updateDeliveryServiceModal.addEventListener('show.bs.modal', function (event) {
        //             const button = event.relatedTarget;
        //             // Lấy dữ liệu từ data-* attributes của nút
        //             const dsId = button.dataset.dsId;
        //             const dsName = button.dataset.dsName;
        //             const dsShippingFee = button.dataset.dsShippingFee;
        //             const dsLogoUrl = button.dataset.dsLogoUrl;

        //             updateDeliveryServiceModal.querySelector('#dsIdUpdate').value = dsId;
        //             updateDeliveryServiceModal.querySelector('#dsNameUpdate').value = dsName;
        //             updateDeliveryServiceModal.querySelector('#dsShippingFeeUpdate').value = dsShippingFee;
        //             updateDeliveryServiceModal.querySelector('#dsLogoPreviewUpdate').src = dsLogoUrl || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=Logo+C%C5%A9';
        //         });
        //     }
        //     // Tương tự cho modal Delete
        // });
    </script>
@endpush