@extends('admin.layouts.app')

@section('title', 'Quản lý Mã Khuyến Mãi')

@section('content')
    <div id="adminPromotionsPage">
        {{-- Header của trang --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-gift me-2"></i>Quản lý Mã Khuyến Mãi</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Bán hàng</li>
                            <li class="breadcrumb-item active">Mã Khuyến Mãi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nội dung chính --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0 text-primary"><i class="bi bi-ticket-detailed-fill me-2"></i>Danh sách Mã Khuyến
                            mãi</h2>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createPromotionModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Mã mới
                        </button>
                    </div>
                    <div class="card-body">
                        {{-- Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                        <div class="row g-3 mb-3 align-items-center">
                            {{-- Thanh tìm kiếm --}}
                            <div class="col-md-4 col-lg-3">
                                <div class="input-group">
                                    <input type="text" id="promotionSearchInput" class="form-control form-control-sm"
                                        placeholder="Tìm kiếm mã, mô tả...">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="promotionSearchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Bộ lọc --}}
                            <div class="col-md-4 col-lg-3">
                                <select id="promotionFilterSelect" class="form-select form-select-sm">
                                    <option value="all">Tất cả trạng thái</option>
                                    <optgroup label="Trạng thái hiệu lực">
                                        <option value="active_effective">Đang hiệu lực</option>
                                        <option value="scheduled_effective">Chưa bắt đầu</option>
                                        <option value="expired_effective">Đã hết hạn/Hết lượt</option>
                                        <option value="inactive_effective">Đã tắt</option>
                                    </optgroup>
                                    <optgroup label="Trạng thái cài đặt">
                                        <option value="manual_active">Đang bật thủ công</option>
                                        <option value="manual_inactive">Đang tắt thủ công</option>
                                    </optgroup>
                                    <optgroup label="Trạng thái hạn sử dụng">
                                        <option value="expiring_soon">Sắp hết hạn (7 ngày)</option>
                                        <option value="expired">Đã hết hạn</option>
                                    </optgroup>
                                    <optgroup label="Trạng thái sử dụng">
                                        <option value="highly_used">Đã dùng nhiều (&gt;80%)</option>
                                        <option value="lowly_used">Đã dùng ít (&lt;20%)</option>
                                        <option value="no_uses">Chưa sử dụng</option>
                                    </optgroup>
                                </select>
                            </div>

                            {{-- Sắp xếp --}}
                            <div class="col-md-4 col-lg-3">
                                <select id="promotionSortSelect" class="form-select form-select-sm">
                                    <option value="latest">Mới nhất</option>
                                    <option value="oldest">Cũ nhất</option>
                                    <option value="code_asc">Mã (A-Z)</option>
                                    <option value="code_desc">Mã (Z-A)</option>
                                    <option value="end_date_asc">Sắp hết hạn nhất</option>
                                    <option value="uses_most">Lượt sử dụng (Cao nhất)</option>
                                    <option value="uses_least">Lượt sử dụng (Thấp nhất)</option>
                                    <option value="discount_highest">Giá trị giảm giá (Cao nhất)</option>
                                    <option value="min_order_highest">Đơn hàng tối thiểu (Cao nhất)</option>
                                    <option value="min_order_lowest">Đơn hàng tối thiểu (Thấp nhất)</option>
                                </select>
                            </div>

                            {{-- Nút hành động hàng loạt --}}
                            <div class="col-12 col-lg-3 text-lg-end">
                                <button class="btn btn-danger btn-sm me-2 mb-2 mb-lg-0" id="bulkDeleteBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#deletePromotionModal">
                                    <i class="bi bi-trash-fill me-1"></i> Xóa (<span id="selectedCountDelete">0</span>)
                                </button>
                                <button class="btn btn-info btn-sm mb-2 mb-lg-0" id="bulkToggleStatusBtn" disabled data-bs-toggle="modal"
                                    data-bs-target="#bulkToggleStatusModal">
                                    <i class="bi bi-arrow-repeat me-1"></i> Trạng thái (<span
                                        id="selectedCountToggle">0</span>)
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width:3%">
                                            <input type="checkbox" id="selectAllPromotions">
                                        </th>
                                        <th scope="col" style="width:5%">STT</th>
                                        <th scope="col">Mã Code</th>
                                        <th scope="col">Mô tả</th>
                                        <th scope="col" class="text-center">Giảm giá</th>
                                        <th scope="col">Thời gian hiệu lực</th>
                                        <th scope="col" class="text-center">Lượt sử dụng</th>
                                        <th scope="col" class="text-center">Trạng thái Cài đặt</th>
                                        <th scope="col" class="text-center">Trạng thái Hiện tại</th>
                                        <th scope="col" class="text-center" style="width: 15%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody id="promotions-table-body">
                                    {{-- Dữ liệu sẽ được tải ở đây bởi Controller và AJAX --}}
                                    @include('admin.sales.promotion.partials._promotion_table_rows', ['promotions' => $promotions])
                                </tbody>
                            </table>
                        </div>

                        {{-- Phân trang --}}
                        @if ($promotions->hasPages())
                            <div class="mt-3 d-flex justify-content-center" id="pagination-links">
                                {{ $promotions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Include tất cả các modals cần thiết cho trang --}}
    @include('admin.sales.promotion.modals.modal_create_promotion')
    @include('admin.sales.promotion.modals.modal_update_promotion')
    @include('admin.sales.promotion.modals.modal_delete_promotion')
    @include('admin.sales.promotion.modals.modal_view_promotion')
    @include('admin.sales.promotion.modals.modal_bulk_toggle_status')

@endsection

@push('scripts')
    {{-- Import script quản lý các hành động AJAX của trang --}}
    <script src="{{ asset('assets_admin/js/promotion_manager.js') }}"></script>
@endpush