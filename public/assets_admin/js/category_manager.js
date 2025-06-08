/**
 * ===================================================================
 * category_manager.js
 * Xử lý JavaScript đầy đủ cho trang quản lý Danh mục sản phẩm.
 * PHIÊN BẢN ĐÃ SỬA LỖI VÀ ĐỒNG BỘ.
 * ===================================================================
 */
function initializeCategoriesPage() {
    console.log("Khởi tạo JS cho trang Quản lý Danh mục...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Hàm trợ giúp hiển thị thông báo chung
    function showAppNotification(type, title, message) {
        if (typeof window.showAppInfoModal === 'function') {
            const messageContent = (typeof message === 'object' && message.html) ? message : String(message);
            window.showAppInfoModal(messageContent, type, title);
        } else {
            console.warn('Hàm window.showAppInfoModal không khả dụng. Hiển thị alert mặc định.');
            const alertMessage = (typeof message === 'object' && message.html) ? title + ": Vui lòng kiểm tra console." : title + ": " + message;
            alert(alertMessage);
        }
    }

    const createCategoryModalElement = document.getElementById('createCategoryModal');
    const updateCategoryModalElement = document.getElementById('updateCategoryModal');
    const viewCategoryModalElement = document.getElementById('viewCategoryModal');
    const deleteCategoryModalElement = document.getElementById('deleteCategoryModal');

    // Hàm điền dữ liệu vào form Cập nhật
    function populateAndUpdateCategoryModal(triggerButton) {
        if (!updateCategoryModalElement || !triggerButton) return;

        const form = updateCategoryModalElement.querySelector('#updateCategoryForm');
        const nameInput = form.querySelector('#categoryNameUpdate');
        const descriptionInput = form.querySelector('#categoryDescriptionUpdate');
        const statusSelect = form.querySelector('#categoryStatusUpdate');
        const submitButton = form.querySelector('button[type="submit"]');

        const name = triggerButton.dataset.name;
        const description = triggerButton.dataset.description;
        const status = triggerButton.dataset.status;
        const updateUrl = triggerButton.dataset.updateUrl;

        form.action = updateUrl;
        if (nameInput) nameInput.value = name;
        if (descriptionInput) descriptionInput.value = description;
        if (statusSelect) statusSelect.value = status;

        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Lưu thay đổi';
        }
    }

    // --- Xử lý cho Modal Xem Chi Tiết ---
    if (viewCategoryModalElement) {
        const categoryIdView = viewCategoryModalElement.querySelector('#categoryIdView');
        const categoryNameView = viewCategoryModalElement.querySelector('#categoryNameView');
        const categoryDescriptionView = viewCategoryModalElement.querySelector('#categoryDescriptionView');
        const categoryStatusView = viewCategoryModalElement.querySelector('#categoryStatusView');
        const categoryCreatedAtView = viewCategoryModalElement.querySelector('#categoryCreatedAtView');
        const categoryUpdatedAtView = viewCategoryModalElement.querySelector('#categoryUpdatedAtView');
        const editCategoryFromViewButton = viewCategoryModalElement.querySelector('#editCategoryFromViewButton');

        viewCategoryModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const { id, name, description, status, createdAt, updatedAt, statusText, statusBadgeClass } = button.dataset;

            if (categoryIdView) categoryIdView.textContent = id || '-';
            if (categoryNameView) categoryNameView.textContent = name || '-';
            if (categoryDescriptionView) categoryDescriptionView.textContent = description || 'Không có mô tả';
            if (categoryCreatedAtView) categoryCreatedAtView.textContent = createdAt || '-';
            if (categoryUpdatedAtView) categoryUpdatedAtView.textContent = updatedAt || '-';

            if (categoryStatusView) {
                const badgeClass = statusBadgeClass || (status === 'active' ? 'bg-success' : 'bg-secondary');
                const text = statusText || (status === 'active' ? 'Hoạt động' : 'Đã ẩn');
                categoryStatusView.innerHTML = `<span class="badge ${badgeClass}">${text}</span>`;
            }

            if (editCategoryFromViewButton) {
                Object.assign(editCategoryFromViewButton.dataset, button.dataset);
            }
        });

        if (editCategoryFromViewButton && updateCategoryModalElement) {
            editCategoryFromViewButton.addEventListener('click', function () {
                const viewModalInstance = bootstrap.Modal.getInstance(viewCategoryModalElement);
                if (viewModalInstance) viewModalInstance.hide();
                populateAndUpdateCategoryModal(this);
                const updateModal = bootstrap.Modal.getInstance(updateCategoryModalElement) || new bootstrap.Modal(updateCategoryModalElement);
                updateModal.show();
            });
        }
    }

    // --- Xử lý cho Form Cập Nhật ---
    if (updateCategoryModalElement) {
        updateCategoryModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-edit-category')) {
                populateAndUpdateCategoryModal(button);
            }
        });

        const updateCategoryForm = updateCategoryModalElement.querySelector('#updateCategoryForm');
        if (updateCategoryForm) {
            const submitButtonUpdate = updateCategoryForm.querySelector('button[type="submit"]');
            updateCategoryForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                submitButtonUpdate.disabled = true;
                submitButtonUpdate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';

                const formData = new FormData(this);
                const actionUrl = this.action;

                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            Object.keys(result.errors).forEach(key => {
                                const inputField = document.getElementById(`category${key.charAt(0).toUpperCase() + key.slice(1)}Update`);
                                const errorField = document.getElementById(`category${key.charAt(0).toUpperCase() + key.slice(1)}UpdateError`);
                                if (inputField) inputField.classList.add('is-invalid');
                                if (errorField) {
                                    errorField.textContent = result.errors[key][0];
                                    errorField.style.display = 'block';
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
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showAppNotification('error', 'Lỗi!', result.message || 'Cập nhật không thành công.');
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật danh mục:', error);
                    showAppNotification('error', 'Lỗi Hệ Thống!', 'Có lỗi xảy ra trong quá trình xử lý.');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    submitButtonUpdate.disabled = false;
                    submitButtonUpdate.innerHTML = 'Lưu thay đổi';
                }
            });
        }
    }

    // --- Xử lý cho Form Xóa ---
    if (deleteCategoryModalElement) {
        const deleteCategoryForm = deleteCategoryModalElement.querySelector('#deleteCategoryForm');
        const categoryNameSpan = deleteCategoryModalElement.querySelector('#categoryNameToDelete');
        const passwordInput = deleteCategoryModalElement.querySelector('#categoryDeletionPassword');
        const passwordErrorDiv = deleteCategoryModalElement.querySelector('#categoryDeletionPasswordError');
        const submitButtonDelete = deleteCategoryForm?.querySelector('button[type="submit"]');

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

        if (deleteCategoryForm) {
            deleteCategoryForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const url = this.action;

                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                submitButtonDelete.disabled = true;
                submitButtonDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xóa...';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors?.deletion_password && passwordInput && passwordErrorDiv) {
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
                        setTimeout(() => window.location.reload(), 1000);
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

    // --- LOGIC QUAN TRỌNG: Xử lý nút "Bật/Tắt Trạng thái" (PHIÊN BẢN ĐÃ SỬA LỖI) ---
    document.querySelectorAll('#adminCategoriesPage .toggle-status-btn').forEach(button => {
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
                    const errorResult = await response.json().catch(() => ({ message: 'Lỗi không xác định.' }));
                    throw new Error(errorResult.message || `Lỗi HTTP: ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    showAppNotification('success', 'Thành công!', result.message);

                    const row = document.getElementById(`category-row-${categoryId}`);
                    if (row) {
                        const statusCell = document.getElementById(`category-status-${categoryId}`);

                        // 1. Cập nhật huy hiệu (badge) bằng dữ liệu mới từ server
                        if (statusCell) {
                            statusCell.innerHTML = `<span class="badge ${result.status_badge_class}">${result.status_text}</span>`;
                        }

                        // 2. Cập nhật tiêu đề (tooltip) của nút
                        currentButton.title = result.new_button_title;

                        // 3. Cập nhật class để thay đổi MÀU SẮC của nút
                        currentButton.classList.remove('btn-danger', 'btn-outline-secondary');
                        if (result.new_status === 'active') {
                            currentButton.classList.add('btn-outline-secondary');
                        } else {
                            currentButton.classList.add('btn-danger');
                        }

                        // 4. Cập nhật class của cả hàng để làm mờ
                        if (result.new_status === 'inactive') {
                            row.classList.add('row-inactive');
                        } else {
                            row.classList.remove('row-inactive');
                        }

                        // 5. Đồng bộ hóa data-attributes trên các nút khác
                        const viewButton = row.querySelector('.btn-view-category');
                        const editButton = row.querySelector('.btn-edit-category');

                        if (viewButton) {
                            viewButton.dataset.status = result.new_status;
                            viewButton.dataset.statusText = result.status_text;
                            viewButton.dataset.statusBadgeClass = result.status_badge_class;
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
                showAppNotification('error', 'Lỗi Cập Nhật!', error.message);
            } finally {
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        });
    });

    // --- Xử lý cho Form Tạo Mới ---
    if (createCategoryModalElement) {
        const createCategoryForm = createCategoryModalElement.querySelector('#createCategoryForm');
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
}