{{-- resources/views/admin/productManagement/product/products.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Quản lý Sản phẩm')


@section('content')
    <div id="adminProductsPage">
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

        <div class="container-fluid">
            <div class="card mb-4">
                {{-- Card Header không đổi --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0"><i class="bi bi-box-seam-fill me-2"></i>Danh sách Sản phẩm</h2>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tạo Sản phẩm mới
                    </button>
                </div>
                <div class="card-body">
                    {{-- NEW: Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                    <div class="row g-3 mb-3 align-items-center">
                        {{-- Thanh tìm kiếm --}}
                        <div class="col-md-4 col-lg-3">
                            <div class="input-group">
                                <input type="text" id="productSearchInput" class="form-control form-control-sm"
                                    placeholder="Tìm kiếm tên, mô tả...">
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="productSearchBtn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Bộ lọc trạng thái --}}
                        <div class="col-md-4 col-lg-3">
                            <select id="productFilterSelect" class="form-select form-select-sm">
                                <option value="all" {{ !$status ? 'selected' : '' }}>Tất cả trạng thái</option>
                                <option value="active_only" {{ $status === 'active_only' ? 'selected' : '' }}>Đang bán
                                </option>
                                <option value="inactive_only" {{ $status === 'inactive_only' ? 'selected' : '' }}>Ngừng bán
                                </option>
                                <option value="trashed" {{ $status === 'trashed' ? 'selected' : '' }}>Trong thùng rác</option>
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

                        {{-- Nút hành động hàng loạt --}}
                        <div class="col-12 col-lg-3 text-lg-end">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                    id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                    Hành động hàng loạt (<span id="selectedCountBulk">0</span>)
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                    <li>
                                        <button class="dropdown-item" id="bulkToggleStatusBtn" data-bs-toggle="modal"
                                            data-bs-target="#bulkToggleStatusProductsModal">
                                            <i class="bi bi-arrow-repeat me-1"></i> Chuyển trạng thái
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-success" id="bulkRestoreBtn"
                                            data-bs-toggle="modal" data-bs-target="#bulkRestoreProductsModal">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Khôi phục
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" id="bulkDeleteBtn" data-bs-toggle="modal"
                                            data-bs-target="#bulkDeleteProductsModal">
                                            <i class="bi bi-trash me-1"></i> Xóa mềm
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" id="bulkForceDeleteBtn"
                                            data-bs-toggle="modal" data-bs-target="#bulkForceDeleteProductsModal">
                                            <i class="bi bi-trash-fill me-1"></i> Xóa vĩnh viễn
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:3%">
                                        <input type="checkbox" id="selectAllProducts">
                                    </th>
                                    <th scope="col" style="width:5%">STT</th>
                                    <th scope="col" style="width: 8%;">Ảnh</th>
                                    <th scope="col">Tên Sản phẩm</th>
                                    <th scope="col" style="width:12%">Danh mục</th>
                                    <th scope="col" style="width:12%">Thương hiệu</th>
                                    <th scope="col" style="width:10%">Giá</th>
                                    <th scope="col" style="width:8%">Tồn kho</th>
                                    <th scope="col" style="width:10%">Trạng thái</th>
                                    <th scope="col" class="text-center" style="width:15%">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                {{-- Products will be loaded here by Controller and AJAX --}}
                                @include('admin.productManagement.product.partials._product_table_rows', ['products' => $products, 'loopIndex' => 0, 'startIndex' => $products->firstItem() - 1])
                            </tbody>
                        </table>
                    </div>
                    @if ($products->hasPages())
                        <div class="mt-3 d-flex justify-content-center" id="pagination-links">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Include Modals --}}
        @include('admin.productManagement.product.modals.create_product')
        @include('admin.productManagement.product.modals.update_product')
        @include('admin.productManagement.product.modals.view_product')
        {{-- Unified Delete/Bulk Delete Modal --}}
        @include('admin.productManagement.product.modals.delete_product_confirm')
        {{-- Unified Force Delete/Bulk Force Delete Modal --}}
        @include('admin.productManagement.product.modals.force_delete_product_confirm')
        {{-- Unified Restore/Bulk Restore Modal --}}
        @include('admin.productManagement.product.modals.restore_product_confirm')
        {{-- New Modal for Bulk Toggle Status --}}
        @include('admin.productManagement.product.modals.modal_bulk_toggle_status_products')

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/product_management.js') }}"></script>
@endpush