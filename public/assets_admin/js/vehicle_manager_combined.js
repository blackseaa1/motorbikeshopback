/**
 * ===================================================================
 * vehicle_manager_combined.js (Phiên bản Đã Sửa Lỗi ReferenceError Checkbox States)
 *
 * Xử lý JavaScript cho trang quản lý chung Hãng xe và Dòng xe.
 * Bao gồm: xem, tạo, sửa, xóa, bật/tắt trạng thái, tìm kiếm, lọc, sắp xếp,
 * và hành động hàng loạt bằng AJAX cho cả hai tab.
 * ===================================================================
 */

// Hàm chính khởi tạo toàn bộ trang. Được gọi từ DOMContentLoaded trong vehicles.blade.php
function initializeVehicleManagementPage() {
    // Đảm bảo script chỉ chạy một lần
    if (document.body.dataset.vehicleManagerInitialized) {
        console.log("Vehicle Manager đã được khởi tạo trước đó. Bỏ qua khởi tạo lại.");
        return;
    }
    document.body.dataset.vehicleManagerInitialized = 'true';
    console.log("Khởi tạo JS cho trang Quản lý Hãng xe & Dòng xe...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token! Script sẽ dừng.');
        return;
    }

    // Lấy các hàm helper toàn cục (showAppLoader, hideAppLoader, showToast)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('DEBUG: Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('DEBUG: Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Lỗi: Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`);
            return;
        }

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    // --- KHAI BÁO CÁC BIẾN VÀ INSTANCE MODAL TOÀN CỤC ---
    let selectedBrandIds = new Set();
    let selectedModelIds = new Set();

    let createVehicleBrandModalInstance;
    let updateVehicleBrandModalInstance;
    let viewVehicleBrandModalInstance;
    let deleteVehicleBrandModalInstance;
    let bulkToggleStatusVehicleBrandModalInstance;

    let createVehicleModelModalInstance;
    let updateVehicleModelModalInstance;
    let viewVehicleModelModalInstance;
    let deleteVehicleModelModalInstance;
    let bulkToggleStatusVehicleModelModalInstance;


    // --- Hàm tiện ích chung cho validation và logo preview ---
    function clearValidationErrors(formElement) {
        if (!formElement) return;
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        formElement.querySelectorAll('[id$="Error"]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    function displayValidationErrors(formElement, errors, prefix) {
        clearValidationErrors(formElement);
        let firstErrorField = null;

        for (const fieldName in errors) {
            if (Object.hasOwnProperty.call(errors, fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);
                let errorDiv = null;

                if (fieldName === 'deletion_password') {
                    inputField = formElement.querySelector(`#bulkToggleStatus${prefix}Password`) || formElement.querySelector(`#adminPasswordDelete${prefix}`);
                    errorDiv = formElement.querySelector(`#bulkToggleStatus${prefix}PasswordError`) || formElement.querySelector(`#adminPasswordDelete${prefix}Error`);
                }
                else if (fieldName === 'logo_url' && prefix === 'Brand') {
                    inputField = formElement.querySelector(`#vbLogoCreate`) || formElement.querySelector(`#vbLogoUpdate`);
                    if (inputField) {
                        errorDiv = inputField.closest('.mb-3')?.querySelector('.invalid-feedback') || inputField.nextElementSibling;
                    }
                }
                else if (fieldName === 'vehicle_brand_id' && prefix === 'Model') {
                    inputField = formElement.querySelector(`#vmVehicleBrandCreate`) || formElement.querySelector(`#vmVehicleBrandUpdate`);
                    if (inputField) {
                        errorDiv = inputField.closest('.mb-3')?.querySelector('.invalid-feedback') || inputField.nextElementSibling;
                    }
                }


                if (inputField) {
                    inputField.classList.add('is-invalid');
                    if (!errorDiv) {
                        errorDiv = inputField.nextElementSibling;
                    }

                    if (errorDiv && (errorDiv.classList.contains('invalid-feedback') || (errorDiv.id && errorDiv.id.endsWith('Error')))) {
                        errorDiv.textContent = errors[fieldName][0];
                        if (errorDiv.id && errorDiv.id.endsWith('Error')) {
                            errorDiv.style.display = 'block';
                        }
                    } else {
                        console.warn(`Không tìm thấy div .invalid-feedback hoặc error cụ thể cho trường: ${fieldName}`);
                    }

                    if (!firstErrorField) {
                        firstErrorField = inputField;
                    }
                } else {
                    console.warn(`Không tìm thấy trường input cho lỗi: ${fieldName}`);
                    showToast(`Lỗi dữ liệu: ${fieldName} - ${errors[fieldName][0]}`, 'error');
                }
            }
        }
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }

    /**
     * Hàm thiết lập preview logo cho input file (chỉ cho brands).
     * @param {string} inputId - ID của input file (e.g., 'vbLogoCreate').
     * @param {string} previewId - ID của thẻ img để hiển thị preview (e.g., 'vbLogoPreviewCreate').
     * @param {HTMLElement} scopeElement - Phần tử cha để giới hạn phạm vi tìm kiếm.
     */
    function setupLogoPreview(inputId, previewId, scopeElement) {
        const input = scopeElement.querySelector(`#${inputId}`);
        const preview = scopeElement.querySelector(`#${previewId}`);
        if (input && preview) {
            if (!preview.dataset.defaultSrc) {
                preview.dataset.defaultSrc = preview.src || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview';
            }
            input.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    preview.src = URL.createObjectURL(file);
                    preview.onload = () => URL.revokeObjectURL(preview.src);
                } else {
                    preview.src = preview.dataset.defaultSrc;
                }
            });
        }
    }

    /**
     * Cập nhật nội dung bảng và liên kết phân trang.
     * @param {string} tableRowsHtml - HTML cho các hàng của bảng.
     * @param {string} paginationLinksHtml - HTML cho các liên kết phân trang.
     * @param {HTMLElement} targetTableBody - Element tbody để cập nhật.
     * @param {HTMLElement} targetPaginationContainer - Element div chứa pagination links.
     * @param {string} tabType - 'brands' hoặc 'models' để đặt colspan đúng.
     */
    function updateTableContent(tableRowsHtml, paginationLinksHtml, targetTableBody, targetPaginationContainer, tabType) {
        if (!targetTableBody) {
            console.error(`Lỗi: Không tìm thấy table body (${tabType}).`);
            return;
        }
        targetTableBody.innerHTML = tableRowsHtml || `
            <tr id="no-vehicle-${tabType}-row"><td colspan="${tabType === 'brands' ? 7 : 8}" class="text-center">
                <div class="alert alert-info mb-0">Không tìm thấy kết quả phù hợp.</div>
            </td></tr>`;

        if (targetPaginationContainer) {
            targetPaginationContainer.innerHTML = paginationLinksHtml || '';
        } else {
            console.warn(`Cảnh báo: Không tìm thấy container phân trang (${tabType}).`);
        }

        updateCheckboxStates(tabType); // Gọi hàm chung với entityType
    }


    /**
     * Hàm xử lý AJAX form submit chung cho cả create/update/delete/bulk actions.
     * @param {string} formId - ID của form (e.g., 'createVehicleBrandForm').
     * @param {object} modalInstance - Instance của Bootstrap Modal chứa form.
     * @param {function} successCallback - Hàm callback khi form gửi thành công.
     * @param {string} entityType - 'Brand' hoặc 'Model' để phân biệt và hiển thị lỗi.
     */
    function setupAjaxForm(formId, modalInstance, successCallback, entityType) {
        const formElement = document.getElementById(formId);
        if (!formElement) {
            console.error(`Không thể thiết lập AJAX form: Form ID "${formId}" không tồn tại.`);
            return;
        }

        formElement.addEventListener('submit', async function (e) {
            e.preventDefault();
            showAppLoader();
            clearValidationErrors(formElement);

            const submitButton = formElement.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
            }

            const formData = new FormData(this);

            const isDeleteForm = formId.includes('delete');
            const isUpdateForm = formId.includes('update');
            const isBulkDeleteForm = isDeleteForm && this.action.includes('bulk-destroy');
            const isBulkToggleStatusForm = formId.includes('bulkToggleStatus');

            if (isUpdateForm) {
                formData.append('_method', 'PUT');
            } else if (isDeleteForm && !isBulkDeleteForm) {
                formData.append('_method', 'DELETE');
            }

            if (isBulkDeleteForm || isBulkToggleStatusForm) {
                const selectedIds = entityType === 'Brand' ? Array.from(selectedBrandIds) : Array.from(selectedModelIds);
                formData.append('ids', JSON.stringify(selectedIds));
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const result = await response.json();

                if (response.ok) {
                    modalInstance.hide();
                    showToast(result.message, 'success');
                    if (successCallback) {
                        if (isDeleteForm) {
                            const deletedIds = result.deleted_ids || (isBulkDeleteForm ? (entityType === 'Brand' ? Array.from(selectedBrandIds) : Array.from(selectedModelIds)) : [parseInt(formElement.dataset.id, 10)]);
                            successCallback(deletedIds, entityType);
                        } else if (isBulkToggleStatusForm) {
                            successCallback(result[entityType.toLowerCase() + 's'], entityType);
                        } else {
                            successCallback(result[entityType.toLowerCase() + entityType], entityType);
                        }
                    }
                } else if (response.status === 422 && result.errors) {
                    displayValidationErrors(formElement, result.errors, entityType);
                    showToast(result.message || 'Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else {
                    showToast(result.message || 'Đã xảy ra lỗi không xác định.', 'error');
                }
            } catch (error) {
                console.error(`Lỗi Fetch cho ${entityType} form:`, error);
                showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader();
                if (submitButton) {
                    submitButton.disabled = false;
                    const defaultTextMap = {
                        'createVehicleBrandForm': 'Tạo Hãng xe', 'updateVehicleBrandForm': 'Lưu thay đổi', 'deleteVehicleBrandForm': 'Xóa Vĩnh Viễn', 'bulkToggleStatusBrandForm': 'Xác nhận',
                        'createVehicleModelForm': 'Tạo Dòng xe', 'updateVehicleModelForm': 'Lưu thay đổi', 'deleteVehicleModelForm': 'Xóa Vĩnh Viễn', 'bulkToggleStatusModelForm': 'Xác nhận'
                    };
                    submitButton.innerHTML = defaultTextMap[formId] || 'Submit';
                }
            }
        });
    }

    /**
     * Hàm xử lý hiển thị modal View hoặc Update.
     * @param {HTMLElement} triggerButton - Nút kích hoạt modal.
     * @param {object} modalInstance - Instance của Bootstrap Modal.
     * @param {string} type - 'view' hoặc 'update'.
     * @param {string} entityType - 'Brand' hoặc 'Model'.
     */
    async function handleShowModal(triggerButton, modalInstance, type, entityType) {
        showAppLoader();
        try {
            const url = triggerButton.dataset.url;
            if (!url) throw new Error('Không tìm thấy data-url trên nút kích hoạt modal.');

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
            }
            const data = await response.json();

            if (type === 'view') {
                const viewModalElement = entityType === 'Brand' ? document.getElementById('viewVehicleBrandModal') : document.getElementById('viewVehicleModelModal');
                if (!viewModalElement) throw new Error('View modal element not found.');

                if (entityType === 'Brand') {
                    viewModalElement.querySelector('#vbIdView').textContent = data.id || '-';
                    viewModalElement.querySelector('#vbNameView').textContent = data.name || '-';
                    viewModalElement.querySelector('#vbDescriptionView').textContent = data.description || 'Không có mô tả';
                    viewModalElement.querySelector('#vbLogoView').src = data.logo_full_url || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                    viewModalElement.querySelector('#vbCreatedAtView').textContent = new Date(data.created_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';
                    viewModalElement.querySelector('#vbUpdatedAtView').textContent = new Date(data.updated_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';
                    viewModalElement.querySelector('#vbStatusViewText').innerHTML = `<span class="badge ${data.status_badge_class}">${data.status_text}</span>`;

                    const editBtn = viewModalElement.querySelector('#editVehicleBrandFromViewButton');
                    if (editBtn) {
                        editBtn.dataset.id = data.id;
                        editBtn.dataset.url = triggerButton.dataset.url;
                        editBtn.dataset.updateUrl = triggerButton.dataset.updateUrl || `/admin/product-management/vehicle-brands/${data.id}`;
                    }
                } else if (entityType === 'Model') {
                    viewModalElement.querySelector('#vmIdView').textContent = data.id || '-';
                    viewModalElement.querySelector('#vmNameView').textContent = data.name || '-';
                    viewModalElement.querySelector('#vmVehicleBrandNameView').textContent = data.vehicle_brand?.name || 'N/A';
                    viewModalElement.querySelector('#vmYearView').textContent = data.year || 'N/A';
                    viewModalElement.querySelector('#vmDescriptionView').textContent = data.description || 'Không có mô tả';
                    viewModalElement.querySelector('#vmCreatedAtView').textContent = new Date(data.created_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';
                    viewModalElement.querySelector('#vmUpdatedAtView').textContent = new Date(data.updated_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';
                    viewModalElement.querySelector('#vmStatusViewText').innerHTML = `<span class="badge ${data.status_badge_class}">${data.status_text}</span>`;

                    const editBtn = viewModalElement.querySelector('#editVehicleModelFromViewButton');
                    if (editBtn) {
                        editBtn.dataset.id = data.id;
                        editBtn.dataset.url = triggerButton.dataset.url;
                        editBtn.dataset.updateUrl = triggerButton.dataset.updateUrl || `/admin/product-management/vehicle-models/${data.id}`;
                    }
                }

            } else if (type === 'update') {
                const updateModalElement = entityType === 'Brand' ? document.getElementById('updateVehicleBrandModal') : document.getElementById('updateVehicleModelModal');
                const form = updateModalElement.querySelector('form');
                if (!form) throw new Error('Update form not found.');

                form.action = triggerButton.dataset.updateUrl || (entityType === 'Brand' ? `/admin/product-management/vehicle-brands/${data.id}` : `/admin/product-management/vehicle-models/${data.id}`);

                if (entityType === 'Brand') {
                    form.querySelector('#vbNameUpdate').value = data.name;
                    form.querySelector('#vbDescriptionUpdate').value = data.description || '';
                    form.querySelector('#vbStatusUpdate').value = data.status;
                    const logoPreview = form.querySelector('#vbLogoPreviewUpdate');
                    if (logoPreview) {
                        logoPreview.src = data.logo_full_url || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                        logoPreview.dataset.defaultSrc = logoPreview.src;
                    }
                    form.querySelector('#vbLogoUpdate').value = '';
                } else if (entityType === 'Model') {
                    form.querySelector('#vmNameUpdate').value = data.name;
                    form.querySelector('#vmVehicleBrandUpdate').value = data.vehicle_brand_id || '';
                    form.querySelector('#vmYearUpdate').value = data.year || '';
                    form.querySelector('#vmDescriptionUpdate').value = data.description || '';
                    form.querySelector('#vmStatusUpdate').value = data.status;
                }
                clearValidationErrors(form);
            }

            modalInstance.show();
        } catch (error) {
            console.error(`Lỗi khi lấy dữ liệu cho modal ${type} ${entityType}:`, error);
            showToast(`Không thể lấy dữ liệu ${entityType}. Vui lòng thử lại.`, 'error');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Hàm xử lý hiển thị modal xác nhận xóa đơn lẻ.
     * @param {HTMLElement} triggerButton - Nút xóa đã kích hoạt.
     * @param {string} entityType - 'Brand' hoặc 'Model'.
     */
    function handleShowDeleteModal(triggerButton, entityType) {
        const deleteModalElement = entityType === 'Brand' ? document.getElementById('deleteVehicleBrandModal') : document.getElementById('deleteVehicleModelModal');
        const form = deleteModalElement.querySelector('form');
        const nameSpan = entityType === 'Brand' ? deleteModalElement.querySelector('#vehicleBrandNameToDelete') : deleteModalElement.querySelector('#deleteVehicleModelName');
        const modalInstance = entityType === 'Brand' ? deleteVehicleBrandModalInstance : deleteVehicleModelModalInstance;

        if (!form || !nameSpan || !modalInstance) return;

        form.action = triggerButton.dataset.deleteUrl;
        nameSpan.textContent = triggerButton.dataset.name;
        form.dataset.id = triggerButton.dataset.id;
        modalInstance.show();
    }

    // --- CÁC HÀM XỬ LÝ CHECKBOX VÀ BULK ACTIONS CHUNG ---
    /**
     * Cập nhật trạng thái các nút hành động hàng loạt (disabled/enabled) và số lượng đã chọn.
     * @param {string} entityType - 'Brand' hoặc 'Model'.
     */
    function updateBulkActionButtons(entityType) {
        let selectedIds, bulkDeleteBtn, bulkToggleStatusBtn, selectedCountDeleteSpan, selectedCountToggleSpan, selectedCountToggleModalSpan;

        if (entityType === 'Brand') {
            selectedIds = selectedBrandIds;
            bulkDeleteBtn = document.getElementById('brandBulkDeleteBtn');
            bulkToggleStatusBtn = document.getElementById('brandBulkToggleStatusBtn');
            selectedCountDeleteSpan = document.getElementById('selectedBrandCountDelete');
            selectedCountToggleSpan = document.getElementById('selectedBrandCountToggle');
            selectedCountToggleModalSpan = document.getElementById('selectedBrandCountToggleModal');
        } else { // Model
            selectedIds = selectedModelIds;
            bulkDeleteBtn = document.getElementById('modelBulkDeleteBtn');
            bulkToggleStatusBtn = document.getElementById('modelBulkToggleStatusBtn');
            selectedCountDeleteSpan = document.getElementById('selectedModelCountDelete');
            selectedCountToggleSpan = document.getElementById('selectedModelCountToggle');
            selectedCountToggleModalSpan = document.getElementById('selectedModelCountToggleModal');
        }

        const count = selectedIds.size;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
        if (bulkToggleStatusBtn) bulkToggleStatusBtn.disabled = count === 0;
        if (selectedCountDeleteSpan) selectedCountDeleteSpan.textContent = count;
        if (selectedCountToggleSpan) selectedCountToggleSpan.textContent = count;
        if (selectedCountToggleModalSpan) selectedCountToggleModalSpan.textContent = count;
    }

    /**
     * Cập nhật trạng thái của từng checkbox (checked/unchecked) dựa trên `selectedIds`.
     * @param {string} entityType - 'Brand' hoặc 'Model'.
     */
    function updateCheckboxStates(entityType) {
        let currentCheckboxes, selectAllCheckboxes, selectedIds;
        let tableBodyElement;

        if (entityType === 'Brand') {
            tableBodyElement = document.getElementById('vehicle-brands-table-body');
            currentCheckboxes = tableBodyElement.querySelectorAll('.brand-checkbox');
            selectAllCheckboxes = document.getElementById('selectAllVehicleBrands');
            selectedIds = selectedBrandIds;
        } else { // Model
            tableBodyElement = document.getElementById('vehicle-models-table-body');
            currentCheckboxes = tableBodyElement.querySelectorAll('.model-checkbox');
            selectAllCheckboxes = document.getElementById('selectAllVehicleModels');
            selectedIds = selectedModelIds;
        }

        if (currentCheckboxes.length === 0) {
            if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
            updateBulkActionButtons(entityType);
            return;
        }

        let allVisibleChecked = true;
        currentCheckboxes.forEach(checkbox => {
            checkbox.checked = selectedIds.has(checkbox.value);
            if (!checkbox.checked) allVisibleChecked = false;
        });

        if (selectAllCheckboxes) selectAllCheckboxes.checked = allVisibleChecked;
        updateBulkActionButtons(entityType);
    }

    /**
     * Xóa tất cả các lựa chọn checkbox và reset trạng thái.
     * @param {string} entityType - 'Brand' hoặc 'Model'.
     */
    function clearSelectedIds(entityType) {
        if (entityType === 'Brand') {
            selectedBrandIds.clear();
            document.querySelectorAll('#vehicle-brands-table-body .brand-checkbox').forEach(cb => cb.checked = false);
            if (document.getElementById('selectAllVehicleBrands')) document.getElementById('selectAllVehicleBrands').checked = false;
        } else { // Model
            selectedModelIds.clear();
            document.querySelectorAll('#vehicle-models-table-body .model-checkbox').forEach(cb => cb.checked = false);
            if (document.getElementById('selectAllVehicleModels')) document.getElementById('selectAllVehicleModels').checked = false;
        }
        updateBulkActionButtons(entityType);
    }


    // ===================================================================
    // SECTION: HÃNG XE (VEHICLE BRANDS) LOGIC
    // ===================================================================
    (function initializeVehicleBrands() {
        const entityType = 'Brand';
        const tableBody = document.getElementById('vehicle-brands-table-body');
        const searchInput = document.getElementById('brandSearchInput');
        const searchBtn = document.getElementById('brandSearchBtn');
        const filterSelect = document.getElementById('brandFilterSelect');
        const sortSelect = document.getElementById('brandSortSelect');
        const paginationContainer = document.getElementById('vehicle-brands-pagination-links');

        // Khởi tạo các instance modal (sử dụng biến toàn cục)
        createVehicleBrandModalInstance = new bootstrap.Modal(document.getElementById('createVehicleBrandModal'));
        updateVehicleBrandModalInstance = new bootstrap.Modal(document.getElementById('updateVehicleBrandModal'));
        viewVehicleBrandModalInstance = new bootstrap.Modal(document.getElementById('viewVehicleBrandModal'));
        deleteVehicleBrandModalInstance = new bootstrap.Modal(document.getElementById('deleteVehicleBrandModal'));
        bulkToggleStatusVehicleBrandModalInstance = new bootstrap.Modal(document.getElementById('bulkToggleStatusVehicleBrandModal'));

        // Bulk action elements (sử dụng biến toàn cục) - đã khai báo ở global, chỉ cần sử dụng
        const selectAllCheckboxes = document.getElementById('selectAllVehicleBrands');
        const bulkDeleteBtn = document.getElementById('brandBulkDeleteBtn');
        const bulkToggleStatusBtn = document.getElementById('brandBulkToggleStatusBtn');


        // --- Logo Preview Setup ---
        setupLogoPreview('vbLogoCreate', 'vbLogoPreviewCreate', document.getElementById('createVehicleBrandModal'));
        setupLogoPreview('vbLogoUpdate', 'vbLogoPreviewUpdate', document.getElementById('updateVehicleBrandModal'));

        // --- Hàm AJAX chung cho search/filter/sort ---
        async function performSearch(page = 1) {
            showAppLoader();
            try {
                const currentSearchQuery = searchInput.value;
                const currentFilter = filterSelect.value;
                const currentSort = sortSelect.value;

                const urlParams = new URLSearchParams();
                urlParams.append('brands_page', page); // Đã sửa: dùng 'brands_page' cho phân trang hãng xe
                urlParams.append('tab', 'brands');
                if (currentSearchQuery) urlParams.append('brand_search', currentSearchQuery);
                if (currentFilter && currentFilter !== 'all') urlParams.append('brand_filter', currentFilter);
                if (currentSort && currentSort !== 'latest') urlParams.append('brand_sort_by', currentSort);

                const url = `/admin/product-management/vehicles?${urlParams.toString()}`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                    throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
                }
                const data = await response.json();
                if (data.active_tab === 'brands') {
                    updateTableContent(data.table_rows, data.pagination_links, tableBody, paginationContainer, 'brands');
                }
            } catch (error) {
                console.error('Lỗi khi tải dữ liệu Hãng xe:', error);
                showToast('Không thể tải dữ liệu Hãng xe. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader();
            }
        }

        // --- Event Listeners cho Search, Filter, Sort, Pagination ---
        searchBtn.addEventListener('click', () => performSearch(1));
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(1);
            }
        });
        filterSelect.addEventListener('change', () => performSearch(1));
        sortSelect.addEventListener('change', () => performSearch(1));

        function attachPaginationListenersForBrands() {
            if (paginationContainer) {
                paginationContainer.removeEventListener('click', handlePaginationClickForBrands);
                paginationContainer.addEventListener('click', handlePaginationClickForBrands);
            }
        }

        function handlePaginationClickForBrands(event) {
            const link = event.target.closest('.pagination a');
            if (link) {
                event.preventDefault();
                const url = new URL(link.href);
                const page = url.searchParams.get('brands_page'); // Đã sửa: lấy 'brands_page'
                if (page) {
                    performSearch(page);
                }
            }
        }

        // --- Callbacks cho AJAX form submit ---
        function handleBrandCRUDSuccess(resultData) {
            let currentPage = '1';
            if (paginationContainer) {
                const activePageLink = paginationContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = activePageLink.textContent;
                }
            }
            performSearch(parseInt(currentPage, 10));
        }

        function handleBrandDeleteSuccess(deletedIds) {
            deletedIds.forEach(id => {
                document.getElementById(`vehicle-brand-row-${id}`)?.remove();
                selectedBrandIds.delete(String(id));
            });
            let currentPage = '1';
            if (paginationContainer) {
                const activePageLink = paginationContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = activePageLink.textContent;
                }
            }
            performSearch(parseInt(currentPage, 10));
            clearSelectedIds(entityType);
        }

        function handleBrandBulkToggleStatusSuccess() {
            let currentPage = '1';
            if (paginationContainer) {
                const activePageLink = paginationContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = activePageLink.textContent;
                }
            }
            performSearch(parseInt(currentPage, 10));
            clearSelectedIds(entityType);
        }

        // --- Setup Modals & Forms ---
        setupAjaxForm('createVehicleBrandForm', createVehicleBrandModalInstance, handleBrandCRUDSuccess, entityType);
        setupAjaxForm('updateVehicleBrandForm', updateVehicleBrandModalInstance, handleBrandCRUDSuccess, entityType);
        setupAjaxForm('deleteVehicleBrandForm', deleteVehicleBrandModalInstance, handleBrandDeleteSuccess, entityType);
        setupAjaxForm('bulkToggleStatusBrandForm', bulkToggleStatusVehicleBrandModalInstance, handleBrandBulkToggleStatusSuccess, entityType);

        // --- Event Listeners for Buttons (View, Edit, Delete, Toggle) ---
        tableBody.addEventListener('click', async function (event) {
            const button = event.target.closest('button');
            if (!button) return;

            if (button.classList.contains('btn-view-vehicle-brand')) {
                event.preventDefault();
                await handleShowModal(button, viewVehicleBrandModalInstance, 'view', entityType);
            } else if (button.classList.contains('btn-edit-vehicle-brand')) {
                event.preventDefault();
                await handleShowModal(button, updateVehicleBrandModalInstance, 'update', entityType);
            } else if (button.classList.contains('btn-delete-vehicle-brand') && !button.closest('#brandBulkDeleteBtn')) {
                event.preventDefault();
                handleShowDeleteModal(button, entityType);
            } else if (button.classList.contains('toggle-status-brand-btn')) {
                event.preventDefault();
                showAppLoader();
                try {
                    const url = button.dataset.url;
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || `Lỗi HTTP: ${response.status}`);
                    }
                    showToast(result.message, 'success');
                    handleBrandCRUDSuccess(result.vehicleBrand, entityType);
                } catch (error) {
                    console.error('Lỗi khi bật/tắt trạng thái Hãng xe:', error);
                    showToast(error.message, 'error');
                } finally {
                    hideAppLoader();
                }
            } else if (button.id === 'editVehicleBrandFromViewButton') {
                viewVehicleBrandModalInstance.hide();
                setTimeout(() => handleShowModal(button, updateVehicleBrandModalInstance, 'update', entityType), 200);
            }
        });

        // --- Delete Modal Specifics ---
        document.getElementById('deleteVehicleBrandModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = this.querySelector('#deleteVehicleBrandForm');
            form.removeAttribute('data-id');

            if (button && button.classList.contains('btn-delete-vehicle-brand') && !button.closest('#brandBulkDeleteBtn')) {
                const brandId = button.dataset.id;
                form.action = button.dataset.deleteUrl;
                form.dataset.id = brandId;
                this.querySelector('#vehicleBrandNameToDelete').textContent = button.dataset.name;
            } else if (button && button.id === 'brandBulkDeleteBtn') {
                form.action = '/admin/product-management/vehicle-brands/bulk-destroy';
                this.querySelector('#vehicleBrandNameToDelete').textContent = `${selectedBrandIds.size} hãng xe đã chọn`;
            }
        });

        // --- Bulk Actions Setup ---
        // updateBrandBulkActionButtons, updateBrandCheckboxStates, clearSelectedBrands are now
        // generalized updateBulkActionButtons, updateCheckboxStates, clearSelectedIds.
        // They will be called by their specific entity functions.

        // Event listener for select all
        selectAllCheckboxes.addEventListener('change', function () {
            const currentCheckboxes = tableBody.querySelectorAll('.brand-checkbox');
            currentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) selectedBrandIds.add(checkbox.value);
                else selectedBrandIds.delete(checkbox.value);
            });
            updateBulkActionButtons(entityType);
        });

        // Event listener for individual checkboxes
        tableBody.addEventListener('change', function (event) {
            const checkbox = event.target.closest('.brand-checkbox');
            if (checkbox) {
                if (checkbox.checked) selectedBrandIds.add(checkbox.value);
                else selectedBrandIds.delete(checkbox.value);
                updateBulkActionButtons(entityType);
                const allIndividualCheckboxes = tableBody.querySelectorAll('.brand-checkbox');
                const checkedIndividualCheckboxes = tableBody.querySelectorAll('.brand-checkbox:checked');
                if (selectAllCheckboxes) {
                    selectAllCheckboxes.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
                }
            }
        });

        bulkDeleteBtn.addEventListener('click', function () {
            document.getElementById('deleteVehicleBrandModal').querySelector('#deleteVehicleBrandForm').action = '/admin/product-management/vehicle-brands/bulk-destroy';
            document.getElementById('deleteVehicleBrandModal').querySelector('#vehicleBrandNameToDelete').textContent = `${selectedBrandIds.size} hãng xe đã chọn`;
            deleteVehicleBrandModalInstance.show();
        });

        bulkToggleStatusBtn.addEventListener('click', function () {
            document.getElementById('bulkToggleStatusVehicleBrandModal').querySelector('#selectedBrandCountToggleModal').textContent = selectedBrandIds.size;
            bulkToggleStatusVehicleBrandModalInstance.show();
        });

        // Initial setup for this tab
        updateCheckboxStates(entityType);
        attachPaginationListenersForBrands();
        performSearch(1);

    })(); // End IIFE for Vehicle Brands

    // ===================================================================
    // SECTION: DÒNG XE (VEHICLE MODELS) LOGIC
    // ===================================================================
    (function initializeVehicleModels() {
        const entityType = 'Model';
        const tableBody = document.getElementById('vehicle-models-table-body');
        const searchInput = document.getElementById('modelSearchInput');
        const searchBtn = document.getElementById('modelSearchBtn');
        const filterSelect = document.getElementById('modelFilterSelect');
        const brandFilterSelect = document.getElementById('modelBrandFilterSelect');
        const sortSelect = document.getElementById('modelSortSelect');
        const paginationContainer = document.getElementById('vehicle-models-pagination-links');

        // Modals (sử dụng biến toàn cục)
        createVehicleModelModalInstance = new bootstrap.Modal(document.getElementById('createVehicleModelModal'));
        updateVehicleModelModalInstance = new bootstrap.Modal(document.getElementById('updateVehicleModelModal'));
        viewVehicleModelModalInstance = new bootstrap.Modal(document.getElementById('viewVehicleModelModal'));
        deleteVehicleModelModalInstance = new bootstrap.Modal(document.getElementById('deleteVehicleModelModal'));
        bulkToggleStatusVehicleModelModalInstance = new bootstrap.Modal(document.getElementById('bulkToggleStatusVehicleModelModal'));

        // Bulk action elements (sử dụng biến toàn cục)
        const selectAllCheckboxes = document.getElementById('selectAllVehicleModels');
        const bulkDeleteBtn = document.getElementById('modelBulkDeleteBtn');
        const bulkToggleStatusBtn = document.getElementById('modelBulkToggleStatusBtn');


        // --- Hàm AJAX chung cho search/filter/sort ---
        async function performSearch(page = 1) {
            showAppLoader();
            try {
                const currentSearchQuery = searchInput.value;
                const currentFilter = filterSelect.value;
                const currentBrandFilter = brandFilterSelect.value;
                const currentSort = sortSelect.value;

                const urlParams = new URLSearchParams();
                urlParams.append('models_page', page); // Đã sửa: dùng 'models_page' cho phân trang dòng xe
                urlParams.append('tab', 'models');
                if (currentSearchQuery) urlParams.append('model_search', currentSearchQuery);
                if (currentFilter && currentFilter !== 'all') urlParams.append('model_filter', currentFilter);

                // SỬA ĐỔI: Thêm model_filter=by_brand khi có brand_id_filter
                if (currentBrandFilter && currentBrandFilter !== '') {
                    urlParams.append('model_filter', 'by_brand'); //
                    urlParams.append('brand_id_filter', currentBrandFilter);
                }

                if (currentSort && currentSort !== 'latest') urlParams.append('model_sort_by', currentSort);

                const url = `/admin/product-management/vehicles?${urlParams.toString()}`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                    throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
                }
                const data = await response.json();
                if (data.active_tab === 'models') {
                    updateTableContent(data.table_rows, data.pagination_links, tableBody, paginationContainer, 'models');
                }
            } catch (error) {
                console.error('Lỗi khi tải dữ liệu Dòng xe:', error);
                showToast('Không thể tải dữ liệu Dòng xe. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader();
            }
        }

        // --- Event Listeners cho Search, Filter, Sort, Pagination ---
        searchBtn.addEventListener('click', () => performSearch(1));
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(1);
            }
        });
        filterSelect.addEventListener('change', () => performSearch(1));
        brandFilterSelect.addEventListener('change', () => performSearch(1));
        sortSelect.addEventListener('change', () => performSearch(1));

        function attachPaginationListenersForModels() {
            if (paginationContainer) {
                paginationContainer.removeEventListener('click', handlePaginationClickForModels);
                paginationContainer.addEventListener('click', handlePaginationClickForModels);
            }
        }

        function handlePaginationClickForModels(event) {
            const link = event.target.closest('.pagination a');
            if (link) {
                event.preventDefault();
                const url = new URL(link.href);
                const page = url.searchParams.get('models_page'); // Đã sửa: lấy 'models_page'
                if (page) {
                    performSearch(page);
                }
            }
        }

        // --- Callbacks cho AJAX form submit ---
        function handleModelCRUDSuccess(resultData) {
            let currentPage = '1';
            if (paginationContainer) {
                const activePageLink = paginationContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = activePageLink.textContent;
                }
            }
            performSearch(parseInt(currentPage, 10));
        }

        function handleModelDeleteSuccess(deletedIds) {
            deletedIds.forEach(id => {
                document.getElementById(`vehicle-model-row-${id}`)?.remove();
                selectedModelIds.delete(String(id));
            });
            let currentPage = '1';
            if (paginationContainer) {
                const activePageLink = paginationContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = activePageLink.textContent;
                }
            }
            performSearch(parseInt(currentPage, 10));
            clearSelectedIds(entityType);
        }

        function handleModelBulkToggleStatusSuccess() {
            let currentPage = '1';
            if (paginationContainer) {
                const activePageLink = paginationContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = activePageLink.textContent;
                }
            }
            performSearch(parseInt(currentPage, 10));
            clearSelectedIds(entityType);
        }

        // --- Setup Modals & Forms ---
        setupAjaxForm('createVehicleModelForm', createVehicleModelModalInstance, handleModelCRUDSuccess, entityType);
        setupAjaxForm('updateVehicleModelForm', updateVehicleModelModalInstance, handleModelCRUDSuccess, entityType);
        setupAjaxForm('deleteVehicleModelForm', deleteVehicleModelModalInstance, handleModelDeleteSuccess, entityType);
        setupAjaxForm('bulkToggleStatusModelForm', bulkToggleStatusVehicleModelModalInstance, handleModelBulkToggleStatusSuccess, entityType);

        // --- Event Listeners for Buttons (View, Edit, Delete, Toggle) ---
        tableBody.addEventListener('click', async function (event) {
            const button = event.target.closest('button');
            if (!button) return;

            if (button.classList.contains('btn-view-vehicle-model')) {
                event.preventDefault();
                await handleShowModal(button, viewVehicleModelModalInstance, 'view', entityType);
            } else if (button.classList.contains('btn-edit-vehicle-model')) {
                event.preventDefault();
                await handleShowModal(button, updateVehicleModelModalInstance, 'update', entityType);
            } else if (button.classList.contains('btn-delete-vehicle-model') && !button.closest('#modelBulkDeleteBtn')) {
                event.preventDefault();
                handleShowDeleteModal(button, entityType);
            } else if (button.classList.contains('toggle-status-model-btn')) {
                event.preventDefault();
                showAppLoader();
                try {
                    const url = button.dataset.url;
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || `Lỗi HTTP: ${response.status}`);
                    }
                    showToast(result.message, 'success');
                    handleModelCRUDSuccess(result.vehicleModel, entityType);
                } catch (error) {
                    console.error('Lỗi khi bật/tắt trạng thái Dòng xe:', error);
                    showToast(error.message, 'error');
                } finally {
                    hideAppLoader();
                }
            } else if (button.id === 'editVehicleModelFromViewButton') {
                viewVehicleModelModalInstance.hide();
                setTimeout(() => handleShowModal(button, updateVehicleModelModalInstance, 'update', entityType), 200);
            }
        });

        // --- Delete Modal Specifics ---
        document.getElementById('deleteVehicleModelModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = this.querySelector('#deleteVehicleModelForm');
            form.removeAttribute('data-id');

            if (button && button.classList.contains('btn-delete-vehicle-model') && !button.closest('#modelBulkDeleteBtn')) {
                const modelId = button.dataset.id;
                form.action = button.dataset.deleteUrl;
                form.dataset.id = modelId;
                this.querySelector('#deleteVehicleModelName').textContent = button.dataset.name;
            } else if (button && button.id === 'modelBulkDeleteBtn') {
                form.action = '/admin/product-management/vehicle-models/bulk-destroy';
                this.querySelector('#deleteVehicleModelName').textContent = `${selectedModelIds.size} dòng xe đã chọn`;
            }
        });

        // --- Bulk Actions Setup ---
        // updateModelBulkActionButtons, updateModelCheckboxStates, clearSelectedModels are now
        // generalized updateBulkActionButtons, updateCheckboxStates, clearSelectedIds.
        // They will be called by their specific entity functions.

        // Event listener for select all
        selectAllCheckboxes.addEventListener('change', function () {
            const currentCheckboxes = tableBody.querySelectorAll('.model-checkbox');
            currentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) selectedModelIds.add(checkbox.value);
                else selectedModelIds.delete(checkbox.value);
            });
            updateBulkActionButtons(entityType);
        });

        // Event listener for individual checkboxes
        tableBody.addEventListener('change', function (event) {
            const checkbox = event.target.closest('.model-checkbox');
            if (checkbox) {
                if (checkbox.checked) selectedModelIds.add(checkbox.value);
                else selectedModelIds.delete(checkbox.value);
                updateBulkActionButtons(entityType);
                const allIndividualCheckboxes = tableBody.querySelectorAll('.model-checkbox');
                const checkedIndividualCheckboxes = tableBody.querySelectorAll('.model-checkbox:checked');
                if (selectAllCheckboxes) {
                    selectAllCheckboxes.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
                }
            }
        });

        bulkDeleteBtn.addEventListener('click', function () {
            document.getElementById('deleteVehicleModelModal').querySelector('#deleteVehicleModelForm').action = '/admin/product-management/vehicle-models/bulk-destroy';
            document.getElementById('deleteVehicleModelModal').querySelector('#deleteVehicleModelName').textContent = `${selectedModelIds.size} dòng xe đã chọn`;
            deleteVehicleModelModalInstance.show();
        });

        bulkToggleStatusBtn.addEventListener('click', function () {
            document.getElementById('bulkToggleStatusVehicleModelModal').querySelector('#selectedModelCountToggleModal').textContent = selectedModelIds.size;
            bulkToggleStatusVehicleModelModalInstance.show();
        });

        // Initial setup for this tab
        updateCheckboxStates(entityType);
        attachPaginationListenersForModels();
        performSearch(1);

    })(); // End IIFE for Vehicle Models


} // End initializeVehicleManagementPage function