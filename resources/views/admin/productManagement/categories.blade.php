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
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $category->name }}</td>
                                                <td>{{ Str::limit($category->description, 70) ?? 'Không có mô tả' }}</td>
                                                <td class="text-center status-cell" id="category-status-{{ $category->id }}">
                                                    @if ($category->isActive())
                                                        <span class="badge bg-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge bg-secondary">Đã ẩn</span>
                                                    @endif
                                                </td>
                                                <td class="text-center action-buttons">
                                                    <button type="button" class="btn btn-sm btn-success btn-view-category"
                                                        data-bs-toggle="modal" data-bs-target="#viewCategoryModal"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-description="{{ $category->description ?? '' }}"
                                                        data-status="{{ $category->status }}"
                                                        data-created-at="{{ $category->created_at->format('H:i:s d/m/Y') }}"
                                                        data-updated-at="{{ $category->updated_at->format('H:i:s d/m/Y') }}"
                                                        data-update-url="{{ route('admin.productManagement.categories.update', $category->id) }}"
                                                        title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-status-btn"
                                                        data-id="{{ $category->id }}"
                                                        data-url="{{ route('admin.productManagement.categories.toggleStatus', $category->id) }}"
                                                        title="{{ $category->isActive() ? 'Ẩn danh mục này' : 'Hiển thị danh mục này' }}">
                                                        <i
                                                            class="bi {{ $category->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-info btn-edit-category"
                                                        data-bs-toggle="modal" data-bs-target="#updateCategoryModal"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-description="{{ $category->description ?? '' }}"
                                                        data-status="{{ $category->status }}"
                                                        data-update-url="{{ route('admin.productManagement.categories.update', $category->id) }}"
                                                        title="Cập nhật">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

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
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Create Category Modal --}}
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="createCategoryForm" action="{{ route('admin.productManagement.categories.store') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="_form_marker" value="create_category">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo
                            Danh mục mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryNameCreate" class="form-label">Tên Danh mục <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name', '_form_marker') is-invalid @enderror"
                                id="categoryNameCreate" name="name" value="{{ old('name') }}" required>
                            @error('name', '_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescriptionCreate" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description', '_form_marker') is-invalid @enderror"
                                id="categoryDescriptionCreate" name="description"
                                rows="3">{{ old('description') }}</textarea>
                            @error('description', '_form_marker') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="categoryStatusCreate" class="form-label">Trạng thái <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status', '_form_marker') is-invalid @enderror"
                                id="categoryStatusCreate" name="status" required>
                                <option value="{{ \App\Models\Category::STATUS_ACTIVE }}" {{ old('status', \App\Models\Category::STATUS_ACTIVE) == \App\Models\Category::STATUS_ACTIVE ? 'selected' : '' }}>
                                    Hoạt động (Hiển thị)
                                </option>
                                <option value="{{ \App\Models\Category::STATUS_INACTIVE }}" {{ old('status') == \App\Models\Category::STATUS_INACTIVE ? 'selected' : '' }}>
                                    Ẩn (Không hiển thị)
                                </option>
                            </select>
                            @error('status', '_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu Danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Update Category Modal --}}
    <div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateCategoryForm" method="POST"> {{-- action sẽ được JS đặt --}}
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập
                            nhật Danh mục</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryNameUpdate" class="form-label">Tên Danh mục <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryNameUpdate" name="name" required>
                            <div class="invalid-feedback" id="categoryNameUpdateError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescriptionUpdate" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="categoryDescriptionUpdate" name="description"
                                rows="3"></textarea>
                            <div class="invalid-feedback" id="categoryDescriptionUpdateError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="categoryStatusUpdate" class="form-label">Trạng thái <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="categoryStatusUpdate" name="status" required>
                                <option value="{{ \App\Models\Category::STATUS_ACTIVE }}">Hoạt động (Hiển thị)</option>
                                <option value="{{ \App\Models\Category::STATUS_INACTIVE }}">Ẩn (Không hiển thị)</option>
                            </select>
                            <div class="invalid-feedback" id="categoryStatusUpdateError"></div>
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

    {{-- Delete Category Modal --}}
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteCategoryForm" method="POST"> {{-- action sẽ được JS đặt --}}
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận
                            Xóa Danh mục</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa vĩnh viễn danh mục "<strong id="categoryNameToDelete"></strong>"?</p>
                        @if(Config::get('admin.deletion_password'))
                            <div class="mb-3">
                                <label for="categoryDeletionPassword" class="form-label">Mật khẩu xác nhận xóa <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="categoryDeletionPassword"
                                    name="deletion_password" required>
                                <div class="invalid-feedback" id="categoryDeletionPasswordError"></div>
                            </div>
                        @endif
                        <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa Vĩnh Viễn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Category Modal --}}
    <div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCategoryModalLabel"><i class="bi bi-tags-fill me-2"></i>Chi tiết Danh
                        mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID Danh mục:</dt>
                        <dd class="col-sm-9" id="categoryIdView">-</dd>

                        <dt class="col-sm-3">Tên Danh mục:</dt>
                        <dd class="col-sm-9" id="categoryNameView">-</dd>

                        <dt class="col-sm-3">Mô tả:</dt>
                        <dd class="col-sm-9" id="categoryDescriptionView"
                            style="white-space: pre-wrap; word-break: break-word;">-</dd>

                        <dt class="col-sm-3">Trạng thái:</dt>
                        <dd class="col-sm-9" id="categoryStatusView">-</dd>

                        <dt class="col-sm-3">Ngày tạo:</dt>
                        <dd class="col-sm-9" id="categoryCreatedAtView">-</dd>

                        <dt class="col-sm-3">Cập nhật lần cuối:</dt>
                        <dd class="col-sm-9" id="categoryUpdatedAtView">-</dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="editCategoryFromViewButton">
                        <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/category_manager.js') }}"></script>
    <script>
        @if ($errors->any() && old('_form_marker') === 'create_category')
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('createCategoryModal')) {
                    var createModalInstance = new bootstrap.Modal(document.getElementById('createCategoryModal'));
                    if (createModalInstance) createModalInstance.show();
                }
            });
        @endif
        // Logic mở lại modal update khi có lỗi validation từ server (nếu submit truyền thống)
        // Hiện tại, category_manager.js xử lý update bằng AJAX.
    </script>
@endpush