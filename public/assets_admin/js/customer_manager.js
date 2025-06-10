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
        window.showAppInfoModal("CSRF Token không hợp lệ. Vui lòng tải lại trang.", 'error', 'Lỗi Hệ thống');
        return;
    }

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
        // Đặt phương thức POST để khớp với route
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
            resetBtn.dataset.url = `/admin/userManagement/customers/${customer.id}/reset-password`;
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
        if (typeof window.setupAjaxForm !== 'function') {
            console.error('Hàm setupAjaxForm không tồn tại!');
            return;
        }
        const reloadPage = () => setTimeout(() => {
            if (typeof Turbo !== 'undefined') {
                Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                window.location.reload();
            }
        }, 1200);

        window.setupAjaxForm('createCustomerForm', 'createCustomerModal', reloadPage);
        window.setupAjaxForm('updateCustomerForm', 'updateCustomerModal', reloadPage);
        window.setupAjaxForm('deleteCustomerForm', 'confirmDeleteModal', reloadPage);
        window.setupAjaxForm('forceDeleteCustomerForm', 'confirmForceDeleteModal', reloadPage);
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
            }

            // Mở Modal Xóa Vĩnh Viễn
            else if (button.classList.contains('btn-force-delete-customer')) {
                e.preventDefault();
                const form = document.getElementById('forceDeleteCustomerForm');
                form.action = button.dataset.deleteUrl;
                document.getElementById('customerNameToForceDelete').textContent = button.dataset.name || 'Khách hàng này';
            }

            // Xử lý Khóa / Mở khóa
            else if (button.classList.contains('toggle-status-customer-btn')) {
                e.preventDefault();
                if (!button.dataset.url) {
                    console.error('Không có data-url trên nút toggle-status. Hành động bị hủy.');
                    return;
                }
                window.showAppLoader();
                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || "Có lỗi xảy ra.");
                    window.showAppInfoModal(result.message, 'success', 'Thành công');
                    setTimeout(() => location.reload(), 1200);
                } catch (error) {
                    console.error('Lỗi khi toggle trạng thái:', error);
                    window.showAppInfoModal(error.message || 'Có lỗi xảy ra.', 'error', 'Lỗi');
                } finally {
                    window.hideAppLoader();
                }
            }

            // Xử lý Reset Mật khẩu hoặc Khôi phục
            else if (button.classList.contains('btn-reset-password') || button.classList.contains('btn-restore-customer')) {
                e.preventDefault();
                const isRestore = button.classList.contains('btn-restore-customer');
                const itemName = button.dataset.name || 'Khách hàng này';
                const url = button.dataset.url;
                const confirmModalEl = document.getElementById('confirmActionModal');
                const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
                const confirmBtn = confirmModalEl.querySelector('#confirmActionButton');

                confirmModalEl.querySelector('#confirmActionModalLabel').textContent = isRestore ? 'Xác nhận Khôi phục' : 'Xác nhận Reset Mật khẩu';
                confirmModalEl.querySelector('#confirmActionMessage').innerHTML = isRestore
                    ? `Bạn có chắc muốn khôi phục tài khoản cho <strong>${itemName}</strong>?`
                    : `Bạn có chắc muốn reset mật khẩu cho <strong>${itemName}</strong> về "12345"?`;
                confirmBtn.className = `btn ${isRestore ? 'btn-success' : 'btn-warning'}`;
                confirmBtn.textContent = isRestore ? 'Đồng ý' : 'Xác nhận';

                confirmBtn.onclick = async () => {
                    window.showAppLoader();
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                        });
                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message || 'Có lỗi xảy ra.');
                        confirmModal.hide();
                        window.showAppInfoModal(result.message, 'success', 'Thành công');
                        setTimeout(() => location.reload(), 1200);
                    } catch (error) {
                        console.error(`Lỗi khi ${isRestore ? 'khôi phục' : 'reset mật khẩu'}:`, error);
                        window.showAppInfoModal(error.message || 'Có lỗi xảy ra.', 'error', 'Lỗi');
                    } finally {
                        window.hideAppLoader();
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
                editBtn.dataset.updateUrl = `/admin/userManagement/customers/${customer.id}`;
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