/**
 * ===================================================================
 * category_manager.js
 * Xử lý JavaScript đầy đủ cho trang quản lý Danh mục sản phẩm.
 * PHIÊN BẢN ĐÃ SỬA LỖI VÀ ĐỒNG BỘ - ĐÃ CẬP NHẬT TOAST NOTIFICATIONS.
 * ===================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict'; // Chế độ nghiêm ngặt cho JavaScript

    console.log("Khởi tạo JS cho trang Quản lý Danh mục...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // Lấy các hàm helper toàn cục (showAppLoader, hideAppLoader, showToast)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Fallback đơn giản nếu showToast không được định nghĩa toàn cục
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
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


    const createCategoryModalElement = document.getElementById('createCategoryModal');
    const updateCategoryModalElement = document.getElementById('updateCategoryModal');
    const viewCategoryModalElement = document.getElementById('viewCategoryModal');
    const deleteCategoryModalElement = document.getElementById('deleteCategoryModal');

    // Hàm hiển thị lỗi validation dưới trường input
    function displayValidationErrors(formElement, errors) {
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        for (const fieldName in errors) {
            if (errors.hasOwnProperty(fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    const errorDiv = inputField.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0];
                    } else {
                        // Fallback if the standard structure is not found (e.g., custom error div)
                        const specificErrorDiv = formElement.querySelector(`#category${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}Error`);
                        if (specificErrorDiv) {
                            specificErrorDiv.textContent = errors[fieldName][0];
                            specificErrorDiv.style.display = 'block';
                        } else {
                            console.warn(`Không tìm thấy div .invalid-feedback hoặc error cụ thể cho trường: ${fieldName}`);
                        }
                    }
                } else {
                    console.warn(`Không tìm thấy trường input cho lỗi: ${fieldName}`);
                    // If no specific input field, show as a general toast
                    showToast(`Lỗi: ${fieldName} - ${errors[fieldName][0]}`, 'error');
                }
            }
        }
    }

    // Hàm điền dữ liệu vào form cập nhật danh mục và hiển thị modal
    function populateAndUpdateCategoryModal(triggerButton) {
        if (!updateCategoryModalElement || !triggerButton) return;

        const nameInput = updateCategoryModalElement.querySelector('#categoryNameUpdate');
        const descriptionInput = updateCategoryModalElement.querySelector('#categoryDescriptionUpdate');
        const statusSelect = updateCategoryModalElement.querySelector('#categoryStatusUpdate');
        const updateCategoryForm = updateCategoryModalElement.querySelector('#updateCategoryForm');
        const submitButtonUpdate = updateCategoryForm ? updateCategoryForm.querySelector('button[type="submit"]') : null;

        const name = triggerButton.dataset.name;
        const description = triggerButton.dataset.description;
        const status = triggerButton.dataset.status;
        const updateUrl = triggerButton.dataset.updateUrl;

        if (updateCategoryForm) updateCategoryForm.action = updateUrl;
        if (nameInput) nameInput.value = name;
        if (descriptionInput) descriptionInput.value = description;
        if (statusSelect) statusSelect.value = status;

        // Clear validation errors on modal open
        if (updateCategoryForm) {
            updateCategoryForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            updateCategoryForm.querySelectorAll('.invalid-feedback').forEach(el => {
                if (el.id && el.id.endsWith('Error')) el.textContent = '';
                el.textContent = '';
            });
        }

        if (submitButtonUpdate) {
            submitButtonUpdate.disabled = false;
            submitButtonUpdate.innerHTML = 'Lưu thay đổi';
        }
    }

    // Xử lý modal cập nhật danh mục
    if (updateCategoryModalElement) {
        updateCategoryModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            populateAndUpdateCategoryModal(button);
        });

        const updateCategoryForm = updateCategoryModalElement.querySelector('#updateCategoryForm');
        if (updateCategoryForm) {
            const submitButtonUpdate = updateCategoryForm.querySelector('button[type="submit"]');
            updateCategoryForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                showAppLoader();
                if (submitButtonUpdate) {
                    submitButtonUpdate.disabled = true;
                    submitButtonUpdate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
                }

                const formData = new FormData(this);
                formData.append('_method', 'PUT'); // Sử dụng PUT cho cập nhật
                const actionUrl = this.action;

                // Clear previous validation errors
                updateCategoryForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                updateCategoryForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST', // Luôn là POST khi dùng FormData với _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    });
                    const result = await response.json();

                    if (response.ok) {
                        const modalInstance = bootstrap.Modal.getInstance(updateCategoryModalElement);
                        if (modalInstance) modalInstance.hide();
                        showToast(result.message, 'success');
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else if (response.status === 422 && result.errors) {
                        displayValidationErrors(updateCategoryForm, result.errors);
                        showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                    } else {
                        showToast(result.message || 'Cập nhật không thành công.', 'error');
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật danh mục:', error);
                    showToast('Có lỗi xảy ra trong quá trình xử lý.', 'error');
                } finally {
                    hideAppLoader();
                    if (submitButtonUpdate) {
                        submitButtonUpdate.disabled = false;
                        submitButtonUpdate.innerHTML = 'Lưu thay đổi';
                    }
                }
            });
        }
    }


    // Xử lý modal xem chi tiết danh mục
    if (viewCategoryModalElement) {
        const categoryIdView = viewCategoryModalElement.querySelector('#categoryIdView');
        const categoryNameView = viewCategoryModalElement.querySelector('#categoryNameView');
        const categoryDescriptionView = viewCategoryModalElement.querySelector('#categoryDescriptionView');
        const categoryStatusViewText = viewCategoryModalElement.querySelector('#categoryStatusView');
        const categoryCreatedAtView = viewCategoryModalElement.querySelector('#categoryCreatedAtView');
        const categoryUpdatedAtView = viewCategoryModalElement.querySelector('#categoryUpdatedAtView');
        const editCategoryFromViewButton = viewCategoryModalElement.querySelector('#editCategoryFromViewButton');

        viewCategoryModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const categoryId = button.dataset.id;
            const name = button.dataset.name;
            const description = button.dataset.description;
            const statusValue = button.dataset.status;
            const createdAt = button.dataset.createdAt;
            const updatedAt = button.dataset.updatedAt;
            const updateUrl = button.dataset.updateUrl;

            if (categoryIdView) categoryIdView.textContent = categoryId || '-';
            if (categoryNameView) categoryNameView.textContent = name || '-';
            if (categoryDescriptionView) categoryDescriptionView.textContent = description || 'Không có mô tả';
            if (categoryCreatedAtView) categoryCreatedAtView.textContent = createdAt || '-';
            if (categoryUpdatedAtView) categoryUpdatedAtView.textContent = updatedAt || '-';

            if (categoryStatusViewText) {
                if (statusValue === 'active') {
                    categoryStatusViewText.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
                } else if (statusValue === 'inactive') {
                    categoryStatusViewText.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
                } else {
                    categoryStatusViewText.textContent = statusValue || '-';
                }
            }

            if (editCategoryFromViewButton) {
                editCategoryFromViewButton.dataset.id = categoryId;
                editCategoryFromViewButton.dataset.name = name;
                editCategoryFromViewButton.dataset.description = description;
                editCategoryFromViewButton.dataset.status = statusValue;
                editCategoryFromViewButton.dataset.updateUrl = updateUrl;
            }
        });

        if (editCategoryFromViewButton && updateCategoryModalElement) {
            editCategoryFromViewButton.addEventListener('click', function () {
                const viewModalInstance = bootstrap.Modal.getInstance(viewCategoryModalElement);
                if (viewModalInstance) viewModalInstance.hide();
                setTimeout(() => {
                    populateAndUpdateCategoryModal(this);
                    const updateModal = bootstrap.Modal.getInstance(updateCategoryModalElement) || new bootstrap.Modal(updateCategoryModalElement);
                    updateModal.show();
                }, 200);
            });
        }
    }


    // Xử lý modal xóa danh mục
    if (deleteCategoryModalElement) {
        const deleteCategoryForm = deleteCategoryModalElement.querySelector('#deleteCategoryForm');
        const categoryNameSpan = deleteCategoryModalElement.querySelector('#categoryNameToDelete');
        const passwordInput = deleteCategoryModalElement.querySelector('#categoryDeletionPassword');
        const passwordErrorDiv = deleteCategoryModalElement.querySelector('#categoryDeletionPasswordError');
        const submitButtonDelete = deleteCategoryForm ? deleteCategoryForm.querySelector('button[type="submit"]') : null;

        deleteCategoryModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const name = button.dataset.name;
            const deleteUrl = button.dataset.deleteUrl;

            if (deleteCategoryForm) deleteCategoryForm.action = deleteUrl;
            if (categoryNameSpan) categoryNameSpan.textContent = name;
            if (passwordInput) {
                passwordInput.value = '';
                passwordInput.classList.remove('is-invalid');
            }
            if (passwordErrorDiv) {
                passwordErrorDiv.textContent = '';
                passwordErrorDiv.style.display = 'none';
            }
            if (submitButtonDelete) {
                submitButtonDelete.disabled = false;
                submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
            }
        });

        if (deleteCategoryForm && submitButtonDelete) {
            deleteCategoryForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const url = this.action;
                const formData = new FormData(this);
                formData.set('_method', 'DELETE');

                showAppLoader();
                submitButtonDelete.disabled = true;
                submitButtonDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xóa...';

                if (passwordInput) passwordInput.classList.remove('is-invalid');
                if (passwordErrorDiv) {
                    passwordErrorDiv.textContent = '';
                    passwordErrorDiv.style.display = 'none';
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (response.ok) {
                        const modalInstance = bootstrap.Modal.getInstance(deleteCategoryModalElement);
                        if (modalInstance) modalInstance.hide();
                        showToast(result.message, 'success');
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else if (response.status === 422 && result.errors && result.errors.deletion_password && passwordInput && passwordErrorDiv) {
                        passwordInput.classList.add('is-invalid');
                        passwordErrorDiv.textContent = result.errors.deletion_password[0];
                        passwordErrorDiv.style.display = 'block';
                        showToast('Vui lòng nhập đúng mật khẩu xác nhận.', 'error');
                    } else {
                        showToast(result.message || `Lỗi HTTP: ${response.status}`, 'error');
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa danh mục:', error);
                    showToast('Có lỗi xảy ra trong quá trình xử lý xóa.', 'error');
                } finally {
                    hideAppLoader();
                    submitButtonDelete.disabled = false;
                    submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
                }
            });
        }
    }


    // Xử lý nút toggle-status (sử dụng event delegation)
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('.toggle-status-btn');
        if (!button) return;

        event.preventDefault();

        const categoryId = button.dataset.id;
        const url = button.dataset.url;

        showAppLoader();
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorResult.message || `Lỗi HTTP: ${response.status}`);
            }
            const result = await response.json();
            if (result.success) {
                showToast(result.message || 'Cập nhật trạng thái thành công.', 'success');

                const row = document.getElementById(`category-row-${categoryId}`);
                if (row) {
                    const statusCell = document.getElementById(`category-status-${categoryId}`);
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                    }
                    button.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                    button.title = result.new_button_title;

                    if (result.new_status === 'inactive') {
                        row.classList.add('row-inactive');
                    } else {
                        row.classList.remove('row-inactive');
                    }

                    button.classList.remove('btn-danger', 'btn-outline-secondary');
                    if (result.new_status === 'active') {
                        button.classList.add('btn-outline-secondary');
                    } else {
                        button.classList.add('btn-danger');
                    }

                    // Update dataset for view/edit buttons in the same row
                    const viewButton = row.querySelector('.btn-view-category');
                    const editButton = row.querySelector('.btn-edit-category');
                    if (viewButton) {
                        viewButton.dataset.status = result.new_status;
                        viewButton.dataset.statusText = result.status_text; // Ensure these are updated
                        viewButton.dataset.statusBadgeClass = result.status_badge_class; // If you use specific classes
                    }
                    if (editButton) {
                        editButton.dataset.status = result.new_status;
                    }
                }
            } else {
                throw new Error(result.message || 'Có lỗi khi cập nhật trạng thái.');
            }
        } catch (error) {
            console.error('Lỗi khi thay đổi trạng thái danh mục:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    });


    // --- Xử lý cho Form Tạo Mới ---
    if (createCategoryModalElement) {
        const createCategoryForm = createCategoryModalElement.querySelector('#createCategoryForm');
        const createCategoryModalInstance = new bootstrap.Modal(createCategoryModalElement);

        if (createCategoryForm) {
            createCategoryForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                showAppLoader();
                const submitButtonCreate = createCategoryForm.querySelector('button[type="submit"]');
                if (submitButtonCreate) {
                    submitButtonCreate.disabled = true;
                    submitButtonCreate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
                }

                const formData = new FormData(this);

                // Clear previous validation errors
                createCategoryForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                createCategoryForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (response.ok) {
                        createCategoryModalInstance.hide();
                        showToast(result.message, 'success');
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else if (response.status === 422 && result.errors) {
                        displayValidationErrors(createCategoryForm, result.errors);
                        showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                    } else {
                        showToast(result.message || 'Tạo danh mục không thành công.', 'error');
                    }
                } catch (error) {
                    console.error('Lỗi khi tạo danh mục:', error);
                    showToast('Có lỗi xảy ra trong quá trình xử lý tạo mới.', 'error');
                } finally {
                    hideAppLoader();
                    if (submitButtonCreate) {
                        submitButtonCreate.disabled = false;
                        submitButtonCreate.innerHTML = 'Tạo Danh Mục';
                    }
                }
            });
        }

        // Reset form khi đóng modal tạo
        createCategoryModalElement.addEventListener('hidden.bs.modal', () => {
            const form = createCategoryModalElement.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }
        });
    }

    // Reset form khi đóng modal cập nhật
    if (updateCategoryModalElement) {
        updateCategoryModalElement.addEventListener('hidden.bs.modal', () => {
            const form = updateCategoryModalElement.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }
        });
    }

    // Xử lý mở lại modal tạo danh mục nếu có lỗi validation sau khi reload trang
    // Điều này thường xảy ra khi Laravel flash lỗi vào session và reload trang.
    // Nếu bạn muốn xử lý hoàn toàn bằng AJAX mà không reload trang khi có lỗi,
    // thì phần này có thể không cần thiết hoặc cần logic khác.
    const urlParams = new URLSearchParams(window.location.search);
    const hasValidationErrors = urlParams.get('hasValidationErrors') === 'true';
    const formMarker = urlParams.get('formMarker');

    if (hasValidationErrors && formMarker === 'create_category') {
        if (createCategoryModalElement) {
            const createModalInstance = new bootstrap.Modal(createCategoryModalElement);
            if (createModalInstance) {
                createModalInstance.show();
            }
        }
    }

    console.log("Module Quản lý Danh mục đã được khởi tạo thành công.");
});