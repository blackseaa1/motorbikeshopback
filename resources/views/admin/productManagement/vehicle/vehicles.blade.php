@extends('admin.layouts.app')

@section('title', 'Quản lý Xe')

@section('content')
    <div id="adminVehicleManagementPage">
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
                        <button class="nav-link {{ $activeTab === 'brands' ? 'active' : '' }}"
                            id="tab-vehicle-brands-button" data-bs-toggle="tab"
                            data-bs-target="#tab-vehicle-brands-content" type="button" role="tab"
                            aria-controls="tab-vehicle-brands-content" aria-selected="{{ $activeTab === 'brands' ? 'true' : 'false' }}">
                            <i class="bi bi-building me-1"></i> Hãng xe
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'models' ? 'active' : '' }}"
                            id="tab-vehicle-models-button" data-bs-toggle="tab"
                            data-bs-target="#tab-vehicle-models-content" type="button" role="tab"
                            aria-controls="tab-vehicle-models-content" aria-selected="{{ $activeTab === 'models' ? 'true' : 'false' }}">
                            <i class="bi bi-car-front-fill me-1"></i> Dòng xe
                        </button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="vehicleTabsContent">
                    {{-- TAB HÃNG XE --}}
                    <div class="tab-pane fade {{ $activeTab === 'brands' ? 'show active' : '' }}"
                        id="tab-vehicle-brands-content" role="tabpanel" aria-labelledby="tab-vehicle-brands-button">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>Danh sách Hãng xe</h2>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#createVehicleBrandModal">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tạo Hãng xe mới
                                </button>
                            </div>
                            <div class="card-body">
                                {{-- Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                                <div class="row g-3 mb-3 align-items-center">
                                    {{-- Thanh tìm kiếm --}}
                                    <div class="col-md-4 col-lg-3">
                                        <div class="input-group">
                                            <input type="text" id="brandSearchInput" class="form-control form-control-sm"
                                                placeholder="Tìm kiếm tên, mô tả hãng xe...">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="brandSearchBtn">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Bộ lọc --}}
                                    <div class="col-md-4 col-lg-3">
                                        <select id="brandFilterSelect" class="form-select form-select-sm">
                                            <option value="all">Tất cả trạng thái</option>
                                            <option value="active_only">Hoạt động</option>
                                            <option value="inactive_only">Đã ẩn</option>
                                        </select>
                                    </div>

                                    {{-- Sắp xếp --}}
                                    <div class="col-md-4 col-lg-3">
                                        <select id="brandSortSelect" class="form-select form-select-sm">
                                            <option value="latest">Mới nhất</option>
                                            <option value="oldest">Cũ nhất</option>
                                            <option value="name_asc">Tên (A-Z)</option>
                                            <option value="name_desc">Tên (Z-A)</option>
                                        </select>
                                    </div>

                                    {{-- Nút hành động hàng loạt --}}
                                    <div class="col-12 col-lg-3 text-lg-end">
                                        <button class="btn btn-danger btn-sm me-2 mb-2 mb-lg-0" id="brandBulkDeleteBtn" disabled
                                            data-bs-toggle="modal" data-bs-target="#deleteVehicleBrandModal">
                                            <i class="bi bi-trash-fill me-1"></i> Xóa (<span id="selectedBrandCountDelete">0</span>)
                                        </button>
                                        <button class="btn btn-info btn-sm mb-2 mb-lg-0" id="brandBulkToggleStatusBtn" disabled data-bs-toggle="modal"
                                            data-bs-target="#bulkToggleStatusVehicleBrandModal"> {{-- ID riêng --}}
                                            <i class="bi bi-arrow-repeat me-1"></i> Trạng thái (<span
                                                id="selectedBrandCountToggle">0</span>)
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width:3%">
                                                    <input type="checkbox" id="selectAllVehicleBrands"> {{-- ID riêng --}}
                                                </th>
                                                <th scope="col" style="width:5%">STT</th>
                                                <th scope="col" style="width: 10%;" class="text-center">Logo</th>
                                                <th scope="col" style="width: 20%;">Tên Hãng xe</th>
                                                <th scope="col">Mô tả</th>
                                                <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                                <th scope="col" class="text-center" style="width: 15%;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody id="vehicle-brands-table-body"> {{-- ID riêng --}}
                                            {{-- Calculate startIndex for initial load --}}
                                            @php
                                                $brandStartIndex = $vehicleBrands->firstItem() ? ($vehicleBrands->firstItem() - 1) : 0;
                                            @endphp
                                            @include('admin.productManagement.vehicle.partials._vehicle_brand_table_rows', [
                                                'brands' => $vehicleBrands,
                                                'startIndex' => $brandStartIndex
                                            ])
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 d-flex justify-content-center" id="vehicle-brands-pagination-links"> {{-- ID riêng --}}
                                    {{ $vehicleBrands->links() }}
                                </div>
                            </div>
                        </div>
                        {{-- Include Modals for Vehicle Brands --}}
                        @include('admin.productManagement.vehicle.partials.vehicleBrandModals')
                        {{-- New modal for bulk status toggle --}}
                        @include('admin.productManagement.vehicle.modals.modal_bulk_toggle_status_vehicle_brand')
                    </div>

                    {{-- TAB DÒNG XE --}}
                    <div class="tab-pane fade {{ $activeTab === 'models' ? 'show active' : '' }}"
                        id="tab-vehicle-models-content" role="tabpanel" aria-labelledby="tab-vehicle-models-button">
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
                                {{-- Hàng điều khiển: Tìm kiếm, Lọc, Sắp xếp và Hành động hàng loạt --}}
                                <div class="row g-3 mb-3 justify-content-between">
                                    {{-- Thanh tìm kiếm --}}
                                    <div class="col-md-4 col-lg-2">
                                        <div class="input-group">
                                            <input type="text" id="modelSearchInput" class="form-control form-control-sm"
                                                placeholder="Tìm kiếm tên, mô tả dòng xe...">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="modelSearchBtn">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Bộ lọc --}}
                                    <div class="col-md-4 col-lg-2">
                                        <select id="modelFilterSelect" class="form-select form-select-sm">
                                            <option value="all">Tất cả trạng thái</option>
                                            <option value="active_only">Hoạt động</option>
                                            <option value="inactive_only">Đã ẩn</option>
                                        </select>
                                    </div>
                                     <div class="col-md-4 col-lg-2"> {{-- Bộ lọc theo hãng xe --}}
                                        <select class="form-select form-select-sm" id="modelBrandFilterSelect">
                                            <option value="">-- Tất cả Hãng xe --</option>
                                            @foreach($allVehicleBrandsForFilter as $brand)
                                                <option value="{{ $brand->id }}" {{ ($selectedBrandIdForFilter == $brand->id) ? 'selected' : '' }}>
                                                    {{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Sắp xếp --}}
                                    <div class="col-md-4 col-lg-2">
                                        <select id="modelSortSelect" class="form-select form-select-sm">
                                            <option value="latest">Mới nhất</option>
                                            <option value="oldest">Cũ nhất</option>
                                            <option value="name_asc">Tên (A-Z)</option>
                                            <option value="name_desc">Tên (Z-A)</option>
                                            <option value="year_asc">Năm SX (Tăng dần)</option>
                                            <option value="year_desc">Năm SX (Giảm dần)</option>
                                            <option value="brand_name_asc">Hãng xe (A-Z)</option>
                                        </select>
                                    </div>

                                    {{-- Nút hành động hàng loạt --}}
                                    <div class="col-12 col-lg-3 text-lg-end">
                                        <button class="btn btn-danger btn-sm me-2 mb-2 mb-lg-0" id="modelBulkDeleteBtn" disabled
                                            data-bs-toggle="modal" data-bs-target="#deleteVehicleModelModal">
                                            <i class="bi bi-trash-fill me-1"></i> Xóa (<span id="selectedModelCountDelete">0</span>)
                                        </button>
                                        <button class="btn btn-info btn-sm mb-2 mb-lg-0" id="modelBulkToggleStatusBtn" disabled data-bs-toggle="modal"
                                            data-bs-target="#bulkToggleStatusVehicleModelModal"> {{-- ID riêng --}}
                                            <i class="bi bi-arrow-repeat me-1"></i> Trạng thái (<span
                                                id="selectedModelCountToggle">0</span>)
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width:3%">
                                                    <input type="checkbox" id="selectAllVehicleModels"> {{-- ID riêng --}}
                                                </th>
                                                <th style="width: 5%;">STT</th>
                                                <th>Tên Dòng xe</th>
                                                <th>Hãng xe</th>
                                                <th class="text-center">Năm SX</th>
                                                <th>Mô tả</th>
                                                <th class="text-center" style="width:10%">Trạng thái</th>
                                                <th class="text-center" style="width: 15%;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody id="vehicle-models-table-body"> {{-- ID riêng --}}
                                            {{-- Calculate startIndex for initial load --}}
                                            @php
                                                $modelStartIndex = $vehicleModels->firstItem() ? ($vehicleModels->firstItem() - 1) : 0;
                                            @endphp
                                            @include('admin.productManagement.vehicle.partials._vehicle_model_table_rows', [
                                                'models' => $vehicleModels,
                                                'startIndex' => $modelStartIndex
                                            ])
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 d-flex justify-content-center" id="vehicle-models-pagination-links"> {{-- ID riêng --}}
                                    {{ $vehicleModels->links() }}
                                </div>
                            </div>
                        </div>
                        {{-- Include Modals for Vehicle Models --}}
                        @include('admin.productManagement.vehicle.partials.vehicleModelModals', ['allVehicleBrands' => $allVehicleBrandsForFilter])
                        {{-- New modal for bulk status toggle --}}
                        @include('admin.productManagement.vehicle.modals.modal_bulk_toggle_status_vehicle_model')
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- Include file JS hợp nhất mới --}}
    <script src="{{ asset('assets_admin/js/vehicle_manager_combined.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Function to load content for a specific tab via AJAX (if not already loaded/handled by controller)
            // This is primarily for when the page loads initially or when changing tabs via JS directly.
            // The main data loading (performSearch) is already called by the JS modules.
            const handleTabContentLoad = (tabId) => {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('tab', tabId);
                const url = `${window.location.pathname}?${urlParams.toString()}`;

                // Update URL without reloading page
                window.history.pushState({}, '', url);

                // No need to manually fetch table content here, as performSearch in each module will handle it
                // when called initially or when filters/sorts change.
            };

            const vehicleTabsButtons = document.querySelectorAll('#vehicleTabs button[data-bs-toggle="tab"]');
            vehicleTabsButtons.forEach(tabButton => {
                tabButton.addEventListener('shown.bs.tab', function (event) {
                    const activeTabId = event.target.id.includes('models') ? 'models' : 'brands';
                    sessionStorage.setItem('activeVehicleManagementTab', activeTabId);
                    handleTabContentLoad(activeTabId);

                    // Re-initialize relevant section's JS logic if it hasn't run yet or needs re-running
                    // This is handled by the IIFE in vehicle_manager_combined.js, but explicitly ensure.
                    if (activeTabId === 'brands' && typeof initializeVehicleBrands === 'function') {
                        // If it's already running globally, no need to call again.
                        // We put `initializeVehicleBrands()` inside vehicle_manager_combined.js's global scope.
                    } else if (activeTabId === 'models' && typeof initializeVehicleModels === 'function') {
                         // Similar for models.
                    }
                });
            });

            // Initial active tab based on URL param or sessionStorage
            const urlParams = new URLSearchParams(window.location.search);
            let activeTabIdFromUrl = urlParams.get('tab');
            let activeTabIdFromSession = sessionStorage.getItem('activeVehicleManagementTab');

            let initialActiveTabButton = null;

            if (activeTabIdFromUrl === 'models' && document.getElementById('tab-vehicle-models-button')) {
                initialActiveTabButton = document.getElementById('tab-vehicle-models-button');
            } else if (activeTabIdFromUrl === 'brands' && document.getElementById('tab-vehicle-brands-button')) {
                initialActiveTabButton = document.getElementById('tab-vehicle-brands-button');
            } else if (activeTabIdFromSession === 'models' && document.getElementById('tab-vehicle-models-button')) {
                 initialActiveTabButton = document.getElementById('tab-vehicle-models-button');
            } else if (activeTabIdFromSession === 'brands' && document.getElementById('tab-vehicle-brands-button')) {
                 initialActiveTabButton = document.getElementById('tab-vehicle-brands-button');
            } else {
                // Default to brands tab if no specific tab is found or active
                initialActiveTabButton = document.getElementById('tab-vehicle-brands-button');
            }

            if (initialActiveTabButton) {
                new bootstrap.Tab(initialActiveTabButton).show();
            }

            // Handle validation errors from traditional form submissions (if any)
            @if ($errors->any())
                const oldFormMarker = @json(old('_form_marker'));
                if (oldFormMarker === 'create_vehicle_brand' && document.getElementById('createVehicleBrandModal')) {
                    new bootstrap.Modal(document.getElementById('createVehicleBrandModal')).show();
                    new bootstrap.Tab(document.getElementById('tab-vehicle-brands-button')).show();
                } else if (oldFormMarker === 'update_vehicle_brand' && document.getElementById('updateVehicleBrandModal')) {
                    new bootstrap.Modal(document.getElementById('updateVehicleBrandModal')).show();
                    new bootstrap.Tab(document.getElementById('tab-vehicle-brands-button')).show();
                    // Populate update modal with old data if validation fails (complex with AJAX, might need specific implementation if you pass old data via flash)
                }
                else if (oldFormMarker === 'create_vehicle_model' && document.getElementById('createVehicleModelModal')) {
                    new bootstrap.Modal(document.getElementById('createVehicleModelModal')).show();
                    new bootstrap.Tab(document.getElementById('tab-vehicle-models-button')).show();
                } else if (oldFormMarker === 'update_vehicle_model' && document.getElementById('updateVehicleModelModal')) {
                    new bootstrap.Modal(document.getElementById('updateVehicleModelModal')).show();
                    new bootstrap.Tab(document.getElementById('tab-vehicle-models-button')).show();
                }
            @endif
        });
    </script>
@endpush