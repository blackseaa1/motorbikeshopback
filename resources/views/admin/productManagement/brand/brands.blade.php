@extends('admin.layouts.app')

@section('title', 'Quản lý Thương hiệu')

@section('content')
    <div id="adminBrandsPage">
        {{-- Content Header --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-bookmark-star-fill me-2"></i>Thương hiệu Sản phẩm</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý sản phẩm</li>
                            <li class="breadcrumb-item active">Thương hiệu</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card mb-4 shadow-sm"> {{-- Thêm shadow-sm để đồng bộ --}}
                    <div class="card-header bg-light d-flex justify-content-between align-items-center"> {{-- Thêm bg-light --}}
                        <h2 class="h5 mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>Danh sách Thương hiệu</h2> {{-- Thêm text-primary --}}
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createBrandModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Thương hiệu mới
                        </button>
                    </div>
                    <div class="card-body">
                        {{-- Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                        <div class="row g-3 mb-3 align-items-center">
                            {{-- Thanh tìm kiếm --}}
                            <div class="col-md-4 col-lg-3">
                                <div class="input-group">
                                    <input type="text" id="brandSearchInput" class="form-control form-control-sm"
                                        placeholder="Tìm kiếm tên, mô tả...">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="brandSearchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Bộ lọc --}}
                            <div class="col-md-4 col-lg-3">
                                <select id="brandFilterSelect" class="form-select form-select-sm">
                                    <option value="all">Tất cả trạng thái</option>
                                    <option value="active_only">Hoạt động</option>
                                    <option value="inactive_only">Đã ẩn</option>
                                </select>
                            </div>

                            {{-- Sắp xếp --}}
                            <div class="col-md-4 col-lg-3">
                                <select id="brandSortSelect" class="form-select form-select-sm">
                                    <option value="latest">Mới nhất</option>
                                    <option value="oldest">Cũ nhất</option>
                                    <option value="name_asc">Tên (A-Z)</option>
                                    <option value="name_desc">Tên (Z-A)</option>
                                </select>
                            </div>

                            {{-- Nút hành động hàng loạt --}}
                            <div class="col-12 col-lg-3 text-lg-end">
                                <button class="btn btn-danger btn-sm me-2 mb-2 mb-lg-0" id="bulkDeleteBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#deleteBrandModal">
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
                                        <th scope="col" style="width:3%"> {{-- Tăng width cho checkbox --}}
                                            <input type="checkbox" id="selectAllBrands"> {{-- ID khác để tránh xung đột --}}
                                        </th>
                                        <th scope="col" style="width:5%">STT</th>
                                        <th scope="col" style="width: 10%;" class="text-center">Logo</th>
                                        <th scope="col" style="width: 20%;">Tên Thương hiệu</th> {{-- Giảm width --}}
                                        <th scope="col">Mô tả</th>
                                        <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                        <th scope="col" class="text-center" style="width: 15%;">Hành động</th> {{-- Giảm width --}}
                                    </tr>
                                </thead>
                                <tbody id="brands-table-body"> {{-- Đổi ID --}}
                                    {{-- Dữ liệu sẽ được tải ở đây bởi Controller và AJAX --}}
                                    @include('admin.productManagement.brand.partials._brand_table_rows', ['brands' => $brands])
                                </tbody>
                            </table>
                        </div>

                        {{-- Hiển thị link phân trang --}}
                        @if ($brands->hasPages())
                            <div class="mt-3 d-flex justify-content-center" id="pagination-links">
                                {{ $brands->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Include Modals --}}
    @include('admin.productManagement.brand.modals.create_brand_modal')
    @include('admin.productManagement.brand.modals.update_brand_modal')
    @include('admin.productManagement.brand.modals.delete_brand_modal')
    @include('admin.productManagement.brand.modals.view_brand_modal')
    {{-- NEW: Modal for Bulk Toggle Status --}}
    @include('admin.productManagement.brand.modals.modal_bulk_toggle_status')

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/brand_manager.js') }}"></script>
@endpush