{{-- Create Vehicle Model Modal --}}
<div class="modal fade" id="createVehicleModelModal" tabindex="-1" aria-labelledby="createVehicleModelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createVehicleModelForm" action="{{ route('admin.productManagement.vehicleModels.store') }}"
                method="POST">
                @csrf
                <input type="hidden" name="_form_marker" value="create_vehicle_model">
                <div class="modal-header">
                    <h5 class="modal-title" id="createVehicleModelModalLabel"><i
                            class="bi bi-plus-circle-fill me-2"></i>Tạo Dòng xe mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vmVehicleBrandCreate" class="form-label">Hãng xe:<span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('vehicle_brand_id', '_form_marker') is-invalid @enderror"
                            id="vmVehicleBrandCreate" name="vehicle_brand_id" required>
                            <option value="">-- Chọn Hãng xe --</option>
                            {{-- $allVehicleBrands được truyền từ vehicle.blade.php khi include partial này --}}
                            @foreach($allVehicleBrands as $brand)
                                <option value="{{ $brand->id }}" {{ old('vehicle_brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_brand_id', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="vmNameCreate" class="form-label">Tên Dòng xe:<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', '_form_marker') is-invalid @enderror"
                            id="vmNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="vmYearCreate" class="form-label">Năm sản xuất:</label>
                        <input type="number" class="form-control @error('year', '_form_marker') is-invalid @enderror"
                            id="vmYearCreate" name="year" value="{{ old('year') }}" min="1900"
                            max="{{ date('Y') + 5 }}">
                        @error('year', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="vmDescriptionCreate" class="form-label">Mô tả:</label>
                        <textarea class="form-control @error('description', '_form_marker') is-invalid @enderror"
                            id="vmDescriptionCreate" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="vmStatusCreate" class="form-label">Trạng thái <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('status', '_form_marker') is-invalid @enderror"
                            id="vmStatusCreate" name="status" required>
                            <option value="{{ \App\Models\VehicleModel::STATUS_ACTIVE }}" {{ old('status', \App\Models\VehicleModel::STATUS_ACTIVE) == \App\Models\VehicleModel::STATUS_ACTIVE ? 'selected' : '' }}>Hoạt động</option>
                            <option value="{{ \App\Models\VehicleModel::STATUS_INACTIVE }}" {{ old('status') == \App\Models\VehicleModel::STATUS_INACTIVE ? 'selected' : '' }}>Đã ẩn
                            </option>
                        </select>
                        @error('status', '_form_marker')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Dòng xe</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Update Vehicle Model Modal --}}
<div class="modal fade" id="updateVehicleModelModal" tabindex="-1" aria-labelledby="updateVehicleModelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="updateVehicleModelForm" method="POST"> {{-- action sẽ được JS đặt --}}
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateVehicleModelModalLabel"><i
                            class="bi bi-pencil-square me-2"></i>Cập nhật Dòng xe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vmVehicleBrandUpdate" class="form-label">Hãng xe:<span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="vmVehicleBrandUpdate" name="vehicle_brand_id" required>
                            <option value="">-- Chọn Hãng xe --</option>
                            @foreach($allVehicleBrands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="vmVehicle_brand_idUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vmNameUpdate" class="form-label">Tên Dòng xe:<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vmNameUpdate" name="name" required>
                        <div class="invalid-feedback" id="vmNameUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vmYearUpdate" class="form-label">Năm sản xuất:</label>
                        <input type="number" class="form-control" id="vmYearUpdate" name="year" min="1900"
                            max="{{ date('Y') + 5 }}">
                        <div class="invalid-feedback" id="vmYearUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vmDescriptionUpdate" class="form-label">Mô tả:</label>
                        <textarea class="form-control" id="vmDescriptionUpdate" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="vmDescriptionUpdateError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vmStatusUpdate" class="form-label">Trạng thái <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="vmStatusUpdate" name="status" required>
                            <option value="{{ \App\Models\VehicleModel::STATUS_ACTIVE }}">Hoạt động</option>
                            <option value="{{ \App\Models\VehicleModel::STATUS_INACTIVE }}">Đã ẩn</option>
                        </select>
                        <div class="invalid-feedback" id="vmStatusUpdateError"></div>
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

{{-- Delete Vehicle Model Modal --}}
<div class="modal fade" id="deleteVehicleModelModal" tabindex="-1" aria-labelledby="deleteVehicleModelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteVehicleModelForm" method="POST"> {{-- action sẽ được JS đặt --}}
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteVehicleModelModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác
                        nhận Xóa Dòng xe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa dòng xe "<strong id="deleteVehicleModelName"></strong>"?</p>

                    @if(Config('admin.deletion_password'))
                        <div class="mb-3 mt-3">
                            <label for="adminPasswordDeleteVehicleModel" class="form-label">Nhập Mật khẩu Xóa Chung:<span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="adminPasswordDeleteVehicleModel"
                                name="admin_password_delete_vehicle_model" required autocomplete="new-password">
                            <div class="invalid-feedback" id="adminPasswordDeleteVehicleModelError"></div>
                        </div>
                    @endif

                    <p class="text-danger">Lưu ý: Thao tác này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Dòng xe</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View VehicleModel Modal --}}
<div class="modal fade" id="viewVehicleModelModal" tabindex="-1" aria-labelledby="viewVehicleModelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewVehicleModelModalLabel"><i class="bi bi-car-front-fill me-2"></i>Chi
                    tiết Dòng xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">ID Dòng xe:</dt>
                    <dd class="col-sm-9" id="vmIdView">-</dd>
                    <dt class="col-sm-3">Tên Dòng xe:</dt>
                    <dd class="col-sm-9" id="vmNameView">-</dd>
                    <dt class="col-sm-3">Hãng xe:</dt>
                    <dd class="col-sm-9" id="vmVehicleBrandNameView">-</dd>
                    <dt class="col-sm-3">Năm sản xuất:</dt>
                    <dd class="col-sm-9" id="vmYearView">-</dd>
                    <dt class="col-sm-3">Mô tả:</dt>
                    <dd class="col-sm-9" id="vmDescriptionView" style="white-space: pre-wrap; word-break: break-word;">-
                    </dd>
                    <dt class="col-sm-3">Trạng thái:</dt>
                    <dd class="col-sm-9" id="vmStatusViewText">-</dd>
                    <dt class="col-sm-3">Ngày tạo:</dt>
                    <dd class="col-sm-9" id="vmCreatedAtView">-</dd>
                    <dt class="col-sm-3">Cập nhật lần cuối:</dt>
                    <dd class="col-sm-9" id="vmUpdatedAtView">-</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="editVehicleModelFromViewButton">
                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>