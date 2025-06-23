/**
 * ===================================================================
 * brand_manager.js
 * Xử lý JavaScript cho trang quản lý Thương hiệu.
 * PHIÊN BẢN ĐÃ CẬP NHẬT: Sửa lỗi tên hàm initializeBrandPage và loại bỏ khởi tạo dư thừa.
 * Đã tích hợp Toast Notification từ promotion_manager.js.
 * ===================================================================
 */

let isInitialized = false; // Flag to prevent multiple initializations

document.addEventListener('DOMContentLoaded', function () {
    // Chỉ khởi tạo nếu chưa được khởi tạo
    if (isInitialized) {
        console.log("Brand Manager đã được khởi tạo trước đó. Bỏ qua khởi tạo lại.");
        return;
    }
    isInitialized = true;
    console.log("Khởi tạo JS cho trang Thương hiệu...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        // Dừng thực thi nếu không có CSRF token để tránh lỗi không cần thiết
        return;
    }

    // Lấy các hàm helper toàn cục (đặc biệt là showToast)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Fallback đơn giản nếu showToast không được định nghĩa toàn cục
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`); // Fallback sang alert nếu không có container
            return;
        }

        const toastEl = document.createElement('div');
        // Sử dụng các lớp Bootstrap Toast
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
        toastContainer.appendChild(toastEl); // Thêm vào container thay vì body

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    // Hàm hiển thị lỗi validation dưới trường input
    function displayValidationErrors(formElement, errors) {
        // Clear previous errors first
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        for (const fieldName in errors) {
            if (errors.hasOwnProperty(fieldName)) {
                // Find input field by name, or adjust for specific IDs
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);

                // Special handling for logo_url which maps to brandLogoUpdate/Create
                if (!inputField && fieldName === 'logo_url') {
                    // Try to find the specific logo input based on context
                    if (formElement.id === 'createBrandForm') {
                        inputField = formElement.querySelector('#brandLogoCreate');
                    } else if (formElement.id === 'updateBrandForm') {
                        inputField = formElement.querySelector('#brandLogoUpdate');
                    }
                }

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    // Find the corresponding error message element (assuming an adjacent .invalid-feedback)
                    const errorDiv = inputField.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0];
                    } else {
                        // Fallback if the standard structure is not found (e.g., custom error div)
                        const specificErrorDiv = formElement.querySelector(`#brand${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}Error`);
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


    // Hàm thiết lập preview logo
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
            if (!preview.dataset.defaultSrc && preview.src) {
                preview.dataset.defaultSrc = preview.src;
            }
        }
    }
    setupLogoPreview('brandLogoCreate', 'brandLogoPreviewCreate');
    setupLogoPreview('brandLogoUpdate', 'brandLogoPreviewUpdate');

    // Xử lý modal cập nhật thương hiệu
    const updateBrandModalElement = document.getElementById('updateBrandModal');
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

        // Clear validation errors on modal open
        updateBrandForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        updateBrandForm.querySelectorAll('.invalid-feedback').forEach(el => {
            if (el.id && el.id.endsWith('Error')) el.textContent = ''; // Clear specific error divs
            el.textContent = ''; // Clear generic invalid-feedback
        });

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
                showAppLoader();
                if (submitButtonUpdate) {
                    submitButtonUpdate.disabled = true;
                    submitButtonUpdate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
                }

                const formData = new FormData(this);
                // Laravel expects _method for PUT/PATCH via POST
                formData.append('_method', 'PUT'); // The actual method is handled by Laravel's _method field
                const actionUrl = this.action;

                // Clear previous validation errors
                updateBrandForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                updateBrandForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST', // Always POST for FormData, Laravel reads _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    });
                    const result = await response.json();

                    if (response.ok) { // Status code 2xx
                        const modalInstance = bootstrap.Modal.getInstance(updateBrandModalElement);
                        if (modalInstance) modalInstance.hide();
                        showToast(result.message, 'success');
                        if (result.redirect_url) {
                            setTimeout(() => window.location.href = result.redirect_url, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else if (response.status === 422 && result.errors) {
                        displayValidationErrors(updateBrandForm, result.errors);
                        showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                    } else { // Other errors
                        showToast(result.message || 'Cập nhật không thành công.', 'error');
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật thương hiệu:', error);
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

    // Xử lý modal xem chi tiết thương hiệu
    const viewBrandModalElement = document.getElementById('viewBrandModal');
    if (viewBrandModalElement) {
        const brandIdView = viewBrandModalElement.querySelector('#brandIdView');
        const brandNameView = viewBrandModalElement.querySelector('#brandNameView');
        const brandDescriptionView = viewBrandModalElement.querySelector('#brandDescriptionView');
        const brandStatusViewText = viewBrandModalElement.querySelector('#brandStatusView');
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
                // Delay to allow view modal to fully hide before showing update modal
                setTimeout(() => {
                    populateAndUpdateBrandModal(this);
                    const updateModal = bootstrap.Modal.getInstance(updateBrandModalElement) || new bootstrap.Modal(updateBrandModalElement);
                    updateModal.show();
                }, 200); // Small delay
            });
        }
    }

    // Xử lý modal xóa thương hiệu
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
                        method: 'POST', // Always POST for FormData with _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (response.ok) { // Status code 2xx
                        const modalInstance = bootstrap.Modal.getInstance(deleteBrandModalElement);
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
                    console.error('Lỗi khi xóa thương hiệu:', error);
                    showToast('Có lỗi xảy ra trong quá trình xử lý xóa.', 'error');
                } finally {
                    hideAppLoader();
                    submitButtonDelete.disabled = false;
                    submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
                }
            });
        }
    }

    // Xử lý nút toggle-status
    // Dùng event delegation thay vì querySelectorAll + forEach để xử lý các nút được thêm/xóa khỏi DOM
    // sau khi trang tải ban đầu (nếu có)
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('.toggle-status-btn');
        if (!button) return;

        event.preventDefault(); // Prevent default action

        const brandId = button.dataset.id;
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

                const row = document.getElementById(`brand-row-${brandId}`);
                if (row) {
                    const statusCell = document.getElementById(`brand-status-${brandId}`);
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
                        button.classList.add('btn-outline-secondary'); // Example: active uses outline-secondary
                    } else {
                        button.classList.add('btn-danger'); // Example: inactive uses danger
                    }

                    // Update dataset for view/edit buttons in the same row
                    const viewButton = row.querySelector('.btn-view-brand');
                    const editButton = row.querySelector('.btn-edit-brand');
                    if (viewButton) {
                        viewButton.dataset.status = result.new_status;
                    }
                    if (editButton) {
                        editButton.dataset.status = result.new_status;
                    }
                }
            } else {
                throw new Error(result.message || 'Có lỗi khi cập nhật trạng thái.');
            }
        } catch (error) {
            console.error('Lỗi khi thay đổi trạng thái thương hiệu:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    });


    // Xử lý form tạo thương hiệu
    const createBrandForm = document.getElementById('createBrandForm');
    const createBrandModalElement = document.getElementById('createBrandModal');
    const createBrandModalInstance = createBrandModalElement ? new bootstrap.Modal(createBrandModalElement) : null;

    if (createBrandForm) {
        createBrandForm.addEventListener('submit', async function (event) {
            event.preventDefault(); // Ngăn chặn submit mặc định
            showAppLoader();
            const submitButtonCreate = createBrandForm.querySelector('button[type="submit"]');
            if (submitButtonCreate) {
                submitButtonCreate.disabled = true;
                submitButtonCreate.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...';
            }

            const formData = new FormData(this);

            // Clear previous validation errors
            createBrandForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            createBrandForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();

                if (response.ok) { // Status code 2xx
                    if (createBrandModalInstance) createBrandModalInstance.hide();
                    showToast(result.message, 'success');
                    if (result.redirect_url) {
                        setTimeout(() => window.location.href = result.redirect_url, 1000);
                    } else {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else if (response.status === 422 && result.errors) {
                    displayValidationErrors(createBrandForm, result.errors);
                    showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else {
                    showToast(result.message || 'Tạo thương hiệu không thành công.', 'error');
                }
            } catch (error) {
                console.error('Lỗi khi tạo thương hiệu:', error);
                showToast('Có lỗi xảy ra trong quá trình xử lý tạo mới.', 'error');
            } finally {
                hideAppLoader();
                if (submitButtonCreate) {
                    submitButtonCreate.disabled = false;
                    submitButtonCreate.innerHTML = 'Tạo Thương Hiệu';
                }
            }
        });
    }

    // Xử lý đóng modal tạo để reset form
    if (createBrandModalElement) {
        createBrandModalElement.addEventListener('hidden.bs.modal', () => {
            const form = createBrandModalElement.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                const logoPreviewCreate = document.getElementById('brandLogoPreviewCreate');
                if (logoPreviewCreate) {
                    logoPreviewCreate.src = logoPreviewCreate.dataset.defaultSrc || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview';
                }
            }
        });
    }
    // Xử lý đóng modal cập nhật để reset form
    if (updateBrandModalElement) {
        updateBrandModalElement.addEventListener('hidden.bs.modal', () => {
            const form = updateBrandModalElement.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                const logoPreviewUpdate = document.getElementById('brandLogoPreviewUpdate');
                if (logoPreviewUpdate) {
                    logoPreviewUpdate.src = logoPreviewUpdate.dataset.defaultSrc || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                }
            }
        });
    }

    // Xử lý mở lại modal tạo thương hiệu nếu có lỗi validation sau khi reload trang
    // Điều này thường xảy ra khi Laravel flash lỗi vào session và reload trang.
    // Nếu bạn muốn xử lý hoàn toàn bằng AJAX mà không reload trang khi có lỗi,
    // thì phần này có thể không cần thiết hoặc cần logic khác.
    // Dựa trên brand_manager.js cũ, nó có `hasValidationErrors` và `formMarker`
    // => giả định đôi khi trang reload với lỗi.
    const urlParams = new URLSearchParams(window.location.search);
    const hasValidationErrors = urlParams.get('hasValidationErrors') === 'true';
    const formMarker = urlParams.get('formMarker');

    if (hasValidationErrors && formMarker === 'create_brand') {
        if (createBrandModalInstance) {
            createBrandModalInstance.show();
        }
    }

    console.log("Module Quản lý Thương hiệu đã được khởi tạo thành công.");
});