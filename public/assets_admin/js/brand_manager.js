/**
 * ===================================================================
 * brand_manager.js
 * Xử lý JavaScript cho trang quản lý Thương hiệu.
 * ===================================================================
 */
function initializeBrandsPage() {
    console.log("Khởi tạo JS cho trang Thương hiệu...");

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

    function setupLogoPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (input && preview) {
            input.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    preview.src = URL.createObjectURL(file);
                    preview.onload = () => URL.revokeObjectURL(preview.src);
                } else {
                    const defaultSrc = preview.dataset.defaultSrc || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview';
                    preview.src = defaultSrc;
                }
            });
            // Lưu trữ src mặc định để reset nếu cần
            if (!preview.dataset.defaultSrc && preview.src) {
                preview.dataset.defaultSrc = preview.src;
            }
        }
    }
    setupLogoPreview('brandLogoCreate', 'brandLogoPreviewCreate');
    setupLogoPreview('brandLogoUpdate', 'brandLogoPreviewUpdate');

    const updateBrandModalElement = document.getElementById('updateBrandModal');
    const viewBrandModalElement = document.getElementById('viewBrandModal');

    function populateAndUpdateBrandModal(triggerButton) {
        if (!updateBrandModalElement || !triggerButton) return;

        const nameInput = updateBrandModalElement.querySelector('#brandNameUpdate');
        const descriptionInput = updateBrandModalElement.querySelector('#brandDescriptionUpdate');
        const statusSelect = updateBrandModalElement.querySelector('#brandStatusUpdate');
        const logoPreview = updateBrandModalElement.querySelector('#brandLogoPreviewUpdate');
        const logoInput = updateBrandModalElement.querySelector('#brandLogoUpdate');
        const updateBrandForm = updateBrandModalElement.querySelector('#updateBrandForm');
        const submitButtonUpdate = updateBrandForm ? updateBrandForm.querySelector('button[type="submit"]') : null;

        const name = triggerButton.dataset.name;
        const description = triggerButton.dataset.description;
        const status = triggerButton.dataset.status;
        const currentLogoUrl = triggerButton.dataset.logoUrl;
        const updateUrl = triggerButton.dataset.updateUrl;

        if (updateBrandForm) updateBrandForm.action = updateUrl;
        if (nameInput) nameInput.value = name;
        if (descriptionInput) descriptionInput.value = description;
        if (statusSelect) statusSelect.value = status;
        if (logoPreview) {
            logoPreview.src = currentLogoUrl || 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=LOGO';
            logoPreview.dataset.defaultSrc = currentLogoUrl || 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=LOGO';
        }
        if (logoInput) logoInput.value = '';

        updateBrandForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        updateBrandForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        if (submitButtonUpdate) {
            submitButtonUpdate.disabled = false;
            submitButtonUpdate.innerHTML = 'Lưu thay đổi';
        }
    }

    if (updateBrandModalElement) {
        updateBrandModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            populateAndUpdateBrandModal(button);
        });

        const updateBrandForm = updateBrandModalElement.querySelector('#updateBrandForm');
        if (updateBrandForm) {
            const submitButtonUpdate = updateBrandForm.querySelector('button[type="submit"]');
            updateBrandForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (typeof window.showAppLoader === 'function') window.showAppLoader();
                if (submitButtonUpdate) {
                    submitButtonUpdate.disabled = true;
                    submitButtonUpdate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
                }

                const formData = new FormData(this);
                const actionUrl = this.action;

                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => {
                    if (el.id && el.id.endsWith('Error')) el.textContent = '';
                });

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
                                let fieldName = key;
                                if (key === 'logo_url') fieldName = 'logoUrl'; // Map key từ server sang ID
                                const errorField = document.getElementById(`brand${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}UpdateError`);
                                const inputField = document.getElementById(`brand${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}Update`);

                                if (inputField) inputField.classList.add('is-invalid');
                                if (errorField) {
                                    errorField.textContent = result.errors[key][0];
                                    errorField.style.display = 'block';
                                } else {
                                    showAppNotification('validation_error', 'Lỗi Dữ Liệu', `${key}: ${result.errors[key][0]}`);
                                }
                            });
                        } else {
                            showAppNotification('error', 'Lỗi Cập Nhật!', result.message || 'Không thể cập nhật thương hiệu.');
                        }
                        return;
                    }

                    if (result.success) {
                        const modalInstance = bootstrap.Modal.getInstance(updateBrandModalElement);
                        if (modalInstance) modalInstance.hide();
                        showAppNotification('success', 'Thành công!', result.message);
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        showAppNotification('error', 'Lỗi!', result.message || 'Cập nhật không thành công.');
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật thương hiệu:', error);
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

    if (viewBrandModalElement) {
        const brandIdView = viewBrandModalElement.querySelector('#brandIdView');
        const brandNameView = viewBrandModalElement.querySelector('#brandNameView');
        const brandDescriptionView = viewBrandModalElement.querySelector('#brandDescriptionView');
        const brandStatusViewText = viewBrandModalElement.querySelector('#brandStatusView'); // Sửa tên biến
        const brandLogoView = viewBrandModalElement.querySelector('#brandLogoView');
        const brandCreatedAtView = viewBrandModalElement.querySelector('#brandCreatedAtView');
        const brandUpdatedAtView = viewBrandModalElement.querySelector('#brandUpdatedAtView');
        const editBrandFromViewButton = viewBrandModalElement.querySelector('#editBrandFromViewButton');

        viewBrandModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const brandId = button.dataset.id;
            const name = button.dataset.name;
            const description = button.dataset.description;
            const statusValue = button.dataset.status;
            const logoUrl = button.dataset.logoUrl;
            const createdAt = button.dataset.createdAt;
            const updatedAt = button.dataset.updatedAt;
            const updateUrl = button.dataset.updateUrl;

            if (brandIdView) brandIdView.textContent = brandId || '-';
            if (brandNameView) brandNameView.textContent = name || '-';
            if (brandDescriptionView) brandDescriptionView.textContent = description || 'Không có mô tả';
            if (brandLogoView) brandLogoView.src = logoUrl || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
            if (brandCreatedAtView) brandCreatedAtView.textContent = createdAt || '-';
            if (brandUpdatedAtView) brandUpdatedAtView.textContent = updatedAt || '-';

            if (brandStatusViewText) {
                if (statusValue === 'active') {
                    brandStatusViewText.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
                } else if (statusValue === 'inactive') {
                    brandStatusViewText.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
                } else {
                    brandStatusViewText.textContent = statusValue || '-';
                }
            }

            if (editBrandFromViewButton) {
                editBrandFromViewButton.dataset.id = brandId;
                editBrandFromViewButton.dataset.name = name;
                editBrandFromViewButton.dataset.description = description;
                editBrandFromViewButton.dataset.status = statusValue;
                editBrandFromViewButton.dataset.logoUrl = logoUrl;
                editBrandFromViewButton.dataset.updateUrl = updateUrl;
            }
        });

        if (editBrandFromViewButton && updateBrandModalElement) {
            editBrandFromViewButton.addEventListener('click', function () {
                const viewModalInstance = bootstrap.Modal.getInstance(viewBrandModalElement);
                if (viewModalInstance) viewModalInstance.hide();

                populateAndUpdateBrandModal(this); // 'this' là editBrandFromViewButton

                const updateModal = bootstrap.Modal.getInstance(updateBrandModalElement) || new bootstrap.Modal(updateBrandModalElement);
                updateModal.show();
            });
        }
    }

    const deleteBrandModalElement = document.getElementById('deleteBrandModal');
    if (deleteBrandModalElement) {
        const deleteBrandForm = deleteBrandModalElement.querySelector('#deleteBrandForm');
        const brandNameSpan = deleteBrandModalElement.querySelector('#brandNameToDelete');
        const passwordInput = deleteBrandModalElement.querySelector('#brandDeletionPassword');
        const passwordErrorDiv = deleteBrandModalElement.querySelector('#brandDeletionPasswordError');
        const submitButtonDelete = deleteBrandForm ? deleteBrandForm.querySelector('button[type="submit"]') : null;

        deleteBrandModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const name = button.dataset.name;
            const deleteUrl = button.dataset.deleteUrl;

            if (deleteBrandForm) deleteBrandForm.action = deleteUrl;
            if (brandNameSpan) brandNameSpan.textContent = name;
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

        if (deleteBrandForm && submitButtonDelete) {
            deleteBrandForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const url = this.action;
                const formData = new FormData();
                formData.append('_method', 'DELETE');

                if (passwordInput && document.getElementById('brandDeletionPassword')) { // Check if password field exists in DOM
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
                        const modalInstance = bootstrap.Modal.getInstance(deleteBrandModalElement);
                        if (modalInstance) modalInstance.hide();
                        showAppNotification('success', 'Thành công!', result.message);
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        showAppNotification('error', 'Lỗi!', result.message || 'Không thể xóa thương hiệu.');
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa thương hiệu:', error);
                    showAppNotification('error', 'Lỗi Hệ Thống!', 'Có lỗi xảy ra trong quá trình xử lý.');
                } finally {
                    if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
                    submitButtonDelete.disabled = false;
                    submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
                }
            });
        }
    }

    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const brandId = this.dataset.id;
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
                    const row = document.getElementById(`brand-row-${brandId}`);
                    const statusCell = document.getElementById(`brand-status-${brandId}`);
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
                console.error('Lỗi khi thay đổi trạng thái thương hiệu:', error);
                showAppNotification('error', 'Lỗi Cập Nhật!', error.message);
            } finally {
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        });
    });

    const createBrandForm = document.getElementById('createBrandForm');
    if (createBrandForm) {
        const submitButtonCreate = createBrandForm.querySelector('button[type="submit"]');
        createBrandForm.addEventListener('submit', function () {
            if (submitButtonCreate) {
                submitButtonCreate.disabled = true;
                submitButtonCreate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
            }
        });
    }
}