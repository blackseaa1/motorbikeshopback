// blackseaa1/motorbikeshop/motorbikeshop-0b35a37b31bf4b9b69dc80f5b881813a9422bec0/public/assets_customer/js/customer_layout.js
/**
 * ===================================================================
 * customer_layout.js - PHIÊN BẢN HOÀN CHỈNH
 *
 * Tệp JavaScript chính (Nhạc trưởng)
 * - Định nghĩa các hàm helper toàn cục (Global Helpers).
 * - Xử lý cả thông báo từ server (qua messages.blade.php) và
 * thông báo từ AJAX (qua setupAjaxForm).
 * - Điều phối việc gọi các hàm khởi tạo cho từng trang cụ thể.
 * ===================================================================
 */

(function () {
    'use strict';

    /* ===============================================================
     * A. CÁC HÀM TOÀN CỤC (GLOBAL HELPERS)
     =============================================================== */

    /**
     * A.1. Hiển thị Lớp phủ Tải
     * Điều khiển element #loading-overlay từ file loading.blade.php.
     */
    window.showAppLoader = () => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.add('active');
    };

    /**
     * A.2. Ẩn Lớp phủ Tải
     */
    window.hideAppLoader = () => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.remove('active');
    };

    /**
     * A.3. Hiển thị Modal Thông Báo Chung
     * Điều khiển Bootstrap Modal #appInfoModal trong file app.blade.php.
     * @param {string} message - Nội dung thông báo.
     * @param {string} type - 'success', 'error', 'warning', 'info'.
     * @param {string} title - Tiêu đề của modal.
     */
    window.showAppInfoModal = (message, type = 'info', title = 'Thông báo') => {
        const modalEl = document.getElementById('appInfoModal');
        if (!modalEl) {
            console.error('Modal #appInfoModal not found. Please ensure it exists in your app.blade.php.');
            return;
        }

        const modalTitle = modalEl.querySelector('.modal-title');
        const modalBody = modalEl.querySelector('.modal-body');
        const modalHeader = modalEl.querySelector('.modal-header');

        if (modalTitle) modalTitle.textContent = title;
        if (modalBody) modalBody.innerHTML = message; // innerHTML để cho phép HTML
        if (modalHeader) {
            // Xóa các class màu cũ
            modalHeader.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white', 'text-dark', 'bg-light');
            // Thêm class màu mới
            switch (type) {
                case 'success':
                    modalHeader.classList.add('bg-success', 'text-white');
                    break;
                case 'error':
                    modalHeader.classList.add('bg-danger', 'text-white');
                    break;
                case 'warning':
                    modalHeader.classList.add('bg-warning', 'text-dark'); // text-dark cho nền vàng
                    break;
                case 'info':
                    modalHeader.classList.add('bg-info', 'text-white');
                    break;
                case 'danger': // Added for confirm modal specifically
                    modalHeader.classList.add('bg-danger', 'text-white');
                    break;
                default:
                    modalHeader.classList.add('bg-light', 'text-dark');
            }
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    /**
     * A.4. Hiển thị Modal Xác Nhận Chung
     * Điều khiển Bootstrap Modal #appConfirmModal trong file app.blade.php.
     * @param {string} message - Nội dung thông báo xác nhận.
     * @param {string} title - Tiêu đề của modal.
     * @param {string} type - 'success', 'error', 'warning', 'info', 'danger' (để thay đổi màu header).
     * @param {Function} onConfirm - Hàm sẽ được gọi khi người dùng xác nhận.
     */
    window.showAppConfirmModal = (message, title = 'Xác nhận', type = 'info', onConfirm = () => { }) => {
        const modalEl = document.getElementById('appConfirmModal');
        if (!modalEl) {
            console.error('Modal #appConfirmModal not found. Please ensure it exists in your app.blade.php.');
            return;
        }

        const modalTitle = modalEl.querySelector('.modal-title');
        const modalBody = modalEl.querySelector('.modal-body');
        const modalHeader = modalEl.querySelector('.modal-header'); // Get modal header
        const confirmBtn = modalEl.querySelector('#appConfirmModalConfirmBtn'); // Nút xác nhận trong modal

        if (modalTitle) modalTitle.textContent = title;
        if (modalBody) modalBody.innerHTML = message;

        // Apply header styling based on type
        if (modalHeader) {
            modalHeader.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white', 'text-dark', 'bg-light');
            switch (type) {
                case 'success':
                    modalHeader.classList.add('bg-success', 'text-white');
                    break;
                case 'error': // Alias for danger in confirm modal
                case 'danger':
                    modalHeader.classList.add('bg-danger', 'text-white');
                    break;
                case 'warning':
                    modalHeader.classList.add('bg-warning', 'text-dark');
                    break;
                case 'info':
                    modalHeader.classList.add('bg-info', 'text-white');
                    break;
                default:
                    modalHeader.classList.add('bg-light', 'text-dark');
            }
        }

        // Remove old listener to prevent multiple calls
        const oldConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(oldConfirmBtn, confirmBtn);

        // Attach new listener
        oldConfirmBtn.addEventListener('click', () => {
            onConfirm(); // Call the passed confirmation function
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide(); // Hide modal after confirmation
            }
        });

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };


    /* ===============================================================
     * B. CÁC HÀM KHỞI TẠO CỤ THỂ CHO TỪNG TRANG
     =============================================================== */

    function runPageSpecificInitializers() {
        // Hàm này chạy các hàm khởi tạo cho từng trang cụ thể
        // Các hàm này cần được định nghĩa trong các tệp JS riêng của từng trang
        // và được gán vào window để có thể truy cập ở đây.

        if (typeof window.initializeHomePage === 'function') {
            window.initializeHomePage();
        }
        if (typeof window.initializeAccountPage === 'function') {
            window.initializeAccountPage();
        }
        if (typeof window.initializeCategoriesPage === 'function') {
            window.initializeCategoriesPage();
        }
        if (typeof window.initializeShopPage === 'function') {
            window.initializeShopPage();
        }
        if (typeof window.initializeProductDetailPage === 'function') {
            window.initializeProductDetailPage();
        }
        if (typeof window.initializeCartHandler === 'function') {
            window.initializeCartHandler();
        }
        if (typeof window.initializeAddressForms === 'function') {
            window.initializeAddressForms();
        }
        if (typeof window.initializeCheckoutPage === 'function') {
            window.initializeCheckoutPage();
        }
        // Thêm các hàm khởi tạo trang khác của bạn vào đây nếu có
    }

    /* ===============================================================
     * C. ĐIỂM BẮT ĐẦU THỰC THI
     =============================================================== */
    document.addEventListener("DOMContentLoaded", () => {
        // Hiển thị loader ngay khi DOM được tải
        window.showAppLoader();
        // Chạy các hàm khởi tạo cụ thể của trang
        runPageSpecificInitializers();
        // Ẩn loader sau khi tất cả các khởi tạo đã chạy
        window.hideAppLoader();
    });

})();