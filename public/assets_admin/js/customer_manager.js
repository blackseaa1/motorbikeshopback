/**
 * ===================================================================
 * customer_manager.js
 *
 * Xử lý logic cho trang Quản lý Khách hàng.
 * Bao gồm các bản vá lỗi cho modal chi tiết và hành động.
 * Cập nhật: 06/06/2025
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

    /**
     * Điền dữ liệu vào modal Cập nhật khi nhấn nút "Sửa".
     * @param {HTMLElement} button - Nút bấm kích hoạt.
     */
    function populateUpdateModal(button) {
        const form = document.getElementById('updateCustomerForm');
        if (!form || !button) {
            console.error("Không tìm thấy form updateCustomerForm hoặc button:", button);
            return;
        }

        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const customer = JSON.parse(button.dataset.customer || '{}');
        form.action = button.dataset.updateUrl;

        form.querySelector('[name="name"]').value = customer.name || '';
        form.querySelector('[name="email_display"]').value = customer.email || '';
        form.querySelector('[name="phone"]').value = customer.phone || '';
        form.querySelector('[name="status"]').value = customer.status || 'active';
        form.querySelector('#customerAvatarPreviewUpdate').src = customer.avatar_url || 'https://placehold.co/100x100';

        const resetBtnInModal = form.querySelector('.btn-reset-password');
        if (resetBtnInModal) {
            resetBtnInModal.dataset.url = `/admin/userManagement/customers/${customer.id}/reset-password`;
            resetBtnInModal.dataset.name = customer.name;
        }
    }

    // Gọi hàm setup form chung từ admin_layout.js
    window.setupAjaxForm('createCustomerForm', 'createCustomerModal');
    window.setupAjaxForm('updateCustomerForm', 'updateCustomerModal');
    window.setupAjaxForm('deleteCustomerForm', 'confirmDeleteModal');
    window.setupAjaxForm('forceDeleteCustomerForm', 'confirmForceDeleteModal');

    // Bộ lắng nghe sự kiện chính (event delegation)
    document.body.addEventListener('click', async (e) => {
        const button = e.target.closest('button.btn-action');
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
            document.getElementById('deleteCustomerForm').action = button.dataset.deleteUrl;
            document.getElementById('customerNameToDelete').textContent = button.dataset.name;
        }

        // Mở Modal Xóa Vĩnh Viễn
        else if (button.classList.contains('btn-force-delete-customer')) {
            e.preventDefault();
            document.getElementById('forceDeleteCustomerForm').action = button.dataset.deleteUrl;
            document.getElementById('customerNameToForceDelete').textContent = button.dataset.name;
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
                setTimeout(() => window.location.reload(), 1200);

            } catch (error) {
                console.error('Lỗi khi toggle trạng thái:', error);
                window.showAppInfoModal(error.message, 'error', 'Lỗi');
            } finally {
                window.hideAppLoader();
            }
        }

        // Xử lý Reset Mật khẩu & Khôi phục
        else if (button.classList.contains('btn-reset-password') || button.classList.contains('btn-restore-customer')) {
            e.preventDefault();
            const isRestore = button.classList.contains('btn-restore-customer');
            const itemName = button.dataset.name;
            const url = button.dataset.url;
            const confirmModalEl = document.getElementById('confirmActionModal');
            const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
            const confirmBtn = confirmModalEl.querySelector('#confirmActionButton');

            confirmModalEl.querySelector('#confirmActionModalLabel').textContent = isRestore ? 'Xác nhận Khôi phục' : 'Xác nhận Reset Mật khẩu';
            confirmModalEl.querySelector('#confirmActionMessage').innerHTML = isRestore ? `Bạn có chắc muốn khôi phục tài khoản cho <strong>${itemName}</strong>?` : `Bạn có chắc muốn reset mật khẩu cho <strong>${itemName}</strong> về "12345"?`;
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
                    if (!response.ok) throw new Error(result.message);
                    confirmModal.hide();
                    window.showAppInfoModal(result.message, 'success', 'Thành công');
                    setTimeout(() => window.location.reload(), 1200);
                } catch (error) {
                    window.showAppInfoModal(error.message, 'error', 'Lỗi');
                } finally {
                    window.hideAppLoader();
                }
            };
            confirmModal.show();
        }
    });

    // Xử lý Modal Chi tiết
    const detailModalEl = document.getElementById('customerDetailModal');
    if (detailModalEl) {
        detailModalEl.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const customer = JSON.parse(button.dataset.customer || '{}');

            // Lấy các nút hành động trong footer của modal
            const editBtn = detailModalEl.querySelector('#editFromDetailBtn');
            const toggleStatusBtn = detailModalEl.querySelector('.toggle-status-customer-btn');

            // Điền dữ liệu chung vào modal
            detailModalEl.querySelector('#detailAvatar').src = customer.avatar_url;
            detailModalEl.querySelector('#detailNameDisplay').textContent = customer.name;
            detailModalEl.querySelector('#detailEmailDisplay').textContent = customer.email;
            detailModalEl.querySelector('#detailId').textContent = customer.id;
            detailModalEl.querySelector('#detailName').textContent = customer.name;
            detailModalEl.querySelector('#detailEmail').textContent = customer.email;
            detailModalEl.querySelector('#detailPhone').textContent = customer.phone || 'Chưa cập nhật';
            const statusBadge = detailModalEl.querySelector('#detailStatusBadge');
            statusBadge.className = `badge ${customer.status_badge_class}`;
            statusBadge.textContent = customer.status_text;
            detailModalEl.querySelector('#detailPasswordRequired').textContent = customer.password_change_required ? 'Có' : 'Không';
            detailModalEl.querySelector('#detailCreatedAt').textContent = new Date(customer.created_at).toLocaleString('vi-VN');
            detailModalEl.querySelector('#detailUpdatedAt').textContent = new Date(customer.updated_at).toLocaleString('vi-VN');

            // Logic ẩn/hiện và cấu hình các nút hành động
            if (customer.deleted_at) {
                // Nếu ở trong thùng rác, ẩn tất cả các nút hành động
                editBtn.style.display = 'none';
                toggleStatusBtn.style.display = 'none';
            } else {
                // Nếu không ở trong thùng rác, hiển thị và cấu hình các nút

                // 1. Cấu hình và hiển thị nút "Chỉnh sửa"
                editBtn.style.display = 'inline-block';
                editBtn.dataset.customer = button.dataset.customer;
                editBtn.dataset.updateUrl = `/admin/userManagement/customers/${customer.id}`;

                // 2. Cấu hình và hiển thị nút "Khóa/Mở khóa"
                toggleStatusBtn.style.display = 'inline-block';
                const isActive = customer.is_active;
                toggleStatusBtn.dataset.url = `/admin/userManagement/customers/${customer.id}/toggle-status`;
                toggleStatusBtn.title = isActive ? 'Khóa tài khoản này' : 'Mở khóa tài khoản này';
                toggleStatusBtn.className = `btn btn-sm btn-action toggle-status-customer-btn ${isActive ? 'btn-secondary' : 'btn-success'}`;
                toggleStatusBtn.querySelector('i').className = `bi ${isActive ? 'bi-lock-fill' : 'bi-unlock-fill'}`;
            }
        });
    }
}

// Hàm này được gọi bởi runPageSpecificInitializers trong admin_layout.js,
// không cần gọi lại ở đây để tránh lặp.
// initializeCustomersPage();