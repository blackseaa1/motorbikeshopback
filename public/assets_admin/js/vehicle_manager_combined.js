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

            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = modalElement.id.includes('create') ? `Lưu ${sectionTitle}` : 'Lưu thay đổi';
            }
        }
        
        function handleAjaxFormSubmit(formElement, successCallback) {
            if (!formElement) return;
            const submitButton = formElement.querySelector('button[type="submit"]');

            formElement.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
                }

                const formData = new FormData(this);
                const actionUrl = this.action;
                const method = this.querySelector('input[name="_method"]')?.value || this.method; // PUT or POST

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
                                if (inputField) {
                                    inputField.classList.add('is-invalid');
                                    let errorDiv = inputField.closest('.mb-3,.row')?.querySelector(`.invalid-feedback[id$="${key.replace(/_url$/, 'Url')}UpdateError"],.invalid-feedback[id$="${key}CreateError"],.invalid-feedback`);
                                    if (errorDiv) {
                                        errorDiv.textContent = result.errors[key][0];
                                        errorDiv.style.display = 'block';
                                    } else {
                                         window.showAppInfoModal(result.errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                                    }
                                } else { // Lỗi chung không gắn với trường cụ thể
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
        }
        
        // Update Modal
        if (updateModalElement) {
            updateModalElement.addEventListener('show.bs.modal', function(event) {
                populateAndUpdateModal(event.relatedTarget, this);
            });
            const updateForm = updateModalElement.querySelector('#updateVehicleBrandForm');
            handleAjaxFormSubmit(updateForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
                if (modalInstance) modalInstance.hide();
                 window.showAppInfoModal(result.message || `Cập nhật ${sectionTitle} thành công!`, 'success', 'Thành công!');
                setTimeout(() => window.location.reload(), 1000); // Reload để thấy thay đổi
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

                if(idView) idView.textContent = button.dataset.id || '-';
                if(nameView) nameView.textContent = button.dataset.name || '-';
                if(descriptionView) descriptionView.textContent = button.dataset.description || 'Không có mô tả';
                if(logoView) logoView.src = button.dataset.logoUrl || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                if(createdAtView) createdAtView.textContent = button.dataset.createdAt || '-';
                if(updatedAtView) updatedAtView.textContent = button.dataset.updatedAt || '-';

                if (statusViewText) {
                    if (button.dataset.status === 'active') statusViewText.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
                    else if (button.dataset.status === 'inactive') statusViewText.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
                    else statusViewText.textContent = button.dataset.status || '-';
                }
                
                if (editButtonFromView) {
                    Object.keys(button.dataset).forEach(key => {
                        editButtonFromView.dataset[key] = button.dataset[key];
                    });
                }
            });

            if (editButtonFromView && updateModalElement) {
                editButtonFromView.addEventListener('click', function() {
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


            deleteModalElement.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                if (!button) return;
                if(deleteForm) deleteForm.action = button.dataset.deleteUrl;
                if(nameSpan) nameSpan.textContent = button.dataset.name;
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
            button.addEventListener('click', async function() {
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
            if(nameInput) nameInput.value = triggerButton.dataset.name || '';
            if(brandSelect) brandSelect.value = triggerButton.dataset.vehicleBrandId || '';
            if(yearInput) yearInput.value = triggerButton.dataset.year || '';
            if(descriptionInput) descriptionInput.value = (triggerButton.dataset.description && triggerButton.dataset.description !== 'null') ? triggerButton.dataset.description : '';
            if(statusSelect) statusSelect.value = triggerButton.dataset.status || 'active';

            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = modalElement.id.includes('create') ? `Lưu ${sectionTitle}` : 'Lưu thay đổi';
            }
        }

        function handleAjaxFormSubmit(formElement, successCallback) {
            if (!formElement) return;
            const submitButton = formElement.querySelector('button[type="submit"]');

            formElement.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
                }
                const formData = new FormData(this);
                const actionUrl = this.action;
                const method = this.querySelector('input[name="_method"]')?.value || this.method;

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
                                if (inputField) {
                                    inputField.classList.add('is-invalid');
                                    let errorDiv = inputField.closest('.mb-3')?.querySelector(`.invalid-feedback[id^="vm${key.charAt(0).toUpperCase() + key.slice(1).replace(/_id$/, 'Id')}"],.invalid-feedback`);
                                    if (errorDiv) {
                                        errorDiv.textContent = result.errors[key][0];
                                        errorDiv.style.display = 'block';
                                    } else {
                                         window.showAppInfoModal(result.errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                                    }
                                }  else {
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
        }

        // Update Modal
        if (updateModalElement) {
            updateModalElement.addEventListener('show.bs.modal', function(event) {
                populateAndUpdateModal(event.relatedTarget, this);
            });
            const updateForm = updateModalElement.querySelector('#updateVehicleModelForm');
            handleAjaxFormSubmit(updateForm, (result) => {
                const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
                if (modalInstance) modalInstance.hide();
                window.showAppInfoModal(result.message || `Cập nhật ${sectionTitle} thành công!`, 'success', 'Thành công!');
                setTimeout(() => window.location.reload(), 1000);
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

                if(idView) idView.textContent = button.dataset.id || '-';
                if(nameView) nameView.textContent = button.dataset.name || '-';
                if(brandNameView) brandNameView.textContent = button.dataset.vehicleBrandName || 'N/A';
                if(yearView) yearView.textContent = button.dataset.year || 'N/A';
                if(descriptionView) descriptionView.textContent = button.dataset.description || 'Không có mô tả';
                if(statusViewText) {
                    if (button.dataset.status === 'active') statusViewText.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
                    else if (button.dataset.status === 'inactive') statusViewText.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
                    else statusViewText.textContent = button.dataset.status || '-';
                }
                if(createdAtView) createdAtView.textContent = button.dataset.createdAt || '-';
                if(updatedAtView) updatedAtView.textContent = button.dataset.updatedAt || '-';
                
                if (editButtonFromView) {
                    Object.keys(button.dataset).forEach(key => {
                         if (['id', 'name', 'vehicleBrandId', 'year', 'description', 'status', 'updateUrl'].includes(key)) {
                            editButtonFromView.dataset[key] = button.dataset[key];
                         }
                    });
                }
            });

            if (editButtonFromView && updateModalElement) {
                editButtonFromView.addEventListener('click', function() {
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

            deleteModalElement.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                if (!button) return;
                if(deleteForm) deleteForm.action = button.dataset.deleteUrl;
                if(nameSpan) nameSpan.textContent = button.dataset.name;
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
            button.addEventListener('click', async function() {
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