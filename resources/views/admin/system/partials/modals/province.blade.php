{{-- Modal Create Province --}}
<div class="modal fade" id="createProvinceModal" tabindex="-1" aria-labelledby="createProvinceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProvinceModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Thêm
                    Tỉnh/Thành phố mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createProvinceForm" method="POST" action="{{ route('admin.system.geography.provinces.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="provinceNameCreate" class="form-label">Tên Tỉnh/Thành phố:<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'storeProvince') is-invalid @enderror"
                            id="provinceNameCreate" name="name" value="{{ old('name') }}" required>
                        @error('name', 'storeProvince') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="provinceGsoIdCreate" class="form-label">Mã GSO (nếu có):</label>
                        <input type="text" class="form-control @error('gso_id', 'storeProvince') is-invalid @enderror"
                            id="provinceGsoIdCreate" name="gso_id" value="{{ old('gso_id') }}">
                        @error('gso_id', 'storeProvince') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu Tỉnh/Thành</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Update Province --}}
<div class="modal fade" id="updateProvinceModal" tabindex="-1" aria-labelledby="updateProvinceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProvinceModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập nhật
                    Tỉnh/Thành phố</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateProvinceForm" method="POST" action=""> {{-- Action sẽ được set bằng JS --}}
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="provinceNameUpdate" class="form-label">Tên Tỉnh/Thành phố:<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'updateProvince') is-invalid @enderror"
                            id="provinceNameUpdate" name="name" required value="{{ old('name') }}">
                        @error('name', 'updateProvince') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="provinceGsoIdUpdate" class="form-label">Mã GSO (nếu có):</label>
                        <input type="text" class="form-control @error('gso_id', 'updateProvince') is-invalid @enderror"
                            id="provinceGsoIdUpdate" name="gso_id" value="{{ old('gso_id') }}">
                        @error('gso_id', 'updateProvince') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

{{-- Modal Delete Province --}}
<div class="modal fade" id="deleteProvinceModal" tabindex="-1" aria-labelledby="deleteProvinceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProvinceModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận Xóa
                    Tỉnh/Thành phố</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="deleteProvinceForm" method="POST" action=""> {{-- Action sẽ được set bằng JS --}}
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa Tỉnh/Thành phố "<strong id="deleteProvinceName"></strong>" không?</p>
                    <p class="text-danger"><strong>Lưu ý:</strong> Thao tác này sẽ tự động xóa tất cả các Quận/Huyện và
                        Phường/Xã liên quan (nếu không có ràng buộc khóa ngoại từ bảng khác như đơn hàng).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Tỉnh/Thành</button>
                </div>
            </form>
        </div>
    </div>
</div>