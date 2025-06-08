@extends('admin.layouts.app')

@section('title', 'Quản lý Mã Khuyến Mãi')

@section('content')
    <div id="adminPromotionsPage">
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
                                        <th scope="col">Mô tả</th>
                                        <th scope="col">Giảm giá</th>
                                        <th scope="col">Thời gian hiệu lực</th>
                                        <th scope="col">Lượt sử dụng</th>
                                        <th scope="col" class="text-center">Trạng thái Cài đặt</th>
                                        <th scope="col" class="text-center">Trạng thái Hiện tại</th>
                                        <th scope="col" class="text-center" style="width: 10%;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($promotions as $promotion)
                                        {{-- SỬA ĐỔI 1: Thay hằng số STATUS_ACTIVE thành STATUS_MANUAL_ACTIVE --}}
                                        <tr id="promotion-row-{{ $promotion->id }}" class="{{ $promotion->status == \App\Models\Promotion::STATUS_MANUAL_INACTIVE ? 'row-inactive' : '' }}">
                                            <th scope="row">{{ $loop->index + $promotions->firstItem() }}</th>
                                            <td class="fw-bold text-primary">{{ $promotion->code }}</td>
                                            <td>{{ Str::limit($promotion->description, 50) }}</td>
                                            <td class="text-danger fw-bold">{{ $promotion->formatted_discount }}</td>
                                            <td>
                                                <small>
                                                    Từ: {{ $promotion->start_date->format('d/m/Y H:i') }}<br>
                                                    Đến: {{ $promotion->end_date->format('d/m/Y H:i') }}
                                                </small>
                                            </td>
                                            <td>{{ $promotion->uses_count }} / {{ $promotion->max_uses ?? 'Không giới hạn' }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $promotion->manual_status_badge_class }}">{{ $promotion->manual_status_text }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $promotion->effective_status_badge_class }}">{{ $promotion->effective_status_text }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    {{-- SỬA ĐỔI 2: Thay hằng số STATUS_ACTIVE thành STATUS_MANUAL_ACTIVE --}}
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary me-2 toggle-status-btn"
                                                        data-id="{{ $promotion->id }}"
                                                        data-current-status="{{ $promotion->status }}"
                                                        data-url="{{ route('admin.sales.promotions.toggleStatus', $promotion->id) }}"
                                                        title="{{ $promotion->status === \App\Models\Promotion::STATUS_MANUAL_ACTIVE ? 'Tắt mã này' : 'Bật mã này' }}">
                                                        <i class="bi bi-power fs-5"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary me-2 edit-promotion-btn"
                                                            data-id="{{ $promotion->id }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#updatePromotionModal">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-promotion-btn"
                                                            data-id="{{ $promotion->id }}"
                                                            data-code="{{ $promotion->code }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deletePromotionModal">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-container mt-4">
                            {{ $promotions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('admin.sales.promotion.modals.modal_create_promotion')
    @include('admin.sales.promotion.modals.modal_update_promotion')
    @include('admin.sales.promotion.modals.modal_delete_promotion')
    @include("admin.sales.promotion.modals.modal_view_promotion")

@endsection

@push('scripts')
    {{-- Import a script to handle AJAX for promotions page --}}
    <script src="{{ asset('assets_admin/js/promotion_manager.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo các event listeners và logic từ file JS
            if (typeof initializePromotionsPage === 'function') {
                initializePromotionsPage();
            }
        });
    </script>
@endpush 