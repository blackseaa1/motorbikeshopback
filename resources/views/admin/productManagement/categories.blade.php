@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục')

@section('content')
    <header class="content-header">
        <h1><i class="bi bi-shop me-2"></i>Danh Mục sản phẩm</h1>
    </header>

    <div class="container-fluid">
        {{-- Section for displaying session messages and validation errors --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Có lỗi xảy ra!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-tags-fill me-2"></i>Quản lý Danh mục Sản phẩm</h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Danh mục mới
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                {{-- Đã xóa cột Logo --}}
                                <th scope="col" style="width: 5%;">ID</th>
                                <th scope="col" style="width: 30%;">Tên Danh mục</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col" class="text-center" style="width: 15%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    {{-- Đã xóa cột hiển thị Logo --}}
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description ?? 'Không có mô tả' }}</td>
                                    <td class="text-center">
                                        {{-- Đã xóa data-logo-url --}}
                                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                            data-bs-target="#updateCategoryModal" data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}" data-description="{{ $category->description }}"
                                            data-update-url="{{ route('admin.productManagement.categories.update', $category->id) }}"
                                            title="Cập nhật">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                            data-bs-target="#deleteCategoryModal" data-name="{{ $category->name }}"
                                            data-delete-url="{{ route('admin.productManagement.categories.destroy', $category->id) }}"
                                            title="Xóa">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Cập nhật colspan="4" --}}
                                    <td colspan="4" class="text-center">Chưa có danh mục nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="createCategoryForm" action="{{ route('admin.productManagement.categories.store') }}"
                    method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo
                            Danh mục Sản phẩm mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryNameCreate" class="form-label">Tên Danh mục <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryNameCreate" name="name"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescriptionCreate" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="categoryDescriptionCreate" name="description"
                                rows="3">{{ old('description') }}</textarea>
                        </div>
                        {{-- Đã xóa phần input file logo --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu Danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập
                            nhật
                            Danh mục Sản phẩm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryNameUpdate" class="form-label">Tên Danh mục <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryNameUpdate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescriptionUpdate" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="categoryDescriptionUpdate" name="description"
                                rows="3"></textarea>
                        </div>
                        {{-- Đã xóa phần input file logo --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteCategoryForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận
                            Xóa Danh mục</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa danh mục "<strong id="categoryNameToDelete"></strong>"?</p>
                        <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác và sẽ xóa vĩnh viễn
                            danh mục.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa Vĩnh Viễn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        <script src="{{ asset('js/admin/category_manager.js') }}"></script>

    // Mở lại modal nếu có lỗi validation
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
        var createModal = new bootstrap.Modal(document.getElementById('createCategoryModal'));
        createModal.show();
        });
    @endif
    </script>
@endpush