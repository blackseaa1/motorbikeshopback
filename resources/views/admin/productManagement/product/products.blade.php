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
                    {{-- Nút Thùng rác không đổi --}}
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('admin.productManagement.products.index') }}" class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-dark' }} me-2"><i class="bi bi-list-ul me-1"></i> Tất cả</a>
                        <a href="{{ route('admin.productManagement.products.index', ['status' => 'trashed']) }}" class="btn btn-sm {{ $status === 'trashed' ? 'btn-dark' : 'btn-outline-dark' }}"><i class="bi bi-trash me-1"></i> Thùng rác</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            {{-- thead đã sửa đổi --}}
                            <thead class="table-light">
                                <tr>
                                    {{-- SỬA ĐỔI: Thay ID bằng STT --}}
                                    <th scope="col">STT</th>
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
                                @forelse ($products as $product)
                                    <tr id="product-row-{{ $product->id }}" class="{{ $product->trashed() ? 'row-trashed' : ($product->status === 'inactive' ? 'row-inactive' : '') }}">
                                        {{-- SỬA ĐỔI: Thay ID bằng công thức tính STT --}}
                                        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-thumbnail img-thumbnail-small">
                                        </td>
                                        <td class="product-name">{{ $product->name }}</td>
                                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                                        <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                        <td>{{ $product->formatted_price }}</td>
                                        <td>{{ $product->stock_quantity }}</td>
                                        <td class="status-cell">
                                            @if($product->trashed())
                                                <span class="badge bg-danger">Trong thùng rác</span>
                                            @else
                                                <span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center action-buttons">
                                            @if ($product->trashed())
                                                <button class="btn btn-success btn-sm btn-action btn-restore-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Khôi phục"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                <button class="btn btn-danger btn-sm btn-action btn-force-delete-product" data-delete-url="{{ route('admin.productManagement.products.forceDelete', $product->id) }}" data-name="{{ $product->name }}" title="Xóa vĩnh viễn"><i class="bi bi-trash-fill"></i></button>
                                            @else
                                                <button class="btn btn-info btn-sm btn-action btn-view" data-id="{{ $product->id }}" title="Xem chi tiết"><i class="bi bi-eye-fill"></i></button>
                                                <button class="btn btn-warning btn-sm btn-action btn-edit" data-id="{{ $product->id }}" title="Cập nhật"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-sm btn-action toggle-status-product-btn {{ $product->status === 'active' ? 'btn-secondary' : 'btn-success' }}" data-url="{{ route('admin.productManagement.products.toggleStatus', $product) }}" title="{{ $product->status === 'active' ? 'Dừng bán' : 'Mở bán' }}"><i class="bi {{ $product->status === 'active' ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }}"></i></button>
                                                <button class="btn btn-danger btn-sm btn-action btn-delete" data-id="{{ $product->id }}" data-name="{{ $product->name }}" title="Xóa"><i class="bi bi-trash"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center">Chưa có sản phẩm nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($products->hasPages())
                        <div class="mt-3 d-flex justify-content-end">{{ $products->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Include các modal không đổi --}}
        @include('admin.productManagement.product.modals.create_product')
        @include('admin.productManagement.product.modals.update_product')
        @include('admin.productManagement.product.modals.view_product')
        @include('admin.productManagement.product.modals.confirm_delete_product')
        @include('admin.productManagement.product.modals.confirm_force_delete_product')
        @include('admin.productManagement.product.modals.confirm_restore_product')
    </div>
@endsection

@push('scripts')
    {{-- Giữ nguyên script cũ --}}
    <script src="{{ asset('assets_admin/js/product_management.js') }}"></script>
@endpush