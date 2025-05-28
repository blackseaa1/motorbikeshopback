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
    // Chỉ thực thi mã nếu element đặc trưng của trang này tồn tại.
    if (!geographyTabsContent) {
        return;
    }
    console.log("Geography JS: Trang địa lý được phát hiện. Bắt đầu khởi tạo...");

    // Hàm tiện ích: Gỡ bỏ và gắn lại listener để tránh lặp lại do Turbo cache
    function rebindEventListener(element, eventType, handler) {
        if (!element) return;
        const newElement = element.cloneNode(true);
        element.parentNode.replaceChild(newElement, element);
        newElement.addEventListener(eventType, handler);
        return newElement;
    }

    // Lấy các biến lỗi được truyền từ Laravel (nếu có)
    const allLaravelErrors = window.laravelErrors || {};
    const errorUpdateProvinceId = window.errorUpdateProvinceId || null;
    const errorUpdateDistrictId = window.errorUpdateDistrictId || null;
    const errorUpdateWardId = window.errorUpdateWardId || null;

    // --- Chức năng tải Quận/Huyện theo Tỉnh/Thành ---
    const fetchDistrictsForModal = async (provinceId, districtSelectElement, selectedDistrictId = null, placeholder = '-- Chọn Quận/Huyện --') => {
        if (!provinceId) {
            districtSelectElement.innerHTML = `<option value="">-- Chọn Tỉnh/Thành trước --</option>`;
            districtSelectElement.disabled = true;
            return;
        }
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
        }
    };

    // --- Chức năng tự động mở modal nếu có lỗi validation từ server ---
    const setupModalForErrors = (options) => {
        const { modalId, errorBagPrefix, errorIdValue, baseUrl } = options;
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;

        const errorBag = allLaravelErrors[errorBagPrefix];
        if (errorBag && Object.keys(errorBag).length > 0) {
            const isUpdateModal = modalId.startsWith('update');
            if (!isUpdateModal || (isUpdateModal && errorIdValue)) {
                // Sử dụng `window.Modal` đã được gán từ app.js
                let modalInstance = window.Modal.getInstance(modalEl) || new window.Modal(modalEl);
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
                // Xử lý đặc biệt cho modal Phường/Xã để tải lại quận/huyện theo tỉnh đã chọn sai
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

    const provinceBaseUrl = "/admin/system/geography/provinces";
    const districtBaseUrl = "/admin/system/geography/districts";
    const wardBaseUrl = "/admin/system/geography/wards";

    setupModalForErrors({ modalId: 'createProvinceModal', errorBagPrefix: 'storeProvince' });
    setupModalForErrors({ modalId: 'updateProvinceModal', errorBagPrefix: 'updateProvince', errorIdValue: errorUpdateProvinceId, baseUrl: provinceBaseUrl });
    setupModalForErrors({ modalId: 'createDistrictModal', errorBagPrefix: 'storeDistrict' });
    setupModalForErrors({ modalId: 'updateDistrictModal', errorBagPrefix: 'updateDistrict', errorIdValue: errorUpdateDistrictId, baseUrl: districtBaseUrl });
    setupModalForErrors({ modalId: 'createWardModal', errorBagPrefix: 'storeWard' });
    setupModalForErrors({ modalId: 'updateWardModal', errorBagPrefix: 'updateWard', errorIdValue: errorUpdateWardId, baseUrl: wardBaseUrl });

    // --- Gắn sự kiện 'change' cho các dropdown Tỉnh/Thành ---
    ['provinceForWardCreate', 'provinceForWardUpdate'].forEach(selectId => {
        const provinceSelect = document.getElementById(selectId);
        if (provinceSelect) {
            const form = provinceSelect.closest('form');
            const districtSelect = form.querySelector('[name="district_id"]');
            if (districtSelect) {
                rebindEventListener(provinceSelect, 'change', (event) => fetchDistrictsForModal(event.target.value, districtSelect));
                // Nếu dropdown đã có giá trị (trường hợp `old()` của Laravel), gọi fetch luôn
                if (provinceSelect.value) {
                    const selectedDistrictId = districtSelect.value;
                    fetchDistrictsForModal(provinceSelect.value, districtSelect, selectedDistrictId);
                }
            }
        }
    });

    // --- Gắn sự kiện 'show.bs.modal' để điền dữ liệu khi mở modal ---
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        const modalId = button.dataset.bsTarget;
        if (!modalId) return;
        const modalEl = document.getElementById(modalId.substring(1));

        // Chỉ gắn listener 1 lần duy nhất cho mỗi modal để tránh lặp
        if (modalEl && !modalEl.hasAttribute('data-modal-listener-setup')) {
            modalEl.addEventListener('show.bs.modal', function (event) {
                const triggerButton = event.relatedTarget;
                if (!triggerButton) return; // Thoát nếu modal được mở bằng JS
                const form = modalEl.querySelector('form');
                if (!form) return;

                // Reset form và xóa các lỗi validation cũ
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                let currentBaseUrl = '';
                if (modalId.includes('Province')) currentBaseUrl = provinceBaseUrl;
                else if (modalId.includes('District')) currentBaseUrl = districtBaseUrl;
                else if (modalId.includes('Ward')) currentBaseUrl = wardBaseUrl;

                // --- Xử lý cho modal UPDATE ---
                if (modalId.includes('update')) {
                    modalEl.querySelector('.modal-title').textContent = `Cập nhật: ${triggerButton.dataset.name || ''}`;
                    if (currentBaseUrl && triggerButton.dataset.id) {
                        form.action = `${currentBaseUrl}/${triggerButton.dataset.id}`;
                    }
                    // Tự động điền dữ liệu từ data-* attributes của nút vào các input có name tương ứng
                    Object.keys(triggerButton.dataset).forEach(key => {
                        const formKey = key.replace(/([A-Z])/g, '_$1').toLowerCase(); // Chuyển từ camelCase (data-provinceId) sang snake_case (province_id)
                        const input = form.querySelector(`[name="${formKey}"]`);
                        if (input) {
                            input.value = triggerButton.dataset[key];
                        }
                    });

                    // Xử lý đặc biệt cho modal cập nhật Phường/Xã
                    if (modalId === '#updateWardModal') {
                        const provinceIdForWard = triggerButton.dataset.province_id_for_ward;
                        const districtIdForWard = triggerButton.dataset.district_id;
                        const provinceSelect = form.querySelector('[name="province_id_for_ward"]');
                        const districtSelect = form.querySelector('[name="district_id"]');
                        if (provinceSelect && districtSelect && provinceIdForWard) {
                            provinceSelect.value = provinceIdForWard;
                            // Tải danh sách quận huyện tương ứng và chọn đúng quận/huyện cũ
                            fetchDistrictsForModal(provinceIdForWard, districtSelect, districtIdForWard);
                        }
                    }
                }
                // --- Xử lý cho modal DELETE ---
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

    console.log("Geography JS: Khởi tạo HOÀN TẤT.");
}