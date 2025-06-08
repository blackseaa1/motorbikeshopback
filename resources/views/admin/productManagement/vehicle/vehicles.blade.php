@extends('admin.layouts.app')

@section('title', 'Quản lý Xe')@section('content')<div id="adminVehicleManagementPage">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-truck me-2"></i>Quản lý Hãng xe & Dòng xe</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Quản lý sản phẩm</li>
                            <li class="breadcrumb-item active">Hãng & Dòng xe</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <ul class="nav nav-tabs" id="vehicleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-vehicle-brands-button" data-bs-toggle="tab"
                            data-bs-target="#tab-vehicle-brands-content" type="button" role="tab"
                            aria-controls="tab-vehicle-brands-content" aria-selected="true">
                            <i class="bi bi-building me-1"></i> Hãng xe
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-vehicle-models-button" data-bs-toggle="tab"
                            data-bs-target="#tab-vehicle-models-content" type="button" role="tab"
                            aria-controls="tab-vehicle-models-content" aria-selected="false">
                            <i class="bi bi-car-front-fill me-1"></i> Dòng xe
                        </button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="vehicleTabsContent">
                    {{-- TAB HÃNG XE --}}
                    <div class="tab-pane fade show active" id="tab-vehicle-brands-content" role="tabpanel"
                        aria-labelledby="tab-vehicle-brands-button">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>Danh sách Hãng xe</h2>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#createVehicleBrandModal">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Hãng xe mới
                                </button>
                            </div>
                            <div class="card-body">
                                @if ($vehicleBrands->isEmpty())
                                    <div class="alert alert-info mb-0"><i class="bi bi-info-circle me-2"></i>Hiện chưa có hãng
                                        xe nào.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%;">STT</th>
                                                    <th style="width: 10%;" class="text-center">Logo</th>
                                                    <th style="width: 25%;">Tên Hãng xe</th>
                                                    <th>Mô tả</th>
                                                    <th style="width: 10%;" class="text-center">Trạng thái</th>
                                                    <th class="text-center" style="width: 25%;">Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($vehicleBrands as $vehicleBrand)
                                                    <tr id="vehicle-brand-row-{{ $vehicleBrand->id }}"
                                                        class="{{ !$vehicleBrand->isActive() ? 'row-inactive' : '' }}">
                                                        <td>{{ $vehicleBrands->firstItem() + $loop->index }}</td>
                                                        <td class="text-center">
                                                            <img src="{{ $vehicleBrand->logo_url ? Storage::url($vehicleBrand->logo_url) : 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=N/A' }}"
                                                                alt="{{ $vehicleBrand->name }}" class="img-thumbnail"
                                                                style="width: 50px; height: 50px; object-fit: contain;">
                                                        </td>
                                                        <td>{{ $vehicleBrand->name }}</td>
                                                        <td>{{ Str::words($vehicleBrand->description, 12, '...') ?? 'Không có mô tả' }}
                                                        </td>
                                                        <td class="text-center status-cell"
                                                            id="vehicle-brand-status-{{ $vehicleBrand->id }}">
                                                            @if ($vehicleBrand->isActive())
                                                                <span class="badge bg-success">Hoạt động</span>
                                                            @else
                                                                <span class="badge bg-secondary">Đã ẩn</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center action-buttons">
                                                            <button type="button"
                                                                class="btn btn-sm btn-success btn-view-vehicle-brand"
                                                                data-bs-toggle="modal" data-bs-target="#viewVehicleBrandModal"
                                                                data-id="{{ $vehicleBrand->id }}"
                                                                data-name="{{ $vehicleBrand->name }}"
                                                                data-description="{{ $vehicleBrand->description ?? '' }}"
                                                                data-status="{{ $vehicleBrand->status }}"
                                                                data-logo-url="{{ $vehicleBrand->logo_url ? Storage::url($vehicleBrand->logo_url) : 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO' }}"
                                                                data-created-at="{{ $vehicleBrand->created_at->format('H:i:s d/m/Y') }}"
                                                                data-updated-at="{{ $vehicleBrand->updated_at->format('H:i:s d/m/Y') }}"
                                                                data-update-url="{{ route('admin.productManagement.vehicleBrands.update', $vehicleBrand->id) }}"
                                                                title="Xem chi tiết"><i class="bi bi-eye-fill"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary toggle-status-btn"
                                                                data-id="{{ $vehicleBrand->id }}"
                                                                data-url="{{ route('admin.productManagement.vehicleBrands.toggleStatus', $vehicleBrand->id) }}"
                                                                title="{{ $vehicleBrand->isActive() ? 'Ẩn' : 'Hiện' }}"><i
                                                                    class="bi {{ $vehicleBrand->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-info btn-edit-vehicle-brand"
                                                                data-bs-toggle="modal" data-bs-target="#updateVehicleBrandModal"
                                                                data-id="{{ $vehicleBrand->id }}"
                                                                data-name="{{ $vehicleBrand->name }}"
                                                                data-description="{{ $vehicleBrand->description ?? '' }}"
                                                                data-status="{{ $vehicleBrand->status }}"
                                                                data-logo-url="{{ $vehicleBrand->logo_url ? Storage::url($vehicleBrand->logo_url) : 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=N/A' }}"
                                                                data-update-url="{{ route('admin.productManagement.vehicleBrands.update', $vehicleBrand->id) }}"
                                                                title="Cập nhật"><i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger btn-delete-vehicle-brand"
                                                                data-bs-toggle="modal" data-bs-target="#deleteVehicleBrandModal"
                                                                data-id="{{ $vehicleBrand->id }}"
                                                                data-name="{{ $vehicleBrand->name }}"
                                                                data-delete-url="{{ route('admin.productManagement.vehicleBrands.destroy', $vehicleBrand->id) }}"
                                                                title="Xóa"><i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $vehicleBrands->appends(request()->except('models_page'))->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        {{-- Include Modals for Vehicle Brands --}}
                        @include('admin.productManagement.vehicle.partials.vehicleBrandModals')
                    </div>

                    {{-- TAB DÒNG XE --}}
                    <div class="tab-pane fade" id="tab-vehicle-models-content" role="tabpanel"
                        aria-labelledby="tab-vehicle-models-button">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0 text-primary">
                                    <i class="bi bi-list-task me-2"></i>Danh sách Dòng xe
                                    @if(isset($selectedBrandIdForFilter) && $allVehicleBrandsForFilter->find($selectedBrandIdForFilter))
                                        <span class="text-muted fw-normal small">- Hãng:
                                            {{ $allVehicleBrandsForFilter->find($selectedBrandIdForFilter)->name }}</span>
                                    @endif
                                </h2>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#createVehicleModelModal">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Dòng xe mới
                                </button>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.productManagement.vehicle.index') }}"
                                    class="mb-3">
                                    <input type="hidden" name="tab" value="models"> {{-- Để giữ tab active khi submit filter
                                    --}}
                                    <div class="row gx-2">
                                        <div class="col-md-4">
                                            <label for="filter_vehicle_brand_id_models"
                                                class="form-label visually-hidden">Lọc theo hãng xe</label>
                                            <select class="form-select form-select-sm" name="filter_vehicle_brand_id"
                                                id="filter_vehicle_brand_id_models">
                                                <option value="">-- Tất cả Hãng xe --</option>
                                                @foreach($allVehicleBrandsForFilter as $brand)
                                                    <option value="{{ $brand->id }}" {{ ($selectedBrandIdForFilter == $brand->id) ? 'selected' : '' }}>
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Lọc</button>
                                            <a href="{{ route('admin.productManagement.vehicle.index', ['tab' => 'models']) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Bỏ lọc"><i
                                                    class="bi bi-x-lg"></i></a>
                                        </div>
                                    </div>
                                </form>

                                @if ($vehicleModels->isEmpty())
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>Không có dòng xe nào
                                        @if(isset($selectedBrandIdForFilter) && $allVehicleBrandsForFilter->find($selectedBrandIdForFilter))
                                        của hãng {{ $allVehicleBrandsForFilter->find($selectedBrandIdForFilter)->name }} @endif.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%;">STT</th>
                                                    <th>Tên Dòng xe</th>
                                                    <th>Hãng xe</th>
                                                    <th class="text-center">Năm SX</th>
                                                    <th>Mô tả</th>
                                                    <th class="text-center" style="width:10%">Trạng thái</th>
                                                    <th class="text-center" style="width: 25%;">Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($vehicleModels as $model)
                                                    <tr id="vehicle-model-row-{{ $model->id }}"
                                                        class="{{ !$model->isActive() ? 'row-inactive' : '' }}">
                                                        <td>{{ $vehicleModels->firstItem() + $loop->index }}</td>
                                                        <td>{{ $model->name }}</td>
                                                        <td>{{ $model->vehicleBrand->name ?? 'N/A' }}</td>
                                                        <td class="text-center">{{ $model->year ?? 'N/A' }}</td>
                                                        <td>{{ Str::words($model->description, 10, '...') ?? 'Không có' }}</td>
                                                        <td class="text-center status-cell"
                                                            id="vehicle-model-status-{{ $model->id }}">
                                                            @if ($model->isActive())
                                                                <span class="badge bg-success">Hoạt động</span>
                                                            @else
                                                                <span class="badge bg-secondary">Đã ẩn</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center action-buttons">
                                                            <button type="button"
                                                                class="btn btn-sm btn-success btn-view-vehicle-model"
                                                                data-bs-toggle="modal" data-bs-target="#viewVehicleModelModal"
                                                                data-id="{{ $model->id }}" data-name="{{ $model->name }}"
                                                                data-vehicle-brand-id="{{ $model->vehicle_brand_id }}"
                                                                data-vehicle-brand-name="{{ $model->vehicleBrand->name ?? 'N/A' }}"
                                                                data-year="{{ $model->year }}"
                                                                data-description="{{ $model->description ?? '' }}"
                                                                data-status="{{ $model->status }}"
                                                                data-created-at="{{ $model->created_at->format('H:i:s d/m/Y') }}"
                                                                data-updated-at="{{ $model->updated_at->format('H:i:s d/m/Y') }}"
                                                                data-update-url="{{ route('admin.productManagement.vehicleModels.update', $model->id) }}"
                                                                title="Xem chi tiết"><i class="bi bi-eye-fill"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary toggle-status-btn"
                                                                data-id="{{ $model->id }}"
                                                                data-url="{{ route('admin.productManagement.vehicleModels.toggleStatus', $model->id) }}"
                                                                title="{{ $model->isActive() ? 'Ẩn' : 'Hiện' }}"><i
                                                                    class="bi {{ $model->isActive() ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-info btn-edit-vehicle-model"
                                                                data-bs-toggle="modal" data-bs-target="#updateVehicleModelModal"
                                                                data-id="{{ $model->id }}" data-name="{{ $model->name }}"
                                                                data-vehicle-brand-id="{{ $model->vehicle_brand_id }}"
                                                                data-year="{{ $model->year }}"
                                                                data-description="{{ $model->description ?? '' }}"
                                                                data-status="{{ $model->status }}"
                                                                data-update-url="{{ route('admin.productManagement.vehicleModels.update', $model->id) }}"
                                                                title="Cập nhật"><i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger btn-delete-vehicle-model"
                                                                data-bs-toggle="modal" data-bs-target="#deleteVehicleModelModal"
                                                                data-id="{{ $model->id }}" data-name="{{ $model->name }}"
                                                                data-delete-url="{{ route('admin.productManagement.vehicleModels.destroy', $model->id) }}"
                                                                title="Xóa"><i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $vehicleModels->appends(request()->except('brands_page'))->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        {{-- Include Modals for Vehicle Models --}}
                        @include('admin.productManagement.vehicle.partials.vehicleModelModals', ['allVehicleBrands' => $allVehicleBrandsForFilter])
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- Include file JS hợp nhất mới --}}
    <script src="{{ asset('assets_admin/js/vehicle_manager_combined.js') }}"></script> {{-- Tên file mới --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('tab-vehicle-brands-content') && typeof initializeVehicleBrandsPage === 'function') {
                initializeVehicleBrandsPage();
            }
            if (document.getElementById('tab-vehicle-models-content') && typeof initializeVehicleModelsPage === 'function') {
                initializeVehicleModelsPage();
            }

            @if ($errors->any())
                const oldFormMarker = @json(old('_form_marker'));
                if (oldFormMarker === 'create_vehicle_brand' && document.getElementById('createVehicleBrandModal')) {
                    new bootstrap.Modal(document.getElementById('createVehicleBrandModal')).show();
                    const brandTabButton = document.getElementById('tab-vehicle-brands-button');
                    if (brandTabButton) new bootstrap.Tab(brandTabButton).show();
                } else if (oldFormMarker === 'create_vehicle_model' && document.getElementById('createVehicleModelModal')) {
                    new bootstrap.Modal(document.getElementById('createVehicleModelModal')).show();
                    const modelTabButton = document.getElementById('tab-vehicle-models-button');
                    if (modelTabButton) new bootstrap.Tab(modelTabButton).show();
                }
            @endif

                const urlParams = new URLSearchParams(window.location.search);
            let activeTabId = urlParams.get('tab');
            if (!activeTabId) { // Nếu không có query param, thử lấy từ sessionStorage
                activeTabId = sessionStorage.getItem('activeVehicleManagementTab');
            }

            if (activeTabId === 'models' && document.getElementById('tab-vehicle-models-button')) {
                new bootstrap.Tab(document.getElementById('tab-vehicle-models-button')).show();
            } else if (activeTabId === 'brands' && document.getElementById('tab-vehicle-brands-button')) {
                new bootstrap.Tab(document.getElementById('tab-vehicle-brands-button')).show();
            } else {
                // Mặc định active tab đầu tiên nếu không có gì được chỉ định
                const defaultTab = document.querySelector('#vehicleTabs button[data-bs-toggle="tab"]');
                if (defaultTab) new bootstrap.Tab(defaultTab).show();
            }

            const vehicleTabs = document.querySelectorAll('#vehicleTabs button[data-bs-toggle="tab"]');
            vehicleTabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function (event) {
                    sessionStorage.setItem('activeVehicleManagementTab', event.target.id.includes('models') ? 'models' : 'brands');
                    // Cập nhật URL không reload trang để giữ query param (nếu muốn)
                    const url = new URL(window.location);
                    url.searchParams.set('tab', event.target.id.includes('models') ? 'models' : 'brands');
                    window.history.pushState({}, '', url);
                });
            });
        });
    </script>
@endpush