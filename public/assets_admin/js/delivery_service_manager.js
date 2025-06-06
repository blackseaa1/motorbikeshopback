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
        // Xóa các lỗi cũ
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        Object.keys(errors).forEach(key => {
            // Input name trong form là 'logo_url', 'shipping_fee', nhưng ID của error div có thể là 'dsLogo_urlCreateError'
            const fieldName = key.replace(/_/g, ''); // Chuyển shipping_fee -> shippingfee
            const formType = formElement.id.includes('create') ? 'Create' : 'Update'; // createDeliveryServiceForm -> Create

            // Cố gắng tìm input field
            const inputField = formElement.querySelector(`[name="${key}"]`);
            let errorDiv = null;

            if (inputField) {
                inputField.classList.add('is-invalid');
                // Ưu tiên tìm error div theo ID quy ước: ds<FieldName><FormType>Error (vd: dsShipping_feeCreateError)
                // Hoặc ds<FieldName><FormType>Error (vd: dsLogo_urlUpdateError)
                const errorDivId = `ds${key.charAt(0).toUpperCase() + key.slice(1).replace(/_([a-z])/g, g => g[1].toUpperCase())}${formType}Error`;
                errorDiv = formElement.querySelector(`#${errorDivId}`);


                // Fallback: tìm div lỗi ngay sau input hoặc trong .mb-3 gần nhất
                if (!errorDiv) {
                    errorDiv = inputField.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        const parentGroup = inputField.closest('.mb-3');
                        if (parentGroup) errorDiv = parentGroup.querySelector('.invalid-feedback');
                    }
                }
            } else if (key === 'deletion_password') { // Trường hợp đặc biệt cho mật khẩu xóa
                errorDiv = formElement.querySelector('#dsDeletionPasswordError');
                const passInput = formElement.querySelector('#dsDeletionPassword');
                if (passInput) passInput.classList.add('is-invalid');
            }


            if (errorDiv) {
                errorDiv.textContent = errors[key][0];
                errorDiv.style.display = 'block';
            } else {
                // Nếu không tìm thấy chỗ hiển thị inline, dùng modal chung
                window.showAppInfoModal(errors[key][0], 'validation_error', 'Lỗi Dữ Liệu');
                console.warn(`Không tìm thấy error div cho trường: ${key} với ID dự kiến: ds${key.charAt(0).toUpperCase() + key.slice(1)}${formType}Error`);
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
            updateForm.querySelector('#dsShippingFeeUpdate').value = button.dataset.shippingFee || '';
            updateForm.querySelector('#dsStatusUpdate').value = button.dataset.status || 'active';

            const logoPreview = updateForm.querySelector('#dsLogoPreviewUpdate');
            const defaultLogoSrc = logoPreview.dataset.defaultSrc || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current';
            logoPreview.src = button.dataset.logoUrl && button.dataset.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' ? button.dataset.logoUrl : defaultLogoSrc;

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
                updatedRow.cells[3].textContent = parseFloat(result.deliveryService.shipping_fee).toLocaleString('vi-VN'); // Phí
                const statusCell = updatedRow.cells[4].querySelector('span');
                statusCell.textContent = result.deliveryService.status === 'active' ? 'Hoạt động' : 'Đã ẩn';
                statusCell.className = `badge ${result.deliveryService.status === 'active' ? 'bg-success' : 'bg-secondary'}`;

                // Cập nhật logo nếu có thay đổi (cần URL đầy đủ từ server)
                const logoImg = updatedRow.cells[1].querySelector('img');
                if (result.deliveryService.logo_url) { // Giả sử server trả về logo_url đầy đủ
                    logoImg.src = `/storage/${result.deliveryService.logo_url}`; // Điều chỉnh path nếu cần
                } else if (result.deliveryService.logo_url === null && logoImg.src !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A') {
                    // Nếu logo bị xóa và không có logo mới
                    logoImg.src = 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';
                }


                // Cập nhật data attributes trên các nút của dòng đó
                const viewBtn = updatedRow.querySelector('.btn-view-ds');
                const editBtn = updatedRow.querySelector('.btn-edit-ds');
                const toggleBtn = updatedRow.querySelector('.toggle-status-btn');

                if (viewBtn) {
                    viewBtn.dataset.name = result.deliveryService.name;
                    viewBtn.dataset.shippingFee = result.deliveryService.shipping_fee;
                    viewBtn.dataset.status = result.deliveryService.status;
                    if (result.deliveryService.logo_url) viewBtn.dataset.logoUrl = `/storage/${result.deliveryService.logo_url}`;
                    else viewBtn.dataset.logoUrl = 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';
                    viewBtn.dataset.updatedAt = new Date(result.deliveryService.updated_at).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
                }
                if (editBtn) {
                    editBtn.dataset.name = result.deliveryService.name;
                    editBtn.dataset.shippingFee = result.deliveryService.shipping_fee;
                    editBtn.dataset.status = result.deliveryService.status;
                    if (result.deliveryService.logo_url) editBtn.dataset.logoUrl = `/storage/${result.deliveryService.logo_url}`;
                    else editBtn.dataset.logoUrl = 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A';
                }
                if (toggleBtn) {
                    toggleBtn.title = result.deliveryService.status === 'active' ? 'Ẩn đơn vị này' : 'Hiển thị đơn vị này';
                    toggleBtn.innerHTML = `<i class="bi ${result.deliveryService.status === 'active' ? 'bi-eye-slash-fill' : 'bi-eye-fill'}"></i>`;
                    updatedRow.classList.toggle('row-inactive', result.deliveryService.status !== 'active');
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
            if (!button) return;

            viewModalElement.querySelector('#dsIdView').textContent = button.dataset.id || '-';
            viewModalElement.querySelector('#dsNameView').textContent = button.dataset.name || '-';
            viewModalElement.querySelector('#dsLogoView').src = (button.dataset.logoUrl && button.dataset.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A') ? button.dataset.logoUrl : 'https://placehold.co/150x75/EFEFEF/AAAAAA&text=LOGO';
            const fee = parseFloat(button.dataset.shippingFee);
            viewModalElement.querySelector('#dsShippingFeeView').textContent = isNaN(fee) ? '-' : fee.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

            const statusTextElement = viewModalElement.querySelector('#dsStatusViewText');
            if (button.dataset.status === 'active') {
                statusTextElement.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
            } else if (button.dataset.status === 'inactive') {
                statusTextElement.innerHTML = '<span class="badge bg-secondary">Đã ẩn</span>';
            } else {
                statusTextElement.textContent = button.dataset.status || '-';
            }
            viewModalElement.querySelector('#dsCreatedAtView').textContent = button.dataset.createdAt || '-';
            viewModalElement.querySelector('#dsUpdatedAtView').textContent = button.dataset.updatedAt || '-';

            // Setup "Edit from View" button
            const editFromViewButton = viewModalElement.querySelector('#editDeliveryServiceFromViewButton');
            if (editFromViewButton && updateModalElement) {
                // Copy all relevant data attributes from the view button to the edit button's dataset
                // This makes the edit button behave as if it was the original edit button in the table row
                Object.keys(button.dataset).forEach(key => {
                    editFromViewButton.dataset[key] = button.dataset[key];
                });

                editFromViewButton.onclick = function () { // Use .onclick for simplicity or manage event listeners carefully
                    const viewModalInstance = bootstrap.Modal.getInstance(viewModalElement);
                    if (viewModalInstance) viewModalInstance.hide();

                    // Directly trigger the show event for the update modal, passing this button as relatedTarget
                    const updateModalInstance = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);
                    // Manually call the event listener logic if direct event dispatch is tricky
                    const showModalEvent = new CustomEvent('show.bs.modal', { detail: { relatedTarget: this } });
                    updateModalElement.dispatchEvent(showModalEvent); // Might not work if BS uses internal listeners
                    // Safer: Just call the population logic directly or ensure the modal's 'show.bs.modal' listener fires
                    // For now, we rely on the 'show.bs.modal' event of updateDeliveryServiceModal
                    // which should pick up `this` as relatedTarget when we call .show()
                    const tempButtonForUpdate = document.createElement('button');
                    Object.assign(tempButtonForUpdate.dataset, this.dataset);
                    updateModalElement.settings = { relatedTarget: tempButtonForUpdate }; // Hacky, better to refactor population

                    updateModalInstance.show(); // This should trigger the 'show.bs.modal' on updateModalElement
                    // and its listener should use `this` (editFromViewButton) as `event.relatedTarget`
                };
            }
        });
        // Re-do the Edit from View button logic to be more robust
        const editFromViewButton = viewModalElement.querySelector('#editDeliveryServiceFromViewButton');
        if (editFromViewButton && updateModalElement) {
            editFromViewButton.addEventListener('click', function () {
                const viewModalInstance = bootstrap.Modal.getInstance(viewModalElement);
                if (viewModalInstance) viewModalInstance.hide();

                // Create a temporary button-like object with the necessary dataset
                // to pass to the update modal's show event or population logic.
                const triggerData = { ...this.dataset }; // Clone dataset from the "Edit from View" button

                // Get or create Bootstrap modal instance for update
                const updateModalBsInstance = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);

                // Manually populate the update form using the cloned data,
                // mimicking what the 'show.bs.modal' listener on updateModalElement would do.
                const updateForm = updateModalElement.querySelector('#updateDeliveryServiceForm');
                if (updateForm) {
                    updateForm.action = triggerData.updateUrl;
                    updateForm.querySelector('#dsNameUpdate').value = triggerData.name || '';
                    updateForm.querySelector('#dsShippingFeeUpdate').value = triggerData.shippingFee || '';
                    updateForm.querySelector('#dsStatusUpdate').value = triggerData.status || 'active';
                    const logoPreview = updateForm.querySelector('#dsLogoPreviewUpdate');
                    const defaultLogoSrc = logoPreview.dataset.defaultSrc || 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=Current';
                    logoPreview.src = triggerData.logoUrl && triggerData.logoUrl !== 'https://placehold.co/100x50/EFEFEF/AAAAAA&text=N/A' ? triggerData.logoUrl : defaultLogoSrc;
                    updateForm.querySelector('#dsLogoUpdate').value = '';
                    updateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    updateForm.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
                }
                updateModalBsInstance.show();
            });
        }
    }


    // --- DELETE DELIVERY SERVICE ---
    const deleteModalElement = document.getElementById('deleteDeliveryServiceModal');
    if (deleteModalElement) {
        const deleteForm = deleteModalElement.querySelector('#deleteDeliveryServiceForm');
        const nameSpan = deleteModalElement.querySelector('#dsNameToDelete');
        const passwordInput = deleteModalElement.querySelector('#dsDeletionPassword');
        const passwordErrorDiv = deleteModalElement.querySelector('#dsDeletionPasswordError');

        deleteModalElement.addEventListener('show.bs.modal', function (event) {
            // ... (logic populate modal delete không đổi)
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
            // BẮT ĐẦU PHẦN THAY ĐỔI
            const modalInstance = bootstrap.Modal.getInstance(deleteModalElement);
            if (modalInstance) modalInstance.hide();
            window.showAppInfoModal(result.message || 'Xóa Đơn vị Giao hàng thành công!', 'success', 'Thành công!');

            // Luôn tải lại trang để cập nhật STT và phân trang
            setTimeout(() => {
                window.location.reload();
            }, 1200); // Đợi 1.2 giây để người dùng đọc thông báo rồi mới reload
            // KẾT THÚC PHẦN THAY ĐỔI
        }, 'DELETE');
    }

    // --- TOGGLE STATUS ---
    document.querySelectorAll(pageScope + '.toggle-status-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const deliveryServiceId = this.dataset.id;
            const url = this.dataset.url;
            const currentButton = this;

            if (typeof window.showAppLoader === 'function') window.showAppLoader();
            try {
                const response = await fetch(url, {
                    method: 'POST', // Or PATCH
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json' // If sending JSON body
                    },
                    // body: JSON.stringify({}) // If your backend expects a JSON body
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `HTTP error ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    const row = document.getElementById(`ds-row-${deliveryServiceId}`);
                    const statusCell = document.getElementById(`ds-status-${deliveryServiceId}`);

                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                    }
                    currentButton.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                    currentButton.title = result.new_button_title;

                    if (row) {
                        row.classList.toggle('row-inactive', result.new_status === 'inactive');
                        // Update data-status on other buttons in the same row if needed
                        row.querySelectorAll('[data-status]').forEach(el => el.dataset.status = result.new_status);
                    }
                    // window.showAppInfoModal(result.message, 'success', 'Thành công'); // Optional: notify on toggle
                } else {
                    window.showAppInfoModal(result.message || 'Lỗi cập nhật trạng thái.', 'error', 'Lỗi!');
                }
            } catch (error) {
                console.error('Lỗi toggle status:', error);
                window.showAppInfoModal(error.message || 'Không thể cập nhật trạng thái.', 'error', 'Lỗi!');
            } finally {
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        });
    });

} // End initializeDeliveryServicesPage

// Chạy hàm khởi tạo khi DOM sẵn sàng
// Hàm này sẽ được gọi bởi runPageSpecificInitializers trong admin_layout.js
// nếu document.getElementById('adminDeliveryServicesPage') tồn tại.
// Nếu admin_layout.js không gọi, bạn có thể cần:
// document.addEventListener('DOMContentLoaded', initializeDeliveryServicesPage);
// Nhưng với cấu trúc hiện tại, admin_layout.js sẽ đảm nhiệm việc này.