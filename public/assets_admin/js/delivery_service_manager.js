/**
 * ===================================================================
 * delivery_service_manager.js
 *
 * Xử lý JavaScript cho trang quản lý Đơn vị Giao hàng.
 * - Sử dụng các hàm global: window.showAppLoader, window.hideAppLoader, window.showAppInfoModal
 * - Quản lý modals (Create, Update, View, Delete) và AJAX cho Delivery Services.
 * ===================================================================
 */
function initializeDeliveryServicesPage() {
    console.log("Khởi tạo JS cho trang Quản lý Đơn vị Giao hàng...");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const pageScope = '#adminDeliveryServicesPage '; // Scope để tránh xung đột nếu cần

    // --- HELPER FUNCTIONS ---

    // Hàm hiển thị lỗi validation inline từ AJAX response
    function displayValidationErrors(formElement, errors) {
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        Object.keys(errors).forEach(key => {
            const inputField = formElement.querySelector(`[name="${key}"]`);
            const formType = formElement.id.includes('create') ? 'Create' : 'Update';
            const errorDivId = `ds${key.charAt(0).toUpperCase() + key.slice(1).replace(/_([a-z])/g, g => g[1].toUpperCase())}${formType}Error`;
            const errorDiv = formElement.querySelector(`#${errorDivId}`);

            if (inputField && errorDiv) {
                inputField.classList.add('is-invalid');
                errorDiv.textContent = errors[key][0];
                errorDiv.style.display = 'block';
            } else if (key === 'deletion_password') {
                const passInput = formElement.querySelector('#dsDeletionPassword');
                const passErrorDiv = formElement.querySelector('#dsDeletionPasswordError');
                if (passInput && passErrorDiv) {
                    passInput.classList.add('is-invalid');
                    passErrorDiv.textContent = errors[key][0];
                    passErrorDiv.style.display = 'block';
                }
            } else {
                window.showAppInfoModal(errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                console.warn(`Không tìm thấy input hoặc error div cho trường: ${key}`);
            }
        });
    }

    // Hàm xử lý AJAX form submit chung
    function handleAjaxFormSubmit(formElement, modalElement, sectionTitle, successCallback, httpMethod = 'POST') {
        if (!formElement) return;

        formElement.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (typeof window.showAppLoader === 'function') window.showAppLoader();

            const submitButton = formElement.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.innerHTML : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...`;
            }

            // Xóa lỗi validation cũ (cho AJAX)
            formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            formElement.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });

            const formData = new FormData(formElement);
            // Với method PUT/PATCH/DELETE qua FormData, Laravel cần _method
            if (httpMethod.toUpperCase() !== 'POST') {
                formData.append('_method', httpMethod.toUpperCase());
            }

            try {
                const response = await fetch(formElement.action, {
                    method: 'POST', // Luôn là POST vì dùng _method cho FormData
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
                        window.showAppInfoModal('Vui lòng kiểm tra lại các trường dữ liệu.', 'validation_error', `Lỗi nhập liệu ${sectionTitle}`);
                    } else {
                        window.showAppInfoModal(result.message || `Đã có lỗi xảy ra khi ${httpMethod === 'POST' ? 'thêm' : (httpMethod === 'PUT' ? 'cập nhật' : 'xóa')} ${sectionTitle}.`, 'error', 'Lỗi!');
                    }
                    throw new Error(result.message || 'Server error');
                }

                if (successCallback && typeof successCallback === 'function') {
                    successCallback(result);
                } else {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) modalInstance.hide();
                    window.showAppInfoModal(result.message || `${httpMethod === 'POST' ? 'Thêm' : (httpMethod === 'PUT' ? 'Cập nhật' : 'Xóa')} ${sectionTitle} thành công!`, 'success', 'Thành công!');
                    setTimeout(() => window.location.reload(), 1200);
                }

            } catch (error) {
                console.error(`Lỗi khi submit form ${formElement.id}:`, error);
                if (response && response.status !== 422 && response.status !== 200) { // Tránh hiển thị modal lỗi chung nếu đã có lỗi validation
                    window.showAppInfoModal(error.message || `Không thể xử lý yêu cầu cho ${sectionTitle}. Vui lòng thử lại.`, 'error', 'Lỗi nghiêm trọng');
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        });
    }

    // --- CREATE DELIVERY SERVICE ---
    const createModalElement = document.getElementById('createDeliveryServiceModal');
    if (createModalElement) {
        const createForm = createModalElement.querySelector('#createDeliveryServiceForm');
        handleAjaxFormSubmit(createForm, createModalElement, "Đơn vị Giao hàng", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(createModalElement);
            if (modalInstance) modalInstance.hide();
            createForm.reset(); // Reset form sau khi thành công
            // Reset preview image
            const imgPreview = createModalElement.querySelector('#dsLogoPreviewCreate');
            if (imgPreview && imgPreview.dataset.defaultSrc) {
                imgPreview.src = imgPreview.dataset.defaultSrc;
            }
            window.showAppInfoModal(result.message || 'Thêm Đơn vị Giao hàng thành công!', 'success', 'Thành công!');
            setTimeout(() => window.location.reload(), 1200);
        }, 'POST');

        // Reset form và preview khi modal bị ẩn (do nhấn nút X hoặc Esc)
        createModalElement.addEventListener('hidden.bs.modal', function () {
            createForm.reset();
            createForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            createForm.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
            const imgPreview = createModalElement.querySelector('#dsLogoPreviewCreate');
            if (imgPreview && imgPreview.dataset.defaultSrc) {
                imgPreview.src = imgPreview.dataset.defaultSrc;
            }
        });
    }

    // --- UPDATE DELIVERY SERVICE ---
    const updateModalElement = document.getElementById('updateDeliveryServiceModal');
    if (updateModalElement) {
        const updateForm = updateModalElement.querySelector('#updateDeliveryServiceForm');

        updateModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            if (!button) return;

            const id = button.dataset.id;
            updateForm.action = button.dataset.updateUrl; // Lấy từ data-update-url của nút edit

            // Populate form fields
            updateForm.querySelector('#dsNameUpdate').value = button.dataset.name || '';
            // updateForm.querySelector('#dsShippingFeeUpdate').value = button.dataset.shippingFee || ''; // Loại bỏ dòng này
            updateForm.querySelector('#dsStatusUpdate').value = button.dataset.status || 'active';

            // Handle logo preview and existing logo URL
            const logoPreview = updateForm.querySelector('#dsLogoPreviewUpdate');
            const existingLogoInput = updateForm.querySelector('#dsExistingLogoUrl');
            const defaultLogoSrc = logoPreview.dataset.defaultSrc || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current';
            const logoUrl = button.dataset.logoUrl && button.dataset.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' ? button.dataset.logoUrl : defaultLogoSrc;
            logoPreview.src = logoUrl;
            existingLogoInput.value = logoUrl !== defaultLogoSrc ? button.dataset.logoUrl : '';

            // Clear old file input if any
            updateForm.querySelector('#dsLogoUpdate').value = '';

            // Xóa lỗi validation cũ
            updateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            updateForm.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
        });

        handleAjaxFormSubmit(updateForm, updateModalElement, "Đơn vị Giao hàng", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
            if (modalInstance) modalInstance.hide();
            window.showAppInfoModal(result.message || 'Cập nhật Đơn vị Giao hàng thành công!', 'success', 'Thành công!');
            // Cập nhật UI trực tiếp thay vì reload (ví dụ)
            const updatedRow = document.getElementById(`ds-row-${result.deliveryService.id}`);
            if (updatedRow) {
                updatedRow.cells[2].textContent = result.deliveryService.name; // Tên
                updatedRow.cells[3].textContent = 'Miễn phí'; // Cập nhật phí thành "Miễn phí"
                const statusCell = updatedRow.cells[4].querySelector('span');
                statusCell.textContent = result.deliveryService.status === 'active' ? 'Hoạt động' : 'Đã ẩn';
                statusCell.className = `badge ${result.deliveryService.status === 'active' ? 'bg-success' : 'bg-secondary'}`;

                // Cập nhật logo sử dụng logo_full_url từ server
                const logoImg = updatedRow.cells[1].querySelector('img');
                logoImg.src = result.deliveryService.logo_full_url || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';

                // Cập nhật data attributes trên các nút của dòng đó
                const viewBtn = updatedRow.querySelector('.btn-view-ds');
                const editBtn = updatedRow.querySelector('.btn-edit-ds');
                const toggleBtn = updatedRow.querySelector('.toggle-status-btn');

                if (viewBtn) {
                    viewBtn.dataset.name = result.deliveryService.name;
                    // viewBtn.dataset.shippingFee = result.deliveryService.shipping_fee; // Loại bỏ dòng này
                    viewBtn.dataset.status = result.deliveryService.status;
                    viewBtn.dataset.logoUrl = result.deliveryService.logo_full_url || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';
                    viewBtn.dataset.updatedAt = new Date(result.deliveryService.updated_at).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
                }
                if (editBtn) {
                    editBtn.dataset.name = result.deliveryService.name;
                    // editBtn.dataset.shippingFee = result.deliveryService.shipping_fee; // Loại bỏ dòng này
                    editBtn.dataset.status = result.deliveryService.status;
                    editBtn.dataset.logoUrl = result.deliveryService.logo_full_url || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';
                }
                if (toggleBtn) {
                    toggleBtn.title = result.deliveryService.status === 'active' ? 'Ẩn đơn vị này' : 'Hiển thị đơn vị này';
                    // Giữ nguyên icon bi-power, không cập nhật icon bi-eye-slash-fill/bi-eye-fill ở đây
                    // toggleBtn.innerHTML = `<i class="bi ${result.deliveryService.status === 'active' ? 'bi-eye-slash-fill' : 'bi-eye-fill'}"></i>`; // Dòng này đã được loại bỏ
                    updatedRow.classList.toggle('row-inactive', result.deliveryService.status !== 'active');

                    // Cập nhật class btn-danger/btn-outline-secondary cho toggleBtn
                    toggleBtn.classList.remove('btn-danger', 'btn-outline-secondary');
                    if (result.deliveryService.status === 'active') {
                        toggleBtn.classList.add('btn-outline-secondary');
                    } else {
                        toggleBtn.classList.add('btn-danger');
                    }
                }
            } else {
                setTimeout(() => window.location.reload(), 1200); // Fallback reload
            }
        }, 'PUT'); // Method là PUT, nhưng form sẽ gửi POST với _method=PUT
    }

    // --- VIEW DELIVERY SERVICE ---
    const viewModalElement = document.getElementById('viewDeliveryServiceModal');
    if (viewModalElement) {
        viewModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                console.error('Không tìm thấy nút kích hoạt modal xem chi tiết.');
                window.showAppInfoModal('Không thể tải dữ liệu đơn vị giao hàng.', 'error', 'Lỗi!');
                return;
            }

            console.log('Dữ liệu nút kích hoạt:', button.dataset); // Ghi log để kiểm tra dữ liệu

            // Điền dữ liệu vào modal
            viewModalElement.querySelector('#dsIdView').textContent = button.dataset.id || '-';
            viewModalElement.querySelector('#dsNameView').textContent = button.dataset.name || '-';
            viewModalElement.querySelector('#dsLogoView').src = (button.dataset.logoUrl && button.dataset.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A') ? button.dataset.logoUrl : 'https://placehold.co/150x75/EFEFEF/AAAAAA&text=LOGO';
            // const fee = parseFloat(button.dataset.shippingFee); // Loại bỏ dòng này
            viewModalElement.querySelector('#dsShippingFeeView').textContent = 'Miễn phí'; // Hiển thị "Miễn phí"

            const statusTextElement = viewModalElement.querySelector('#dsStatusViewText');
            if (button.dataset.status === 'active') {
                statusTextElement.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
            } else if (button.dataset.status === 'inactive') {
                statusTextElement.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
            } else {
                statusTextElement.textContent = button.dataset.status || '-';
                console.warn('Trạng thái không hợp lệ:', button.dataset.status);
            }

            viewModalElement.querySelector('#dsCreatedAtView').textContent = button.dataset.createdAt || '-';
            viewModalElement.querySelector('#dsUpdatedAtView').textContent = button.dataset.updatedAt || '-';

            // Setup "Edit from View" button
            const editFromViewButton = viewModalElement.querySelector('#editDeliveryServiceFromViewButton');
            if (editFromViewButton && updateModalElement) {
                editFromViewButton.addEventListener('click', function () {
                    const viewModalInstance = bootstrap.Modal.getInstance(viewModalElement);
                    if (viewModalInstance) viewModalInstance.hide();

                    // Create a temporary button-like object with the necessary dataset
                    const triggerData = { ...button.dataset }; // Clone dataset from the view button

                    // Get or create Bootstrap modal instance for update
                    const updateModalBsInstance = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);

                    // Manually populate the update form using the cloned data
                    const updateForm = updateModalElement.querySelector('#updateDeliveryServiceForm');
                    if (updateForm) {
                        updateForm.action = triggerData.updateUrl;
                        updateForm.querySelector('#dsNameUpdate').value = triggerData.name || '';
                        // updateForm.querySelector('#dsShippingFeeUpdate').value = triggerData.shippingFee || ''; // Loại bỏ dòng này
                        updateForm.querySelector('#dsStatusUpdate').value = triggerData.status || 'active';
                        const logoPreview = updateForm.querySelector('#dsLogoPreviewUpdate');
                        const existingLogoInput = updateForm.querySelector('#dsExistingLogoUrl');
                        const defaultLogoSrc = logoPreview.dataset.defaultSrc || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current';
                        const logoUrl = triggerData.logoUrl && triggerData.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' ? triggerData.logoUrl : defaultLogoSrc;
                        logoPreview.src = logoUrl;
                        existingLogoInput.value = logoUrl !== defaultLogoSrc ? triggerData.logoUrl : '';
                        updateForm.querySelector('#dsLogoUpdate').value = '';
                        updateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        updateForm.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
                    }
                    updateModalBsInstance.show();
                }, { once: true }); // Ensure the listener is added only once
            }
        });
    }

    // --- DELETE DELIVERY SERVICE ---
    const deleteModalElement = document.getElementById('deleteDeliveryServiceModal');
    if (deleteModalElement) {
        const deleteForm = deleteModalElement.querySelector('#deleteDeliveryServiceForm');
        const nameSpan = deleteModalElement.querySelector('#dsNameToDelete');
        const passwordInput = deleteModalElement.querySelector('#dsDeletionPassword');
        const passwordErrorDiv = deleteModalElement.querySelector('#dsDeletionPasswordError');

        deleteModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            deleteForm.action = button.dataset.deleteUrl;
            nameSpan.textContent = button.dataset.name || 'N/A';

            if (passwordInput) {
                passwordInput.value = '';
                passwordInput.classList.remove('is-invalid');
            }
            if (passwordErrorDiv) {
                passwordErrorDiv.textContent = '';
                passwordErrorDiv.style.display = 'none';
            }
            deleteForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            deleteForm.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
        });

        handleAjaxFormSubmit(deleteForm, deleteModalElement, "Đơn vị Giao hàng", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(deleteModalElement);
            if (modalInstance) modalInstance.hide();
            window.showAppInfoModal(result.message || 'Xóa Đơn vị Giao hàng thành công!', 'success', 'Thành công!');
            setTimeout(() => window.location.reload(), 1200);
        }, 'DELETE');
    }

    // --- TOGGLE STATUS ---
    document.querySelectorAll(pageScope + '.toggle-status-btn').forEach(button => {
        button.removeEventListener('click', handleToggleStatus); // Xóa listener cũ để tránh double-trigger
        button.addEventListener('click', handleToggleStatus); // Gắn listener mới
    });

    async function handleToggleStatus() {
        const deliveryServiceId = this.dataset.id;
        const url = this.dataset.url;
        const currentButton = this;

        if (typeof window.showAppLoader === 'function') window.showAppLoader();
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorResult.message || `Lỗi HTTP: ${response.status}`);
            }
            const result = await response.json();
            if (result.success) {
                window.showAppInfoModal(result.message || 'Cập nhật trạng thái thành công!', 'success', 'Thành công!');

                const row = document.getElementById(`ds-row-${deliveryServiceId}`);
                if (row) {
                    const statusCell = document.getElementById(`ds-status-${deliveryServiceId}`);
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                    }
                    currentButton.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                    currentButton.title = result.new_button_title;

                    if (result.new_status === 'inactive') {
                        row.classList.add('row-inactive');
                    } else {
                        row.classList.remove('row-inactive');
                    }

                    currentButton.classList.remove('btn-danger', 'btn-outline-secondary');
                    if (result.new_status === 'active') {
                        currentButton.classList.add('btn-outline-secondary');
                    } else {
                        currentButton.classList.add('btn-danger');
                    }

                    const viewButton = row.querySelector('.btn-view-ds');
                    const editButton = row.querySelector('.btn-edit-ds');
                    if (viewButton) {
                        viewButton.dataset.status = result.new_status;
                        // viewButton.dataset.shippingFee = result.deliveryService.shipping_fee; // Loại bỏ dòng này
                    }
                    if (editButton) {
                        editButton.dataset.status = result.new_status;
                        // editButton.dataset.shippingFee = result.deliveryService.shipping_fee; // Loại bỏ dòng này
                    }
                }
            } else {
                throw new Error(result.message || 'Có lỗi khi cập nhật trạng thái.');
            }
        } catch (error) {
            console.error('Lỗi khi thay đổi trạng thái đơn vị giao hàng:', error);
            window.showAppInfoModal(error.message || 'Lỗi cập nhật trạng thái.', 'error', 'Lỗi!');
        } finally {
            if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
        }
    }

} // End initializeDeliveryServicesPage