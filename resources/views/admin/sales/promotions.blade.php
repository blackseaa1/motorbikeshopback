@extends('admin.layouts.app')

@section('title', 'Quản lý Mã Khuyến Mãi')@section('content')
    <div id="adminPromotionsPage"> {{-- ID cho trang --}}
        <header class="content-header">
            <h1><i class="bi bi-gift me-2"></i>Mã Khuyến Mãi</h1>
        </header>

        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0"><i class="bi bi-ticket-detailed-fill me-2"></i>Danh sách Mã Khuyến mãi</h2>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPromotionModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tạo Mã mới
                    </button>
                </div>
                <div class="card-body">
                    @if ($promotions->isEmpty())
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2"></i>Hiện chưa có mã khuyến mãi nào.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width:5%">STT</th>
                                        <th scope="col">Mã Code</th>
                                        <th scope="col" style="width:20%;">Mô tả</th>
                                        <th scope="col" class="text-center">Giảm (%)</th>
                                        <th scope="col">Ngày bắt đầu</th>
                                        <th scope="col">Ngày kết thúc</th>
                                        <th scope="col" class="text-center">Đã dùng / Tối đa</th>
                                        <th scope="col" class="text-center">Trạng thái Hiện tại</th>
                                        <th scope="col" class="text-center">Trạng thái Cài đặt</th>
                                        <th scope="col" class="text-center" style="width: 17%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($promotions as $index => $promotion)
                                        @php
                                            // Lấy thông tin trạng thái từ Model
                                            $configCellText = $promotion->getConfigStatusText();
                                            $configCellBadge = $promotion->getConfigStatusBadgeClass();
                                            $effectiveDisplayText = $promotion->getCurrentDisplayStatus();
                                            $effectiveBadgeClass = $promotion->getStatusBadgeClass();
                                            $isManuallyActive = $promotion->isManuallyActive();
                                            $isExpiredOrFullyUsed = $promotion->getEffectiveStatusKey() === \App\Models\Promotion::STATUS_EFFECTIVE_EXPIRED;

                                            // Chuẩn bị data cho modal (để không lặp lại logic phức tạp trong JS)
                                            $modalData = [
                                                'id' => $promotion->id,
                                                'code' => $promotion->code,
                                                'description' => $promotion->description,
                                                'discount_percentage' => number_format($promotion->discount_percentage, 2, '.', ''),
                                                'start_date_form' => $promotion->start_date ? $promotion->start_date->format('Y-m-d\TH:i') : '',
                                                'end_date_form' => $promotion->end_date ? $promotion->end_date->format('Y-m-d\TH:i') : '',
                                                'start_date_display' => $promotion->start_date ? $promotion->start_date->format('d/m/Y H:i') : 'N/A',
                                                'end_date_display' => $promotion->end_date ? $promotion->end_date->format('d/m/Y H:i') : 'N/A',
                                                'max_uses' => $promotion->max_uses,
                                                'uses_count' => $promotion->uses_count,
                                                'status_config_text' => $configCellText, // Trạng thái cài đặt text
                                                'status_display' => $effectiveDisplayText, // Trạng thái hiệu lực text
                                                'status_display_badge' => $effectiveBadgeClass, // Badge cho trạng thái hiệu lực
                                                'manual_status' => $promotion->status, // Trạng thái cài đặt (active/inactive)
                                                'update_url' => route('admin.sales.promotions.update', $promotion->id),
                                                'delete_url' => route('admin.sales.promotions.destroy', $promotion->id),
                                                'toggle_status_url' => route('admin.sales.promotions.toggleStatus', $promotion->id),
                                            ];
                                        @endphp
                                        <tr id="promotion-row-{{ $promotion->id }}"
                                            class="{{ !$isManuallyActive ? 'row-inactive' : '' }}">
                                            <td>{{ $promotions->firstItem() + $index }}</td>
                                            <td><strong>{{ $promotion->code }}</strong></td>
                                            <td>{{ Str::limit($promotion->description, 40, '...') ?? 'N/A' }}</td>
                                            <td class="text-center">{{ number_format($promotion->discount_percentage, 2) }}</td>
                                            <td>{{ $promotion->start_date ? $promotion->start_date->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td>{{ $promotion->end_date ? $promotion->end_date->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td class="text-center">{{ $promotion->uses_count }} /
                                                {{ $promotion->max_uses ?? '∞' }}
                                            </td>
                                            <td class="text-center status-cell-display"
                                                id="promotion-status-display-{{ $promotion->id }}">
                                                <span class="badge {{ $effectiveBadgeClass }}">{{ $effectiveDisplayText }}</span>
                                            </td>
                                            <td class="text-center status-cell-config"
                                                id="promotion-status-config-{{ $promotion->id }}">
                                                <span class="badge {{ $configCellBadge }}">{{ $configCellText }}</span>
                                            </td>
                                            <td class="text-center action-buttons">
                                                <button type="button" class="btn btn-info btn-sm btn-action view-details-btn"
                                                    data-bs-toggle="modal" data-bs-target="#viewPromotionModal"
                                                    data-promotion-data="{{ json_encode($modalData) }}" title="Xem Chi tiết">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary toggle-status-btn btn-action"
                                                    data-id="{{ $promotion->id }}"
                                                    data-url="{{ $modalData['toggle_status_url'] }}"
                                                    title="{{ $isManuallyActive ? 'Tắt mã này (thủ công)' : 'Bật mã này (thủ công)' }}"
                                                    {{ $isExpiredOrFullyUsed && $isManuallyActive ? 'disabled' : '' }}>
                                                    <i class="bi {{ $isManuallyActive ? 'bi-power text-danger fs-5' : 'bi-power text-success fs-5' }}"></i>
                                                </button>

                                                <button type="button" class="btn btn-sm btn-warning update-promotion-btn btn-action" {{-- Sửa lại class để phân biệt với View --}}
                                                    data-bs-toggle="modal" data-bs-target="#updatePromotionModal"
                                                    data-promotion-data="{{ json_encode($modalData) }}"
                                                    title="Cập nhật">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <button type="button" class="btn btn-sm btn-danger delete-promotion-btn btn-action"
                                                    data-bs-toggle="modal" data-bs-target="#deletePromotionModal"
                                                    data-id="{{ $promotion->id }}" data-code="{{ $promotion->code }}"
                                                    data-uses-count="{{ $promotion->uses_count }}"
                                                    data-delete-url="{{ $modalData['delete_url'] }}"
                                                    title="Xóa">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($promotions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $promotions->hasPages())
                            <div class="mt-3 d-flex justify-content-center">{{ $promotions->links('pagination::bootstrap-5') }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Create Promotion Modal --}}
        <div class="modal fade" id="createPromotionModal" tabindex="-1" aria-labelledby="createPromotionModalLabel"
            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="createPromotionForm" action="{{ route('admin.sales.promotions.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form_identifier" value="create_promotion_form">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createPromotionModalLabel"><i
                                    class="bi bi-plus-circle-fill me-2"></i>Tạo Mã Khuyến mãi mới</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="promoCodeCreate" class="form-label">Mã Code:<span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase @error('code', 'create_promotion_form') is-invalid @enderror" id="promoCodeCreate" name="code" value="{{ old('code') }}" required placeholder="VD: TET2025">
                                @error('code', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="promoDescriptionCreate" class="form-label">Mô tả ngắn:</label>
                                <input type="text" class="form-control @error('description', 'create_promotion_form') is-invalid @enderror" id="promoDescriptionCreate" name="description" value="{{ old('description') }}" placeholder="VD: Giảm giá mừng xuân">
                                @error('description', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="promoDiscountCreate" class="form-label">Phần trăm giảm giá (%):<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('discount_percentage', 'create_promotion_form') is-invalid @enderror" id="promoDiscountCreate" name="discount_percentage" step="0.01" min="0.01" max="100.00" value="{{ old('discount_percentage') }}" required placeholder="VD: 10.5">
                                    @error('discount_percentage', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="promoMaxUsesCreate" class="form-label">Số lượt sử dụng tối đa:</label>
                                    <input type="number" class="form-control @error('max_uses', 'create_promotion_form') is-invalid @enderror" id="promoMaxUsesCreate" name="max_uses" min="1" value="{{ old('max_uses') }}" placeholder="Bỏ trống nếu không giới hạn">
                                    @error('max_uses', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="promoStartDateCreate" class="form-label">Ngày giờ bắt đầu:<span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('start_date', 'create_promotion_form') is-invalid @enderror" id="promoStartDateCreate" name="start_date" value="{{ old('start_date', \Carbon\Carbon::today()->format('Y-m-d\TH:00:00')) }}" required>
                                    @error('start_date', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="promoEndDateCreate" class="form-label">Ngày giờ kết thúc:<span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('end_date', 'create_promotion_form') is-invalid @enderror" id="promoEndDateCreate" name="end_date" value="{{ old('end_date', \Carbon\Carbon::now()->addDays(7)->endOfDay()->format('Y-m-d\TH:i')) }}" required>
                                    @error('end_date', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="promoStatusCreate" class="form-label">Trạng thái cài đặt:<span class="text-danger">*</span></label>
                                <select class="form-select @error('status', 'create_promotion_form') is-invalid @enderror" id="promoStatusCreate" name="status" required>
                                    <option value="{{ \App\Models\Promotion::STATUS_ACTIVE }}" {{ old('status', \App\Models\Promotion::STATUS_ACTIVE) == \App\Models\Promotion::STATUS_ACTIVE ? 'selected' : '' }}>Hoạt động (Bật)</option>
                                    <option value="{{ \App\Models\Promotion::STATUS_INACTIVE }}" {{ old('status') == \App\Models\Promotion::STATUS_INACTIVE ? 'selected' : '' }}>Tạm tắt (Tắt)</option>
                                </select>
                                @error('status', 'create_promotion_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">"Hoạt động" sẽ có hiệu lực nếu trong thời gian khuyến mãi. "Tạm tắt" sẽ vô hiệu hóa mã bất kể thời gian.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Lưu Mã Khuyến mãi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Update Promotion Modal --}}
        <div class="modal fade" id="updatePromotionModal" tabindex="-1" aria-labelledby="updatePromotionModalLabel"
            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="updatePromotionForm" method="POST"> {{-- action sẽ được JS đặt --}}
                        @csrf
                        @method('PUT')
                        {{-- Không cần input hidden ID ở đây nữa vì action URL đã chứa ID --}}
                        <div class="modal-header">
                            <h5 class="modal-title" id="updatePromotionModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Mã Khuyến mãi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="promoCodeUpdate" class="form-label">Mã Code:<span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase" id="promoCodeUpdate" name="code" required>
                                <div class="invalid-feedback" id="promoCodeUpdateError"></div>
                            </div>
                            <div class="mb-3">
                                <label for="promoDescriptionUpdate" class="form-label">Mô tả ngắn:</label>
                                <input type="text" class="form-control" id="promoDescriptionUpdate" name="description">
                                <div class="invalid-feedback" id="promoDescriptionUpdateError"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="promoDiscountUpdate" class="form-label">Phần trăm giảm giá (%):<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="promoDiscountUpdate" name="discount_percentage" step="0.01" min="0.01" max="100.00" required>
                                    <div class="invalid-feedback" id="promoDiscount_percentageUpdateError"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="promoMaxUsesUpdate" class="form-label">Số lượt sử dụng tối đa:</label>
                                    <input type="number" class="form-control" id="promoMaxUsesUpdate" name="max_uses" min="1" placeholder="Bỏ trống nếu không giới hạn">
                                     <div class="invalid-feedback" id="promoMax_usesUpdateError"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="promoStartDateUpdate" class="form-label">Ngày giờ bắt đầu:<span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="promoStartDateUpdate" name="start_date" required>
                                     <div class="invalid-feedback" id="promoStart_dateUpdateError"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="promoEndDateUpdate" class="form-label">Ngày giờ kết thúc:<span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="promoEndDateUpdate" name="end_date" required>
                                    <div class="invalid-feedback" id="promoEnd_dateUpdateError"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="promoStatusUpdate" class="form-label">Trạng thái cài đặt:<span class="text-danger">*</span></label>
                                <select class="form-select" id="promoStatusUpdate" name="status" required>
                                    <option value="{{ \App\Models\Promotion::STATUS_ACTIVE }}">Hoạt động (Bật)</option>
                                    <option value="{{ \App\Models\Promotion::STATUS_INACTIVE }}">Tạm tắt (Tắt)</option>
                                </select>
                                <div class="invalid-feedback" id="promoStatusUpdateError"></div>
                                <small class="form-text text-muted">"Hoạt động" sẽ có hiệu lực nếu trong thời gian khuyến mãi. "Tạm tắt" sẽ vô hiệu hóa mã bất kể thời gian.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete Promotion Modal --}}
        <div class="modal fade" id="deletePromotionModal" tabindex="-1" aria-labelledby="deletePromotionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="deletePromotionForm" method="POST"> {{-- action sẽ được JS đặt --}}
                        @csrf
                        @method('DELETE')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deletePromotionModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa Mã Khuyến mãi</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc chắn muốn xóa vĩnh viễn mã khuyến mãi "<strong id="promoCodeNameToDelete">N/A</strong>"?</p>
                            <p id="deleteWarningUsesCount" class="text-danger fw-bold" style="display:none;">Lưu ý: Mã này đã được sử dụng <span id="promoUsesCountDisplayForDelete">0</span> lần. Việc xóa có thể ảnh hưởng đến các đơn hàng cũ.</p>

                            @if(Config::get('admin.deletion_password'))
                                <div class="mb-3 mt-3">
                                    <label for="adminPasswordDeletePromotion" class="form-label">Nhập Mật khẩu Xóa Chung:<span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="adminPasswordDeletePromotion" name="admin_password_delete_promotion" required autocomplete="new-password">
                                    <div class="invalid-feedback" id="admin_password_delete_promotionError"></div>
                                </div>
                            @endif
                            <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xóa Vĩnh Viễn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- View Promotion Modal --}}
        <div class="modal fade" id="viewPromotionModal" tabindex="-1" aria-labelledby="viewPromotionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewPromotionModalLabel"><i class="bi bi-eye-fill me-2"></i>Chi tiết Mã Khuyến mãi: <strong id="viewModalPromoCodeStrong">N/A</strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr><th style="width: 30%;">Mã Code</th><td id="viewDetailPromoCode"></td></tr>
                                <tr><th>Mô tả</th><td id="viewDetailPromoDescription"></td></tr>
                                <tr><th>Phần trăm giảm giá (%)</th><td id="viewDetailPromoDiscount"></td></tr>
                                <tr><th>Ngày giờ bắt đầu</th><td id="viewDetailPromoStartDate"></td></tr>
                                <tr><th>Ngày giờ kết thúc</th><td id="viewDetailPromoEndDate"></td></tr>
                                <tr><th>Số lượt sử dụng tối đa</th><td id="viewDetailPromoMaxUses"></td></tr>
                                <tr><th>Số lượt đã sử dụng</th><td id="viewDetailPromoUsesCount"></td></tr>
                                <tr><th>Trạng thái Cài đặt</th><td id="viewDetailPromoStatusConfigText"></td></tr>
                                <tr><th>Trạng thái Hiện tại (Hiệu lực)</th><td><span id="viewDetailPromoStatusDisplayBadgeSpan"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning me-auto" id="editFromViewModalBtn" data-bs-dismiss="modal"> {{-- data-bs-dismiss để đóng modal view trước khi mở modal update --}}
                            <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/promotion_manager.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mở lại modal CREATE nếu có lỗi validation từ server
            @if ($errors->getBag('create_promotion_form')->any() && old('_form_identifier') === 'create_promotion_form')
                const createModalEl = document.getElementById('createPromotionModal');
                if (createModalEl) {
                    try { new bootstrap.Modal(createModalEl).show(); }
                    catch (e) { console.error("Lỗi khi mở lại modal tạo khuyến mãi:", e); }
                }
            @endif

            // Mở lại modal UPDATE nếu có lỗi validation từ server (ít dùng với AJAX, nhưng để phòng)
            @if ($errors->getBag('update_promotion_form')->any() && old('_form_identifier') === 'update_promotion_form' && session('reopen_update_modal_promotion_id'))
                const updateModalEl = document.getElementById('updatePromotionModal');
                const promotionIdToUpdateOnError = "{{ session('reopen_update_modal_promotion_id') }}";
                if (updateModalEl && promotionIdToUpdateOnError) {
                    const originalUpdateButton = document.querySelector(`.update-promotion-btn[data-id="${promotionIdToUpdateOnError}"]`);
                    if (originalUpdateButton && originalUpdateButton.dataset.promotionData) {
                        try {
                            const promotionData = JSON.parse(originalUpdateButton.dataset.promotionData);
                            if (window.populateUpdateModalPromotion) { // Hàm này từ promotion_manager.js
                                window.populateUpdateModalPromotion(promotionData); // Gọi hàm populate
                                new bootstrap.Modal(updateModalEl).show();
                            }
                        } catch (e) { console.error("Lỗi khi mở lại modal cập nhật (populate/show):", e); }
                    }
                    @php session()->forget('reopen_update_modal_promotion_id'); @endphp
                }
            @endif

            // Mở lại modal DELETE nếu có lỗi validation từ server (ví dụ: sai mật khẩu xóa)
             @if ($errors->getBag('delete_promotion_form')->any() && old('_form_identifier') === 'delete_promotion_form' && session('reopen_delete_modal_promotion_id'))
                const deleteModalEl = document.getElementById('deletePromotionModal');
                const promotionIdToDeleteOnError = "{{ session('reopen_delete_modal_promotion_id') }}";
                if (deleteModalEl && promotionIdToDeleteOnError) {
                    const originalDeleteButton = document.querySelector(`.delete-promotion-btn[data-id="${promotionIdToDeleteOnError}"]`);
                    if (originalDeleteButton) {
                        if (window.populateDeleteModalPromotion) { // Hàm này từ promotion_manager.js
                            window.populateDeleteModalPromotion(originalDeleteButton); // Gọi hàm populate
                            new bootstrap.Modal(deleteModalEl).show();
                        }
                    }
                     @php session()->forget('reopen_delete_modal_promotion_id'); @endphp
                }
            @endif
        });
    </script>
@endpush