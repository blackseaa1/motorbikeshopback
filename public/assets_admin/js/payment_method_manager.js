/**
 * ===================================================================
 * payment_method_manager.js
 *
 * Xử lý JavaScript cho trang quản lý Phương thức Thanh toán.
 * - Quản lý modals (Create, Update, Delete) và AJAX.
 * - Xử lý bật/tắt trạng thái với cập nhật giao diện trực tiếp.
 * - Sử dụng các hàm global: window.showAppLoader, window.hideAppLoader, window.showAppInfoModal
 * ===================================================================
 */
function initializePaymentMethodsPage() {
    console.log("Khởi tạo JS cho trang Quản lý Phương thức Thanh toán...");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const pageScope = '#adminPaymentMethodsPage ';

    // --- HELPER FUNCTIONS ---

    // Hàm hiển thị lỗi validation từ server
    function displayValidationErrors(formElement, errors) {
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        Object.keys(errors).forEach(key => {
            const inputField = formElement.querySelector(`[name="${key}"]`);
            const formType = formElement.id.includes('create') ? 'Create' : 'Update';
            // Tạo ID cho div lỗi, ví dụ: pmNameCreateError, pmCodeUpdateError
            const errorDivId = `pm${key.charAt(0).toUpperCase() + key.slice(1).replace(/_([a-z])/g, g => g[1].toUpperCase())}${formType}Error`;
            const errorDiv = formElement.querySelector(`#${errorDivId}`);

            if (inputField && errorDiv) {
                inputField.classList.add('is-invalid');
                errorDiv.textContent = errors[key][0];
                errorDiv.style.display = 'block';
            } else {
                // Hiển thị lỗi chung nếu không tìm thấy trường cụ thể
                window.showAppInfoModal(errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                console.warn(`Không tìm thấy input hoặc error div cho trường: ${key}`);
            }
        });
    }

    // Hàm xử lý submit form AJAX chung
    function handleAjaxFormSubmit(formElement, modalElement, sectionTitle, successCallback, httpMethod = 'POST') {
        if (!formElement) return;

        // Gắn một handler duy nhất để tránh gắn chồng chéo
        if (formElement.submitHandler) {
            formElement.removeEventListener('submit', formElement.submitHandler);
        }

        formElement.submitHandler = async function (event) {
            event.preventDefault();
            if (typeof window.showAppLoader === 'function') window.showAppLoader();

            const submitButton = formElement.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.innerHTML : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...`;
            }

            formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            formElement.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });

            const formData = new FormData(formElement);
            if (httpMethod.toUpperCase() !== 'POST') {
                formData.append('_method', httpMethod.toUpperCase());
            }

            try {
                const response = await fetch(formElement.action, {
                    method: 'POST', // Luôn là POST, Laravel xử lý qua _method
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        displayValidationErrors(formElement, result.errors);
                        window.showAppInfoModal(result.message || 'Vui lòng kiểm tra lại các trường dữ liệu.', 'validation_error', `Lỗi nhập liệu ${sectionTitle}`);
                    } else {
                        window.showAppInfoModal(result.message || `Đã có lỗi xảy ra.`, 'error', 'Lỗi!');
                    }
                    throw new Error(result.message || 'Server error');
                }

                if (successCallback && typeof successCallback === 'function') {
                    successCallback(result);
                }

            } catch (error) {
                console.error(`Lỗi khi submit form ${formElement.id}:`, error);
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        };

        formElement.addEventListener('submit', formElement.submitHandler);
    }

    // --- CREATE PAYMENT METHOD ---
    const createModalElement = document.getElementById('createPaymentMethodModal');
    if (createModalElement) {
        const createForm = createModalElement.querySelector('#createPaymentMethodForm');
        handleAjaxFormSubmit(createForm, createModalElement, "Phương thức Thanh toán", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(createModalElement);
            if (modalInstance) modalInstance.hide();
            window.showAppInfoModal(result.message, 'success', 'Thành công!');
            setTimeout(() => window.location.reload(), 1200); // Tải lại để hiển thị mục mới
        }, 'POST');

        createModalElement.addEventListener('hidden.bs.modal', function () {
            createForm.reset();
            createForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            createForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            const imgPreview = createModalElement.querySelector('#pmLogoPreviewCreate');
            if (imgPreview && imgPreview.dataset.defaultSrc) {
                imgPreview.src = imgPreview.dataset.defaultSrc;
            }
        });
    }

    // --- UPDATE PAYMENT METHOD ---
    const updateModalElement = document.getElementById('updatePaymentMethodModal');
    if (updateModalElement) {
        const updateForm = updateModalElement.querySelector('#updatePaymentMethodForm');

        updateModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            updateForm.action = button.dataset.updateUrl;
            updateForm.querySelector('#pmNameUpdate').value = button.dataset.name || '';
            updateForm.querySelector('#pmCodeUpdate').value = button.dataset.code || '';
            updateForm.querySelector('#pmDescriptionUpdate').value = button.dataset.description || '';
            updateForm.querySelector('#pmStatusUpdate').value = button.dataset.status || 'active';

            const logoPreview = updateForm.querySelector('#pmLogoPreviewUpdate');
            const defaultLogoSrc = logoPreview.dataset.defaultSrc || 'https://placehold.co/150x75/EFEFEF/AAAAAA&text=Current';
            logoPreview.src = button.dataset.logoUrl && button.dataset.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' ? button.dataset.logoUrl : defaultLogoSrc;
            updateForm.querySelector('#pmLogoUpdate').value = '';
        });

        handleAjaxFormSubmit(updateForm, updateModalElement, "Phương thức Thanh toán", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
            if (modalInstance) modalInstance.hide();
            window.showAppInfoModal(result.message, 'success', 'Thành công!');

            const method = result.paymentMethod;
            const updatedRow = document.getElementById(`pm-row-${method.id}`);
            if (updatedRow) {
                // Cập nhật giao diện trực tiếp
                updatedRow.cells[1].querySelector('img').src = method.logo_full_url;
                updatedRow.cells[2].textContent = method.name;
                updatedRow.cells[3].textContent = method.code;
                updatedRow.querySelector('.status-cell span').className = `badge ${method.status_badge_class}`;
                updatedRow.querySelector('.status-cell span').textContent = method.status_text;

                // Cập nhật lại data attributes cho các nút
                const editBtn = updatedRow.querySelector('.btn-edit-pm');
                if (editBtn) {
                    editBtn.dataset.name = method.name;
                    editBtn.dataset.code = method.code;
                    editBtn.dataset.description = method.description;
                    editBtn.dataset.logoUrl = method.logo_full_url;
                    editBtn.dataset.status = method.status;
                }
            } else {
                setTimeout(() => window.location.reload(), 1200);
            }
        }, 'PUT');
    }

    // --- DELETE PAYMENT METHOD ---
    const deleteModalElement = document.getElementById('deletePaymentMethodModal');
    if (deleteModalElement) {
        const deleteForm = deleteModalElement.querySelector('#deletePaymentMethodForm');

        deleteModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            deleteForm.action = button.dataset.deleteUrl;
            deleteModalElement.querySelector('#pmNameToDelete').textContent = button.dataset.name || 'N/A';
        });

        handleAjaxFormSubmit(deleteForm, deleteModalElement, "Phương thức Thanh toán", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(deleteModalElement);
            if (modalInstance) modalInstance.hide();
            window.showAppInfoModal(result.message, 'success', 'Thành công!');
            setTimeout(() => window.location.reload(), 1200);
        }, 'DELETE');
    }

    // --- TOGGLE STATUS ---
    async function handleToggleStatus(event) {
        event.preventDefault();
        const currentButton = this;
        const url = currentButton.dataset.url;
        const methodId = currentButton.dataset.id;

        if (!url || !methodId) {
            window.showAppInfoModal('Lỗi cấu hình: Thiếu thông tin trên nút.', 'error');
            return;
        }

        currentButton.disabled = true;
        if (typeof window.showAppLoader === 'function') window.showAppLoader();

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Lỗi không xác định từ máy chủ.');
            }

            const row = document.getElementById(`pm-row-${methodId}`);
            if (row) {
                const method = result.paymentMethod;
                const statusCell = row.querySelector('.status-cell span');

                if (statusCell) {
                    statusCell.className = `badge ${method.status_badge_class}`;
                    statusCell.textContent = method.status_text;
                }

                currentButton.title = result.new_button_title;
                currentButton.className = `btn btn-sm toggle-status-btn ${method.status === 'active' ? 'btn-outline-secondary' : 'btn-danger'}`;

                row.classList.toggle('row-inactive', method.status !== 'active');

                const editButton = row.querySelector('.btn-edit-pm');
                if (editButton) {
                    editButton.dataset.status = method.status;
                }
                window.showAppInfoModal(result.message, 'success', 'Thành công!');
            } else {
                setTimeout(() => window.location.reload(), 1200);
            }

        } catch (error) {
            console.error('[Toggle Status] Lỗi:', error);
            window.showAppInfoModal(error.message, 'error', 'Lỗi!');
        } finally {
            currentButton.disabled = false;
            if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
        }
    }

    // --- BIND EVENT LISTENERS ---
    function bindAllEventListeners() {
        document.querySelectorAll(pageScope + '.toggle-status-btn').forEach(button => {
            button.removeEventListener('click', handleToggleStatus);
            button.addEventListener('click', handleToggleStatus);
        });

        // Image preview for Create Modal
        const createLogoInput = document.getElementById('pmLogoCreate');
        const createLogoPreview = document.getElementById('pmLogoPreviewCreate');
        if (createLogoInput && createLogoPreview) {
            createLogoInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    createLogoPreview.src = URL.createObjectURL(file);
                }
            });
        }

        // Image preview for Update Modal
        const updateLogoInput = document.getElementById('pmLogoUpdate');
        const updateLogoPreview = document.getElementById('pmLogoPreviewUpdate');
        if (updateLogoInput && updateLogoPreview) {
            updateLogoInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    updateLogoPreview.src = URL.createObjectURL(file);
                }
            });
        }
    }

    // Initial setup
    bindAllEventListeners();
}

