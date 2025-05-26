@extends('admin.layouts.app')

@section('title', 'Categories') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-shop me-2"></i>Danh Mục sản phẩm</h1>
    </header>
    <div class="container-fluid">
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
                                <th scope="col">ID</th>
                                <th scope="col">Logo</th>
                                <th scope="col">Tên Danh mục</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua dữ liệu từ controller ở đây --}}
                            <tr>
                                <td>1</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=Logo" alt="Logo danh mục"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Thời trang Nam</td>
                                <td>Các sản phẩm thời trang dành cho nam giới.</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateCategoryModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteCategoryModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=Logo" alt="Logo danh mục"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Điện thoại & Phụ kiện</td>
                                <td>Các loại điện thoại thông minh và phụ kiện đi kèm.</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateCategoryModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteCategoryModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Kết thúc lặp --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals for Category Management --}}

    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo
                        Danh mục Sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createCategoryForm" enctype="multipart/form-data"> {{-- Thêm enctype nếu tải lên file logo
                        --}}
                        {{-- @csrf --}}
                        <div class="mb-3">
                            <label for="categoryNameCreate" class="form-label">Tên Danh mục:</label>
                            <input type="text" class="form-control" id="categoryNameCreate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescriptionCreate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="categoryDescriptionCreate" name="description"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="categoryLogoCreate" class="form-label">Logo Danh mục:</label>
                            <input type="file" class="form-control" id="categoryLogoCreate" name="logo_url">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Logo" alt="Xem trước logo"
                                id="categoryLogoPreviewCreate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" form="createCategoryForm">Lưu Danh mục</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCategoryModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                        Danh mục Sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateCategoryForm" enctype="multipart/form-data">
                        {{-- @csrf --}}
                        {{-- @method('PUT') --}}
                        <input type="hidden" id="categoryIdUpdate" name="category_id"> {{-- Để lưu ID của danh mục cần cập
                        nhật --}}
                        <div class="mb-3">
                            <label for="categoryNameUpdate" class="form-label">Tên Danh mục:</label>
                            <input type="text" class="form-control" id="categoryNameUpdate" name="name"
                                value="Thời trang Nam" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescriptionUpdate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="categoryDescriptionUpdate" name="description"
                                rows="3">Các sản phẩm thời trang dành cho nam giới.</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="categoryLogoUpdate" class="form-label">Logo Danh mục mới (để trống nếu không
                                đổi):</label>
                            <input type="file" class="form-control" id="categoryLogoUpdate" name="logo_url">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Logo+Cũ" alt="Xem trước logo"
                                id="categoryLogoPreviewUpdate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" form="updateCategoryForm">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa
                        Danh mục</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa danh mục "<strong>Thời trang Nam</strong>" không?</p>
                    <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác. Nếu có sản phẩm đang
                        thuộc danh mục này, việc xóa có thể bị chặn.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteCategoryForm" method="POST" action="#"> {{-- action sẽ được cập nhật bằng JS hoặc khi
                        load dữ liệu --}}
                        {{-- @csrf --}}
                        {{-- @method('DELETE') --}}
                        <button type="submit" class="btn btn-danger">Xóa Danh mục</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('styles')
@endpush

@push('scripts')
@endpush