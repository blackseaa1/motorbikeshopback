{{-- Modal Create District --}}
<div class="modal fade" id="createDistrictModal" tabindex="-1" aria-labelledby="createDistrictModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDistrictModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Thêm Quận/Huyện mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDistrictForm" method="POST" action="{{ route('admin.system.geography.districts.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="districtProvinceIdCreate" class="form-label">Thuộc Tỉnh/Thành phố:<span class="text-danger">*</span></label>
                        <select class="form-select @error('province_id', 'storeDistrict') is-invalid @enderror" id="districtProvinceIdCreate" name="province_id" required>
                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            @foreach($allProvinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                        @error('province_id', 'storeDistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="districtNameCreate" class="form-label">Tên Quận/Huyện:<span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'storeDistrict') is-invalid @enderror" id="districtNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name', 'storeDistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="districtGsoIdCreate" class="form-label">Mã GSO (nếu có):</label>
                        <input type="text" class="form-control @error('gso_id', 'storeDistrict') is-invalid @enderror" id="districtGsoIdCreate" name="gso_id" value="{{ old('gso_id') }}">
                        @error('gso_id', 'storeDistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Quận/Huyện</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Update District --}}
<div class="modal fade" id="updateDistrictModal" tabindex="-1" aria-labelledby="updateDistrictModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateDistrictModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Quận/Huyện</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateDistrictForm" method="POST" action=""> {{-- Action sẽ được set bằng JS --}}
                @csrf
                @method('PUT')
                <div class="modal-body">
                     <div class="mb-3">
                        <label for="districtProvinceIdUpdate" class="form-label">Thuộc Tỉnh/Thành phố:<span class="text-danger">*</span></label>
                        <select class="form-select @error('province_id', 'updateDistrict') is-invalid @enderror" id="districtProvinceIdUpdate" name="province_id" required>
                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            @foreach($allProvinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                        @error('province_id', 'updateDistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="districtNameUpdate" class="form-label">Tên Quận/Huyện:<span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'updateDistrict') is-invalid @enderror" id="districtNameUpdate" name="name" required value="{{ old('name') }}">
                        @error('name', 'updateDistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="districtGsoIdUpdate" class="form-label">Mã GSO (nếu có):</label>
                        <input type="text" class="form-control @error('gso_id', 'updateDistrict') is-invalid @enderror" id="districtGsoIdUpdate" name="gso_id" value="{{ old('gso_id') }}">
                        @error('gso_id', 'updateDistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

{{-- Modal Delete District --}}
<div class="modal fade" id="deleteDistrictModal" tabindex="-1" aria-labelledby="deleteDistrictModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteDistrictModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa Quận/Huyện</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteDistrictForm" method="POST" action=""> {{-- Action sẽ được set bằng JS --}}
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa Quận/Huyện "<strong id="deleteDistrictName"></strong>" không?</p>
                    <p class="text-danger"><strong>Lưu ý:</strong> Thao tác này sẽ tự động xóa tất cả các Phường/Xã liên quan (nếu không có ràng buộc khóa ngoại từ bảng khác như đơn hàng).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Quận/Huyện</button>
                </div>
            </form>
        </div>
    </div>
</div>