@extends('admin.layouts.app')

@section('title', 'Quản lý Địa lý')

@section('content')
    <div class="content-header mb-4">
        <h1><i class="bi bi-globe-americas me-2"></i>Quản lý Đơn vị Hành chính</h1>
    </div>

    <div class="container-fluid">

        {{-- Hiển thị thông báo --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-upload me-1"></i>Import Dữ liệu Excel</h2>
                <div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importGeographyModal">
                        <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Chọn File & Import
                    </button>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-map-fill me-2"></i>Bản đồ Hành chính</h2>
            </div>
            <div class="card-body">
                {{-- Nav tabs (Blade sẽ quyết định tab nào active) --}}
                <ul class="nav nav-tabs mb-3" id="geographyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !request('tab') || request('tab') == 'provinces' ? 'active' : '' }}"
                            id="provinces-tab-btn" data-bs-toggle="tab" data-bs-target="#provinces-tab-pane" type="button"
                            role="tab" aria-controls="provinces-tab-pane"
                            aria-selected="{{ !request('tab') || request('tab') == 'provinces' ? 'true' : 'false' }}">
                            <i class="bi bi-geo-alt-fill me-1"></i> Tỉnh/Thành phố
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'districts' ? 'active' : '' }}" id="districts-tab-btn"
                            data-bs-toggle="tab" data-bs-target="#districts-tab-pane" type="button" role="tab"
                            aria-controls="districts-tab-pane"
                            aria-selected="{{ request('tab') == 'districts' ? 'true' : 'false' }}">
                            <i class="bi bi-pin-map-fill me-1"></i> Quận/Huyện
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'wards' ? 'active' : '' }}" id="wards-tab-btn"
                            data-bs-toggle="tab" data-bs-target="#wards-tab-pane" type="button" role="tab"
                            aria-controls="wards-tab-pane"
                            aria-selected="{{ request('tab') == 'wards' ? 'true' : 'false' }}">
                            <i class="bi bi-pin-angle-fill me-1"></i> Phường/Xã
                        </button>
                    </li>
                </ul>

                {{-- Tab content (Blade sẽ quyết định tab nào active) --}}
                <div class="tab-content" id="geographyTabsContent">
                    <div class="tab-pane fade {{ !request('tab') || request('tab') == 'provinces' ? 'show active' : '' }}"
                        id="provinces-tab-pane" role="tabpanel" aria-labelledby="provinces-tab-btn" tabindex="0">
                        @include('admin.system.partials.tabs.provinces_tab')
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'districts' ? 'show active' : '' }}"
                        id="districts-tab-pane" role="tabpanel" aria-labelledby="districts-tab-btn" tabindex="0">
                        @include('admin.system.partials.tabs.districts_tab')
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'wards' ? 'show active' : '' }}" id="wards-tab-pane"
                        role="tabpanel" aria-labelledby="wards-tab-btn" tabindex="0">
                        @include('admin.system.partials.tabs.wards_tab')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Các modal không thay đổi --}}
    @include('admin.system.partials.modals.import')
    @include('admin.system.partials.modals.province')
    @include('admin.system.partials.modals.district', ['allProvinces' => $allProvinces])
    @include('admin.system.partials.modals.ward', ['allProvinces' => $allProvinces])

@endsection

@push('scripts')
    {{-- PHIÊN BẢN SCRIPT HOÀN CHỈNH - SỬA LỖI GẮN SỰ KIỆN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const geographyTabsContent = document.getElementById('geographyTabsContent');
            const allLaravelErrors = @json($errors->getBags());

            // --- CÁC HÀM XỬ LÝ CHÍNH ---

            // Hàm gắn các sự kiện AJAX vào một vùng chứa (container)
            const attachAjaxListeners = (container) => {
                // 1. Gắn sự kiện cho các FORM LỌC
                container.querySelectorAll('form[method="get"]').forEach(form => {
                    // Gỡ bỏ listener cũ để tránh gắn trùng lặp (quan trọng)
                    const newForm = form.cloneNode(true);
                    form.parentNode.replaceChild(newForm, form);

                    newForm.addEventListener('submit', function (event) {
                        event.preventDefault();
                        const formData = new URLSearchParams(new FormData(newForm)).toString();
                        const url = `${newForm.action}?${formData}`;
                        loadTabContent(url);
                    });
                });

                // 2. Gắn sự kiện cho các LINK PHÂN TRANG
                container.querySelectorAll('a.page-link').forEach(link => {
                    // Gỡ bỏ listener cũ
                    const newLink = link.cloneNode(true);
                    link.parentNode.replaceChild(newLink, link);

                    newLink.addEventListener('click', function (event) {
                        event.preventDefault();
                        const url = newLink.href;
                        if (url) loadTabContent(url);
                    });
                });
            };

            // Hàm chính để tải nội dung bằng AJAX
            const loadTabContent = async (url, pushState = true) => {
                const activeTabPane = geographyTabsContent.querySelector('.tab-pane.active');
                if (activeTabPane) {
                    activeTabPane.style.opacity = '0.5';
                }

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const data = await response.json();
                    if (activeTabPane && data.html) {
                        activeTabPane.innerHTML = data.html; // Cập nhật nội dung
                        attachAjaxListeners(activeTabPane); // QUAN TRỌNG: Gắn lại sự kiện cho nội dung mới
                    }
                    if (pushState) {
                        history.pushState({ path: url }, '', url);
                    }
                } catch (error) {
                    console.error('Failed to load tab content:', error);
                    if (activeTabPane) activeTabPane.innerHTML = `<div class="alert alert-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</div>`;
                } finally {
                    if (activeTabPane) {
                        activeTabPane.style.opacity = '1';
                    }
                }
            };

            // --- KHỞI TẠO VÀ GẮN CÁC SỰ KIỆN BAN ĐẦU ---

            // Gắn sự kiện AJAX cho toàn bộ các tab lần đầu tiên
            attachAjaxListeners(geographyTabsContent);

            // Cập nhật URL khi người dùng tự tay bấm chuyển tab
            document.querySelectorAll('#geographyTabs button[data-bs-toggle="tab"]').forEach(triggerEl => {
                triggerEl.addEventListener('shown.bs.tab', function (event) {
                    const newTabParam = this.getAttribute('data-bs-target').replace('#', '').replace('-tab-pane', '');
                    const url = new URL(window.location);
                    url.searchParams.set('tab', newTabParam);
                    url.searchParams.delete('provinces_page');
                    url.searchParams.delete('districts_page');
                    url.searchParams.delete('wards_page');
                    history.pushState({}, '', url);
                });
            });

            // Xử lý nút back/forward của trình duyệt
            window.addEventListener('popstate', (event) => {
                if (event.state && event.state.path) {
                    loadTabContent(event.state.path, false);
                } else if (location.href.includes('/admin/system/geography')) {
                    // Tải lại nếu không có state để tránh lỗi
                    location.reload();
                }
            });

            // --- CÁC LOGIC VỀ MODAL (giữ nguyên) ---

            const fetchDistricts = async (provinceId, districtSelectElement, selectedDistrictId = null, placeholder = '-- Chọn Quận/Huyện --') => {
                if (!provinceId) {
                    districtSelectElement.innerHTML = `<option value="">-- Chọn Tỉnh/Thành trước --</option>`;
                    districtSelectElement.disabled = true;
                    return;
                }
                districtSelectElement.disabled = true;
                districtSelectElement.innerHTML = `<option value="">Đang tải...</option>`;
                try {
                    const apiUrl = `{{ route('api.provinces.districts', ['province' => ':provinceId']) }}`.replace(':provinceId', provinceId);
                    const response = await fetch(apiUrl);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();
                    districtSelectElement.innerHTML = `<option value="">${placeholder}</option>`;
                    if (data.length > 0) {
                        data.forEach(district => {
                            const option = new Option(district.name, district.id);
                            if (selectedDistrictId && district.id == selectedDistrictId) { option.selected = true; }
                            districtSelectElement.add(option);
                        });
                    } else {
                        districtSelectElement.innerHTML = `<option value="">Không có quận/huyện</option>`;
                    }
                } catch (error) {
                    console.error('Error fetching districts:', error);
                    districtSelectElement.innerHTML = `<option value="">Lỗi tải Quận/Huyện</option>`;
                } finally {
                    districtSelectElement.disabled = false;
                }
            };

            const setupModalForErrors = (options) => {
                const { modalId, errorBagPrefix, errorIdValue, baseUrl } = options;
                const modalEl = document.getElementById(modalId);
                if (!modalEl) return;
                const errorBag = allLaravelErrors[errorBagPrefix];
                if (errorBag && Object.keys(errorBag).length > 0) {
                    const isUpdateModal = modalId.startsWith('update');
                    if (!isUpdateModal || (isUpdateModal && errorIdValue)) {
                        const modalInstance = new bootstrap.Modal(modalEl);
                        const form = modalEl.querySelector('form');
                        if (isUpdateModal && errorIdValue) form.action = `${baseUrl}/${errorIdValue}`;
                        for (const field in errorBag) {
                            const inputField = form.querySelector(`[name="${field}"]`);
                            if (inputField) {
                                inputField.classList.add('is-invalid');
                                let errorElement = inputField.nextElementSibling;
                                if (errorElement && errorElement.classList.contains('invalid-feedback')) errorElement.textContent = errorBag[field][0];
                            }
                        }
                        if (modalId.endsWith('WardModal')) {
                            const oldProvinceId = form.querySelector('[name="province_id_for_ward"]').value;
                            const oldDistrictId = form.querySelector('[name="district_id"]').value;
                            if (oldProvinceId) fetchDistricts(oldProvinceId, form.querySelector('[name="district_id"]'), oldDistrictId);
                        }
                        modalInstance.show();
                    }
                }
            };

            setupModalForErrors({ modalId: 'createProvinceModal', errorBagPrefix: 'storeProvince' });
            setupModalForErrors({ modalId: 'updateProvinceModal', errorBagPrefix: 'updateProvince', errorIdValue: "{{ session('error_update_province_id') }}", baseUrl: "{{ url('admin/system/geography/provinces') }}" });
            setupModalForErrors({ modalId: 'createDistrictModal', errorBagPrefix: 'storeDistrict' });
            setupModalForErrors({ modalId: 'updateDistrictModal', errorBagPrefix: 'updateDistrict', errorIdValue: "{{ session('error_update_district_id') }}", baseUrl: "{{ url('admin/system/geography/districts') }}" });
            setupModalForErrors({ modalId: 'createWardModal', errorBagPrefix: 'storeWard' });
            setupModalForErrors({ modalId: 'updateWardModal', errorBagPrefix: 'updateWard', errorIdValue: "{{ session('error_update_ward_id') }}", baseUrl: "{{ url('admin/system/geography/wards') }}" });

            document.body.addEventListener('change', function (event) {
                if (event.target.id === 'provinceForWardCreate') fetchDistricts(event.target.value, document.getElementById('wardDistrictIdCreate'));
                if (event.target.id === 'provinceForWardUpdate') fetchDistricts(event.target.value, document.getElementById('wardDistrictIdUpdate'));
            });
        });
    </script>
@endpush