@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục')

@section('content')
    <div id="adminCategoriesPage">

        {{-- Content Header --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-tags-fill me-2"></i>Danh mục Sản phẩm</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý sản phẩm</li>
                            <li class="breadcrumb-item active">Danh mục</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>Danh sách Danh mục</h2>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createCategoryModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Danh mục mới
                        </button>
                    </div>
                    <div class="card-body">
                        {{-- Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                        <div class="row g-3 mb-3 align-items-center">
                            {{-- Thanh tìm kiếm --}}
                            <div class="col-md-4 col-lg-3">
                                <div class="input-group">
                                    <input type="text" id="categorySearchInput" class="form-control form-control-sm"
                                        placeholder="Tìm kiếm tên, mô tả...">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="categorySearchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Bộ lọc --}}
                            <div class="col-md-4 col-lg-3">
                                <select id="categoryFilterSelect" class="form-select form-select-sm">
                                    <option value="all">Tất cả trạng thái</option>
                                    <option value="active_only">Hoạt động</option>
                                    <option value="inactive_only">Đã ẩn</option>
                                </select>
                            </div>

                            {{-- Sắp xếp --}}
                            <div class="col-md-4 col-lg-3">
                                <select id="categorySortSelect" class="form-select form-select-sm">
                                    <option value="latest">Mới nhất</option>
                                    <option value="oldest">Cũ nhất</option>
                                    <option value="name_asc">Tên (A-Z)</option>
                                    <option value="name_desc">Tên (Z-A)</option>
                                </select>
                            </div>

                            {{-- Nút hành động hàng loạt --}}
                            <div class="col-12 col-lg-3 text-lg-end">
                                <button class="btn btn-danger btn-sm me-2 mb-2 mb-lg-0" id="bulkDeleteBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                    <i class="bi bi-trash-fill me-1"></i> Xóa (<span id="selectedCountDelete">0</span>)
                                </button>
                                <button class="btn btn-info btn-sm mb-2 mb-lg-0" id="bulkToggleStatusBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#bulkToggleStatusModal">
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
                                            <input type="checkbox" id="selectAllCategories"> {{-- ID khác để tránh xung đột
                                            --}}
                                        </th>
                                        <th scope="col" style="width:5%">STT</th>
                                        <th scope="col" style="width: 25%;">Tên Danh mục</th> {{-- Giảm width để vừa đủ --}}
                                        <th scope="col">Mô tả</th>
                                        <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                        <th scope="col" class="text-center" style="width: 20%;">Hành động</th> {{-- Giảm
                                        width --}}
                                    </tr>
                                </thead>
                                <tbody id="categories-table-body">
                                    {{-- Dữ liệu sẽ được tải ở đây bởi Controller và AJAX --}}
                                    @include('admin.productManagement.category.partials._category_table_rows', ['categories' => $categories])
                                </tbody>
                            </table>
                        </div>

                        {{-- Phân trang --}}
                        @if ($categories->hasPages())
                            <div class="mt-3 d-flex justify-content-center" id="pagination-links">
                                {{ $categories->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Include modals --}}
    @include('admin.productManagement.category.modals.create_category_modal')
    @include('admin.productManagement.category.modals.update_category_modal')
    @include('admin.productManagement.category.modals.delete_category_modal')
    @include('admin.productManagement.category.modals.view_category_modal')
    {{-- NEW: Modal for Bulk Toggle Status --}}
    @include('admin.productManagement.category.modals.modal_bulk_toggle_status')

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/category_manager.js') }}"></script>
@endpush