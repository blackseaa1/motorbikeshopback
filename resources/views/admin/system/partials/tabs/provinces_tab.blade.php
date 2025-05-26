<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Danh sách Tỉnh/Thành phố</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
        data-bs-target="#createProvinceModal">
        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Tỉnh/Thành mới
    </button>
</div>

<form method="GET" action="{{ route('admin.system.geography.index') }}" class="mb-3">
    <input type="hidden" name="tab" value="provinces">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="search_name_province" class="form-label">Tên Tỉnh/Thành:</label>
            <input type="text" class="form-control form-control-sm" id="search_name_province"
                name="search_name_province" value="{{ request('search_name_province') }}">
        </div>
        <div class="col-md-3">
            <label for="search_gso_id_province" class="form-label">Mã GSO:</label>
            <input type="text" class="form-control form-control-sm" id="search_gso_id_province"
                name="search_gso_id_province" value="{{ request('search_gso_id_province') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i>
                Lọc</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.system.geography.index', ['tab' => 'provinces']) }}"
                class="btn btn-secondary btn-sm w-100"><i class="bi bi-arrow-clockwise"></i>
                Reset</a>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Tên Tỉnh/Thành phố</th>
                <th scope="col">Mã GSO</th>
                <th scope="col" class="text-center">Số Quận/Huyện</th>
                <th scope="col" class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($provinces as $province)
                <tr>
                    <td>{{ $province->id }}</td>
                    <td>{{ $province->name }}</td>
                    <td>{{ $province->gso_id ?? 'N/A' }}</td>
                    <td class="text-center">{{ $province->districts_count }}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                            data-bs-target="#updateProvinceModal" 
                            data-id="{{ $province->id }}"
                            data-name="{{ $province->name }}"
                            data-gso_id="{{ $province->gso_id ?? '' }}" title="Cập nhật">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                            data-bs-target="#deleteProvinceModal" 
                            data-id="{{ $province->id }}"
                            data-name="{{ $province->name }}" title="Xóa">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Không có Tỉnh/Thành phố nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(isset($provinces) && $provinces->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{ $provinces->appends(request()->query())->links('admin.vendor.pagination') }}
    </div>
@endif