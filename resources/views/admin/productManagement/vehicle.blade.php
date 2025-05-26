@extends('admin.layouts.app')

@section('title', 'Vehicle Modals') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-car-front-fill me-2"></i>Các Dòng Xe</h1>
    </header>

    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">
                    <i class="bi bi-car-front-fill me-2"></i>Quản lý Dòng xe (Mẫu xe)
                    {{-- Hiển thị tên hãng xe nếu đang lọc theo hãng (phần này sẽ cần logic backend) --}}
                    {{-- @if(isset($selectedVehicleBrand))
                    <span class="text-muted fst-italic">- {{ $selectedVehicleBrand->name }}</span>
                    @endif --}}
                </h2>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createVehicleModelModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Dòng xe mới
                </button>
            </div>
            <div class="card-body">
                {{-- (Tùy chọn) Bộ lọc theo Hãng xe --}}
                <form method="GET" action="#" class="mb-3"> {{-- Action # tạm thời --}}
                    <div class="row">
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" name="vehicle_brand_id"
                                onchange="this.form.submit()">
                                <option value="">-- Lọc theo Hãng xe --</option>
                                {{-- Giả sử bạn có biến $allVehicleBrands từ controller --}}
                                {{-- @foreach($allVehicleBrands as $brand) --}}
                                {{-- <option value="{{ $brand->id }}">{{ $brand->name }}</option> --}}
                                {{-- @endforeach --}}
                                <option value="1">Honda</option>
                                <option value="2">Yamaha</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tên Dòng xe</th>
                                <th scope="col">Hãng xe</th>
                                <th scope="col">Năm sản xuất</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu --}}
                            <tr>
                                <td>1</td>
                                <td>Wave Alpha</td>
                                <td>Honda</td>
                                <td>2023</td>
                                <td>Dòng xe số phổ thông, bền bỉ.</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateVehicleModelModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteVehicleModelModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Sirius</td>
                                <td>Yamaha</td>
                                <td>2022</td>
                                <td>Thiết kế thể thao, tiết kiệm nhiên liệu.</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateVehicleModelModal" title="Cập nhật">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deleteVehicleModelModal" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Nếu không có dữ liệu --}}
                            {{-- <tr>
                                <td colspan="6" class="text-center">Không có dòng xe nào.</td>
                            </tr> --}}
                        </tbody>
                    </table>
                </div>
                {{-- Phân trang (sẽ cần logic backend) --}}
                {{-- <div class="mt-3">
                    {{ $vehicleModels->links() }}
                </div> --}}
            </div>
        </div>
    </div>

    {{-- Modals for Vehicle Model Management --}}

    <div class="modal fade" id="createVehicleModelModal" tabindex="-1" aria-labelledby="createVehicleModelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createVehicleModelModalLabel"><i class="bi bi-plus-circle-fill me-2"></i>Tạo
                        Dòng xe mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createVehicleModelForm"> {{-- Bỏ action và method --}}
                        <div class="mb-3">
                            <label for="vmVehicleBrandCreate" class="form-label">Hãng xe:<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="vmVehicleBrandCreate" name="vehicle_brand_id" required>
                                <option value="">-- Chọn Hãng xe --</option>
                                {{-- Lặp qua danh sách các hãng xe từ controller --}}
                                <option value="1">Honda</option>
                                <option value="2">Yamaha</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="vmNameCreate" class="form-label">Tên Dòng xe:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vmNameCreate" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="vmYearCreate" class="form-label">Năm sản xuất:</label>
                            <input type="number" class="form-control" id="vmYearCreate" name="year" min="1900"
                                max="{{ date('Y') + 1 }}">
                        </div>
                        <div class="mb-3">
                            <label for="vmDescriptionCreate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="vmDescriptionCreate" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="createVehicleModelForm">Lưu Dòng xe</button> {{--
                    type="button" --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateVehicleModelModal" tabindex="-1" aria-labelledby="updateVehicleModelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateVehicleModelModalLabel"><i class="bi bi-pencil-square me-2"></i>Cập
                        nhật Dòng xe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateVehicleModelForm"> {{-- Bỏ action và method --}}
                        <input type="hidden" id="vmIdUpdate" name="vehicle_model_id">
                        <div class="mb-3">
                            <label for="vmVehicleBrandUpdate" class="form-label">Hãng xe:<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="vmVehicleBrandUpdate" name="vehicle_brand_id" required>
                                <option value="">-- Chọn Hãng xe --</option>
                                <option value="1" selected>Honda</option> {{-- Dữ liệu mẫu --}}
                                <option value="2">Yamaha</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="vmNameUpdate" class="form-label">Tên Dòng xe:<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vmNameUpdate" name="name" value="Wave Alpha"
                                required> {{-- Dữ liệu mẫu --}}
                        </div>
                        <div class="mb-3">
                            <label for="vmYearUpdate" class="form-label">Năm sản xuất:</label>
                            <input type="number" class="form-control" id="vmYearUpdate" name="year" value="2023" min="1900"
                                max="{{ date('Y') + 1 }}"> {{-- Dữ liệu mẫu --}}
                        </div>
                        <div class="mb-3">
                            <label for="vmDescriptionUpdate" class="form-label">Mô tả:</label>
                            <textarea class="form-control" id="vmDescriptionUpdate" name="description"
                                rows="3">Dòng xe số phổ thông, bền bỉ.</textarea> {{-- Dữ liệu mẫu --}}
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="updateVehicleModelForm">Lưu thay đổi</button> {{--
                    type="button" --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteVehicleModelModal" tabindex="-1" aria-labelledby="deleteVehicleModelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteVehicleModelModalLabel"><i class="bi bi-trash-fill me-2"></i>Xác nhận
                        Xóa Dòng xe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa dòng xe "<strong id="deleteVehicleModelName">Wave Alpha</strong>" không?
                    </p> {{-- Dữ liệu mẫu --}}
                    <p class="text-danger">Lưu ý: Thao tác này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteVehicleModelForm"> {{-- Bỏ action và method --}}
                        <button type="button" class="btn btn-danger">Xóa Dòng xe</button> {{-- type="button" --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{--
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Xử lý điền dữ liệu vào modal Update
            const updateVehicleModelModal = document.getElementById('updateVehicleModelModal');
            if (updateVehicleModelModal) {
                updateVehicleModelModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.dataset.id;
                    const name = button.dataset.name;
                    const vehicleBrandId = button.dataset.vehicle_brand_id;
                    const year = button.dataset.year;
                    const description = button.dataset.description;

                    const modalTitle = updateVehicleModelModal.querySelector('.modal-title');
                    const form = updateVehicleModelModal.querySelector('#updateVehicleModelForm');

                    form.action = `{{ url('admin/product-management/vehicle-models') }}/${id}`; // Cập nhật action của form

                    updateVehicleModelModal.querySelector('#vmIdUpdate').value = id;
                    updateVehicleModelModal.querySelector('#vmNameUpdate').value = name;
                    updateVehicleModelModal.querySelector('#vmVehicleBrandUpdate').value = vehicleBrandId;
                    updateVehicleModelModal.querySelector('#vmYearUpdate').value = year;
                    updateVehicleModelModal.querySelector('#vmDescriptionUpdate').value = description;
                    modalTitle.textContent = `Cập nhật Dòng xe: ${name}`;
                });
            }

            // Xử lý điền dữ liệu vào modal Delete
            const deleteVehicleModelModal = document.getElementById('deleteVehicleModelModal');
            if (deleteVehicleModelModal) {
                deleteVehicleModelModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.dataset.id;
                    const name = button.dataset.name;

                    const form = deleteVehicleModelModal.querySelector('#deleteVehicleModelForm');
                    form.action = `{{ url('admin/product-management/vehicle-models') }}/${id}`; // Cập nhật action của form
                    deleteVehicleModelModal.querySelector('#deleteVehicleModelName').textContent = name;
                });
            }
        });
    </script> --}}
@endpush