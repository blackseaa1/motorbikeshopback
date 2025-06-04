/**
 * ===================================================================
 * geography.js
 *
 * Chứa toàn bộ logic JavaScript cho trang quản lý Địa lý (Tỉnh/Thành, Quận/Huyện, Phường/Xã).
 * Được gọi bởi file `admin_layout.js` mỗi khi trang được tải hoặc render.
 * ===================================================================
 */
function initializeGeographyPage() {
    const geographyTabsContent = document.getElementById('geographyTabsContent');
    if (!geographyTabsContent) {
        return;
    }
    console.log("Geography JS: Trang địa lý được phát hiện. Bắt đầu khởi tạo...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function rebindEventListener(element, eventType, handler) {
        if (!element) return;
        const newElement = element.cloneNode(true);
        element.parentNode.replaceChild(newElement, element);
        newElement.addEventListener(eventType, handler);
        return newElement;
    }

    const allLaravelErrors = window.laravelErrors || {};
    const errorUpdateProvinceId = window.errorUpdateProvinceId || null;
    const errorUpdateDistrictId = window.errorUpdateDistrictId || null;
    const errorUpdateWardId = window.errorUpdateWardId || null;

    const provinceBaseUrl = "/admin/system/geography/provinces";
    const districtBaseUrl = "/admin/system/geography/districts";
    const wardBaseUrl = "/admin/system/geography/wards";

    // --- Chức năng tải Quận/Huyện theo Tỉnh/Thành ---
    const fetchDistrictsForModal = async (provinceId, districtSelectElement, selectedDistrictId = null, placeholder = '-- Chọn Quận/Huyện --') => {
        if (!provinceId) {
            districtSelectElement.innerHTML = `<option value="">-- Chọn Tỉnh/Thành trước --</option>`;
            districtSelectElement.disabled = true;
            return;
        }

        if (typeof window.showAppLoader === 'function') window.showAppLoader(); // HIỂN THỊ LOADER
        districtSelectElement.disabled = true;
        districtSelectElement.innerHTML = `<option value="">Đang tải...</option>`;

        try {
            const response = await fetch(`/api/provinces/${provinceId}/districts`);
            if (!response.ok) throw new Error('Lỗi mạng khi fetch districts');
            const data = await response.json();

            districtSelectElement.innerHTML = `<option value="">${placeholder}</option>`;
            if (data && data.length > 0) {
                data.forEach(district => {
                    const option = new Option(district.name, district.id);
                    if (selectedDistrictId && district.id == selectedDistrictId) {
                        option.selected = true;
                    }
                    districtSelectElement.add(option);
                });
            } else {
                districtSelectElement.innerHTML = `<option value="">Không có quận/huyện</option>`;
            }
        } catch (error) {
            console.error('Lỗi fetchDistrictsForModal:', error);
            districtSelectElement.innerHTML = `<option value="">Lỗi tải Quận/Huyện</option>`;
        } finally {
            districtSelectElement.disabled = false;
            if (typeof window.hideAppLoader === 'function') window.hideAppLoader(); // ẨN LOADER
        }
    };

    // --- Chức năng tự động mở modal nếu có lỗi validation từ server ---
    // (Giữ nguyên hàm setupModalForErrors của bạn)
    const setupModalForErrors = (options) => {
        const { modalId, errorBagPrefix, errorIdValue, baseUrl } = options;
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;

        const errorBag = allLaravelErrors[errorBagPrefix];
        if (errorBag && Object.keys(errorBag).length > 0) {
            const isUpdateModal = modalId.startsWith('update');
            if (!isUpdateModal || (isUpdateModal && errorIdValue)) {
                let modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                const form = modalEl.querySelector('form');
                if (form && isUpdateModal && errorIdValue && baseUrl) {
                    form.action = `${baseUrl}/${errorIdValue}`;
                }

                for (const field in errorBag) {
                    if (form) {
                        const inputField = form.querySelector(`[name="${field}"]`);
                        if (inputField) {
                            inputField.classList.add('is-invalid');
                            let errorElement = inputField.nextElementSibling;
                            if (errorElement && errorElement.classList.contains('invalid-feedback')) {
                                errorElement.textContent = errorBag[field][0];
                            }
                        }
                    }
                }
                if (modalId.endsWith('WardModal') && form) {
                    const oldProvinceId = form.querySelector('[name="province_id_for_ward"]')?.value;
                    const oldDistrictId = form.querySelector('[name="district_id"]')?.value;
                    if (oldProvinceId) {
                        fetchDistrictsForModal(oldProvinceId, form.querySelector('[name="district_id"]'), oldDistrictId);
                    }
                }
                modalInstance.show();
            }
        }
    };

    setupModalForErrors({ modalId: 'createProvinceModal', errorBagPrefix: 'storeProvince' });
    setupModalForErrors({ modalId: 'updateProvinceModal', errorBagPrefix: 'updateProvince', errorIdValue: errorUpdateProvinceId, baseUrl: provinceBaseUrl });
    setupModalForErrors({ modalId: 'createDistrictModal', errorBagPrefix: 'storeDistrict' });
    setupModalForErrors({ modalId: 'updateDistrictModal', errorBagPrefix: 'updateDistrict', errorIdValue: errorUpdateDistrictId, baseUrl: districtBaseUrl });
    setupModalForErrors({ modalId: 'createWardModal', errorBagPrefix: 'storeWard' });
    setupModalForErrors({ modalId: 'updateWardModal', errorBagPrefix: 'updateWard', errorIdValue: errorUpdateWardId, baseUrl: wardBaseUrl });


    // --- Gắn sự kiện 'change' cho các dropdown Tỉnh/Thành ---
    // (Giữ nguyên phần này)
    ['provinceForWardCreate', 'provinceForWardUpdate'].forEach(selectId => {
        const provinceSelect = document.getElementById(selectId);
        if (provinceSelect) {
            const form = provinceSelect.closest('form');
            const districtSelect = form.querySelector('[name="district_id"]');
            if (districtSelect) {
                rebindEventListener(provinceSelect, 'change', (event) => fetchDistrictsForModal(event.target.value, districtSelect));
                if (provinceSelect.value) {
                    const selectedDistrictId = districtSelect.value; // Lưu lại giá trị district_id nếu có (old value)
                    fetchDistrictsForModal(provinceSelect.value, districtSelect, selectedDistrictId);
                }
            }
        }
    });

    // --- Gắn sự kiện 'show.bs.modal' để điền dữ liệu khi mở modal ---
    // (Giữ nguyên phần này)
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        const modalId = button.dataset.bsTarget;
        if (!modalId) return;
        const modalEl = document.getElementById(modalId.substring(1));

        if (modalEl && !modalEl.hasAttribute('data-modal-listener-setup')) {
            modalEl.addEventListener('show.bs.modal', function (event) {
                const triggerButton = event.relatedTarget;
                if (!triggerButton) return;
                const form = modalEl.querySelector('form');
                if (!form) return;

                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                let currentBaseUrl = '';
                if (modalId.includes('Province')) currentBaseUrl = provinceBaseUrl;
                else if (modalId.includes('District')) currentBaseUrl = districtBaseUrl;
                else if (modalId.includes('Ward')) currentBaseUrl = wardBaseUrl;

                if (modalId.includes('update')) {
                    modalEl.querySelector('.modal-title').textContent = `Cập nhật: ${triggerButton.dataset.name || ''}`;
                    if (currentBaseUrl && triggerButton.dataset.id) {
                        form.action = `${currentBaseUrl}/${triggerButton.dataset.id}`;
                    }
                    Object.keys(triggerButton.dataset).forEach(key => {
                        const formKey = key.replace(/([A-Z])/g, '_$1').toLowerCase();
                        const input = form.querySelector(`[name="${formKey}"]`);
                        if (input) {
                            input.value = triggerButton.dataset[key];
                        }
                    });

                    if (modalId === '#updateWardModal') {
                        const provinceIdForWard = triggerButton.dataset.province_id_for_ward;
                        const districtIdForWard = triggerButton.dataset.district_id;
                        const provinceSelect = form.querySelector('[name="province_id_for_ward"]');
                        const districtSelect = form.querySelector('[name="district_id"]');
                        if (provinceSelect && districtSelect && provinceIdForWard) {
                            provinceSelect.value = provinceIdForWard;
                            fetchDistrictsForModal(provinceIdForWard, districtSelect, districtIdForWard);
                        }
                    }
                }
                else if (modalId.includes('delete')) {
                    if (currentBaseUrl && triggerButton.dataset.id) {
                        form.action = `${currentBaseUrl}/${triggerButton.dataset.id}`;
                    }
                    const strongEl = modalEl.querySelector('.modal-body strong');
                    if (strongEl) {
                        strongEl.textContent = triggerButton.dataset.name;
                    }
                }
            });
            modalEl.setAttribute('data-modal-listener-setup', 'true');
        }
    });

    // --- AJAX Form Submission Handling (Ví dụ cho form tạo Tỉnh/Thành) ---
    // Bạn cần lặp lại logic này cho các form khác (update/delete, district, ward)
    const createProvinceForm = document.getElementById('createProvinceForm'); // Giả sử ID của form tạo tỉnh/thành
    if (createProvinceForm) {
        createProvinceForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            if (typeof window.showAppLoader === 'function') window.showAppLoader();

            const formData = new FormData(this);
            // const actionUrl = this.action; // Hoặc bạn có thể hardcode URL nếu muốn
            const actionUrl = provinceBaseUrl; // Ví dụ: /admin/system/geography/provinces

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    if (typeof window.showAppInfoModal === 'function') {
                        window.showAppInfoModal(result.message || 'Tạo mới thành công!', 'success');
                    } else {
                        alert(result.message || 'Tạo mới thành công!');
                    }
                    const modalEl = this.closest('.modal');
                    if (modalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    }
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        setTimeout(() => location.reload(), 1500); // Tải lại trang để cập nhật bảng
                    }
                } else if (response.status === 422 && result.errors) { // Validation errors
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                     // Hiển thị lỗi validation (bạn có thể có hàm riêng để xử lý việc này)
                    Object.keys(result.errors).forEach(field => {
                        const inputField = this.querySelector(`[name="${field}"]`);
                        if (inputField) {
                            inputField.classList.add('is-invalid');
                            let errorElement = inputField.nextElementSibling;
                            if (errorElement && errorElement.classList.contains('invalid-feedback')) {
                                errorElement.textContent = result.errors[field][0];
                            } else { // Tạo nếu chưa có
                                errorElement = document.createElement('div');
                                errorElement.className = 'invalid-feedback d-block';
                                errorElement.textContent = result.errors[field][0];
                                inputField.parentNode.insertBefore(errorElement, inputField.nextSibling);
                            }
                        }
                    });
                    if (typeof window.showAppInfoModal === 'function') {
                         window.showAppInfoModal('Vui lòng kiểm tra lại thông tin nhập.', 'validation_error', 'Lỗi nhập liệu');
                    } else {
                        alert('Vui lòng kiểm tra lại thông tin nhập.');
                    }

                } else {
                    throw new Error(result.message || 'Có lỗi xảy ra khi tạo mới.');
                }
            } catch (error) {
                console.error('Lỗi khi tạo mới:', error);
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                if (typeof window.showAppInfoModal === 'function') {
                    window.showAppInfoModal(error.message, 'error');
                } else {
                    alert(error.message);
                }
            }
        });
    }
    // **LƯU Ý:** Bạn cần thêm các event listener tương tự cho các form submit khác:
    // - updateProvinceForm, deleteProvinceForm
    // - createDistrictForm, updateDistrictForm, deleteDistrictForm
    // - createWardForm, updateWardForm, deleteWardForm
    // Nhớ điều chỉnh `actionUrl`, `method`, và cách xử lý cho phù hợp.

    console.log("Geography JS: Khởi tạo HOÀN TẤT.");
}