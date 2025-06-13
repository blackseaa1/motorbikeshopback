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
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách Thương hiệu</h2>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createBrandModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Thương hiệu mới
                        </button>
                    </div>
                    <div class="card-body">
                        @if ($brands->isEmpty())
                            <div class="alert alert-info" role="alert">
                                <i class="bi bi-info-circle me-2"></i>Hiện chưa có thương hiệu nào.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 5%;">STT</th>
                                            <th scope="col" style="width: 10%;" class="text-center">Logo</th>
                                            <th scope="col" style="width: 25%;">Tên Thương hiệu</th>
                                            <th scope="col">Mô tả</th>
                                            <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                            <th scope="col" class="text-center" style="width: 25%;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($brands as $brand)
                                            <tr id="brand-row-{{ $brand->id }}"
                                                class="{{ !$brand->isActive() ? 'row-inactive' : '' }}">
                                                {{-- Sử dụng accessor 'logo_full_url' đã được tối ưu --}}
                                                <td>{{ $loop->iteration + $brands->firstItem() - 1 }}</td> {{-- Sửa STT cho đúng với
                                                phân trang --}}
                                                <td class="text-center">
                                                    <img src="{{ $brand->logo_full_url }}" alt="{{ $brand->name }}"
                                                        class="img-thumbnail"
                                                        style="max-width: 50px; max-height: 50px; object-fit: contain;">
                                                </td>
                                                <td>{{ $brand->name }}</td>
                                                <td>{{ Str::limit($brand->description, 60) ?? 'Không có mô tả' }}</td>
                                                <td class="text-center status-cell" id="brand-status-{{ $brand->id }}">
                                                    @if ($brand->isActive())
                                                        <span class="badge bg-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge bg-secondary">Đã ẩn</span>
                                                    @endif
                                                </td>
                                                <td class="text-center action-buttons">
                                                    {{-- Sử dụng accessor 'logo_full_url' đã được tối ưu --}}
                                                    <button type="button" class="btn btn-sm btn-success btn-view-brand"
                                                        data-bs-toggle="modal" data-bs-target="#viewBrandModal"
                                                        data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                                                        data-description="{{ $brand->description ?? '' }}"
                                                        data-status="{{ $brand->status }}"
                                                        data-logo-url="{{ $brand->logo_full_url }}"
                                                        data-created-at="{{ $brand->created_at->format('H:i:s d/m/Y') }}"
                                                        data-updated-at="{{ $brand->updated_at->format('H:i:s d/m/Y') }}"
                                                        data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}"
                                                        title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    {{-- Thay đổi class của button dựa trên trạng thái của brand --}}
                                                    <button type="button"
                                                        class="btn btn-sm toggle-status-btn {{ $brand->isActive() ? 'btn-outline-secondary' : 'btn-danger' }}"
                                                        data-id="{{ $brand->id }}"
                                                        data-url="{{ route('admin.productManagement.brands.toggleStatus', $brand->id) }}"
                                                        title="{{ $brand->isActive() ? 'Ẩn thương hiệu này' : 'Hiển thị thương hiệu này' }}">
                                                        <i class="bi bi-power"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info btn-edit-brand"
                                                        data-bs-toggle="modal" data-bs-target="#updateBrandModal"
                                                        data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                                                        data-description="{{ $brand->description ?? '' }}"
                                                        data-status="{{ $brand->status }}"
                                                        data-logo-url="{{ $brand->logo_full_url }}"
                                                        data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}"
                                                        title="Cập nhật">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete-brand"
                                                        data-bs-toggle="modal" data-bs-target="#deleteBrandModal"
                                                        data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                                                        data-delete-url="{{ route('admin.productManagement.brands.destroy', $brand->id) }}"
                                                        title="Xóa">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- Hiển thị link phân trang --}}
                            <div class="mt-3 d-flex justify-content-end">
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

@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/brand_manager.js') }}"></script>
    {{--
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Khởi tạo tất cả các xử lý JS cho trang này
            if (typeof initializeBrandPage === 'function') {
                initializeBrandPage();
            } else {
                console.error('Lỗi: Hàm initializeBrandsPage() không được tìm thấy trong brand_manager.js.');
            }

            // Script để mở lại modal "Create" nếu có lỗi validation từ server
            @if ($errors -> any() && old('_form_marker') === 'create_brand')
                const createModalElement = document.getElementById('createBrandModal');
            if (createModalElement) {
                const createModalInstance = new bootstrap.Modal(createModalElement);
                if (createModalInstance) {
                    createModalInstance.show();
                }
            }
            @endif
        });
    </script> --}}
@endpush