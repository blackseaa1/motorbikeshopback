@extends('admin.layouts.app')

@section('title', 'Quản lý Thương hiệu')
@section('content') <header class="content-header">
        <h1><i class="bi bi-slack me-2"></i>Thương Hiệu</h1>
    </header>

    <div class="container-fluid">
        {{-- Section for displaying messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Có lỗi xảy ra! Vui lòng kiểm tra lại dữ liệu.</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-bookmark-star-fill me-2"></i>Quản lý Thương hiệu Sản phẩm</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBrandModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Thương hiệu mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 5%;">ID</th>
                                <th scope="col" style="width: 10%;">Logo</th>
                                <th scope="col" style="width: 25%;">Tên Thương hiệu</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col" class="text-center" style="width: 15%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td>
                                        <img src="{{ $brand->logo_url ? asset('storage/' . $brand->logo_url) : 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=N/A' }}"
                                            alt="{{ $brand->name }}" class="img-thumbnail"
                                            style="width: 50px; height: 50px; object-fit: contain;">
                                    </td>
                                    <td>{{ $brand->name }}</td>
                                    <td>{{ Str::words($brand->description, 20, '...') ?? 'Không có mô tả' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                            data-bs-target="#updateBrandModal" data-id="{{ $brand->id }}"
                                            data-name="{{ $brand->name }}" data-description="{{ $brand->description }}"
                                            data-logo-url="{{ $brand->logo_url ? asset('storage/' . $brand->logo_url) : 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=N/A' }}"
                                            data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}"
                                            title="Cập nhật">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                            data-bs-target="#deleteBrandModal" data-name="{{ $brand->name }}"
                                            data-delete-url="{{ route('admin.productManagement.brands.destroy', $brand->id) }}"
                                            title="Xóa">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có thương hiệu nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals for Brand Management --}}

    <div class="modal fade" id="createBrandModal" tabindex="-1" aria-labelledby="createBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="createBrandForm" action="{{ route('admin.productManagement.brands.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createBrandModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo
                            Thương
                            hiệu mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="brandNameCreate" class="form-label">Tên Thương hiệu:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="brandNameCreate"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="brandDescriptionCreate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="brandDescriptionCreate" name="description"
                                rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="brandLogoCreate" class="form-label">Logo Thương hiệu:</label>
                            <input type="file" class="form-control" id="brandLogoCreate" name="logo_url" accept="image/*">

                            {{-- THÊM THẺ IMG NÀY VÀO --}}
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Preview" alt="Xem trước logo"
                                id="brandLogoPreviewCreate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu Thương hiệu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateBrandModal" tabindex="-1" aria-labelledby="updateBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateBrandForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateBrandModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập
                            nhật
                            Thương hiệu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="brandNameUpdate" class="form-label">Tên Thương hiệu:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brandNameUpdate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="brandDescriptionUpdate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="brandDescriptionUpdate" name="description"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="brandLogoUpdate" class="form-label">Logo Thương hiệu mới (để trống nếu không
                                đổi):</label>
                            <input type="file" class="form-control" id="brandLogoUpdate" name="logo_url" accept="image/*">
                            <img src="" alt="Logo hiện tại" id="brandLogoPreviewUpdate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteBrandForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteBrandModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận
                            Xóa
                            Thương hiệu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa thương hiệu "<strong id="brandNameToDelete"></strong>"?</p>
                        <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa Thương hiệu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Bạn có thể tạo một file brand-manager.js tương tự như category-manager.js --}}

    <script src="{{ asset('assets_admin/js/brand_manager.js') }}"></script>

    <script>
        // Mở lại modal nếu có lỗi validation
        @if ($errors->any())
            @if (old('form_type') !== 'update') // Giả sử bạn thêm một input hidden để phân biệt form
                document.addEventListener('DOMContentLoaded', function () {
                    var createModal = new bootstrap.Modal(document.getElementById('createBrandModal'));
                    createModal.show();
                });
            @endif
        @endif
    </script>
@endpush