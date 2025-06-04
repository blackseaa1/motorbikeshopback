@extends('admin.layouts.app')

@section('title', 'Quản lý Thương hiệu')

@section('content')
    <div id="adminBrandsPage"> {{-- Wrapper cho trang --}}

        {{-- Content Header (Giống trang Category đã nâng cấp) --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-bookmark-star-fill me-2"></i>Thương hiệu Sản phẩm</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Thương hiệu</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

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
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="text-center">
                                                    @if ($brand->logo_url)
                                                        <img src="{{ Storage::url($brand->logo_url) }}" alt="{{ $brand->name }}"
                                                            class="img-thumbnail"
                                                            style="max-width: 50px; max-height: 50px; object-fit: contain;">
                                                    @else
                                                        <span class="text-muted small">Không có logo</span>
                                                    @endif
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
                                                    <button type="button" class="btn btn-sm btn-success btn-view-brand"
                                                        data-bs-toggle="modal" data-bs-target="#viewBrandModal"
                                                        data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                                                        data-description="{{ $brand->description ?? '' }}"
                                                        data-status="{{ $brand->status }}"
                                                        data-logo-url="{{ $brand->logo_url ? Storage::url($brand->logo_url) : 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO' }}"
                                                        data-created-at="{{ $brand->created_at->format('H:i:s d/m/Y') }}"
                                                        data-updated-at="{{ $brand->updated_at->format('H:i:s d/m/Y') }}"
                                                        data-update-url="{{ route('admin.productManagement.brands.update', $brand->id) }}"
                                                        title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-status-btn"
                                                        data-id="{{ $brand->id }}"
                                                        data-url="{{ route('admin.productManagement.brands.toggleStatus', $brand->id) }}"
                                                        title="{{ $brand->isActive() ? 'Ẩn thương hiệu này' : 'Hiển thị thương hiệu này' }}">
                                                        <i
                                                            class="bi {{ $brand->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-info btn-edit-brand"
                                                        data-bs-toggle="modal" data-bs-target="#updateBrandModal"
                                                        data-id="{{ $brand->id }}" data-name="{{ $brand->name }}"
                                                        data-description="{{ $brand->description ?? '' }}"
                                                        data-status="{{ $brand->status }}"
                                                        data-logo-url="{{ $brand->logo_url ? Storage::url($brand->logo_url) : 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=LOGO' }}"
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
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Create Brand Modal --}}
    <div class="modal fade" id="createBrandModal" tabindex="-1" aria-labelledby="createBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="createBrandForm" action="{{ route('admin.productManagement.brands.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_form_marker" value="create_brand"> {{-- Để JS mở lại modal nếu có lỗi
                    validation server --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="createBrandModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo
                            Thương hiệu mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="brandNameCreate" class="form-label">Tên Thương hiệu <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name', '_form_marker') is-invalid @enderror"
                                id="brandNameCreate" name="name" value="{{ old('name') }}" required>
                            @error('name', '_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="brandDescriptionCreate" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description', '_form_marker') is-invalid @enderror"
                                id="brandDescriptionCreate" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description', '_form_marker') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brandLogoCreate" class="form-label">Logo thương hiệu</label>
                                    <input type="file"
                                        class="form-control @error('logo_url', '_form_marker') is-invalid @enderror"
                                        id="brandLogoCreate" name="logo_url" accept="image/*">
                                    <small class="form-text text-muted">Định dạng: JPG, PNG, GIF, SVG, WEBP. Tối đa
                                        2MB.</small>
                                    @error('logo_url', '_form_marker') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <img id="brandLogoPreviewCreate"
                                    src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview" alt="Xem trước logo"
                                    class="img-thumbnail mt-2"
                                    style="max-height: 150px; max-width: 150px; object-fit: contain;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="brandStatusCreate" class="form-label">Trạng thái <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status', '_form_marker') is-invalid @enderror"
                                id="brandStatusCreate" name="status" required>
                                <option value="{{ \App\Models\Brand::STATUS_ACTIVE }}" {{ old('status', \App\Models\Brand::STATUS_ACTIVE) == \App\Models\Brand::STATUS_ACTIVE ? 'selected' : '' }}>
                                    Hoạt động (Hiển thị)
                                </option>
                                <option value="{{ \App\Models\Brand::STATUS_INACTIVE }}" {{ old('status') == \App\Models\Brand::STATUS_INACTIVE ? 'selected' : '' }}>
                                    Ẩn (Không hiển thị)
                                </option>
                            </select>
                            @error('status', '_form_marker') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

    {{-- Update Brand Modal --}}
    <div class="modal fade" id="updateBrandModal" tabindex="-1" aria-labelledby="updateBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateBrandForm" method="POST" enctype="multipart/form-data"> {{-- action sẽ được JS đặt --}}
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateBrandModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                            Thương hiệu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="brandNameUpdate" class="form-label">Tên Thương hiệu <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brandNameUpdate" name="name" required>
                            <div class="invalid-feedback" id="brandNameUpdateError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="brandDescriptionUpdate" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="brandDescriptionUpdate" name="description"
                                rows="3"></textarea>
                            <div class="invalid-feedback" id="brandDescriptionUpdateError"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brandLogoUpdate" class="form-label">Logo mới (để trống nếu không
                                        đổi)</label>
                                    <input type="file" class="form-control" id="brandLogoUpdate" name="logo_url"
                                        accept="image/*">
                                    <small class="form-text text-muted">Định dạng: JPG, PNG, GIF, SVG, WEBP. Tối đa
                                        2MB.</small>
                                    <div class="invalid-feedback" id="brandLogoUrlUpdateError"></div> {{-- Sửa ID cho đúng
                                    với key 'logo_url' --}}
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <img id="brandLogoPreviewUpdate"
                                    src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=Current"
                                    alt="Xem trước logo hiện tại" class="img-thumbnail mt-2"
                                    style="max-height: 150px; max-width: 150px; object-fit: contain;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="brandStatusUpdate" class="form-label">Trạng thái <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="brandStatusUpdate" name="status" required>
                                <option value="{{ \App\Models\Brand::STATUS_ACTIVE }}">Hoạt động (Hiển thị)</option>
                                <option value="{{ \App\Models\Brand::STATUS_INACTIVE }}">Ẩn (Không hiển thị)</option>
                            </select>
                            <div class="invalid-feedback" id="brandStatusUpdateError"></div>
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

    {{-- Delete Brand Modal --}}
    <div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteBrandForm" method="POST"> {{-- action sẽ được JS đặt --}}
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteBrandModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa
                            Thương hiệu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa vĩnh viễn thương hiệu "<strong id="brandNameToDelete"></strong>"?</p>
                        @if(Config::get('admin.deletion_password'))
                            <div class="mb-3">
                                <label for="brandDeletionPassword" class="form-label">Mật khẩu xác nhận xóa <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="brandDeletionPassword" name="deletion_password"
                                    required>
                                <div class="invalid-feedback" id="brandDeletionPasswordError"></div>
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

    {{-- View Brand Modal --}}
    <div class="modal fade" id="viewBrandModal" tabindex="-1" aria-labelledby="viewBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBrandModalLabel"><i class="bi bi-bookmark-star-fill me-2"></i>Chi tiết
                        Thương hiệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="brandLogoView" src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO"
                                alt="Logo thương hiệu" class="img-thumbnail mb-3"
                                style="max-height: 150px; max-width: 150px; object-fit: contain;">
                        </div>
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4">ID Thương hiệu:</dt>
                                <dd class="col-sm-8" id="brandIdView">-</dd>

                                <dt class="col-sm-4">Tên Thương hiệu:</dt>
                                <dd class="col-sm-8" id="brandNameView">-</dd>

                                <dt class="col-sm-4">Mô tả:</dt>
                                <dd class="col-sm-8" id="brandDescriptionView"
                                    style="white-space: pre-wrap; word-break: break-word;">-</dd>

                                <dt class="col-sm-4">Trạng thái:</dt>
                                <dd class="col-sm-8" id="brandStatusView">-</dd>

                                <dt class="col-sm-4">Ngày tạo:</dt>
                                <dd class="col-sm-8" id="brandCreatedAtView">-</dd>

                                <dt class="col-sm-4">Cập nhật lần cuối:</dt>
                                <dd class="col-sm-8" id="brandUpdatedAtView">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="editBrandFromViewButton">
                        <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/brand_manager.js') }}"></script>
    <script>
        @if ($errors->any() && old('_form_marker') === 'create_brand')
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('createBrandModal')) {
                    var createModalInstance = new bootstrap.Modal(document.getElementById('createBrandModal'));
                    if (createModalInstance) createModalInstance.show();
                }
            });
        @endif
    </script>
@endpush