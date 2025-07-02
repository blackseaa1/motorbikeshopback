{{-- File: resources/views/admin/productManagement/product/products.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div id="adminProductsPage">
        {{-- Header của trang --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-tags-fill me-2"></i>Sản phẩm</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý sản phẩm</li>
                            <li class="breadcrumb-item active">Danh sách sản phẩm</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nội dung chính --}}
        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0"><i class="bi bi-box-seam-fill me-2"></i>Danh sách Sản phẩm</h2>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tạo Sản phẩm mới
                    </button>
                </div>
                <div class="card-body">
                    {{-- Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                    <div class="row g-3 mb-3 align-items-center">
                        {{-- Thanh tìm kiếm --}}
                        <div class="col-md-4 col-lg-3">
                            <div class="input-group">
                                <input type="text" id="productSearchInput" class="form-control form-control-sm"
                                    placeholder="Tìm kiếm tên sản phẩm...">
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="productSearchBtn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Bộ lọc --}}
                        <div class="col-md-4 col-lg-3">
                            <select id="productFilterSelect" class="form-select form-select-sm">
                                <option value="all">Tất cả trạng thái</option>
                                <optgroup label="Trạng thái bán hàng">
                                    <option value="active">Đang bán</option>
                                    <option value="inactive">Ngừng bán</option>
                                </optgroup>
                                <optgroup label="Trạng thái tồn kho">
                                    <option value="out_of_stock">Hết hàng</option>
                                    <option value="low_stock">Sắp hết hàng (&lt;10)</option>
                                </optgroup>
                                <optgroup label="Trạng thái hệ thống">
                                    <option value="trashed">Trong thùng rác</option>
                                </optgroup>
                            </select>
                        </div>


                        {{-- Sắp xếp --}}
                        <div class="col-md-4 col-lg-3">
                            <select id="productSortSelect" class="form-select form-select-sm">
                                <option value="latest">Mới nhất</option>
                                <option value="oldest">Cũ nhất</option>
                                <option value="name_asc">Tên (A-Z)</option>
                                <option value="name_desc">Tên (Z-A)</option>
                                <option value="price_asc">Giá (Thấp đến Cao)</option>
                                <option value="price_desc">Giá (Cao đến Thấp)</option>
                                <option value="stock_asc">Tồn kho (Thấp đến Cao)</option>
                                <option value="stock_desc">Tồn kho (Cao đến Thấp)</option>
                            </select>
                        </div>

                        {{-- Nút hành động hàng loạt (Điều kiện hiển thị) --}}
                        <div class="col-12 col-lg-3 text-lg-end">
                            {{-- Nút cho trạng thái "Tất cả" --}}
                            <div id="bulkActionsNormal" class="{{ $status_query_param === 'trashed' ? 'd-none' : '' }}">
                                <button class="btn btn-danger btn-sm me-2 mb-2 mb-lg-0" id="bulkSoftDeleteBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#bulkDeleteProductModal">
                                    <i class="bi bi-trash-fill me-1"></i> Xóa mềm (<span
                                        id="selectedCountSoftDelete">0</span>)
                                </button>
                                <button class="btn btn-info btn-sm mb-2 mb-lg-0" id="bulkToggleStatusBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#bulkToggleStatusProductModal">
                                    <i class="bi bi-arrow-repeat me-1"></i> Trạng thái (<span
                                        id="selectedCountToggle">0</span>)
                                </button>
                            </div>

                            {{-- Nút cho trạng thái "Thùng rác" --}}
                            <div id="bulkActionsTrashed" class="{{ $status_query_param !== 'trashed' ? 'd-none' : '' }}">
                                <button class="btn btn-success btn-sm me-2 mb-2 mb-lg-0" id="bulkRestoreBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#bulkRestoreProductModal">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Khôi phục (<span
                                        id="selectedCountRestore">0</span>)
                                </button>
                                <button class="btn btn-danger btn-sm mb-2 mb-lg-0" id="bulkForceDeleteBtn" disabled
                                    data-bs-toggle="modal" data-bs-target="#bulkForceDeleteProductModal">
                                    <i class="bi bi-trash-fill me-1"></i> Xóa vĩnh viễn (<span
                                        id="selectedCountForceDelete">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Các nút "Tất cả" và "Thùng rác" cũ (nếu muốn giữ làm tab) --}}
                    {{-- These are redundant if productFilterSelect handles the 'trashed' filter.
                         They can still be used as simple links to trigger the filter. --}}
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('admin.productManagement.products.index') }}" id="allProductsTab"
                            class="btn btn-sm {{ !$status_query_param ? 'btn-dark' : 'btn-outline-dark' }} me-2"><i
                                class="bi bi-list-ul me-1"></i> Tất cả</a>
                        <a href="{{ route('admin.productManagement.products.index', ['status' => 'trashed']) }}"
                            id="trashedProductsTab"
                            class="btn btn-sm {{ $status_query_param === 'trashed' ? 'btn-dark' : 'btn-outline-dark' }}"><i
                                class="bi bi-trash me-1"></i> Thùng rác</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:3%">
                                        <input type="checkbox" id="selectAllProducts">
                                    </th>
                                    <th scope="col" style="width:5%">STT</th>
                                    <th scope="col">Ảnh</th>
                                    <th scope="col">Tên Sản phẩm</th>
                                    <th scope="col">Danh mục</th>
                                    <th scope="col">Thương hiệu</th>
                                    <th scope="col">Giá</th>
                                    <th scope="col">Tồn kho</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                @include('admin.productManagement.product.partials._product_table_rows', ['products' => $products])
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang --}}
                    @if ($products->hasPages())
                        <div class="mt-3 d-flex justify-content-center" id="pagination-links">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Include tất cả các modals cần thiết cho trang --}}
        @include('admin.productManagement.product.modals.create_product')
        @include('admin.productManagement.product.modals.update_product')
        @include('admin.productManagement.product.modals.view_product')
        {{-- FIX: Use confirm_delete_product for single delete --}}
        @include('admin.productManagement.product.modals.confirm_delete_product')
        @include('admin.productManagement.product.modals.confirm_force_delete_product')
        @include('admin.productManagement.product.modals.confirm_restore_product')
        @include('admin.productManagement.product.modals.modal_bulk_toggle_status_product')
        @include('admin.productManagement.product.modals.modal_bulk_delete_product')
        @include('admin.productManagement.product.modals.modal_bulk_restore_product')
        @include('admin.productManagement.product.modals.modal_bulk_force_delete_product')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/product_management.js') }}"></script>
@endpush
