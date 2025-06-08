{{-- Create VehicleBrand Modal --}}
<div class="modal fade" id="createVehicleBrandModal" tabindex="-1" aria-labelledby="createVehicleBrandModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createVehicleBrandForm" action="{{ route('admin.productManagement.vehicleBrands.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_form_marker" value="create_vehicle_brand">
                <div class="modal-header">
                    <h5 class="modal-title" id="createVehicleBrandModalLabel"><i
                            class="bi bi-plus-circle-fill me-2"></i>Tạo Hãng xe mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vbNameCreate" class="form-label">Tên Hãng xe:<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', '_form_marker') is-invalid @enderror"
                            id="vbNameCreate" name="name" value="{{ old('name') }}" required>
                        {{-- Div lỗi cho AJAX validation (nếu cần, vì form này đang submit truyền thống) --}}
                        {{-- <div class="invalid-feedback" id="vbNameCreateError"></div> --}}
                        @error('name', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="vbDescriptionCreate" class="form-label">Mô tả:</label>
                        <textarea class="form-control @error('description', '_form_marker') is-invalid @enderror"
                            id="vbDescriptionCreate" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vbLogoCreate" class="form-label">Logo Hãng xe:</label>
                                <input type="file"
                                    class="form-control @error('logo_url', '_form_marker') is-invalid @enderror"
                                    id="vbLogoCreate" name="logo_url" accept="image/*">
                                @error('logo_url', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Preview" alt="Xem trước logo"
                                id="vbLogoPreviewCreate" class="img-thumbnail mt-2"
                                style="max-width: 100px; max-height: 100px; object-fit: contain;"
                                data-default-src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=Preview">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vbStatusCreate" class="form-label">Trạng thái <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('status', '_form_marker') is-invalid @enderror"
                            id="vbStatusCreate" name="status" required>
                            <option value="{{ \App\Models\VehicleBrand::STATUS_ACTIVE }}" {{ old('status', \App\Models\VehicleBrand::STATUS_ACTIVE) == \App\Models\VehicleBrand::STATUS_ACTIVE ? 'selected' : '' }}>Hoạt động</option>
                            <option value="{{ \App\Models\VehicleBrand::STATUS_INACTIVE }}" {{ old('status') == \App\Models\VehicleBrand::STATUS_INACTIVE ? 'selected' : '' }}>Đã ẩn
                            </option>
                        </select>
                        @error('status', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Hãng xe</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Update VehicleBrand Modal --}}
<div class="modal fade" id="updateVehicleBrandModal" tabindex="-1" aria-labelledby="updateVehicleBrandModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="updateVehicleBrandForm" method="POST" enctype="multipart/form-data"> {{-- action sẽ được JS đặt
                --}}
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateVehicleBrandModalLabel"><i
                            class="bi bi-pencil-square me-2"></i>Cập nhật Hãng xe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vbNameUpdate" class="form-label">Tên Hãng xe:<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vbNameUpdate" name="name" required>
                        <div class="invalid-feedback" id="vbNameUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vbDescriptionUpdate" class="form-label">Mô tả:</label>
                        <textarea class="form-control" id="vbDescriptionUpdate" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="vbDescriptionUpdateError"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vbLogoUpdate" class="form-label">Logo mới (để trống nếu không đổi):</label>
                                <input type="file" class="form-control" id="vbLogoUpdate" name="logo_url"
                                    accept="image/*">
                                <div class="invalid-feedback" id="vbLogoUrlUpdateError"></div> {{-- Đảm bảo ID này tồn
                                tại và JS nhắm đúng --}}
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=N/A" alt="Logo hiện tại"
                                id="vbLogoPreviewUpdate" class="img-thumbnail mt-2"
                                style="max-width: 100px; max-height: 100px; object-fit: contain;"
                                data-default-src="https://placehold.co/100x100/EFEFEF/AAAAAA&text=N/A">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vbStatusUpdate" class="form-label">Trạng thái <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="vbStatusUpdate" name="status" required>
                            <option value="{{ \App\Models\VehicleBrand::STATUS_ACTIVE }}">Hoạt động</option>
                            <option value="{{ \App\Models\VehicleBrand::STATUS_INACTIVE }}">Đã ẩn</option>
                        </select>
                        <div class="invalid-feedback" id="vbStatusUpdateError"></div>
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

{{-- Delete VehicleBrand Modal --}}
<div class="modal fade" id="deleteVehicleBrandModal" tabindex="-1" aria-labelledby="deleteVehicleBrandModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteVehicleBrandForm" method="POST"> {{-- action sẽ được JS đặt --}}
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteVehicleBrandModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác
                        nhận Xóa Hãng xe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa hãng xe "<strong id="vehicleBrandNameToDelete"></strong>"?</p>

                    @if(Config('admin.deletion_password'))
                        <div class="mb-3 mt-3">
                            <label for="adminPasswordDeleteVehicleBrand" class="form-label">Nhập Mật khẩu Xóa Chung:<span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="adminPasswordDeleteVehicleBrand"
                                name="admin_password_delete_vehicle_brand" required autocomplete="new-password">
                            <div class="invalid-feedback" id="adminPasswordDeleteVehicleBrandError"></div>
                        </div>
                    @endif

                    <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Hãng xe</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View VehicleBrand Modal --}}
<div class="modal fade" id="viewVehicleBrandModal" tabindex="-1" aria-labelledby="viewVehicleBrandModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewVehicleBrandModalLabel"><i class="bi bi-building me-2"></i>Chi tiết Hãng
                    xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="vbLogoView" src="https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO"
                            alt="Logo Hãng xe" class="img-thumbnail mb-3"
                            style="max-height: 150px; max-width: 150px; object-fit: contain;">
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-4">ID Hãng xe:</dt>
                            <dd class="col-sm-8" id="vbIdView">-</dd>
                            <dt class="col-sm-4">Tên Hãng xe:</dt>
                            <dd class="col-sm-8" id="vbNameView">-</dd>
                            <dt class="col-sm-4">Mô tả:</dt>
                            <dd class="col-sm-8" id="vbDescriptionView"
                                style="white-space: pre-wrap; word-break: break-word;">-</dd>
                            <dt class="col-sm-4">Trạng thái:</dt>
                            <dd class="col-sm-8" id="vbStatusViewText">-</dd>
                            <dt class="col-sm-4">Ngày tạo:</dt>
                            <dd class="col-sm-8" id="vbCreatedAtView">-</dd>
                            <dt class="col-sm-4">Cập nhật lần cuối:</dt>
                            <dd class="col-sm-8" id="vbUpdatedAtView">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="editVehicleBrandFromViewButton">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>