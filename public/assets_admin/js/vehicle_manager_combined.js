/**
 * ===================================================================
 * vehicle_manager_combined.js
 *
 * Xử lý JavaScript cho trang quản lý chung Hãng xe và Dòng xe.
 * - Sử dụng các hàm global: window.showAppLoader, window.hideAppLoader, window.showAppInfoModal
 * - Quản lý modals và AJAX cho cả Vehicle Brands và Vehicle Models.
 * ===================================================================
 */
function initializeVehicleManagementPage() {
    console.log("Khởi tạo JS cho trang Quản lý Hãng xe & Dòng xe...");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // --- Định nghĩa Scope cho từng Tab ---
    const brandTabScope = '#tab-vehicle-brands-content ';
    const modelTabScope = '#tab-vehicle-models-content ';

    // ===================================================================
    // SECTION: HÃNG XE (VEHICLE BRANDS)
    // ===================================================================
    (function initializeVehicleBrands() {
        const sectionTitle = "Hãng xe";
        const createModalElement = document.querySelector(brandTabScope + '#createVehicleBrandModal');
        const updateModalElement = document.querySelector(brandTabScope + '#updateVehicleBrandModal');
        const viewModalElement = document.querySelector(brandTabScope + '#viewVehicleBrandModal');
        const deleteModalElement = document.querySelector(brandTabScope + '#deleteVehicleBrandModal');

        // Hàm chung để xem trước logo
        function setupLogoPreview(inputId, previewId, scopeElement = document) {
            const input = scopeElement.querySelector('#' + inputId);
            const preview = scopeElement.querySelector('#' + previewId);
            if (input && preview) {
                const defaultSrc = preview.src; // Lưu lại src ban đầu
                input.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        preview.src = URL.createObjectURL(file);
                        preview.onload = () => URL.revokeObjectURL(preview.src);
                    } else {
                        preview.src = defaultSrc; // Reset về ảnh mặc định nếu không chọn file
                    }
                });
            }
        }
        if (createModalElement) setupLogoPreview('vbLogoCreate', 'vbLogoPreviewCreate', createModalElement);
        if (updateModalElement) setupLogoPreview('vbLogoUpdate', 'vbLogoPreviewUpdate', updateModalElement);


        // Hàm chung để điền dữ liệu vào modal update và reset validation
        function populateAndUpdateModal(triggerButton, modalElement) {
            if (!modalElement || !triggerButton) return;
            const form = modalElement.querySelector('form');
            if (!form) return;

            const nameInput = form.querySelector('#vbNameUpdate');
            const descriptionInput = form.querySelector('#vbDescriptionUpdate');
            const statusSelect = form.querySelector('#vbStatusUpdate');
            const logoPreview = form.querySelector('#vbLogoPreviewUpdate');
            const logoInput = form.querySelector('#vbLogoUpdate');
            const submitButton = form.querySelector('button[type="submit"]');

            form.action = triggerButton.dataset.updateUrl || form.action;
            if (nameInput) nameInput.value = triggerButton.dataset.name || '';
            if (descriptionInput) descriptionInput.value = triggerButton.dataset.description || '';
            if (statusSelect) statusSelect.value = triggerButton.dataset.status || 'active';
            if (logoPreview && triggerButton.dataset.logoUrl) {
                logoPreview.src = triggerButton.dataset.logoUrl;
            } else if (logoPreview) {
                logoPreview.src = 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=N/A';
            }
            if (logoInput) logoInput.value = '';

            // Xóa lỗi validation cũ
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => {
                if (el.id && el.id.endsWith('Error')) el.textContent = '';
            });
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = modalElement.id.includes('create') ? `Lưu ${sectionTitle}` : 'Lưu thay đổi';
            }
        }

        // Hàm xử lý AJAX form submit chung cho cả create/update/delete
        function handleAjaxFormSubmit(formElement, successCallback) {
            if (!formElement) return;
            const submitButton = formElement.querySelector('button[type="submit"]');

            formElement.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
                }

                const formData = new FormData(this);
                const actionUrl = this.action;
                // Method lấy từ _method hidden input nếu có, nếu không thì dùng method của form (POST)
                const method = this.querySelector('input[name="_method"]')?.value || this.method;

                // Xóa lỗi cũ
                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => {
                    if (el.id && el.id.endsWith('Error')) el.textContent = '';
                });

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST', // Luôn là POST vì FormData và _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            Object.keys(result.errors).forEach(key => {
                                const inputField = this.querySelector(`[name="${key}"]`);
                                // Tìm div lỗi phù hợp, có thể theo ID hoặc class
                                const errorDivId = `vb${key.charAt(0).toUpperCase() + key.slice(1).replace(/_url$/, 'Url')}UpdateError`; // Cho update
                                const createErrorDivId = `vb${key.charAt(0).toUpperCase() + key.slice(1).replace(/_url$/, 'Url')}CreateError`; // Cho create
                                const passwordErrorDivId = `adminPasswordDeleteVehicleBrandError`; // Cho delete password

                                let errorDiv = null;
                                if (inputField) {
                                    errorDiv = inputField.closest('.mb-3, .row')?.querySelector(`.invalid-feedback[id="${errorDivId}"], .invalid-feedback[id="${createErrorDivId}"], .invalid-feedback`);
                                } else if (key === 'admin_password_delete_vehicle_brand') {
                                    errorDiv = this.querySelector(`#${passwordErrorDivId}`);
                                }

                                if (inputField) {
                                    inputField.classList.add('is-invalid');
                                }
                                if (errorDiv) {
                                    errorDiv.textContent = result.errors[key][0];
                                    errorDiv.style.display = 'block';
                                } else { // Lỗi chung không gắn với trường cụ thể hoặc không tìm thấy div lỗi
                                    window.showAppInfoModal(result.errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                                }
                            });
                        } else {
                            window.showAppInfoModal(result.message || `Lỗi HTTP ${response.status}`, 'error', 'Thất bại!');
                        }
                        return;
                    }

                    if (result.success) {
                        if (successCallback && typeof successCallback === 'function') {
                            successCallback(result);
                        } else {
                            window.showAppInfoModal(result.message || 'Thao tác thành công!', 'success', 'Thành công!');
                            setTimeout(() => window.location.reload(), 1000); // Default reload
                        }
                    } else {
                        window.showAppInfoModal(result.message || 'Thao tác không thành công.', 'error', 'Lỗi!');
                    }
                } catch (error) {
                    console.error(`Lỗi AJAX form ${sectionTitle}:`, error);
                    window.showAppInfoModal('Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại.', 'error', 'Lỗi Hệ Thống!');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = formElement.id.includes('create') ? `Lưu ${sectionTitle}` : 'Lưu thay đổi';
                    }
                }
            });
        }


        // Create Modal
        if (createModalElement) {
            const createForm = createModalElement.querySelector('#createVehicleBrandForm');
            handleAjaxFormSubmit(createForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(createModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Tạo ${sectionTitle} thành công!`, 'success', 'Thành công!');
                setTimeout(() => {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('tab', 'brands'); // Chuyển về tab brands
                    window.location.href = currentUrl.toString();
                }, 1000);
            });

            // Reset form và preview khi modal bị ẩn (do nhấn nút X hoặc Esc)
            createModalElement.addEventListener('hidden.bs.modal', function () {
                const createForm = this.querySelector('#createVehicleBrandForm');
                if (createForm) {
                    createForm.reset();
                    createForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    createForm.querySelectorAll('.invalid-feedback').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
                    const imgPreview = this.querySelector('#vbLogoPreviewCreate');
                    if (imgPreview && imgPreview.dataset.defaultSrc) {
                        imgPreview.src = imgPreview.dataset.defaultSrc;
                    }
                }
            });
        }

        // Update Modal
        if (updateModalElement) {
            updateModalElement.addEventListener('show.bs.modal', function (event) {
                populateAndUpdateModal(event.relatedTarget, this);
            });
            const updateForm = updateModalElement.querySelector('#updateVehicleBrandForm');
            handleAjaxFormSubmit(updateForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Cập nhật ${sectionTitle} thành công!`, 'success', 'Thành công!');

                // Cập nhật UI trực tiếp cho dòng hãng xe
                const updatedRow = document.getElementById(`vehicle-brand-row-${result.vehicleBrand.id}`);
                if (updatedRow) {
                    updatedRow.cells[2].textContent = result.vehicleBrand.name; // Tên Hãng xe
                    updatedRow.cells[3].textContent = result.vehicleBrand.description ? (result.vehicleBrand.description.length > 50 ? result.vehicleBrand.description.substring(0, 50) + '...' : result.vehicleBrand.description) : 'Không có mô tả'; // Mô tả
                    const statusCell = updatedRow.cells[4].querySelector('span');
                    statusCell.textContent = result.vehicleBrand.status === 'active' ? 'Hoạt động' : 'Đã ẩn';
                    statusCell.className = `badge ${result.vehicleBrand.status === 'active' ? 'bg-success' : 'bg-secondary'}`;

                    // Cập nhật logo
                    const logoImg = updatedRow.cells[1].querySelector('img');
                    logoImg.src = result.vehicleBrand.logo_full_url || 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=N/A';

                    // Cập nhật data attributes trên các nút của dòng đó (quan trọng cho View/Edit/Toggle)
                    const buttonsInRow = updatedRow.querySelectorAll('[data-id]');
                    buttonsInRow.forEach(btn => {
                        btn.dataset.name = result.vehicleBrand.name;
                        btn.dataset.description = result.vehicleBrand.description || '';
                        btn.dataset.status = result.vehicleBrand.status;
                        btn.dataset.logoUrl = result.vehicleBrand.logo_full_url || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                        if (btn.classList.contains('toggle-status-btn')) {
                            btn.title = result.vehicleBrand.status === 'active' ? 'Ẩn' : 'Hiện';
                            btn.innerHTML = `<i class="bi ${result.vehicleBrand.status === 'active' ? 'bi-eye-slash-fill' : 'bi-eye-fill'}"></i>`; // Giữ nguyên icon toggle
                            btn.classList.toggle('btn-outline-secondary', result.vehicleBrand.status === 'active');
                            btn.classList.toggle('btn-danger', result.vehicleBrand.status !== 'active');
                        }
                        if (btn.classList.contains('btn-view-vehicle-brand') || btn.classList.contains('btn-edit-vehicle-brand')) {
                            btn.dataset.updatedAt = new Date(result.vehicleBrand.updated_at).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
                        }
                    });
                    updatedRow.classList.toggle('row-inactive', result.vehicleBrand.status === 'inactive');
                } else {
                    setTimeout(() => window.location.reload(), 1000); // Fallback reload
                }
            });

            // Reset form và preview khi modal bị ẩn
            updateModalElement.addEventListener('hidden.bs.modal', function () {
                const updateForm = this.querySelector('#updateVehicleBrandForm');
                if (updateForm) {
                    updateForm.reset();
                    updateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    updateForm.querySelectorAll('.invalid-feedback').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
                    const imgPreview = this.querySelector('#vbLogoPreviewUpdate');
                    if (imgPreview && imgPreview.dataset.defaultSrc) {
                        imgPreview.src = imgPreview.dataset.defaultSrc;
                    }
                }
            });
        }

        // View Modal
        if (viewModalElement) {
            const idView = viewModalElement.querySelector('#vbIdView');
            const nameView = viewModalElement.querySelector('#vbNameView');
            const descriptionView = viewModalElement.querySelector('#vbDescriptionView');
            const statusViewText = viewModalElement.querySelector('#vbStatusViewText');
            const logoView = viewModalElement.querySelector('#vbLogoView');
            const createdAtView = viewModalElement.querySelector('#vbCreatedAtView');
            const updatedAtView = viewModalElement.querySelector('#vbUpdatedAtView');
            const editButtonFromView = viewModalElement.querySelector('#editVehicleBrandFromViewButton');

            viewModalElement.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                if (idView) idView.textContent = button.dataset.id || '-';
                if (nameView) nameView.textContent = button.dataset.name || '-';
                if (descriptionView) descriptionView.textContent = button.dataset.description || 'Không có mô tả';
                if (logoView) logoView.src = button.dataset.logoUrl || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                if (createdAtView) createdAtView.textContent = button.dataset.createdAt || '-';
                if (updatedAtView) updatedAtView.textContent = button.dataset.updatedAt || '-';

                if (statusViewText) {
                    if (button.dataset.status === 'active') statusViewText.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
                    else if (button.dataset.status === 'inactive') statusViewText.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
                    else statusViewText.textContent = button.dataset.status || '-';
                }

                if (editButtonFromView) {
                    // Clone dataset từ nút kích hoạt view sang nút edit trong view modal
                    Object.keys(button.dataset).forEach(key => {
                        editButtonFromView.dataset[key] = button.dataset[key];
                    });
                }
            });

            if (editButtonFromView && updateModalElement) {
                editButtonFromView.addEventListener('click', function () {
                    const viewModalInstance = bootstrap.Modal.getInstance(viewModalElement);
                    if (viewModalInstance) viewModalInstance.hide();

                    populateAndUpdateModal(this, updateModalElement); // 'this' là editButtonFromView

                    const updateModal = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);
                    updateModal.show();
                });
            }
        }

        // Delete Modal
        if (deleteModalElement) {
            const deleteForm = deleteModalElement.querySelector('#deleteVehicleBrandForm');
            const nameSpan = deleteModalElement.querySelector('#vehicleBrandNameToDelete');
            const passwordInput = deleteModalElement.querySelector('#adminPasswordDeleteVehicleBrand');
            const passwordErrorDiv = deleteModalElement.querySelector('#adminPasswordDeleteVehicleBrandError');
            const submitButton = deleteForm ? deleteForm.querySelector('button[type="submit"]') : null;


            deleteModalElement.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                if (deleteForm) deleteForm.action = button.dataset.deleteUrl;
                if (nameSpan) nameSpan.textContent = button.dataset.name;
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.classList.remove('is-invalid');
                }
                if (passwordErrorDiv) {
                    passwordErrorDiv.textContent = '';
                    passwordErrorDiv.style.display = 'none';
                }
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = `Xóa ${sectionTitle}`;
                }
            });
            handleAjaxFormSubmit(deleteForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(deleteModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Xóa ${sectionTitle} thành công!`, 'success', 'Thành công!');
                setTimeout(() => window.location.reload(), 1000);
            });
        }

        // Toggle Status
        document.querySelectorAll(brandTabScope + '.toggle-status-btn').forEach(button => {
            button.addEventListener('click', async function () {
                const id = this.dataset.id;
                const url = this.dataset.url;
                const currentButton = this;
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error((await response.json().catch(() => ({}))).message || `HTTP error ${response.status}`);
                    const result = await response.json();

                    if (result.success) {
                        window.showAppInfoModal(result.message || `Cập nhật trạng thái ${sectionTitle} thành công!`, 'success', 'Thành công!');

                        const row = document.getElementById(`vehicle-brand-row-${id}`);
                        const statusCell = document.getElementById(`vehicle-brand-status-${id}`);
                        if (statusCell) statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                        currentButton.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                        currentButton.title = result.new_button_title;
                        if (row) {
                            row.classList.toggle('row-inactive', result.new_status === 'inactive');
                            // Update data-status cho các nút khác trong dòng
                            row.querySelectorAll('[data-status]').forEach(el => el.dataset.status = result.new_status);
                        }

                        // THÊM LOGIC RELOAD TRANG ĐỂ CẬP NHẬT DỮ LIỆU DÒNG XE NẾU HÃNG XE BỊ ẨN HOẶC ĐƯỢC KÍCH HOẠT LẠI
                        setTimeout(() => {
                            const currentUrl = new URL(window.location);
                            currentUrl.searchParams.set('tab', 'brands'); // Giữ nguyên ở tab brands sau khi reload
                            window.location.href = currentUrl.toString();
                        }, 1000); // Reload sau 1 giây
                    } else {
                        window.showAppInfoModal(result.message || `Lỗi cập nhật trạng thái ${sectionTitle}.`, 'error', 'Lỗi!');
                    }
                } catch (error) {
                    window.showAppInfoModal(error.message || `Không thể cập nhật trạng thái ${sectionTitle}.`, 'error', 'Lỗi!');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                }
            });
        });
    })(); // End IIFE for Vehicle Brands

    // ===================================================================
    // SECTION: DÒNG XE (VEHICLE MODELS)
    // ===================================================================
    (function initializeVehicleModels() {
        const sectionTitle = "Dòng xe";
        const createModalElement = document.querySelector(modelTabScope + '#createVehicleModelModal');
        const updateModalElement = document.querySelector(modelTabScope + '#updateVehicleModelModal');
        const viewModalElement = document.querySelector(modelTabScope + '#viewVehicleModelModal');
        const deleteModalElement = document.querySelector(modelTabScope + '#deleteVehicleModelModal');

        // Hàm chung để điền dữ liệu vào modal update và reset validation
        function populateAndUpdateModal(triggerButton, modalElement) {
            if (!modalElement || !triggerButton) return;
            const form = modalElement.querySelector('form');
            if (!form) return;

            const nameInput = form.querySelector('#vmNameUpdate');
            const brandSelect = form.querySelector('#vmVehicleBrandUpdate');
            const yearInput = form.querySelector('#vmYearUpdate');
            const descriptionInput = form.querySelector('#vmDescriptionUpdate');
            const statusSelect = form.querySelector('#vmStatusUpdate');
            const submitButton = form.querySelector('button[type="submit"]');


            form.action = triggerButton.dataset.updateUrl || form.action;
            if (nameInput) nameInput.value = triggerButton.dataset.name || '';
            if (brandSelect) brandSelect.value = triggerButton.dataset.vehicleBrandId || '';
            if (yearInput) yearInput.value = triggerButton.dataset.year || '';
            if (descriptionInput) descriptionInput.value = (triggerButton.dataset.description && triggerButton.dataset.description !== 'null') ? triggerButton.dataset.description : '';
            if (statusSelect) statusSelect.value = triggerButton.dataset.status || 'active';

            // Xóa lỗi validation cũ
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => {
                if (el.id && el.id.endsWith('Error')) el.textContent = '';
            });
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = modalElement.id.includes('create') ? `Lưu ${sectionTitle}` : 'Lưu thay đổi';
            }
        }

        // Hàm xử lý AJAX form submit chung cho cả create/update/delete
        function handleAjaxFormSubmit(formElement, successCallback) {
            if (!formElement) return;
            const submitButton = formElement.querySelector('button[type="submit"]');

            formElement.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
                }
                const formData = new FormData(this);
                const actionUrl = this.action;
                const method = this.querySelector('input[name="_method"]')?.value || this.method;

                // Xóa lỗi cũ
                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => {
                    if (el.id && el.id.endsWith('Error')) el.textContent = '';
                });

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            Object.keys(result.errors).forEach(key => {
                                const inputField = this.querySelector(`[name="${key}"]`);
                                const errorDivId = `vm${key.charAt(0).toUpperCase() + key.slice(1).replace(/_id$/, 'Id')}UpdateError`; // Cho update
                                const createErrorDivId = `vm${key.charAt(0).toUpperCase() + key.slice(1).replace(/_id$/, 'Id')}CreateError`; // Cho create
                                const passwordErrorDivId = `adminPasswordDeleteVehicleModelError`; // Cho delete password

                                let errorDiv = null;
                                if (inputField) {
                                    errorDiv = inputField.closest('.mb-3')?.querySelector(`.invalid-feedback[id="${errorDivId}"], .invalid-feedback[id="${createErrorDivId}"], .invalid-feedback`);
                                } else if (key === 'admin_password_delete_vehicle_model') {
                                    errorDiv = this.querySelector(`#${passwordErrorDivId}`);
                                }

                                if (inputField) {
                                    inputField.classList.add('is-invalid');
                                }
                                if (errorDiv) {
                                    errorDiv.textContent = result.errors[key][0];
                                    errorDiv.style.display = 'block';
                                } else {
                                    window.showAppInfoModal(result.errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                                }
                            });
                        } else {
                            window.showAppInfoModal(result.message || `Lỗi HTTP ${response.status}`, 'error', 'Thất bại!');
                        }
                        return;
                    }
                    if (result.success) {
                        if (successCallback && typeof successCallback === 'function') {
                            successCallback(result);
                        } else {
                            window.showAppInfoModal(result.message || 'Thao tác thành công!', 'success', 'Thành công!');
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        window.showAppInfoModal(result.message || 'Thao tác không thành công.', 'error', 'Lỗi!');
                    }
                } catch (error) {
                    console.error(`Lỗi AJAX form ${sectionTitle}:`, error);
                    window.showAppInfoModal('Có lỗi xảy ra trong quá trình xử lý.', 'error', 'Lỗi Hệ Thống!');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = formElement.id.includes('create') ? `Lưu ${sectionTitle}` : 'Lưu thay đổi';
                    }
                }
            });
        }

        // Create Modal
        if (createModalElement) {
            const createForm = createModalElement.querySelector('#createVehicleModelForm');
            handleAjaxFormSubmit(createForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(createModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Tạo ${sectionTitle} thành công!`, 'success', 'Thành công!');
                setTimeout(() => {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('tab', 'models'); // Chuyển về tab models
                    const brandFilter = createForm.querySelector('#vmVehicleBrandCreate').value;
                    if (brandFilter) currentUrl.searchParams.set('filter_vehicle_brand_id', brandFilter);
                    window.location.href = currentUrl.toString();

                }, 1000);
            });
            // Reset form khi modal bị ẩn
            createModalElement.addEventListener('hidden.bs.modal', function () {
                const createForm = this.querySelector('#createVehicleModelForm');
                if (createForm) {
                    createForm.reset();
                    createForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    createForm.querySelectorAll('.invalid-feedback').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
                }
            });
        }

        // Update Modal
        if (updateModalElement) {
            updateModalElement.addEventListener('show.bs.modal', function (event) {
                populateAndUpdateModal(event.relatedTarget, this);
            });
            const updateForm = updateModalElement.querySelector('#updateVehicleModelForm');
            handleAjaxFormSubmit(updateForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Cập nhật ${sectionTitle} thành công!`, 'success', 'Thành công!');

                // Cập nhật UI trực tiếp cho dòng xe
                const updatedRow = document.getElementById(`vehicle-model-row-${result.vehicleModel.id}`);
                if (updatedRow) {
                    updatedRow.cells[1].textContent = result.vehicleModel.name; // Tên Dòng xe
                    updatedRow.cells[2].textContent = result.vehicleModel.vehicle_brand.name || 'N/A'; // Hãng xe (đã load ở controller)
                    updatedRow.cells[3].textContent = result.vehicleModel.year || 'N/A'; // Năm SX
                    updatedRow.cells[4].textContent = result.vehicleModel.description ? (result.vehicleModel.description.length > 50 ? result.vehicleModel.description.substring(0, 50) + '...' : result.vehicleModel.description) : 'Không có'; // Mô tả
                    const statusCell = updatedRow.cells[5].querySelector('span');
                    statusCell.textContent = result.vehicleModel.status === 'active' ? 'Hoạt động' : 'Đã ẩn';
                    statusCell.className = `badge ${result.vehicleModel.status === 'active' ? 'bg-success' : 'bg-secondary'}`;

                    // Cập nhật data attributes trên các nút của dòng đó
                    const buttonsInRow = updatedRow.querySelectorAll('[data-id]');
                    buttonsInRow.forEach(btn => {
                        btn.dataset.name = result.vehicleModel.name;
                        btn.dataset.vehicleBrandId = result.vehicleModel.vehicle_brand_id;
                        btn.dataset.vehicleBrandName = result.vehicleModel.vehicle_brand.name || 'N/A';
                        btn.dataset.year = result.vehicleModel.year;
                        btn.dataset.description = result.vehicleModel.description || '';
                        btn.dataset.status = result.vehicleModel.status;
                        if (btn.classList.contains('toggle-status-btn')) {
                            btn.title = result.vehicleModel.status === 'active' ? 'Ẩn' : 'Hiện';
                            btn.innerHTML = `<i class="bi ${result.vehicleModel.status === 'active' ? 'bi-eye-slash-fill' : 'bi-eye-fill'}"></i>`; // Giữ nguyên icon toggle
                            btn.classList.toggle('btn-outline-secondary', result.vehicleModel.status === 'active');
                            btn.classList.toggle('btn-danger', result.vehicleModel.status !== 'active');
                        }
                        if (btn.classList.contains('btn-view-vehicle-model') || btn.classList.contains('btn-edit-vehicle-model')) {
                            btn.dataset.updatedAt = new Date(result.vehicleModel.updated_at).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
                        }
                    });
                    updatedRow.classList.toggle('row-inactive', result.vehicleModel.status === 'inactive');
                } else {
                    setTimeout(() => window.location.reload(), 1000); // Fallback reload
                }
            });
            // Reset form khi modal bị ẩn
            updateModalElement.addEventListener('hidden.bs.modal', function () {
                const updateForm = this.querySelector('#updateVehicleModelForm');
                if (updateForm) {
                    updateForm.reset();
                    updateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    updateForm.querySelectorAll('.invalid-feedback').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
                }
            });
        }

        // View Modal
        if (viewModalElement) {
            const idView = viewModalElement.querySelector('#vmIdView');
            const nameView = viewModalElement.querySelector('#vmNameView');
            const brandNameView = viewModalElement.querySelector('#vmVehicleBrandNameView');
            const yearView = viewModalElement.querySelector('#vmYearView');
            const descriptionView = viewModalElement.querySelector('#vmDescriptionView');
            const statusViewText = viewModalElement.querySelector('#vmStatusViewText');
            const createdAtView = viewModalElement.querySelector('#vmCreatedAtView');
            const updatedAtView = viewModalElement.querySelector('#vmUpdatedAtView');
            const editButtonFromView = viewModalElement.querySelector('#editVehicleModelFromViewButton');

            viewModalElement.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                if (idView) idView.textContent = button.dataset.id || '-';
                if (nameView) nameView.textContent = button.dataset.name || '-';
                if (brandNameView) brandNameView.textContent = button.dataset.vehicleBrandName || 'N/A';
                if (yearView) yearView.textContent = button.dataset.year || 'N/A';
                if (descriptionView) descriptionView.textContent = button.dataset.description || 'Không có mô tả';
                if (statusViewText) {
                    if (button.dataset.status === 'active') statusViewText.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
                    else if (button.dataset.status === 'inactive') statusViewText.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
                    else statusViewText.textContent = button.dataset.status || '-';
                }
                if (createdAtView) createdAtView.textContent = button.dataset.createdAt || '-';
                if (updatedAtView) updatedAtView.textContent = button.dataset.updatedAt || '-';

                if (editButtonFromView) {
                    // Clone dataset từ nút kích hoạt view sang nút edit trong view modal
                    Object.keys(button.dataset).forEach(key => {
                        // Chỉ copy các data-attribute cần thiết cho form update
                        if (['id', 'name', 'vehicleBrandId', 'year', 'description', 'status', 'updateUrl'].includes(key)) {
                            editButtonFromView.dataset[key] = button.dataset[key];
                        }
                    });
                }
            });

            if (editButtonFromView && updateModalElement) {
                editButtonFromView.addEventListener('click', function () {
                    const viewModalInstance = bootstrap.Modal.getInstance(viewModalElement);
                    if (viewModalInstance) viewModalInstance.hide();

                    populateAndUpdateModal(this, updateModalElement); // 'this' là editButtonFromView

                    const updateModal = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);
                    updateModal.show();
                });
            }
        }

        // Delete Modal
        if (deleteModalElement) {
            const deleteForm = deleteModalElement.querySelector('#deleteVehicleModelForm');
            const nameSpan = deleteModalElement.querySelector('#deleteVehicleModelName');
            const passwordInput = deleteModalElement.querySelector('#adminPasswordDeleteVehicleModel');
            const passwordErrorDiv = deleteModalElement.querySelector('#adminPasswordDeleteVehicleModelError');
            const submitButton = deleteForm ? deleteForm.querySelector('button[type="submit"]') : null;

            deleteModalElement.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                if (deleteForm) deleteForm.action = button.dataset.deleteUrl;
                if (nameSpan) nameSpan.textContent = button.dataset.name;
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.classList.remove('is-invalid');
                }
                if (passwordErrorDiv) {
                    passwordErrorDiv.textContent = '';
                    passwordErrorDiv.style.display = 'none';
                }
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = `Xóa ${sectionTitle}`;
                }
            });
            handleAjaxFormSubmit(deleteForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(deleteModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Xóa ${sectionTitle} thành công!`, 'success', 'Thành công!');
                setTimeout(() => window.location.reload(), 1000);
            });
        }

        // Toggle Status
        document.querySelectorAll(modelTabScope + '.toggle-status-btn').forEach(button => {
            button.addEventListener('click', async function () {
                const id = this.dataset.id;
                const url = this.dataset.url;
                const currentButton = this;
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error((await response.json().catch(() => ({}))).message || `HTTP error ${response.status}`);
                    const result = await response.json();

                    if (result.success) {
                        window.showAppInfoModal(result.message || `Cập nhật trạng thái ${sectionTitle} thành công!`, 'success', 'Thành công!');
                        const row = document.getElementById(`vehicle-model-row-${id}`);
                        const statusCell = document.getElementById(`vehicle-model-status-${id}`);
                        if (statusCell) statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                        currentButton.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                        currentButton.title = result.new_button_title;
                        if (row) {
                            row.classList.toggle('row-inactive', result.new_status === 'inactive');
                            // Update data-status cho các nút khác trong dòng
                            row.querySelectorAll('[data-status]').forEach(el => el.dataset.status = result.new_status);
                        }
                    } else {
                        window.showAppInfoModal(result.message || `Lỗi cập nhật trạng thái ${sectionTitle}.`, 'error', 'Lỗi!');
                    }
                } catch (error) {
                    window.showAppInfoModal(error.message || `Không thể cập nhật trạng thái ${sectionTitle}.`, 'error', 'Lỗi!');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                }
            });
        });
    })(); // End IIFE for Vehicle Models

} // End initializeVehicleManagementPage