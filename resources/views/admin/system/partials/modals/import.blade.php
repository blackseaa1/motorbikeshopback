    {{-- Modal Import Dữ liệu Địa lý --}}
    <div class="modal fade" id="importGeographyModal" tabindex="-1" aria-labelledby="importGeographyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importGeographyModalLabel"><i
                            class="bi bi-file-earmark-arrow-up-fill me-2"></i>Import Dữ liệu Địa lý từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.system.geography.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p>Sử dụng file Excel (.xls, .xlsx) theo cấu trúc từ <a
                                href="https://github.com/kimtrien/vietnam-zone" target="_blank"
                                rel="noopener noreferrer">kimtrien/vietnam-zone</a> để import hàng loạt.</p>
                        <div class="mb-3">
                            <label for="geography_file_modal" class="form-label">Chọn file Excel:</label>
                            <input class="form-control @error('geography_file', 'importGeography') is-invalid @enderror"
                                type="file" id="geography_file_modal" name="geography_file" required accept=".xls,.xlsx">
                            @error('geography_file', 'importGeography')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Lưu ý: Quá trình import có thể mất vài phút. Dữ liệu trùng lặp (dựa trên tên và đơn vị hành chính cấp trên) sẽ được bỏ qua.
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i> Bắt đầu Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>