@extends('admin.layouts.app')

@section('title', 'Quản lý Đánh giá')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Danh sách Đánh giá Sản phẩm</h4>
                </div>
                <div class="card-body">
                    {{-- Form tìm kiếm và lọc --}}
                    <form id="filterReviewsForm" action="{{ route('admin.content.reviews.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo ID, khách hàng, sản phẩm..." value="{{ $selectedFilters['search'] }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status_filter" class="form-select">
                                    <option value="all">Tất cả trạng thái</option>
                                    @foreach($reviewStatuses as $status)
                                        <option value="{{ $status }}" {{ $selectedFilters['status_filter'] == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="rating_filter" class="form-select">
                                    <option value="all">Tất cả sao</option>
                                    @foreach($reviewRatings as $rating)
                                        <option value="{{ $rating }}" {{ $selectedFilters['rating_filter'] == $rating ? 'selected' : '' }}>
                                            {{ $rating }} sao
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="sort_by" class="form-select">
                                    <option value="created_at_desc" {{ $selectedFilters['sort_by'] == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                                    <option value="created_at_asc" {{ $selectedFilters['sort_by'] == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
                                    <option value="rating_desc" {{ $selectedFilters['sort_by'] == 'rating_desc' ? 'selected' : '' }}>Đánh giá (Cao - Thấp)</option>
                                    <option value="rating_asc" {{ $selectedFilters['sort_by'] == 'rating_asc' ? 'selected' : '' }}>Đánh giá (Thấp - Cao)</option>
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Lọc</button>
                                <a href="{{ route('admin.content.reviews.index') }}" class="btn btn-secondary">Đặt lại</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Đánh giá</th>
                                    <th>Bình luận</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                <tr id="review-row-{{ $review->id }}">
                                    <td>{{ $review->id }}</td>
                                    <td>{{ $review->customer->name ?? 'N/A' }} ({{ $review->customer->email ?? 'Khách vãng lai' }})</td>
                                    <td>{{ $review->product->name ?? 'Sản phẩm đã xóa' }}</td>
                                    <td>
                                        @for($i = 0; $i < $review->rating; $i++)<i class="bi bi-star-fill text-warning"></i>@endfor
                                        @for($i = $review->rating; $i < 5; $i++)<i class="bi bi-star text-warning"></i>@endfor
                                    </td>
                                    <td>{{ Str::limit($review->comment, 50) }}</td>
                                    <td id="review-status-{{ $review->id }}">
                                        @if($review->status == 'pending')
                                            <span class="badge bg-warning">Đang chờ</span>
                                        @elseif($review->status == 'approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @else
                                            <span class="badge bg-danger">Đã từ chối</span>
                                        @endif
                                    </td>
                                    <td>{{ $review->created_at->format('d-m-Y H:i:s') }}</td>
                                    <td>
                                        <button class="btn btn-info btn-sm view-review-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewReviewModal"
                                            data-id="{{ $review->id }}"
                                            data-customer-name="{{ $review->customer->name ?? 'N/A' }}"
                                            data-customer-email="{{ $review->customer->email ?? 'Khách vãng lai' }}"
                                            data-product-name="{{ $review->product->name ?? 'Sản phẩm đã xóa' }}"
                                            data-rating="{{ $review->rating }}"
                                            data-comment="{{ $review->comment }}"
                                            data-status="{{ $review->status }}"
                                            data-created-at="{{ $review->created_at->format('d-m-Y H:i:s') }}"
                                            data-product-link="{{ route('products.show', $review->product->id) }}"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-{{ $review->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $review->id }}">
                                                @if($review->status != 'approved')
                                                <li><a class="dropdown-item update-review-status-btn" href="#"
                                                       data-id="{{ $review->id }}"
                                                       data-url="{{ route('admin.content.reviews.updateStatus', $review->id) }}"
                                                       data-status="approved">Duyệt</a></li>
                                                @endif
                                                @if($review->status != 'pending')
                                                <li><a class="dropdown-item update-review-status-btn" href="#"
                                                       data-id="{{ $review->id }}"
                                                       data-url="{{ route('admin.content.reviews.updateStatus', $review->id) }}"
                                                       data-status="pending">Chờ duyệt</a></li>
                                                @endif
                                                @if($review->status != 'rejected')
                                                <li><a class="dropdown-item update-review-status-btn" href="#"
                                                       data-id="{{ $review->id }}"
                                                       data-url="{{ route('admin.content.reviews.updateStatus', $review->id) }}"
                                                       data-status="rejected">Từ chối</a></li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger delete-review-btn" href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#deleteReviewModal"
                                                       data-id="{{ $review->id }}"
                                                       data-url="{{ route('admin.content.reviews.destroy', $review->id) }}"
                                                       data-customer-name="{{ $review->customer->name ?? 'N/A' }}"
                                                       data-product-name="{{ $review->product->name ?? 'Sản phẩm đã xóa' }}"
                                                       >Xóa</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Không có đánh giá nào được tìm thấy.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="d-flex justify-content-center">
                        {{ $reviews->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals for Review Management --}}
@include('admin.content.review.modals.view_review_modal')
@include('admin.content.review.modals.delete_review_modal')
@endsection

@push('scripts')
{{-- Include your custom JS for review management --}}
<script src="{{ asset('assets_admin/js/review_manager.js') }}"></script>
@endpush
