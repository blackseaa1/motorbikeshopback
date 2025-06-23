/**
 * ===================================================================
 * customer_manager.js
 *
 * Xử lý logic cho trang Quản lý Khách hàng.
 * Sửa lỗi TypeError tại dòng 195 (toggleStatusBtn null) và đảm bảo nút "Chỉnh sửa" hoạt động.
 * Đặt phương thức form cập nhật thành POST để khớp với route.
 * Cập nhật: 06/10/2025
 * ===================================================================
 */

function initializeCustomersPage() {
    console.log("Khởi tạo JS cho trang Quản lý Khách Hàng...");
    const pageContainer = document.getElementById('adminCustomersPage');
    if (!pageContainer) {
        console.error("Không tìm thấy #adminCustomersPage. Hàm initializeCustomersPage bị hủy.");
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error("CSRF Token không tìm thấy!");
        // Sử dụng showToast thay cho showAppInfoModal nếu không có token
        showToast("CSRF Token không hợp lệ. Vui lòng tải lại trang.", 'error');
        return;
    }

    // Lấy các hàm helper toàn cục (giả định tồn tại từ admin_layout.js hoặc tương tự)
    // Bao gồm fallbacks tương tự blog_manager.js
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showAppInfoModal = typeof window.showAppInfoModal === 'function' ? window.showAppInfoModal : (msg, type) => alert(`${type}: ${msg}`);
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

    // --- HÀM HỖ TRỢ ---

    /**
     * Điền dữ liệu vào modal Cập nhật.
     * @param {HTMLElement} button - Nút kích hoạt (btn-edit-customer hoặc editFromDetailBtn).
     */
    function populateUpdateModal(button) {
        const form = document.getElementById('updateCustomerForm');
        if (!form || !button) {
            console.error("Không tìm thấy form updateCustomerForm hoặc button:", button);
            return;
        }

        // Reset form và xóa lỗi
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const customer = JSON.parse(button.dataset.customer || '{}');
        form.action = button.dataset.updateUrl;
        // Phương thức thực tế (PUT) được xác định bởi @method trong Blade
        form.setAttribute('method', 'POST');

        // Điền dữ liệu
        form.querySelector('[name="name"]').value = customer.name || '';
        form.querySelector('[name="email_display"]').value = customer.email || '';
        form.querySelector('[name="phone"]').value = customer.phone || '';
        form.querySelector('[name="status"]').value = customer.status || 'active';
        form.querySelector('#customerAvatarPreviewUpdate').src = customer.avatar_url || 'https://placehold.co/100x100';

        // Cấu hình nút reset mật khẩu
        const resetBtn = form.querySelector('.btn-reset-password');
        if (resetBtn) {
            resetBtn.dataset.url = `/admin/user-management/customers/${customer.id}/reset-password`;
            resetBtn.dataset.name = customer.name || 'Khách hàng này';
        }
    }

    /**
     * Thiết lập preview ảnh đại diện.
     * @param {HTMLInputElement} input - Input file.
     * @param {HTMLImageElement} preview - Element hiển thị ảnh.
     */
    function setupAvatarPreview(input, preview) {
        if (!input || !preview) return;
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => preview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    /**
     * Thiết lập reset form khi modal đóng.
     */
    function setupModalResets() {
        const createModal = document.getElementById('createCustomerModal');
        const updateModal = document.getElementById('updateCustomerModal');
        if (createModal) {
            createModal.addEventListener('hidden.bs.modal', () => {
                const form = document.getElementById('createCustomerForm');
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.getElementById('customerAvatarPreviewCreate').src = 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH';
            });
        }
        if (updateModal) {
            updateModal.addEventListener('hidden.bs.modal', () => {
                const form = document.getElementById('updateCustomerForm');
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.getElementById('customerAvatarPreviewUpdate').src = 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=KH';
            });
        }
    }

    // --- KHỞI TẠO AJAX FORM ---

    /**
     * Thiết lập các form AJAX.
     */
    function setupAjaxForms() {
        // Hàm setupAjaxForm này sẽ được chỉnh sửa để sử dụng showToast bên trong.
        // Cần đảm bảo setupAjaxForm gốc (nếu có từ admin_layout.js) cũng được cập nhật,
        // hoặc định nghĩa lại nó ở đây nếu nó không được chia sẻ đúng cách.
        // Để giữ tính nhất quán, tôi sẽ giả định `window.setupAjaxForm` đã tồn tại
        // và bạn sẽ cập nhật nó để sử dụng `showToast` thay vì `showAppInfoModal`.

        // Nếu bạn muốn hàm setupAjaxForm ở đây sử dụng showToast, bạn có thể định nghĩa lại nó:
        const setupAjaxForm = (formId, modalId, successCallback, method = 'POST') => {
            const form = document.getElementById(formId);
            const modalEl = document.getElementById(modalId);
            const modalInstance = modalEl ? (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)) : null;

            if (!form) {
                console.error(`Không thể thiết lập AJAX form: Form ID "${formId}" không tồn tại.`);
                return;
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                showAppLoader();

                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                const formData = new FormData(form);

                if (method === 'PUT' || method === 'DELETE') {
                    formData.append('_method', method);
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showToast(result.message, 'success');
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        successCallback(result); // Pass the whole result object
                    } else if (response.status === 422) {
                        // Display validation errors
                        for (const fieldName in result.errors) {
                            if (result.errors.hasOwnProperty(fieldName)) {
                                let inputField = form.querySelector(`[name="${fieldName}"]`);
                                if (inputField) {
                                    inputField.classList.add('is-invalid');
                                    const errorDiv = inputField.nextElementSibling;
                                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                        errorDiv.textContent = result.errors[fieldName][0];
                                    }
                                }
                            }
                        }
                        showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                    } else {
                        showToast(result.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.', 'error');
                        console.error('AJAX Error:', result);
                    }
                } catch (error) {
                    console.error('Fetch Error:', error);
                    showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
                } finally {
                    hideAppLoader();
                }
            });
        };


        const reloadPage = () => setTimeout(() => {
            if (typeof Turbo !== 'undefined') {
                Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                window.location.reload();
            }
        }, 1200);

        setupAjaxForm('createCustomerForm', 'createCustomerModal', reloadPage);
        setupAjaxForm('updateCustomerForm', 'updateCustomerModal', reloadPage, 'PUT'); // Thêm method PUT
        setupAjaxForm('deleteCustomerForm', 'confirmDeleteModal', reloadPage, 'DELETE');
        setupAjaxForm('forceDeleteCustomerForm', 'confirmForceDeleteModal', reloadPage, 'DELETE');
    }

    // --- XỬ LÝ SỰ KIỆN ---

    /**
     * Thiết lập các event listener.
     */
    function setupEventListeners() {
        // Preview ảnh đại diện
        setupAvatarPreview(
            document.querySelector('#createCustomerForm input[name="img"]'),
            document.getElementById('customerAvatarPreviewCreate')
        );
        setupAvatarPreview(
            document.querySelector('#updateCustomerForm input[name="img"]'),
            document.getElementById('customerAvatarPreviewUpdate')
        );

        // Event delegation cho các nút hành động
        document.body.addEventListener('click', async (e) => {
            const button = e.target.closest('.btn-action, #editFromDetailBtn, .btn-reset-password');
            if (!button) return;

            // Mở Modal Sửa
            if (button.classList.contains('btn-edit-customer') || button.id === 'editFromDetailBtn') {
                e.preventDefault();
                populateUpdateModal(button);
                if (button.id === 'editFromDetailBtn') {
                    bootstrap.Modal.getInstance(document.getElementById('customerDetailModal'))?.hide();
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('updateCustomerModal')).show();
            }

            // Mở Modal Xóa Mềm
            else if (button.classList.contains('btn-delete-customer')) {
                e.preventDefault();
                const form = document.getElementById('deleteCustomerForm');
                form.action = button.dataset.deleteUrl;
                document.getElementById('customerNameToDelete').textContent = button.dataset.name || 'Khách hàng này';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal')).show(); // Show modal
            }

            // Mở Modal Xóa Vĩnh Viễn
            else if (button.classList.contains('btn-force-delete-customer')) {
                e.preventDefault();
                const form = document.getElementById('forceDeleteCustomerForm');
                form.action = button.dataset.deleteUrl;
                document.getElementById('customerNameToForceDelete').textContent = button.dataset.name || 'Khách hàng này';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmForceDeleteModal')).show(); // Show modal
            }

            // Xử lý Khóa / Mở khóa
            else if (button.classList.contains('toggle-status-customer-btn')) {
                e.preventDefault();
                if (!button.dataset.url) {
                    console.error('Không có data-url trên nút toggle-status. Hành động bị hủy.');
                    showToast('Lỗi: Không tìm thấy URL để thay đổi trạng thái.', 'error');
                    return;
                }
                showAppLoader(); // Sử dụng showAppLoader
                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || "Có lỗi xảy ra.");
                    showToast(result.message, 'success'); // Sử dụng showToast
                    setTimeout(() => location.reload(), 1200);
                } catch (error) {
                    console.error('Lỗi khi toggle trạng thái:', error);
                    showToast(error.message || 'Có lỗi xảy ra.', 'error'); // Sử dụng showToast
                } finally {
                    hideAppLoader(); // Sử dụng hideAppLoader
                }
            }

            // Xử lý Reset Mật khẩu hoặc Khôi phục
            else if (button.classList.contains('btn-reset-password') || button.classList.contains('btn-restore-customer')) {
                e.preventDefault();
                const isRestore = button.classList.contains('btn-restore-customer');
                const itemName = button.dataset.name || 'Khách hàng này';
                const url = button.dataset.url;
                const confirmModalEl = document.getElementById('confirmActionModal');
                // Check if confirmActionModal is not null before proceeding
                if (!confirmModalEl) {
                    console.error("Không tìm thấy #confirmActionModal. Không thể hiển thị xác nhận.");
                    showToast("Lỗi hệ thống: Không thể hiển thị hộp thoại xác nhận.", 'error');
                    return;
                }
                const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
                const confirmBtn = confirmModalEl.querySelector('#confirmActionButton');

                confirmModalEl.querySelector('#confirmActionModalLabel').textContent = isRestore ? 'Xác nhận Khôi phục' : 'Xác nhận Reset Mật khẩu';
                confirmModalEl.querySelector('#confirmActionMessage').innerHTML = isRestore
                    ? `Bạn có chắc muốn khôi phục tài khoản cho <strong>${itemName}</strong>?`
                    : `Bạn có chắc muốn reset mật khẩu cho <strong>${itemName}</strong> về "12345"?`;
                confirmBtn.className = `btn ${isRestore ? 'btn-success' : 'btn-warning'}`;
                confirmBtn.textContent = isRestore ? 'Đồng ý' : 'Xác nhận';

                confirmBtn.onclick = async () => {
                    showAppLoader(); // Sử dụng showAppLoader
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                        });
                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message || 'Có lỗi xảy ra.');
                        confirmModal.hide();
                        showToast(result.message, 'success'); // Sử dụng showToast
                        setTimeout(() => location.reload(), 1200);
                    } catch (error) {
                        console.error(`Lỗi khi ${isRestore ? 'khôi phục' : 'reset mật khẩu'}:`, error);
                        showToast(error.message || 'Có lỗi xảy ra.', 'error'); // Sử dụng showToast
                    } finally {
                        hideAppLoader(); // Sử dụng hideAppLoader
                    }
                };
                confirmModal.show();
            }
        });
    }

    // --- XỬ LÝ MODAL CHI TIẾT ---

    /**
     * Thiết lập modal Chi tiết Khách hàng.
     */
    function setupDetailModal() {
        const detailModalEl = document.getElementById('customerDetailModal');
        if (!detailModalEl) return;

        detailModalEl.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const customer = JSON.parse(button.dataset.customer || '{}');

            // Lấy nút chỉnh sửa
            const editBtn = detailModalEl.querySelector('#editFromDetailBtn');

            // Điền dữ liệu
            detailModalEl.querySelector('#detailAvatar').src = customer.avatar_url || 'https://placehold.co/100x100';
            detailModalEl.querySelector('#detailNameDisplay').textContent = customer.name || 'Chưa có tên';
            detailModalEl.querySelector('#detailEmailDisplay').textContent = customer.email || 'Chưa có email';
            detailModalEl.querySelector('#detailId').textContent = customer.id || 'N/A';
            detailModalEl.querySelector('#detailName').textContent = customer.name || 'Chưa có tên';
            detailModalEl.querySelector('#detailEmail').textContent = customer.email || 'Chưa có email';
            detailModalEl.querySelector('#detailPhone').textContent = customer.phone || 'Chưa cập nhật';
            const statusBadge = detailModalEl.querySelector('#detailStatusBadge');
            statusBadge.className = `badge ${customer.status_badge_class || 'bg-secondary'}`;
            statusBadge.textContent = customer.status_text || 'Không xác định';
            detailModalEl.querySelector('#detailPasswordRequired').textContent = customer.password_change_required ? 'Có' : 'Không';
            detailModalEl.querySelector('#detailCreatedAt').textContent = customer.created_at
                ? new Date(customer.created_at).toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' })
                : 'N/A';
            detailModalEl.querySelector('#detailUpdatedAt').textContent = customer.updated_at
                ? new Date(customer.updated_at).toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' })
                : 'N/A';

            // Cấu hình nút chỉnh sửa
            if (!customer.deleted_at) {
                editBtn.style.display = 'inline-block';
                editBtn.dataset.customer = button.dataset.customer;
                // Lấy URL đúng trực tiếp từ nút đã click để mở modal
                editBtn.dataset.updateUrl = button.dataset.updateUrl;
            } else {
                editBtn.style.display = 'none';
            }
        });
    }

    // --- KHỞI TẠO ---

    setupModalResets();
    setupAjaxForms();
    setupEventListeners();
    setupDetailModal();

    console.log("JS cho trang Quản lý Khách hàng đã được khởi tạo thành công!");
}

// Hàm này được gọi bởi runPageSpecificInitializers trong admin_layout.js,
// không cần gọi lại ở đây để tránh lặp.