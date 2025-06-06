/**
 * ===================================================================
 * promotion_manager.js
 * Xử lý JavaScript cho trang quản lý Mã Khuyến Mãi.
 * ===================================================================
 */
function initializePromotionsPage() {
    console.log("Khởi tạo JS cho trang Quản lý Mã Khuyến Mãi...");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // --- HELPER FUNCTIONS (Reference from admin_layout.js or define locally if needed) ---
    const showAppLoader = window.showAppLoader || (() => console.log('showAppLoader missing'));
    const hideAppLoader = window.hideAppLoader || (() => console.log('hideAppLoader missing'));
    const showAppInfoModal = window.showAppInfoModal || ((msg, type, title) => alert(`${title}: ${msg}`));
    // Tự động chuyển Mã Code sang chữ hoa khi nhập liệu
    const promoCodeCreateInput = document.getElementById('promoCodeCreate');
    const promoCodeUpdateInput = document.getElementById('promoCodeUpdate');

    function autoUppercase(event) {
        const start = event.target.selectionStart;
        const end = event.target.selectionEnd;
        event.target.value = event.target.value.toUpperCase();
        event.target.setSelectionRange(start, end); // Giữ vị trí con trỏ
    }

    if (promoCodeCreateInput) {
        promoCodeCreateInput.addEventListener('input', autoUppercase);
    }
    if (promoCodeUpdateInput) {
        promoCodeUpdateInput.addEventListener('input', autoUppercase);
    }

    // Hàm hiển thị lỗi validation inline từ AJAX response
    function displayValidationErrors(formElement, errors, formType = 'Update') { // Mặc định formType là Update
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => {
            if (el.id && (el.id.endsWith('Error') || el.id.startsWith('promo') || el.id.startsWith('adminPassword'))) {
                el.textContent = '';
                el.style.display = 'none';
            }
        });

        Object.keys(errors).forEach(key => {
            const inputField = formElement.querySelector(`[name="${key}"]`);
            let errorDiv = null;
            let errorDivId = '';

            // Xây dựng ID cho div lỗi dựa trên key và formType (Create, Update)
            // Ví dụ: key 'code' và formType 'Update' -> promoCodeUpdateError
            // Ví dụ: key 'admin_password_delete_promotion' -> admin_password_delete_promotionError (cho modal delete)
            if (key.startsWith('admin_password_delete_')) { // Xử lý đặc biệt cho mật khẩu xóa
                errorDivId = `${key.replace(/_/g, '')}Error`;
            } else {
                // Chuyển snake_case (ví dụ: discount_percentage) sang CamelCase (Discount_percentage)
                const camelCaseKey = key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('');
                errorDivId = `promo${camelCaseKey}${formType}Error`;
            }

            errorDiv = formElement.querySelector(`#${errorDivId}`);

            if (inputField) {
                inputField.classList.add('is-invalid');
                // Nếu không tìm thấy errorDiv bằng ID quy ước, thử tìm theo cấu trúc DOM
                if (!errorDiv) {
                    errorDiv = inputField.parentElement.querySelector('.invalid-feedback');
                }
            }

            if (errorDiv) {
                errorDiv.textContent = errors[key][0];
                errorDiv.style.display = 'block';
            } else {
                // Nếu vẫn không tìm thấy, hiển thị qua modal chung
                showAppInfoModal(`${key}: ${errors[key][0]}`, 'validation_error', 'Lỗi Dữ Liệu');
                console.warn(`Không tìm thấy error div cho trường '${key}' với ID dự kiến '${errorDivId}' hoặc gần input.`);
            }
        });
    }


    // Hàm xử lý AJAX form submit chung
    function handleAjaxFormSubmit(formElement, modalElement, sectionTitle, successCallback, httpMethod = 'POST') {
        if (!formElement) return;

        formElement.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();

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

            let response; // Khai báo response ở ngoài try-catch để finally có thể truy cập
            try {
                response = await fetch(formElement.action, { // Gán giá trị cho response
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        const formType = formElement.id.includes('update') ? 'Update' : (formElement.id.includes('delete') ? 'Delete' : 'Create');
                        displayValidationErrors(formElement, result.errors, formType);
                        showAppInfoModal('Vui lòng kiểm tra lại các trường dữ liệu.', 'validation_error', `Lỗi nhập liệu ${sectionTitle}`);
                    } else {
                        showAppInfoModal(result.message || `Đã có lỗi xảy ra khi xử lý ${sectionTitle}.`, 'error', 'Lỗi!');
                    }
                    throw new Error(result.message || `Server error ${response.status}`);
                }

                if (successCallback && typeof successCallback === 'function') {
                    successCallback(result);
                } else {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) modalInstance.hide();
                    showAppInfoModal(result.message || `${sectionTitle} thành công!`, 'success', 'Thành công!');
                    setTimeout(() => window.location.reload(), 1200);
                }

            } catch (error) {
                console.error(`Lỗi khi submit form ${formElement.id}:`, error);
                // Kiểm tra response tồn tại và không phải lỗi validation đã xử lý
                if (response && response.status !== 422 && response.status !== 200) {
                    showAppInfoModal(error.message || `Không thể xử lý yêu cầu cho ${sectionTitle}. Vui lòng thử lại.`, 'error', 'Lỗi Hệ Thống');
                } else if (!response) { // Nếu response không tồn tại (lỗi mạng)
                    showAppInfoModal('Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại kết nối mạng.', 'error', 'Lỗi Mạng');
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
                hideAppLoader();
            }
        });
    }

    // --- CREATE PROMOTION ---
    // Form Create sử dụng submit truyền thống của Laravel, JS chỉ xử lý việc đóng modal sau khi submit
    // hoặc reset nếu modal bị đóng ngang.
    const createModalElement = document.getElementById('createPromotionModal');
    if (createModalElement) {
        const createForm = createModalElement.querySelector('#createPromotionForm');
        // Reset form khi modal bị ẩn (do nhấn nút X, Esc, hoặc nút "Đóng")
        createModalElement.addEventListener('hidden.bs.modal', function () {
            if (createForm) {
                createForm.reset();
                createForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                createForm.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }
        });
        // Logic disable nút submit khi form đang được submit (để tránh double click)
        if (createForm) {
            const submitButton = createForm.querySelector('button[type="submit"]');
            createForm.addEventListener('submit', function () {
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...`;
                }
                // Không preventDefault() để form được submit theo cách truyền thống
            });
        }
    }

    // --- UPDATE PROMOTION ---
    const updateModalElement = document.getElementById('updatePromotionModal');
    const updateFormElement = document.getElementById('updatePromotionForm');

    // Hàm này có thể được gọi từ script trong Blade nếu có lỗi validation server-side
    window.populateUpdateModalPromotion = function (promotionData) {
        if (!updateModalElement || !updateFormElement || !promotionData) return;

        updateFormElement.action = promotionData.update_url;
        updateFormElement.querySelector('#promoCodeUpdate').value = promotionData.code || '';
        updateFormElement.querySelector('#promoDescriptionUpdate').value = promotionData.description || '';
        updateFormElement.querySelector('#promoDiscountUpdate').value = promotionData.discount_percentage || '';
        updateFormElement.querySelector('#promoMaxUsesUpdate').value = promotionData.max_uses || '';
        updateFormElement.querySelector('#promoStartDateUpdate').value = promotionData.start_date_form || '';
        updateFormElement.querySelector('#promoEndDateUpdate').value = promotionData.end_date_form || '';
        updateFormElement.querySelector('#promoStatusUpdate').value = promotionData.manual_status || 'inactive';

        // Xóa lỗi validation cũ
        updateFormElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        updateFormElement.querySelectorAll('.invalid-feedback[id$="Error"]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
    }

    if (updateModalElement && updateFormElement) {
        updateModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Nút đã kích hoạt modal
            if (button && button.dataset.promotionData) {
                try {
                    const promotionData = JSON.parse(button.dataset.promotionData);
                    window.populateUpdateModalPromotion(promotionData);
                } catch (e) {
                    console.error("Lỗi parse JSON data cho modal update:", e);
                    showAppInfoModal('Không thể tải dữ liệu khuyến mãi để cập nhật.', 'error', 'Lỗi Dữ Liệu');
                }
            }
        });

        handleAjaxFormSubmit(updateFormElement, updateModalElement, "Mã Khuyến mãi", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(updateModalElement);
            if (modalInstance) modalInstance.hide();
            showAppInfoModal(result.message || 'Cập nhật Mã Khuyến mãi thành công!', 'success', 'Thành công!');
            setTimeout(() => window.location.reload(), 1200); // Reload để cập nhật bảng
        }, 'PUT');
    }


    // --- VIEW PROMOTION ---
    const viewModalElement = document.getElementById('viewPromotionModal');
    if (viewModalElement) {
        viewModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button || !button.dataset.promotionData) return;

            try {
                const data = JSON.parse(button.dataset.promotionData);
                viewModalElement.querySelector('#viewModalPromoCodeStrong').textContent = data.code || 'N/A';
                viewModalElement.querySelector('#viewDetailPromoCode').textContent = data.code || 'N/A';
                viewModalElement.querySelector('#viewDetailPromoDescription').textContent = data.description || 'Không có';
                viewModalElement.querySelector('#viewDetailPromoDiscount').textContent = data.discount_percentage + '%' || 'N/A';
                viewModalElement.querySelector('#viewDetailPromoStartDate').textContent = data.start_date_display || 'N/A';
                viewModalElement.querySelector('#viewDetailPromoEndDate').textContent = data.end_date_display || 'N/A';
                viewModalElement.querySelector('#viewDetailPromoMaxUses').textContent = data.max_uses ? data.max_uses : 'Không giới hạn';
                viewModalElement.querySelector('#viewDetailPromoUsesCount').textContent = data.uses_count || '0';
                viewModalElement.querySelector('#viewDetailPromoStatusConfigText').textContent = data.status_config_text || 'N/A';

                const statusDisplayBadgeSpan = viewModalElement.querySelector('#viewDetailPromoStatusDisplayBadgeSpan');
                statusDisplayBadgeSpan.className = `badge ${data.status_display_badge || 'bg-secondary'}`;
                statusDisplayBadgeSpan.textContent = data.status_display || 'N/A';

                // Setup "Edit from View" button
                const editFromViewButton = viewModalElement.querySelector('#editFromViewModalBtn');
                if (editFromViewButton) {
                    // Truyền toàn bộ data cho nút edit để nó có thể populate modal update
                    editFromViewButton.dataset.promotionDataForUpdate = JSON.stringify(data);
                }
            } catch (e) {
                console.error("Lỗi parse JSON data cho modal view:", e);
                showAppInfoModal('Không thể tải chi tiết khuyến mãi.', 'error', 'Lỗi Dữ Liệu');
            }
        });

        const editFromViewButton = viewModalElement.querySelector('#editFromViewModalBtn');
        if (editFromViewButton && updateModalElement) {
            editFromViewButton.addEventListener('click', function () {
                if (this.dataset.promotionDataForUpdate) {
                    try {
                        const promotionData = JSON.parse(this.dataset.promotionDataForUpdate);
                        window.populateUpdateModalPromotion(promotionData); // Gọi hàm populate
                        // Đóng modal view đã được xử lý bằng data-bs-dismiss="modal" trên nút
                        const updateModalInstance = bootstrap.Modal.getInstance(updateModalElement) || new bootstrap.Modal(updateModalElement);
                        updateModalInstance.show();
                    } catch (e) {
                        console.error("Lỗi parse JSON data khi edit từ view modal:", e);
                        showAppInfoModal('Không thể mở form cập nhật.', 'error', 'Lỗi');
                    }
                }
            });
        }
    }


    // --- DELETE PROMOTION ---
    const deleteModalElement = document.getElementById('deletePromotionModal');
    const deleteFormElement = document.getElementById('deletePromotionForm');

    // Hàm này có thể được gọi từ script trong Blade nếu có lỗi validation server-side
    window.populateDeleteModalPromotion = function (triggerButton) {
        if (!deleteModalElement || !deleteFormElement || !triggerButton) return;

        const code = triggerButton.dataset.code || 'N/A';
        const deleteUrl = triggerButton.dataset.deleteUrl;
        const usesCount = parseInt(triggerButton.dataset.usesCount || '0');

        deleteFormElement.action = deleteUrl;
        deleteModalElement.querySelector('#promoCodeNameToDelete').textContent = code;

        const warningElement = deleteModalElement.querySelector('#deleteWarningUsesCount');
        const usesCountDisplay = deleteModalElement.querySelector('#promoUsesCountDisplayForDelete');
        if (warningElement && usesCountDisplay) {
            if (usesCount > 0) {
                usesCountDisplay.textContent = usesCount;
                warningElement.style.display = 'block';
            } else {
                warningElement.style.display = 'none';
            }
        }
        // Reset password field và lỗi (nếu có)
        const passwordInput = deleteModalElement.querySelector('#adminPasswordDeletePromotion');
        const passwordErrorDiv = deleteModalElement.querySelector('#admin_password_delete_promotionError');
        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.classList.remove('is-invalid');
        }
        if (passwordErrorDiv) {
            passwordErrorDiv.textContent = '';
            passwordErrorDiv.style.display = 'none';
        }
    }

    if (deleteModalElement && deleteFormElement) {
        deleteModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button) {
                window.populateDeleteModalPromotion(button);
            }
        });

        handleAjaxFormSubmit(deleteFormElement, deleteModalElement, "Mã Khuyến mãi", (result) => {
            const modalInstance = bootstrap.Modal.getInstance(deleteModalElement);
            if (modalInstance) modalInstance.hide();
            showAppInfoModal(result.message || 'Xóa Mã Khuyến mãi thành công!', 'success', 'Thành công!');
            setTimeout(() => window.location.reload(), 1200);
        }, 'DELETE');
    }

    // --- TOGGLE STATUS ---
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const promotionId = this.dataset.id;
            const url = this.dataset.url;
            const currentButton = this;

            showAppLoader();
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || `Lỗi HTTP ${response.status}`);
                }

                if (result.success) {
                    // Cập nhật ô Trạng thái Cài đặt
                    const statusConfigCell = document.getElementById(`promotion-status-config-${promotionId}`);
                    if (statusConfigCell) {
                        statusConfigCell.innerHTML = `<span class="badge ${result.config_status_badge_class}">${result.config_status_text}</span>`;
                    }
                    // Cập nhật ô Trạng thái Hiện tại (Hiệu lực)
                    const statusDisplayCell = document.getElementById(`promotion-status-display-${promotionId}`);
                    if (statusDisplayCell) {
                        statusDisplayCell.innerHTML = `<span class="badge ${result.effective_badge_class}">${result.effective_display_text}</span>`;
                    }

                    // Cập nhật nút toggle
                    currentButton.innerHTML = `<i class="bi ${result.button_icon_class}"></i>`;
                    currentButton.title = result.button_title;

                    // Cập nhật class cho row nếu cần (dựa trên manual_status)
                    const row = document.getElementById(`promotion-row-${promotionId}`);
                    if (row) {
                        if (result.new_manual_status === 'inactive') {
                            row.classList.add('row-inactive');
                        } else {
                            row.classList.remove('row-inactive');
                        }
                    }
                    // Disable nút nếu mã hết hạn và đang được bật
                    if (result.is_disabled_by_date !== undefined) {
                        currentButton.disabled = result.is_disabled_by_date;
                    }

                    // showAppInfoModal(result.message, 'success', 'Thành công'); // Có thể bỏ qua để tránh quá nhiều thông báo
                } else {
                    showAppInfoModal(result.message || 'Lỗi cập nhật trạng thái.', 'error', 'Lỗi!');
                }
            } catch (error) {
                console.error('Lỗi toggle status promotion:', error);
                showAppInfoModal(error.message || 'Không thể cập nhật trạng thái.', 'error', 'Lỗi!');
            } finally {
                hideAppLoader();
            }
        });
    });

} // End initializePromotionsPage

// Script để Blade gọi nếu có lỗi validation server-side (đã được khai báo global)
// window.populateUpdateModalPromotion = function(promotionData) { ... }
// window.populateDeleteModalPromotion = function(triggerButton) { ... }