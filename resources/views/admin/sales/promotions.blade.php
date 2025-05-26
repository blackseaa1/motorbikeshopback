@extends('admin.layouts.app')

@section('title', 'Promotions') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-gift me-2"></i>Mã Khuyến Mại</h1>
    </header>


    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-ticket-detailed-fill me-2"></i>Quản lý Mã Khuyến mãi</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPromotionModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Mã mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Mã Code</th>
                                <th scope="col" class="text-center">Phần trăm giảm giá</th>
                                <th scope="col">Ngày bắt đầu</th>
                                <th scope="col">Ngày kết thúc</th>
                                <th scope="col" class="text-center">Lượt sử dụng</th>
                                <th scope="col" class="text-center">Trạng thái</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua $promotions từ controller --}}
                            <tr>
                                <td>1</td>
                                <td><strong>SUMMER25</strong></td>
                                <td class="text-center">25.00%</td>
                                <td>2025-06-01 00:00:00</td>
                                <td>2025-06-30 23:59:59</td>
                                <td class="text-center">150</td>
                                <td class="text-center"><span class="badge bg-success">Đang hoạt động</span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updatePromotionModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deletePromotionModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><strong>WELCOME10</strong></td>
                                <td class="text-center">10.00%</td>
                                <td>2025-01-01 00:00:00</td>
                                <td>2025-12-31 23:59:59</td>
                                <td class="text-center">578</td>
                                <td class="text-center"><span class="badge bg-secondary">Đã hết hạn</span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updatePromotionModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deletePromotionModal" title="Xóa">
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

    {{-- Modals for Promotion Management --}}

    <div class="modal fade" id="createPromotionModal" tabindex="-1" aria-labelledby="createPromotionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPromotionModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Mã
                        Khuyến mãi mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createPromotionForm">
                        <div class="mb-3">
                            <label for="promoCodeCreate" class="form-label">Mã Code:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="promoCodeCreate" name="code" required
                                placeholder="VD: TET2025">
                        </div>
                        <div class="mb-3">
                            <label for="promoDiscountCreate" class="form-label">Phần trăm giảm giá (%):<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="promoDiscountCreate" name="discount_percentage"
                                step="0.01" min="0.01" max="100.00" required placeholder="VD: 10.5">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="promoStartDateCreate" class="form-label">Ngày bắt đầu:<span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="promoStartDateCreate"
                                    name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="promoEndDateCreate" class="form-label">Ngày kết thúc:<span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="promoEndDateCreate" name="end_date"
                                    required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="createPromotionForm">Lưu Mã Khuyến mãi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updatePromotionModal" tabindex="-1" aria-labelledby="updatePromotionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePromotionModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                        Mã Khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updatePromotionForm">
                        <input type="hidden" id="promoIdUpdate" name="promotion_id">
                        <div class="mb-3">
                            <label for="promoCodeUpdate" class="form-label">Mã Code:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="promoCodeUpdate" name="code"
                                value="SUMMER25" required>
                        </div>
                        <div class="mb-3">
                            <label for="promoDiscountUpdate" class="form-label">Phần trăm giảm giá (%):<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="promoDiscountUpdate" name="discount_percentage"
                                step="0.01" min="0.01" max="100.00" value="25.00" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="promoStartDateUpdate" class="form-label">Ngày bắt đầu:<span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="promoStartDateUpdate"
                                    name="start_date"
                                    value="{{ \Carbon\Carbon::parse('2025-06-01 00:00:00')->format('Y-m-d\TH:i') }}"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="promoEndDateUpdate" class="form-label">Ngày kết thúc:<span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="promoEndDateUpdate" name="end_date"
                                    value="{{ \Carbon\Carbon::parse('2025-06-30 23:59:59')->format('Y-m-d\TH:i') }}"
                                    required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="updatePromotionForm">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deletePromotionModal" tabindex="-1" aria-labelledby="deletePromotionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deletePromotionModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa
                        Mã Khuyến mãi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa mã khuyến mãi "<strong>SUMMER25</strong>" không?</p>
                    <p class="text-danger">Lưu ý: Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deletePromotionForm">
                        <button type="button" class="btn btn-danger">Xóa Mã Khuyến mãi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // JavaScript để điền dữ liệu vào modal Sửa/Xóa khi bạn sẵn sàng thêm tính năng.
        // Ví dụ cho modal Update:
        // document.addEventListener('DOMContentLoaded', function () {
        //     const updatePromotionModal = document.getElementById('updatePromotionModal');
        //     if (updatePromotionModal) {
        //         updatePromotionModal.addEventListener('show.bs.modal', function (event) {
        //             const button = event.relatedTarget; // Nút đã kích hoạt modal
        //             // Lấy dữ liệu từ data-* attributes của nút (bạn cần thêm chúng vào các nút trong bảng)
        //             const promoId = button.dataset.promoId;
        //             const promoCode = button.dataset.promoCode;
        //             const promoDiscount = button.dataset.promoDiscount;
        //             const promoStartDate = button.dataset.promoStartDate; // Format: YYYY-MM-DDTHH:MM
        //             const promoEndDate = button.dataset.promoEndDate;   // Format: YYYY-MM-DDTHH:MM

        //             updatePromotionModal.querySelector('#promoIdUpdate').value = promoId;
        //             updatePromotionModal.querySelector('#promoCodeUpdate').value = promoCode;
        //             updatePromotionModal.querySelector('#promoDiscountUpdate').value = promoDiscount;
        //             updatePromotionModal.querySelector('#promoStartDateUpdate').value = promoStartDate;
        //             updatePromotionModal.querySelector('#promoEndDateUpdate').value = promoEndDate;
        //         });
        //     }
        // Tương tự cho modal Delete
        // });
    </script>
@endpush