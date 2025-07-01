@extends('admin.layouts.app')

@section('title', 'Quản lý Đánh giá')

@section('content')
    <div id="adminReviewsPage"> {{-- Thêm ID tương tự như adminBrandsPage --}}
        {{-- Content Header --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-star-half me-2"></i>Đánh giá Sản phẩm</h1> {{-- Icon phù hợp với Reviews --}}
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý nội dung</li>
                            <li class="breadcrumb-item active">Đánh giá</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <section class="content">
            <div class="container-fluid">
                <!-- Search, Filter and Sort Section -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-funnel me-2"></i>Tìm kiếm, Lọc & Sắp xếp</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.content.reviews.index') }}" method="GET" class="row">
                            <div class="col-md-4 mb-3">
                                <label for="search" class="form-label">Tìm kiếm (Bình luận, Khách hàng, Sản phẩm)</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Tìm kiếm đánh giá..." value="{{ $selectedFilters['search'] ?? '' }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="status_filter" class="form-label">Lọc theo Trạng thái</label>
                                <select name="status_filter" id="status_filter" class="form-control">
                                    <option value="all" {{ ($selectedFilters['status_filter'] ?? '') == 'all' ? 'selected' : '' }}>Tất cả</option>
                                    @foreach ($reviewStatuses as $key => $value)
                                        <option value="{{ $key }}" {{ ($selectedFilters['status_filter'] ?? '') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="rating_filter" class="form-label">Lọc theo Đánh giá</label>
                                <select name="rating_filter" id="rating_filter" class="form-control">
                                    <option value="all" {{ ($selectedFilters['rating_filter'] ?? '') == 'all' ? 'selected' : '' }}>Tất cả</option>
                                    @foreach ($reviewRatings as $rating)
                                        <option value="{{ $rating }}" {{ ($selectedFilters['rating_filter'] ?? '') == $rating ? 'selected' : '' }}>{{ $rating }} sao</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">Áp dụng</button>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="sort_by" class="form-label">Sắp xếp theo</label>
                                <select name="sort_by" id="sort_by" class="form-control" onchange="this.form.submit()">
                                    <option value="created_at_desc" {{ ($selectedFilters['sort_by'] ?? '') == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                                    <option value="created_at_asc" {{ ($selectedFilters['sort_by'] ?? '') == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
                                    <option value="rating_desc" {{ ($selectedFilters['sort_by'] ?? '') == 'rating_desc' ? 'selected' : '' }}>Đánh giá cao nhất</option>
                                    <option value="rating_asc" {{ ($selectedFilters['sort_by'] ?? '') == 'rating_asc' ? 'selected' : '' }}>Đánh giá thấp nhất</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTales Example -->
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center"> {{-- Thêm d-flex, justify-content-between, align-items-center --}}
                        <h2 class="h5 mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách Đánh giá</h2> {{-- Dùng h2 h5 mb-0 --}}
                        {{-- Không có nút "Tạo mới" cho Reviews ở đây vì chúng được tạo bởi khách hàng --}}
                    </div>
                    <div class="card-body">
                        @if ($reviews->isEmpty())
                            <div class="alert alert-info" role="alert"> {{-- Dùng alert alert-info cho thông báo trống --}}
                                <i class="bi bi-info-circle me-2"></i>Hiện chưa có đánh giá nào được tìm thấy.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle"> {{-- Thêm table-hover và align-middle --}}
                                    <thead class="table-light"> {{-- Dùng table-light --}}
                                        <tr>
                                            <th scope="col" class="d-none">ID Khách hàng</th>
                                            <th scope="col" class="d-none">ID Sản phẩm</th>
                                            <th scope="col">Khách hàng</th>
                                            <th scope="col">Sản phẩm</th>
                                            <th scope="col" class="text-center">Đánh giá</th>
                                            <th scope="col" class="d-none">Bình luận</th>
                                            <th scope="col" class="text-center">Trạng thái</th>
                                            <th scope="col">Ngày tạo</th>
                                            <th scope="col" class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviews as $review)
                                        <tr id="review-row-{{ $review->customer_id }}-{{ $review->product_id }}"> {{-- ID hàng cho JS --}}
                                            <td class="d-none">{{ $review->customer_id }}</td>
                                            <td class="d-none">{{ $review->product_id }}</td>
                                            <td>{{ $review->customer->name ?? 'N/A' }} ({{ $review->customer->email ?? 'N/A' }})</td>
                                            <td>{{ $review->product->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $review->rating)
                                                        <i class="bi bi-star-fill text-warning"></i>
                                                    @else
                                                        <i class="bi bi-star text-warning"></i>
                                                    @endif
                                                @endfor
                                            </td>
                                            <td class="d-none">{{ Str::limit($review->comment, 50) }}</td>
                                            <td class="text-center status-cell" id="review-status-{{ $review->customer_id }}-{{ $review->product_id }}">
                                                <span class="badge {{ $review->status_badge_class }}">{{ $review->status_text }}</span>
                                            </td>
                                            <td>{{ $review->created_at->format('d-m-Y H:i:s') }}</td>
                                            <td class="text-center action-buttons"> {{-- Thêm action-buttons --}}
                                                <button type="button" class="btn btn-sm btn-success btn-view-review" {{-- Đổi class --}}
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewReviewModal"
                                                        data-customer-id="{{ $review->customer_id }}"
                                                        data-product-id="{{ $review->product_id }}"
                                                        data-customer-name="{{ $review->customer->name ?? 'N/A' }}"
                                                        data-customer-email="{{ $review->customer->email ?? 'N/A' }}"
                                                        data-product-name="{{ $review->product->name ?? 'N/A' }}"
                                                        data-product-link="{{ route('products.show', $review->product->slug ?? '#') }}" {{-- Sửa tên route --}}
                                                        data-rating="{{ $review->rating }}"
                                                        data-comment="{{ $review->comment }}"
                                                        data-status="{{ $review->status }}"
                                                        data-created-at="{{ $review->created_at->format('d-m-Y H:i:s') }}"
                                                        title="Xem chi tiết">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                                @if ($review->status == \App\Models\Review::STATUS_PENDING)
                                                    <button type="button" class="btn btn-sm btn-primary update-review-status-btn" {{-- Đổi màu --}}
                                                            data-customer-id="{{ $review->customer_id }}"
                                                            data-product-id="{{ $review->product_id }}"
                                                            data-status="{{ \App\Models\Review::STATUS_APPROVED }}"
                                                            data-url="{{ route('admin.content.reviews.updateStatus', ['customer_id' => $review->customer_id, 'product_id' => $review->product_id]) }}"
                                                            title="Duyệt">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary update-review-status-btn" {{-- Đổi màu --}}
                                                            data-customer-id="{{ $review->customer_id }}"
                                                            data-product-id="{{ $review->product_id }}"
                                                            data-status="{{ \App\Models\Review::STATUS_REJECTED }}"
                                                            data-url="{{ route('admin.content.reviews.updateStatus', ['customer_id' => $review->customer_id, 'product_id' => $review->product_id]) }}"
                                                            title="Từ chối">
                                                        <i class="bi bi-x-circle-fill"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-review" {{-- Đổi class --}}
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteReviewModal"
                                                        data-customer-id="{{ $review->customer_id }}"
                                                        data-product-id="{{ $review->product_id }}"
                                                        data-customer-name="{{ $review->customer->name ?? 'N/A' }}"
                                                        data-product-name="{{ $review->product->name ?? 'N/A' }}"
                                                        data-url="{{ route('admin.content.reviews.destroy', ['customer_id' => $review->customer_id, 'product_id' => $review->product_id]) }}"
                                                        title="Xóa">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- Hiển thị link phân trang --}}
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $reviews->links('admin.vendor.pagination') }} {{-- Đảm bảo sử dụng phân trang tùy chỉnh --}}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Include Modals --}}
    @include('admin.content.review.modals.view_review_modal')
    @include('admin.content.review.modals.delete_review_modal')
@endsection

@push('scripts')
<script src="{{ asset('assets_admin/js/review_manager.js') }}"></script>
@endpush
