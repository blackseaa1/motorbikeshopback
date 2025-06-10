/**
 * ===================================================================
 * promotion_manager.js (Phiên bản đầy đủ, đã tái cấu trúc)
 *
 * Xử lý toàn bộ logic JavaScript cho trang Quản lý Mã Khuyến Mãi,
 * bao gồm xem, tạo, sửa, xóa và bật/tắt trạng thái bằng AJAX.
 * ===================================================================
 */

// Bọc toàn bộ code trong sự kiện 'DOMContentLoaded' để đảm bảo DOM đã sẵn sàng.
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // -----------------------------------------------------------------------------
    // SECTION 1: KHAI BÁO BIẾN & LẤY ELEMENTS
    // -----------------------------------------------------------------------------

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        // Dừng thực thi nếu không có CSRF token để tránh lỗi không cần thiết
        return;
    }

    // Lấy các hàm helper toàn cục từ admin_layout.js
    const showAppLoader = window.showAppLoader;
    const hideAppLoader = window.hideAppLoader;
    const showAppInfoModal = window.showAppInfoModal;

    // Lấy các element chính của trang
    const tableBody = document.getElementById('promotions-table-body');
    const createModalEl = document.getElementById('createPromotionModal');
    const updateModalEl = document.getElementById('updatePromotionModal');
    const deleteModalEl = document.getElementById('deletePromotionModal');
    const viewModalEl = document.getElementById('viewPromotionModal');

    // Kiểm tra các element quan trọng trước khi chạy
    if (!tableBody || !createModalEl || !updateModalEl || !deleteModalEl || !viewModalEl) {
        console.warn('Cảnh báo: Một hoặc nhiều element modal/table quan trọng không tồn tại. Script có thể không hoạt động đầy đủ.');
        return;
    }

    // Khởi tạo các đối tượng Modal của Bootstrap một lần duy nhất
    const createModal = new bootstrap.Modal(createModalEl);
    const updateModal = new bootstrap.Modal(updateModalEl);
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    const viewModal = new bootstrap.Modal(viewModalEl);

    // -----------------------------------------------------------------------------
    // SECTION 2: HÀM TIỆN ÍCH (HELPER FUNCTIONS)
    // -----------------------------------------------------------------------------

    /**
     * Định dạng ngày giờ từ chuỗi ISO sang định dạng dd/mm/yyyy, hh:mm:ss.
     * @param {string} dateString - Chuỗi ngày giờ.
     * @returns {string} - Chuỗi đã định dạng hoặc chuỗi rỗng nếu đầu vào không hợp lệ.
     */
    function formatLocaleDateTime(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toLocaleString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Ho_Chi_Minh' // Explicitly set to UTC+7 (Vietnam timezone)
            });
        } catch (e) {
            console.error("Lỗi định dạng ngày:", e);
            return dateString;
        }
    }

    /**
     * Định dạng ngày giờ sang chuẩn cho input `datetime-local`.
     * @param {string} dateString - Chuỗi ngày giờ.
     * @returns {string} - Chuỗi định dạng yyyy-MM-ddTHH:mm.
     */
    function formatForInput(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            // Use Intl.DateTimeFormat to format the date in Asia/Ho_Chi_Minh timezone
            const formatter = new Intl.DateTimeFormat('sv-SE', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Ho_Chi_Minh'
            });
            const parts = formatter.formatToParts(date);
            const year = parts.find(p => p.type === 'year').value;
            const month = parts.find(p => p.type === 'month').value;
            const day = parts.find(p => p.type === 'day').value;
            const hour = parts.find(p => p.type === 'hour').value;
            const minute = parts.find(p => p.type === 'minute').value;
            return `${year}-${month}-${day}T${hour}:${minute}`;
        } catch (e) {
            console.error("Lỗi định dạng ngày cho input:", e);
            return '';
        }
    }

    /**
     * Xóa các lỗi validation đang hiển thị trên form.
     * @param {HTMLElement} formElement - Form cần xóa lỗi.
     */
    function clearValidationErrors(formElement) {
        if (!formElement) return;
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    // -----------------------------------------------------------------------------
    // SECTION 3: CÁC HÀM XỬ LÝ MODAL (HIỂN THỊ DỮ LIỆU)
    // -----------------------------------------------------------------------------

    async function handleShowViewModal(button) {
        showAppLoader();
        try {
            const response = await fetch(button.dataset.url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();

            // Điền dữ liệu vào modal xem chi tiết
            viewModalEl.querySelector('#viewModalPromoCodeStrong').textContent = data.code;
            viewModalEl.querySelector('#viewDetailPromoCode').textContent = data.code;
            viewModalEl.querySelector('#viewDetailPromoDescription').textContent = data.description || '(Không có mô tả)';
            viewModalEl.querySelector('#viewDetailPromoDiscount').textContent = data.formatted_discount;
            viewModalEl.querySelector('#viewDetailPromoStartDate').textContent = formatLocaleDateTime(data.start_date);
            viewModalEl.querySelector('#viewDetailPromoEndDate').textContent = formatLocaleDateTime(data.end_date);
            viewModalEl.querySelector('#viewDetailPromoMaxUses').textContent = data.max_uses || 'Không giới hạn';
            viewModalEl.querySelector('#viewDetailPromoUsesCount').textContent = data.uses_count;
            viewModalEl.querySelector('#viewDetailPromoStatusConfigText').innerHTML = `<span class="badge ${data.manual_status_badge_class}">${data.manual_status_text}</span>`;
            viewModalEl.querySelector('#viewDetailPromoStatusDisplayBadge').innerHTML = `<span class="badge ${data.effective_status_badge_class}">${data.effective_status_text}</span>`;

            // Gán data cho nút "Chỉnh sửa" bên trong modal xem
            const editBtn = viewModalEl.querySelector('#editFromViewBtn');
            editBtn.dataset.url = button.dataset.url;
            editBtn.dataset.updateUrl = button.closest('tr').querySelector('.edit-promotion-btn')?.dataset.updateUrl;

            viewModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu xem chi tiết:', error);
            showAppInfoModal('Không thể lấy dữ liệu chi tiết. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    async function handleShowUpdateModal(button) {
        showAppLoader();
        try {
            const response = await fetch(button.dataset.url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();

            const form = updateModalEl.querySelector('form');
            form.action = button.dataset.updateUrl; // Lấy URL update từ nút sửa

            // Điền dữ liệu vào form cập nhật
            form.querySelector('#promoCodeUpdate').value = data.code;
            form.querySelector('#promoDescriptionUpdate').value = data.description || '';
            form.querySelector('#promoDiscountUpdate').value = parseFloat(data.discount_percentage);
            form.querySelector('#promoStartDateUpdate').value = formatForInput(data.start_date);
            form.querySelector('#promoEndDateUpdate').value = formatForInput(data.end_date);
            form.querySelector('#promoMaxUsesUpdate').value = data.max_uses || '';
            form.querySelector('#promoStatusUpdate').value = data.status;

            updateModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            showAppInfoModal('Không thể lấy dữ liệu để sửa. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    function handleShowDeleteModal(button) {
        const form = deleteModalEl.querySelector('form');
        form.action = button.dataset.deleteUrl;
        deleteModalEl.querySelector('#deletePromotionCode').textContent = button.dataset.code;
        deleteModal.show();
    }

    // -----------------------------------------------------------------------------
    // SECTION 4: GẮN KẾT SỰ KIỆN (EVENT LISTENERS)
    // -----------------------------------------------------------------------------

    // Sử dụng Event Delegation trên body để xử lý tất cả các click
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;

        // Xử lý nút mở Modal Xem Chi Tiết
        if (button.classList.contains('view-promotion-btn')) {
            event.preventDefault();
            await handleShowViewModal(button);
        }

        // Xử lý nút mở Modal Chỉnh Sửa
        if (button.classList.contains('edit-promotion-btn')) {
            event.preventDefault();
            await handleShowUpdateModal(button);
        }

        // Xử lý nút mở Modal Xóa
        if (button.classList.contains('delete-promotion-btn')) {
            event.preventDefault();
            handleShowDeleteModal(button);
        }

        // Xử lý nút "Chỉnh sửa" từ bên trong Modal Xem
        if (button.id === 'editFromViewBtn') {
            viewModal.hide();
            // Đợi một chút để modal cũ đóng hẳn rồi mới mở modal mới
            setTimeout(() => handleShowUpdateModal(button), 200);
        }

        // Xử lý nút Bật/Tắt trạng thái
        if (button.classList.contains('toggle-status-btn')) {
            event.preventDefault();
            showAppLoader();
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || "Lỗi không xác định");

                showAppInfoModal(result.message, 'success');
                // Tải lại trang để cập nhật trạng thái một cách đồng bộ nhất
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                console.error('Lỗi khi bật/tắt trạng thái:', error);
                showAppInfoModal(error.message, 'error');
            } finally {
                hideAppLoader();
            }
        }
    });

    // Reset form khi modal được đóng để tránh giữ lại dữ liệu cũ
    createModalEl.addEventListener('hidden.bs.modal', () => {
        const form = createModalEl.querySelector('form');
        form.reset();
        clearValidationErrors(form);
    });
    updateModalEl.addEventListener('hidden.bs.modal', () => {
        const form = updateModalEl.querySelector('form');
        form.reset();
        clearValidationErrors(form);
    });

    // -----------------------------------------------------------------------------
    // SECTION 5: THIẾT LẬP FORM AJAX
    // -----------------------------------------------------------------------------
    // Sử dụng hàm setupAjaxForm toàn cục từ admin_layout.js
    if (typeof window.setupAjaxForm === 'function') {
        const reloadCallback = () => setTimeout(() => window.location.reload(), 1200);

        window.setupAjaxForm('createPromotionForm', 'createPromotionModal', reloadCallback);
        window.setupAjaxForm('updatePromotionForm', 'updatePromotionModal', reloadCallback);
        window.setupAjaxForm('deletePromotionForm', 'deletePromotionModal', reloadCallback);
    } else {
        console.error("Hàm setupAjaxForm() không tồn tại. Các form sẽ không hoạt động bằng AJAX.");
    }

    console.log("Module Quản lý Mã Khuyến Mãi đã được khởi tạo thành công.");
});