{{-- Create Brand Modal --}}
<div class="modal fade" id="createBrandModal" tabindex="-1" aria-labelledby="createBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createBrandForm" action="{{ route('admin.productManagement.brands.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_form_marker" value="create_brand">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBrandModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo Thương hiệu mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="brandNameCreate" class="form-label">Tên Thương hiệu <span class="text-danger">*</span></label>
                        {{-- FIX: Bỏ error bag '_form_marker' --}}
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="brandNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="brandDescriptionCreate" class="form-label">Mô tả</label>
                         {{-- FIX: Bỏ error bag '_form_marker' --}}
                        <textarea class="form-control @error('description') is-invalid @enderror" id="brandDescriptionCreate" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="brandLogoCreate" class="form-label">Logo thương hiệu</label>
                                 {{-- FIX: Bỏ error bag '_form_marker' --}}
                                <input type="file" class="form-control @error('logo_url') is-invalid @enderror" id="brandLogoCreate" name="logo_url" accept="image/*">
                                <small class="form-text text-muted">Định dạng: JPG, PNG, GIF, SVG, WEBP. Tối đa 2MB.</small>
                                @error('logo_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <img id="brandLogoPreviewCreate" src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview" alt="Xem trước logo" class="img-thumbnail mt-2" style="max-height: 150px; max-width: 150px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="brandStatusCreate" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                         {{-- FIX: Bỏ error bag '_form_marker' --}}
                        <select class="form-select @error('status') is-invalid @enderror" id="brandStatusCreate" name="status" required>
                            <option value="{{ \App\Models\Brand::STATUS_ACTIVE }}" {{ old('status', \App\Models\Brand::STATUS_ACTIVE) == \App\Models\Brand::STATUS_ACTIVE ? 'selected' : '' }}>
                                Hoạt động (Hiển thị)
                            </option>
                            <option value="{{ \App\Models\Brand::STATUS_INACTIVE }}" {{ old('status') == \App\Models\Brand::STATUS_INACTIVE ? 'selected' : '' }}>
                                Ẩn (Không hiển thị)
                            </option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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