@extends('admin.layouts.app')

@section('title', 'Brands') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-slack me-2"></i>Thương Hiệu</h1>
    </header>

    <div class="container-fluid">
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
                                <th scope="col">ID</th>
                                <th scope="col">Logo</th>
                                <th scope="col">Tên Thương hiệu</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua dữ liệu từ controller ở đây --}}
                            <tr>
                                <td>1</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=Logo" alt="Logo thương hiệu"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Castrol</td>
                                <td>Thương hiệu dầu nhớt hàng đầu thế giới.</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateBrandModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteBrandModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=Logo" alt="Logo thương hiệu"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Michelin</td>
                                <td>Thương hiệu lốp xe nổi tiếng.</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateBrandModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteBrandModal" title="Xóa">
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

    {{-- Modals for Brand Management --}}

    <div class="modal fade" id="createBrandModal" tabindex="-1" aria-labelledby="createBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBrandModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Thương
                        hiệu mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createBrandForm"> {{-- Bỏ enctype, action, method nếu chỉ dựng giao diện --}}
                        <div class="mb-3">
                            <label for="brandNameCreate" class="form-label">Tên Thương hiệu:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brandNameCreate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="brandDescriptionCreate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="brandDescriptionCreate" name="description"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="brandLogoCreate" class="form-label">Logo Thương hiệu:</label>
                            <input type="file" class="form-control" id="brandLogoCreate" name="logo_url">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Logo" alt="Xem trước logo"
                                id="brandLogoPreviewCreate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="createBrandForm">Lưu Thương hiệu</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateBrandModal" tabindex="-1" aria-labelledby="updateBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateBrandModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                        Thương hiệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateBrandForm"> {{-- Bỏ enctype, action, method nếu chỉ dựng giao diện --}}
                        <input type="hidden" id="brandIdUpdate" name="brand_id">
                        <div class="mb-3">
                            <label for="brandNameUpdate" class="form-label">Tên Thương hiệu:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brandNameUpdate" name="name" value="Castrol"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="brandDescriptionUpdate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="brandDescriptionUpdate" name="description"
                                rows="3">Thương hiệu dầu nhớt hàng đầu thế giới.</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="brandLogoUpdate" class="form-label">Logo Thương hiệu mới (để trống nếu không
                                đổi):</label>
                            <input type="file" class="form-control" id="brandLogoUpdate" name="logo_url">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Logo+Cũ" alt="Xem trước logo"
                                id="brandLogoPreviewUpdate" class="img-thumbnail mt-2"
                                style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="updateBrandForm">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteBrandModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa
                        Thương hiệu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa thương hiệu "<strong>Castrol</strong>" không?</p>
                    <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác. Nếu có sản phẩm đang
                        thuộc thương hiệu này, việc xóa có thể bị chặn (ON DELETE RESTRICT).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteBrandForm"> {{-- Bỏ action, method nếu chỉ dựng giao diện --}}
                        <button type="button" class="btn btn-danger">Xóa Thương hiệu</button>
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