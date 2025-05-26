{{-- Modal Create Ward --}}
<div class="modal fade" id="createWardModal" tabindex="-1" aria-labelledby="createWardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createWardModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Thêm Phường/Xã mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createWardForm" method="POST" action="{{ route('admin.system.geography.wards.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="provinceForWardCreate" class="form-label">Thuộc Tỉnh/Thành phố:<span class="text-danger">*</span></label>
                        <select class="form-select" id="provinceForWardCreate" name="province_id_for_ward">
                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            @foreach($allProvinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id_for_ward') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="wardDistrictIdCreate" class="form-label">Thuộc Quận/Huyện:<span class="text-danger">*</span></label>
                        <select class="form-select @error('district_id', 'storeWard') is-invalid @enderror" id="wardDistrictIdCreate" name="district_id" required disabled>
                            <option value="">-- Chọn Tỉnh/Thành trước --</option>
                            {{-- Options loaded by JS --}}
                        </select>
                        @error('district_id', 'storeWard') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="wardNameCreate" class="form-label">Tên Phường/Xã:<span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'storeWard') is-invalid @enderror" id="wardNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name', 'storeWard') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="wardGsoIdCreate" class="form-label">Mã GSO (nếu có):</label>
                        <input type="text" class="form-control @error('gso_id', 'storeWard') is-invalid @enderror" id="wardGsoIdCreate" name="gso_id" value="{{ old('gso_id') }}">
                        @error('gso_id', 'storeWard') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Phường/Xã</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Update Ward --}}
<div class="modal fade" id="updateWardModal" tabindex="-1" aria-labelledby="updateWardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateWardModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật Phường/Xã</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateWardForm" method="POST" action=""> {{-- Action sẽ được set bằng JS --}}
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="provinceForWardUpdate" class="form-label">Thuộc Tỉnh/Thành phố:<span class="text-danger">*</span></label>
                        <select class="form-select" id="provinceForWardUpdate" name="province_id_for_ward">
                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            @foreach($allProvinces as $province)
                                 <option value="{{ $province->id }}" {{ old('province_id_for_ward') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="wardDistrictIdUpdate" class="form-label">Thuộc Quận/Huyện:<span class="text-danger">*</span></label>
                        <select class="form-select @error('district_id', 'updateWard') is-invalid @enderror" id="wardDistrictIdUpdate" name="district_id" required>
                            <option value="">-- Chọn Tỉnh/Thành trước --</option>
                            {{-- Options loaded by JS. If old('district_id') exists, JS will select it. --}}
                        </select>
                        @error('district_id', 'updateWard') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="wardNameUpdate" class="form-label">Tên Phường/Xã:<span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'updateWard') is-invalid @enderror" id="wardNameUpdate" name="name" required value="{{ old('name') }}">
                        @error('name', 'updateWard') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="wardGsoIdUpdate" class="form-label">Mã GSO (nếu có):</label>
                        <input type="text" class="form-control @error('gso_id', 'updateWard') is-invalid @enderror" id="wardGsoIdUpdate" name="gso_id" value="{{ old('gso_id') }}">
                        @error('gso_id', 'updateWard') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

{{-- Modal Delete Ward --}}
<div class="modal fade" id="deleteWardModal" tabindex="-1" aria-labelledby="deleteWardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteWardModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa Phường/Xã</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteWardForm" method="POST" action=""> {{-- Action sẽ được set bằng JS --}}
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa Phường/Xã "<strong id="deleteWardName"></strong>" không?</p>
                     <p class="text-danger"><strong>Lưu ý:</strong> Kiểm tra ràng buộc khóa ngoại (ví dụ: đơn hàng) trước khi xóa.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Phường/Xã</button>
                </div>
            </form>
        </div>
    </div>
</div>