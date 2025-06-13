@extends('admin.layouts.app')

@section('title', 'Quản lý Danh mục')

@section('content')
    <div id="adminCategoriesPage"> {{-- Wrapper cho trang --}}

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
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách Danh mục</h2>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createCategoryModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Danh mục mới
                        </button>
                    </div>
                    <div class="card-body">
                        @if ($categories->isEmpty())
                            <div class="alert alert-info" role="alert">
                                <i class="bi bi-info-circle me-2"></i>Hiện chưa có danh mục nào.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 5%;">STT</th>
                                            <th scope="col" style="width: 30%;">Tên Danh mục</th>
                                            <th scope="col">Mô tả</th>
                                            <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                            <th scope="col" class="text-center" style="width: 25%;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($categories as $category)
                                            <tr id="category-row-{{ $category->id }}"
                                                class="{{ !$category->isActive() ? 'row-inactive' : '' }}">
                                                <td>{{ $loop->iteration + $categories->firstItem() - 1 }}</td>
                                                <td>{{ $category->name }}</td>
                                                <td>{{ Str::limit($category->description, 70) ?? 'Không có mô tả' }}</td>
                                                <td class="text-center status-cell" id="category-status-{{ $category->id }}">
                                                    <span
                                                        class="badge {{ $category->status_badge_class }}">{{ $category->status_text }}</span>
                                                </td>
                                                <td class="text-center action-buttons">
                                                    {{-- NÚT XEM --}}
                                                    <button type="button" class="btn btn-sm btn-success btn-view-category"
                                                        data-bs-toggle="modal" data-bs-target="#viewCategoryModal"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-description="{{ $category->description ?? '' }}"
                                                        data-status="{{ $category->status }}"
                                                        data-status-text="{{ $category->status_text }}"
                                                        data-status-badge-class="{{ $category->status_badge_class }}"
                                                        data-created-at="{{ $category->created_at->format('H:i:s d/m/Y') }}"
                                                        data-updated-at="{{ $category->updated_at->format('H:i:s d/m/Y') }}"
                                                        data-update-url="{{ route('admin.productManagement.categories.update', $category->id) }}"
                                                        title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>

                                                    {{-- NÚT CHUYỂN ĐỔI TRẠNG THÁI (ĐÃ CẬP NHẬT) --}}
                                                    <button type="button"
                                                        class="btn btn-sm toggle-status-btn {{ $category->isActive() ? 'btn-outline-secondary' : 'btn-danger' }}"
                                                        data-id="{{ $category->id }}"
                                                        data-url="{{ route('admin.productManagement.categories.toggleStatus', $category->id) }}"
                                                        title="{{ $category->isActive() ? 'Ẩn danh mục này' : 'Hiển thị danh mục này' }}">
                                                        <i class="bi bi-power"></i>
                                                    </button>

                                                    {{-- NÚT SỬA --}}
                                                    <button type="button" class="btn btn-sm btn-info btn-edit-category"
                                                        data-bs-toggle="modal" data-bs-target="#updateCategoryModal"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-description="{{ $category->description ?? '' }}"
                                                        data-status="{{ $category->status }}"
                                                        data-update-url="{{ route('admin.productManagement.categories.update', $category->id) }}"
                                                        title="Cập nhật">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    {{-- NÚT XÓA --}}
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete-category"
                                                        data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-delete-url="{{ route('admin.productManagement.categories.destroy', $category->id) }}"
                                                        title="Xóa">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 d-flex justify-content-end">
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
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/category_manager.js') }}"></script>
@endpush