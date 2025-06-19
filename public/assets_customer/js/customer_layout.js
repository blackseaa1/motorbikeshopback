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
        const modalElement = document.getElementById('appInfoModal');
        if (!modalElement) return;

        const modalTitle = modalElement.querySelector('.modal-title');
        const modalBody = modalElement.querySelector('.modal-body');
        const modalHeader = modalElement.querySelector('.modal-header');

        modalTitle.textContent = title;
        modalBody.innerHTML = message; // Dùng innerHTML để có thể render HTML nếu cần

        // Reset class
        modalHeader.className = 'modal-header';
        switch (type) {
            case 'success':
                modalHeader.classList.add('bg-success', 'text-white');
                break;
            case 'error':
                modalHeader.classList.add('bg-danger', 'text-white');
                break;
            case 'warning':
                modalHeader.classList.add('bg-warning', 'text-dark');
                break;
            default:
                modalHeader.classList.add('bg-primary', 'text-white');
        }

        const modalInstance = new bootstrap.Modal(modalElement);
        modalInstance.show();
    };


    /**
     * A.4. Thiết lập Form AJAX
     * Cấu hình một form để submit qua AJAX thay vì tải lại trang.
     * @param {string} formId - ID của form.
     * @param {function} successCallback - Hàm gọi lại khi thành công.
     * @param {function} errorCallback - Hàm gọi lại khi thất bại.
     */
    window.setupAjaxForm = (formId, successCallback, errorCallback) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            window.showAppLoader();

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        // Xóa các lỗi cũ
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                        // Hiển thị lỗi mới
                        for (const field in result.errors) {
                            const input = form.querySelector(`[name="${field}"]`);
                            const errorContainer = form.querySelector(`#${field}-error`);
                            if (input) input.classList.add('is-invalid');
                            if (errorContainer) errorContainer.textContent = result.errors[field][0];
                        }
                    }
                    // Throw một lỗi để catch block bên dưới xử lý và hiển thị modal
                    throw new Error(result.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                }

                // Nếu thành công, xóa các trạng thái lỗi
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                if (successCallback) {
                    successCallback(result, form);
                }
            } catch (error) {
                console.error(`Lỗi khi submit form ${formId}:`, error);
                window.showAppInfoModal(error.message, 'error', 'Lỗi Hệ Thống');
                if (errorCallback) errorCallback(error);
            } finally {
                window.hideAppLoader();
            }
        });
    };

    /* ===============================================================
     * B. HÀM ĐIỀU PHỐI (ORCHESTRATOR)
     =============================================================== */
    function runPageSpecificInitializers() {
        // Gọi hàm khởi tạo cho trang Đăng nhập/Đăng ký nếu tồn tại
        if (typeof window.initializeHomePage === 'function') {
            window.initializeHomePage();
        }
        if (typeof window.initializeAuthPages === 'function') {
            window.initializeAuthPages();
        }
        // Gọi hàm khởi tạo cho trang Tài khoản nếu tồn tại
        if (typeof window.initializeAccountPage === 'function') {
            window.initializeAccountPage();
        }

        // =============================================================
        //  THÊM LỆNH GỌI CHO TRANG DANH MỤC TẠI ĐÂY
        // =============================================================
        // Nếu hàm initializeCategoriesPage tồn tại (tức là file categories.js đã được tải),
        // thì gọi nó.
        if (typeof window.initializeCategoriesPage === 'function') {
            window.initializeCategoriesPage();
        }
        if (typeof window.initializeShopPage === 'function') {
            window.initializeShopPage();
        }

        // THÊM MỚI: Luôn gọi hàm xử lý giỏ hàng trên mọi trang
        if (typeof window.initializeCartHandler === 'function') {
            window.initializeCartHandler();
        }
    }

    /* ===============================================================
     * C. ĐIỂM BẮT ĐẦU THỰC THI
     =============================================================== */
    document.addEventListener("DOMContentLoaded", () => {
        window.showAppLoader();
        runPageSpecificInitializers();
        window.hideAppLoader();
    });

})();