@extends('admin.layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div id="adminProductsPage">
        <header class="content-header">
            <h1><i class="bi bi-tags-fill me-2"></i>Sản phẩm</h1>
        </header>

        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0"><i class="bi bi-box-seam-fill me-2"></i>Danh sách Sản phẩm</h2>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tạo Sản phẩm mới
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
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
                                    <tr id="product-row-{{ $product->id }}">
                                        <td>{{ $product->id }}</td>
                                        <td>
                                            <img src="{{ $product->images->first() ? Storage::url($product->images->first()->image_path) : 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=N/A' }}"
                                                alt="{{ $product->name }}" class="img-thumbnail img-thumbnail-small">
                                        </td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                                        <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($product->price, 0, ',', '.') }} đ</td>
                                        <td>{{ $product->stock_quantity }}</td>
                                        <td>
                                            @if ($product->status == 'active')
                                                <span class="badge bg-success">Hoạt động</span>
                                            @else
                                                <span class="badge bg-secondary">Tạm ẩn</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm btn-action btn-view"
                                                data-id="{{ $product->id }}" title="Xem chi tiết">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm btn-action btn-edit"
                                                data-id="{{ $product->id }}" title="Cập nhật">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-action btn-delete"
                                                data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                title="Xóa">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Chưa có sản phẩm nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>

        @include('admin.productManagement.modals.create_product')
        @include('admin.productManagement.modals.update_product')
        @include('admin.productManagement.modals.delete_product')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/product_management.js') }}"></script>
@endpush