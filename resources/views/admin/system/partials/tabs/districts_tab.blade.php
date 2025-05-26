<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Danh sách Quận/Huyện</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
        data-bs-target="#createDistrictModal">
        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Quận/Huyện mới
    </button>
</div>

<form method="GET" action="{{ route('admin.system.geography.index') }}" class="mb-3">
    <input type="hidden" name="tab" value="districts">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="search_name_district" class="form-label">Tên Quận/Huyện:</label>
            <input type="text" class="form-control form-control-sm" id="search_name_district"
                name="search_name_district" value="{{ request('search_name_district') }}">
        </div>
        <div class="col-md-3">
            <label for="filter_province_for_district" class="form-label">Lọc theo
                Tỉnh/Thành:</label>
            <select class="form-select form-select-sm" id="filter_province_for_district"
                name="filter_province_for_district">
                <option value="">-- Tất cả Tỉnh/Thành --</option>
                @foreach($allProvinces as $province)
                    <option value="{{ $province->id }}" @selected(request('filter_province_for_district') == $province->id)>
                        {{ $province->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i>
                Lọc</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.system.geography.index', ['tab' => 'districts']) }}"
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
                <th scope="col">Tên Quận/Huyện</th>
                <th scope="col">Thuộc Tỉnh/Thành</th>
                <th scope="col">Mã GSO</th>
                <th scope="col" class="text-center">Số Phường/Xã</th>
                <th scope="col" class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($districts as $district)
                <tr>
                    <td>{{ $district->id }}</td>
                    <td>{{ $district->name }}</td>
                    <td>{{ $district->province->name ?? 'N/A' }}</td>
                    <td>{{ $district->gso_id ?? 'N/A' }}</td>
                    <td class="text-center">{{ $district->wards_count }}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                            data-bs-target="#updateDistrictModal" 
                            data-id="{{ $district->id }}"
                            data-name="{{ $district->name }}"
                            data-gso_id="{{ $district->gso_id ?? '' }}"
                            data-province_id="{{ $district->province_id }}" title="Cập nhật">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                            data-bs-target="#deleteDistrictModal" 
                            data-id="{{ $district->id }}"
                            data-name="{{ $district->name }}" title="Xóa">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Không có Quận/Huyện nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(isset($districts) && $districts->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{ $districts->appends(request()->query())->links('admin.vendor.pagination') }}
    </div>
@endif