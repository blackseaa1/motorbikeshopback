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
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
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
                                {{-- TBody luôn được render để JS có thể tìm thấy ID --}}
                                <tbody id="promotions-table-body">
                                    @forelse ($promotions as $promotion)
                                        <tr id="promotion-row-{{ $promotion->id }}"
                                            class="{{ !$promotion->isManuallyActive() ? 'row-inactive' : '' }}">
                                            <th scope="row">{{ $loop->index + $promotions->firstItem() }}</th>
                                            <td class="fw-bold text-primary">{{ $promotion->code }}</td>
                                            <td>{{ Str::limit($promotion->description, 50) }}</td>
                                            <td class="text-danger fw-bold text-center">
                                                {{ $promotion->display_discount_value }}
                                            </td>
                                            <td>
                                                <small>
                                                    Từ: {{ $promotion->start_date->format('d/m/Y H:i') }}<br>
                                                    Đến: {{ $promotion->end_date->format('d/m/Y H:i') }}
                                                </small>
                                            </td>
                                            <td class="text-center">{{ $promotion->uses_count }} /
                                                {{ $promotion->max_uses ?? '∞' }}</td>
                                            <td class="text-center" id="promotion-status-config-{{ $promotion->id }}">
                                                <span
                                                    class="badge {{ $promotion->manual_status_badge_class }}">{{ $promotion->manual_status_text }}</span>
                                            </td>
                                            <td class="text-center" id="promotion-status-display-{{ $promotion->id }}">
                                                <span
                                                    class="badge {{ $promotion->effective_status_badge_class }}">{{ $promotion->effective_status_text }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    {{-- Nút Bật/Tắt --}}
                                                    <button class="btn btn-sm btn-outline-secondary me-2 toggle-status-btn"
                                                        data-id="{{ $promotion->id }}"
                                                        data-url="{{ route('admin.sales.promotions.toggleStatus', $promotion->id) }}"
                                                        title="{{ $promotion->isManuallyActive() ? 'Tắt mã này' : 'Bật mã này' }}">
                                                        <i class="bi bi-power fs-5"></i>
                                                    </button>

                                                    {{-- Nút Xem --}}
                                                    <button class="btn btn-sm btn-outline-info me-2 view-promotion-btn"
                                                        data-bs-toggle="modal" data-bs-target="#viewPromotionModal"
                                                        data-id="{{ $promotion->id }}"
                                                        data-url="{{ route('admin.sales.promotions.show', $promotion->id) }}"
                                                        title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>

                                                    {{-- Nút Sửa --}}
                                                    <button class="btn btn-sm btn-outline-primary me-2 edit-promotion-btn"
                                                        data-bs-toggle="modal" data-bs-target="#updatePromotionModal"
                                                        data-id="{{ $promotion->id }}"
                                                        data-update-url="{{ route('admin.sales.promotions.update', $promotion->id) }}"
                                                        data-url="{{ route('admin.sales.promotions.show', $promotion->id) }}"
                                                        title="Chỉnh sửa">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    {{-- Nút Xóa --}}
                                                    <button class="btn btn-sm btn-outline-danger delete-promotion-btn"
                                                        data-bs-toggle="modal" data-bs-target="#deletePromotionModal"
                                                        data-id="{{ $promotion->id }}" data-code="{{ $promotion->code }}"
                                                        data-delete-url="{{ route('admin.sales.promotions.destroy', $promotion->id) }}"
                                                        title="Xóa">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Hiển thị thông báo khi không có dữ liệu --}}
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                <div class="alert alert-info mb-0" role="alert">
                                                    <i class="bi bi-info-circle me-2"></i>Hiện chưa có mã khuyến mãi nào.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Chỉ hiển thị phân trang khi cần thiết --}}
                        @if ($promotions->hasPages())
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $promotions->links('pagination::bootstrap-5') }}
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
    @include("admin.sales.promotion.modals.modal_view_promotion")

@endsection

@push('scripts')
    {{-- Import script quản lý các hành động AJAX của trang --}}
    <script src="{{ asset('assets_admin/js/promotion_manager.js') }}"></script>
@endpush