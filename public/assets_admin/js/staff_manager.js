/**
 * ===================================================================
 * staff_manager.js
 * Xử lý JavaScript cho trang quản lý Tài khoản Nhân viên.
 * Cập nhật lần cuối: 06/06/2025
 * - Tách hàm populateUpdateModal để tái sử dụng.
 * - Sửa lỗi chuyển đổi giữa modal Chi tiết và Cập nhật.
 * - Sử dụng Bootstrap Modal cho xác nhận Reset Mật khẩu.
 * - Cập nhật để sử dụng thông báo toast thay vì modal thông tin.
 * ===================================================================
 */

function initializeStaffsPage() {
    console.log("Khởi tạo JS cho trang Quản lý Nhân viên...");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const pageContainer = document.getElementById('adminStaffsPage');

    // Lấy các hàm helper toàn cục (giả định tồn tại từ admin_layout.js hoặc tương tự)
    // Bao gồm fallbacks tương tự blog_manager.js và customer_manager.js
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
    // Thay đổi showAppInfoModal để sử dụng showToast
    const showAppInfoModal = typeof window.showToast === 'function' ? (msg, type, title) => showToast(msg, type) : (msg, type, title) => alert(`[${title || ''}-${type}]: ${msg}`);


    /**
     * Hiển thị các lỗi validation từ server trên form.
     * @param {HTMLElement} formElement - Form cần hiển thị lỗi.
     * @param {object} errors - Object lỗi từ server.
     */
    function displayValidationErrors(formElement, errors) {
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        for (const field in errors) {
            const inputField = formElement.querySelector(`[name="${field}"]`);
            if (inputField) {
                inputField.classList.add('is-invalid');
                let errorDiv = inputField.nextElementSibling;
                if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                    const errorDivById = formElement.querySelector(`#${inputField.id}Error`);
                    if (errorDivById) errorDiv = errorDivById;
                }
                if (errorDiv) {
                    errorDiv.textContent = errors[field][0];
                    errorDiv.style.display = 'block';
                }
            } else {
                if (field === 'admin_password_confirm_delete') {
                    const passInput = document.getElementById('adminPasswordConfirmDelete');
                    const errorDiv = document.getElementById('adminPasswordConfirmDeleteError');
                    if (passInput) passInput.classList.add('is-invalid');
                    if (errorDiv) {
                        errorDiv.textContent = errors[field][0];
                        errorDiv.style.display = 'block';
                    }
                }
            }
        }
    }

    /**
     * Gắn sự kiện submit AJAX cho một form.
     * @param {string} formId - ID của form.
     * @param {string} modalId - ID của modal chứa form.
     * @param {string} sectionTitle - Tiêu đề cho thông báo lỗi.
     * @param {function} successCallback - Hàm callback khi thành công.
     */
    function setupAjaxForm(formId, modalId, sectionTitle, successCallback) {
        const formElement = document.getElementById(formId);
        const modalElement = document.getElementById(modalId);
        if (!formElement) return;

        formElement.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();

            const submitButton = formElement.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.innerHTML : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Đang xử lý...`;
            }

            const formData = new FormData(formElement);
            try {
                const response = await fetch(formElement.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        displayValidationErrors(formElement, result.errors);
                        showToast('Vui lòng kiểm tra lại dữ liệu nhập.', 'error'); // Thay thế showAppInfoModal
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra.', 'error'); // Thay thế showAppInfoModal
                    }
                    return;
                }

                if (modalElement) {
                    const bsModal = bootstrap.Modal.getInstance(modalElement);
                    if (bsModal) bsModal.hide();
                }

                showToast(result.message, 'success'); // Thay thế showAppInfoModal

                if (successCallback) {
                    successCallback(result);
                } else {
                    setTimeout(() => window.location.reload(), 1200);
                }

            } catch (error) {
                console.error(`Lỗi AJAX form ${formId}:`, error);
                showToast('Không thể kết nối đến máy chủ.', 'error'); // Thay thế showAppInfoModal
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
                hideAppLoader();
            }
        });
    }

    /**
     * Điền dữ liệu vào form Cập nhật từ một nút bấm.
     * @param {HTMLElement} button - Nút bấm chứa các thuộc tính data-*.
     */
    function populateUpdateModal(button) {
        const form = document.getElementById('updateStaffForm');
        if (!form || !button) return;

        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        const data = button.dataset;
        form.action = data.updateUrl;
        form.querySelector('#staffIdForUpdateModalInput').value = data.id;
        form.querySelector('#staffNameUpdate').value = data.name || '';
        form.querySelector('#staffEmailUpdate').value = data.email || '';
        // <<< SỬA ĐỔI Ở ĐÂY
        // Kiểm tra nếu dữ liệu là chuỗi "Chưa cập nhật" thì gán giá trị rỗng cho ô input
        const phoneValue = data.phone === 'Chưa cập nhật' ? '' : (data.phone || '');
        form.querySelector('#staffPhoneUpdate').value = phoneValue;
        // <<< KẾT THÚC SỬA ĐỔI
        form.querySelector('#staffRoleUpdate').value = data.role || '';
        form.querySelector('#staffStatusUpdate').value = data.status || 'active';
        form.querySelector('#staffAvatarPreviewUpdate').src = data.avatarUrl || 'https://placehold.co/100x100';

        const resetButtonInModal = document.getElementById('resetPasswordFromModalBtn');
        if (resetButtonInModal && pageContainer) {
            resetButtonInModal.dataset.url = data.resetPasswordUrl;
            resetButtonInModal.dataset.name = data.name;

            const isSuperAdmin = pageContainer.dataset.isSuperAdmin === 'true';
            const loggedInUserId = pageContainer.dataset.loggedInUserId;
            const targetIsSuperAdmin = data.isSuperAdmin === 'true';

            if (isSuperAdmin && loggedInUserId !== data.id && !targetIsSuperAdmin) {
                resetButtonInModal.style.display = 'inline-block';
            } else {
                resetButtonInModal.style.display = 'none';
            }
        }
    }


    // === Gắn sự kiện cho các Modal ===
    setupAjaxForm('createStaffForm', 'createAdminModal', "Tạo mới");
    setupAjaxForm('updateStaffForm', 'updateAdminModal', "Cập nhật", () => setTimeout(() => window.location.reload(), 1200));
    setupAjaxForm('deleteStaffForm', 'confirmDeleteStaffModal', "Xóa", () => setTimeout(() => window.location.reload(), 1200));

    const detailModalEl = document.getElementById('staffDetailModal');
    if (detailModalEl) {
        detailModalEl.addEventListener('show.bs.modal', (event) => {
            const viewButton = event.relatedTarget;
            const data = viewButton.dataset;

            // Điền thông tin vào các trường hiển thị trong modal chi tiết
            document.getElementById('detailAvatar').src = data.avatarUrl;
            document.getElementById('detailNameDisplay').textContent = data.name;
            document.getElementById('detailEmailDisplay').textContent = data.email;
            document.getElementById('detailRoleNameDisplay').className = `badge fs-6 ${data.roleBadgeClass}`;
            document.getElementById('detailRoleNameDisplay').textContent = data.roleName;
            document.getElementById('detailId').textContent = data.id;
            document.getElementById('detailName').textContent = data.name;
            document.getElementById('detailEmail').textContent = data.email;
            document.getElementById('detailPhone').textContent = data.phone;
            document.getElementById('detailRoleBadge').className = `badge ${data.roleBadgeClass}`;
            document.getElementById('detailRoleBadge').textContent = data.roleName;
            document.getElementById('detailStatusBadge').className = `badge ${data.statusBadgeClass}`;
            document.getElementById('detailStatusBadge').textContent = data.statusText;
            document.getElementById('detailCreatedAt').textContent = data.createdAt;
            document.getElementById('detailUpdatedAt').textContent = data.updatedAt;

            // Tìm nút "Chỉnh sửa" bằng ID và sao chép data sang
            const editButtonInModal = document.getElementById('editFromDetailBtn');
            if (editButtonInModal) {
                Object.keys(data).forEach(key => {
                    editButtonInModal.dataset[key] = data[key];
                });
            }
        });
    }


    // === Gắn sự kiện động cho các nút trên toàn trang ===
    document.body.addEventListener('click', async (event) => {
        const button = event.target.closest('button');
        if (!button) return;

        // Xử lý cho nút Sửa (chỉ nút ở ngoài bảng)
        if (button.classList.contains('btn-edit-staff')) {
            populateUpdateModal(button);
        }

        // Xử lý riêng cho nút Chỉnh sửa BÊN TRONG MODAL CHI TIẾT
        if (button.id === 'editFromDetailBtn') {
            event.preventDefault();
            populateUpdateModal(button);

            const detailModal = bootstrap.Modal.getInstance(detailModalEl);
            const updateModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('updateAdminModal'));

            if (detailModal) detailModal.hide();
            if (updateModal) updateModal.show();
        }

        // Handler chung cho các nút Reset Mật khẩu
        if (button.classList.contains('btn-reset-password')) {
            event.preventDefault();

            const url = button.dataset.url;
            const staffName = button.dataset.name;

            if (!url) return console.error("Không tìm thấy URL để đặt lại mật khẩu.");

            const confirmModalEl = document.getElementById('confirmActionModal');
            const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
            const confirmMessage = confirmModalEl.querySelector('#confirmActionMessage');
            const confirmButton = confirmModalEl.querySelector('#confirmActionButton');

            confirmModalEl.querySelector('#confirmActionModalLabel').textContent = 'Xác nhận Đặt lại Mật khẩu';
            confirmMessage.innerHTML = `Bạn có chắc chắn muốn đặt lại mật khẩu cho nhân viên <strong>${staffName}</strong> không?<br><small class="text-muted">Người dùng sẽ bị buộc phải đổi mật khẩu ở lần đăng nhập tiếp theo.</small>`;

            const resetActionHandler = async () => {
                showAppLoader();
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message);
                    showToast(result.message, 'success'); // Thay thế showAppInfoModal
                } catch (error) {
                    showToast(error.message, 'error'); // Thay thế showAppInfoModal
                } finally {
                    hideAppLoader();
                    confirmModal.hide();
                }
            };

            confirmButton.addEventListener('click', resetActionHandler, { once: true });

            confirmModalEl.addEventListener('hidden.bs.modal', () => {
                confirmButton.removeEventListener('click', resetActionHandler);
            }, { once: true });

            confirmModal.show();
        }

        // Handler cho nút Xóa
        if (button.classList.contains('btn-delete-staff')) {
            const form = document.getElementById('deleteStaffForm');
            if (form) {
                form.action = button.dataset.deleteUrl;
                document.getElementById('staffNameToDeleteInModal').textContent = button.dataset.name;
            }
        }

        // Handler cho nút Khóa/Mở khóa
        if (button.classList.contains('toggle-status-staff-btn')) {
            const url = button.dataset.url;
            showAppLoader();
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message);

                showToast(result.message, 'success'); // Thay thế showAppInfoModal
                const staffId = button.dataset.id;
                const row = document.getElementById(`staff-row-${staffId}`);

                row.querySelector('.status-cell').innerHTML = `<span class="badge ${result.status_badge_class}">${result.status_text}</span>`;
                button.className = `btn btn-sm btn-action toggle-status-staff-btn ${result.is_active ? 'btn-secondary action-lock' : 'btn-success action-unlock'}`;
                button.innerHTML = `<i class="bi ${result.button_icon}"></i>`;
                button.title = result.button_title;
                row.classList.toggle('row-inactive', !result.is_active);

                const editButton = row.querySelector('.btn-edit-staff');
                if (editButton) {
                    editButton.dataset.status = result.new_status_key;
                }

            } catch (error) {
                showToast(error.message, 'error'); // Thay thế showAppInfoModal
            } finally {
                hideAppLoader();
            }
        }
    });

    // Xử lý việc mở lại modal nếu có lỗi validation từ server
    if (pageContainer && pageContainer.dataset.reopenCreateModal === 'true') {
        const modal = new bootstrap.Modal(document.getElementById('createAdminModal'));
        modal.show();
    }
    if (pageContainer && pageContainer.dataset.reopenUpdateModalId) {
        const modal = new bootstrap.Modal(document.getElementById('updateAdminModal'));
        modal.show();
    }
}