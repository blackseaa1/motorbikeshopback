{{-- File: resources/views/admin/productManagement/inventory/inventory.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Quản lý Tồn Kho')

@section('content')
    <div id="adminInventoryPage">
       
         <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-tags-fill me-2"></i>Quản lý Tồn Kho</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý sản phẩm</li>
                            <li class="breadcrumb-item active">Quản lý Tồn Kho</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0 text-primary"><i class="bi bi-funnel me-2"></i>Sản phẩm sắp hết hàng</h2>
            </div>
            <div class="card-body">
                {{-- THÊM PHẦN TÌM KIẾM VÀ LỌC VÀO ĐÂY --}}
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inventorySearchInput" placeholder="Tìm kiếm theo tên sản phẩm..." aria-label="Tìm kiếm sản phẩm">
                            <button class="btn btn-primary" type="button" id="inventorySearchBtn">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="inventoryCategoryFilter">
                            <option value="">Lọc theo danh mục</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="inventoryBrandFilter">
                            <option value="">Lọc theo thương hiệu</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex justify-content-end">
                        <button class="btn btn-outline-secondary" type="button" id="inventoryClearFiltersBtn" title="Xóa bộ lọc">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
                {{-- KẾT THÚC PHẦN TÌM KIẾM VÀ LỌC --}}

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 10%;">Hình Ảnh</th>
                                <th scope="col" style="width: 30%;">Tên Sản Phẩm</th>
                                <th scope="col" style="width: 15%;">Danh Mục</th>
                                <th scope="col" style="width: 15%;">Thương Hiệu</th>
                                <th scope="col" class="text-center" style="width: 10%;">Tồn Kho</th>
                                <th scope="col" class="text-end" style="width: 10%;">Giá Bán</th>
                                <th scope="col" class="text-center" style="width: 10%;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table-body">
                            @include('admin.productManagement.inventory.partials._inventory_table_rows', ['lowStockProducts' => $lowStockProducts])
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-center" id="inventory-pagination-links">
                    {{ $lowStockProducts->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Viewing Product Details (giữ nguyên) --}}
    <div class="modal fade" id="viewProductDetailsModal" tabindex="-1" aria-labelledby="viewProductDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewProductDetailsModalLabel"><i class="bi bi-eye me-2"></i>Chi Tiết Sản
                        Phẩm: <span id="viewProductName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 text-center">
                            <img id="viewProductImage" src="https://placehold.co/300x300/EFEFEF/AAAAAA&text=Product"
                                alt="Product Image" class="img-fluid rounded border mb-3"
                                style="max-height: 250px; object-fit: contain;">
                        </div>
                        <div class="col-md-7">
                            <p class="mb-1"><strong>ID:</strong> <span id="viewProductId"></span></p>
                            <p class="mb-1"><strong>Tên:</strong> <span id="viewProductNameDetail"></span></p>
                            <p class="mb-1"><strong>Giá:</strong> <span id="viewProductPrice"></span></p>
                            <p class="mb-1"><strong>Tồn kho:</strong> <span id="viewProductStock"></span></p>
                            <p class="mb-1"><strong>Danh mục:</strong> <span id="viewProductCategory"></span></p>
                            <p class="mb-1"><strong>Thương hiệu:</strong> <span id="viewProductBrand"></span></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> <span id="viewProductStatusBadge"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h6>Mô tả:</h6>
                    <p id="viewProductDescription" class="text-muted"></p>
                    <hr>
                    <h6>Thông số kỹ thuật:</h6>
                    <p id="viewProductSpecifications" class="text-muted"></p>
                    <hr>
                    <h6>Dòng xe tương thích:</h6>
                    <div id="viewProductVehicleModels" class="d-flex flex-wrap gap-2">
                        <span class="text-muted">Đang tải...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-warning open-full-edit-product-modal-btn" data-bs-dismiss="modal">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa đầy đủ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Updating Quantity (giữ nguyên) --}}
    <div class="modal fade" id="updateQuantityModal" tabindex="-1" aria-labelledby="updateQuantityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="updateQuantityModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                        số lượng tồn kho</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateQuantityForm">
                        <input type="hidden" id="updateQuantityProductId">
                        <p class="mb-3">Cập nhật số lượng tồn kho cho sản phẩm: <strong><span
                                    id="updateQuantityProductName"></span></strong></p>
                        <div class="mb-3">
                            <label for="newStockQuantity" class="form-label">Số lượng mới</label>
                            <input type="number" class="form-control" id="newStockQuantity" min="0" value="0">
                            <div class="text-danger mt-1" id="updateQuantityError"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="confirmUpdateQuantityBtn"><i
                            class="bi bi-save me-2"></i>Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Thêm modal chỉnh sửa sản phẩm đầy đủ vào đây --}}
    @include('admin.productManagement.product.modals.update_product', [
        'categories' => $categories, // Truyền biến categories
        'brands' => $brands, // Truyền biến brands
        'vehicleBrands' => $vehicleBrands, // Truyền biến vehicleBrands
    ])
@endsection

@push('styles')
    {{-- Custom styles for this page if needed --}}
@endpush

@push('scripts')
    <script src="{{ asset('assets_admin/js/inventory_manager.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (typeof window.showAppLoader !== 'function' || typeof window.hideAppLoader !== 'function' || typeof window.showAppInfoModal !== 'function') {
                console.error("Lỗi: Các hàm tiện ích (showAppLoader, hideAppLoader, showAppInfoModal) không được định nghĩa. Đảm bảo admin_layout.js đã được tải và chạy.");
                window.showAppLoader = () => console.log('Loader shown (placeholder)');
                window.hideAppLoader = () => console.log('Loader hidden (placeholder)');
                window.showAppInfoModal = (msg, title, type) => alert(`${title}: ${msg}`);
            }
            window.initializeInventoryManager(window.showAppLoader, window.hideAppLoader, window.showAppInfoModal);
        });
    </script>
@endpush