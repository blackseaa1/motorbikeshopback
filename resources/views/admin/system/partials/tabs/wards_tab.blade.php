<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Danh sách Phường/Xã</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createWardModal">
        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Phường/Xã mới
    </button>
</div>

<form method="GET" action="{{ route('admin.system.geography.index') }}" class="mb-3">
    <input type="hidden" name="tab" value="wards">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="search_name_ward" class="form-label">Tên Phường/Xã:</label>
            <input type="text" class="form-control form-control-sm" id="search_name_ward"
                name="search_name_ward" value="{{ request('search_name_ward') }}">
        </div>
        <div class="col-md-3">
            <label for="filter_province_for_ward_display" class="form-label">Lọc theo
                Tỉnh/Thành:</label>
            <select class="form-select form-select-sm" id="filter_province_for_ward_display"
                name="filter_province_for_ward_display">
                <option value="">-- Chọn Tỉnh/Thành --</option>
                @foreach($allProvinces as $province)
                    <option value="{{ $province->id }}" @selected(request('filter_province_for_ward_display') == $province->id)>
                        {{ $province->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="filter_district_for_ward" class="form-label">Lọc theo Quận/Huyện:</label>
            <select class="form-select form-select-sm" id="filter_district_for_ward"
                name="filter_district_for_ward" @disabled(!request('filter_province_for_ward_display'))>
                <option value="">-- Chọn Quận/Huyện --</option>
                {{-- JS sẽ điền các quận/huyện vào đây --}}
                @if(request('filter_province_for_ward_display') && isset($allDistricts))
                    @foreach($allDistricts as $d)
                        <option value="{{ $d->id }}" @selected(request('filter_district_for_ward') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i>
                Lọc</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('admin.system.geography.index', ['tab' => 'wards']) }}"
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
                <th scope="col">Tên Phường/Xã</th>
                <th scope="col">Thuộc Quận/Huyện</th>
                <th scope="col">Thuộc Tỉnh/Thành</th>
                <th scope="col">Mã GSO</th>
                <th scope="col" class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($wards as $ward)
                <tr>
                    <td>{{ $ward->id }}</td>
                    <td>{{ $ward->name }}</td>
                    <td>{{ $ward->district->name ?? 'N/A' }}</td>
                    <td>{{ $ward->district->province->name ?? 'N/A' }}</td>
                    <td>{{ $ward->gso_id ?? 'N/A' }}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                            data-bs-target="#updateWardModal" 
                            data-id="{{ $ward->id }}"
                            data-name="{{ $ward->name }}" 
                            data-gso_id="{{ $ward->gso_id ?? '' }}"
                            data-district_id="{{ $ward->district_id }}"
                            data-province_id="{{ $ward->district->province_id ?? ''}}"
                            data-name-for-js="district_id"
                            title="Cập nhật">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                            data-bs-target="#deleteWardModal" data-id="{{ $ward->id }}"
                            data-name="{{ $ward->name }}" title="Xóa">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Không có Phường/Xã nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(isset($wards) && $wards->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{ $wards->appends(request()->query())->links('admin.vendor.pagination') }}
    </div>
@endif