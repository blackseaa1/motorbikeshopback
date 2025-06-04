/**
 * ===================================================================
 * category_manager.js
 * Xử lý JavaScript cho trang quản lý Danh mục.
 * ===================================================================
 */
function initializeCategoriesPage() {
    console.log("Khởi tạo JS cho trang Quản lý Danh mục...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function showAppNotification(type, title, message) {
        if (typeof window.showAppInfoModal === 'function') {
            let modalType = type;
            const messageContent = (typeof message === 'object' && message.html) ? message : String(message);
            window.showAppInfoModal(messageContent, modalType, title);
        } else {
            console.warn('Hàm window.showAppInfoModal không khả dụng.');
            const alertMessage = (typeof message === 'object' && message.html) ? title + ": Vui lòng kiểm tra console." : title + ": " + message;
            if (typeof message === 'object' && message.html) console.error("Nội dung HTML cho alert:", message.html);
            alert(alertMessage);
        }
    }

    const updateCategoryModalElement = document.getElementById('updateCategoryModal');
    const viewCategoryModalElement = document.getElementById('viewCategoryModal');

    // Hàm chung để điền dữ liệu vào form Update Category Modal
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

        // Reset error states
        updateCategoryForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        updateCategoryForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        if (submitButtonUpdate) {
            submitButtonUpdate.disabled = false;
            submitButtonUpdate.innerHTML = 'Lưu thay đổi';
        }
    }

    // --- Logic for Update Category Modal ---
    if (updateCategoryModalElement) {
        updateCategoryModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Nút .btn-edit-category hoặc #editCategoryFromViewButton
            if (!button) return;
            populateAndUpdateCategoryModal(button);
        });

        const updateCategoryForm = updateCategoryModalElement.querySelector('#updateCategoryForm');
        if (updateCategoryForm) {
            const submitButtonUpdate = updateCategoryForm.querySelector('button[type="submit"]');
            updateCategoryForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                if (submitButtonUpdate) {
                    submitButtonUpdate.disabled = true;
                    submitButtonUpdate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
                }

                const formData = new FormData(this); // Đã có _method='PUT' từ Blade
                const actionUrl = this.action;

                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => {
                    if (el.id && el.id.endsWith('Error')) el.textContent = '';
                });

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST', // Vì FormData và _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            Object.keys(result.errors).forEach(key => {
                                const errorField = document.getElementById(`category${key.charAt(0).toUpperCase() + key.slice(1)}UpdateError`);
                                const inputField = document.getElementById(`category${key.charAt(0).toUpperCase() + key.slice(1)}Update`);
                                if (inputField) inputField.classList.add('is-invalid');
                                if (errorField) {
                                    errorField.textContent = result.errors[key][0];
                                    errorField.style.display = 'block';
                                } else {
                                    showAppNotification('validation_error', 'Lỗi Dữ Liệu', `${key}: ${result.errors[key][0]}`);
                                }
                            });
                        } else {
                            showAppNotification('error', 'Lỗi Cập Nhật!', result.message || 'Không thể cập nhật danh mục.');
                        }
                        return;
                    }

                    if (result.success) {
                        const modalInstance = bootstrap.Modal.getInstance(updateCategoryModalElement);
                        if (modalInstance) modalInstance.hide();
                        showAppNotification('success', 'Thành công!', result.message);
                        if (result.redirect_url) { // Controller Category::update cần trả về redirect_url
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        showAppNotification('error', 'Lỗi!', result.message || 'Cập nhật không thành công.');
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật danh mục:', error);
                    showAppNotification('error', 'Lỗi Hệ Thống!', 'Có lỗi xảy ra trong quá trình xử lý.');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    if (submitButtonUpdate) {
                        submitButtonUpdate.disabled = false;
                        submitButtonUpdate.innerHTML = 'Lưu thay đổi';
                    }
                }
            });
        }
    }

    // --- Logic for View Category Modal ---
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

                populateAndUpdateCategoryModal(this); // 'this' là editCategoryFromViewButton

                const updateModal = bootstrap.Modal.getInstance(updateCategoryModalElement) || new bootstrap.Modal(updateCategoryModalElement);
                updateModal.show();
            });
        }
    }

    // --- Logic for Delete Category Modal ---
    const deleteCategoryModalElement = document.getElementById('deleteCategoryModal');
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
                const formData = new FormData();
                formData.append('_method', 'DELETE');

                if (passwordInput && document.getElementById('categoryDeletionPassword')) {
                    const currentPassword = passwordInput.value;
                    if (!currentPassword.trim() && passwordInput.required) {
                        passwordInput.classList.add('is-invalid');
                        if (passwordErrorDiv) {
                            passwordErrorDiv.textContent = 'Vui lòng nhập mật khẩu xác nhận.';
                            passwordErrorDiv.style.display = 'block';
                        }
                        return;
                    }
                    formData.append('deletion_password', currentPassword);
                }

                if (typeof window.showAppLoader === 'function') window.showAppLoader();
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

                    if (!response.ok) {
                        if (response.status === 422 && result.errors && result.errors.deletion_password && passwordInput && passwordErrorDiv) {
                            passwordInput.classList.add('is-invalid');
                            passwordErrorDiv.textContent = result.errors.deletion_password[0];
                            passwordErrorDiv.style.display = 'block';
                        } else {
                            showAppNotification('error', 'Lỗi Xóa!', result.message || `Lỗi HTTP: ${response.status}`);
                        }
                        return;
                    }

                    if (result.success) {
                        const modalInstance = bootstrap.Modal.getInstance(deleteCategoryModalElement);
                        if (modalInstance) modalInstance.hide();
                        showAppNotification('success', 'Thành công!', result.message);
                        // Controller Category::destroy cần trả về redirect_url
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        showAppNotification('error', 'Lỗi!', result.message || 'Không thể xóa danh mục.');
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa danh mục:', error);
                    showAppNotification('error', 'Lỗi Hệ Thống!', 'Có lỗi xảy ra trong quá trình xử lý.');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    submitButtonDelete.disabled = false;
                    submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
                }
            });
        }
    }

    // --- Logic for Toggle Status Button ---
    document.querySelectorAll('#adminCategoriesPage .toggle-status-btn').forEach(button => { // Thêm scope #adminCategoriesPage
        button.addEventListener('click', async function () {
            const categoryId = this.dataset.id;
            const url = this.dataset.url;
            const currentButton = this;

            if (typeof window.showAppLoader === 'function') window.showAppLoader();
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
                    const row = document.getElementById(`category-row-${categoryId}`);
                    const statusCell = document.getElementById(`category-status-${categoryId}`);
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                    }
                    currentButton.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                    currentButton.title = result.new_button_title;
                    if (row) {
                        if (result.new_status === 'inactive') row.classList.add('row-inactive');
                        else row.classList.remove('row-inactive');
                    }
                } else {
                    throw new Error(result.message || 'Có lỗi khi cập nhật trạng thái.');
                }
            } catch (error) {
                console.error('Lỗi khi thay đổi trạng thái danh mục:', error);
                showAppNotification('error', 'Lỗi Cập Nhật!', error.message);
            } finally {
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        });
    });

    // Xử lý form Create Category (nếu muốn AJAX, hiện tại đang là submit truyền thống)
    const createCategoryForm = document.getElementById('createCategoryForm');
    if (createCategoryForm) {
        const submitButtonCreate = createCategoryForm.querySelector('button[type="submit"]');
        createCategoryForm.addEventListener('submit', function () {
            if (submitButtonCreate) {
                submitButtonCreate.disabled = true;
                submitButtonCreate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
            }
        });
    }
}

// Gọi hàm khởi tạo (trong admin_layout.js)
// if (document.getElementById('adminCategoriesPage') && typeof initializeCategoriesPage === 'function') {
//     initializeCategoriesPage();
// }