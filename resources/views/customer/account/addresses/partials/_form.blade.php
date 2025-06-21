<div class="row">
    <div class="col-md-6 mb-3">
        <label for="full_name" class="form-label">Họ và tên</label>
        <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $address->full_name) }}" required>
        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Số điện thoại</label>
        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $address->phone) }}" required>
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 mb-3">
        <label for="address_line" class="form-label">Địa chỉ chi tiết (Số nhà, tên đường...)</label>
        <input type="text" class="form-control @error('address_line') is-invalid @enderror" id="address_line" name="address_line" value="{{ old('address_line', $address->address_line) }}" required>
        @error('address_line')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Phần chọn địa chỉ Tỉnh/Huyện/Xã sẽ cần JS để hoạt động --}}
    <div class="col-md-4 mb-3">
        <label for="province_id" class="form-label">Tỉnh/Thành phố</label>
        <select class="form-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id" required>
            <option value="">Chọn Tỉnh/Thành</option>
            @foreach($provinces as $province)
                <option value="{{ $province->id }}" {{ old('province_id', $address->province_id) == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
            @endforeach
        </select>
        @error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="district_id" class="form-label">Quận/Huyện</label>
        <select class="form-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
            {{-- JS sẽ điền các lựa chọn vào đây --}}
        </select>
        @error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="ward_id" class="form-label">Phường/Xã</label>
        <select class="form-select @error('ward_id') is-invalid @enderror" id="ward_id" name="ward_id" required>
            {{-- JS sẽ điền các lựa chọn vào đây --}}
        </select>
        @error('ward_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">
                Đặt làm địa chỉ mặc định
            </label>
        </div>
    </div>
</div>