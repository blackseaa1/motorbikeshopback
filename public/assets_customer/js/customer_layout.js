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
     */
    window.showAppInfoModal = function (message, type = 'info', title = 'Thông báo') {
        const modalElement = document.getElementById('appInfoModal');
        if (!modalElement) {
            console.error('Không tìm thấy #appInfoModal. Quay về dùng alert().');
            alert(`${title}: ${message.html || message}`);
            return;
        }

        const modalTitleElement = modalElement.querySelector('#appInfoModalLabel');
        const modalBodyElement = modalElement.querySelector('#appInfoModalBody');
        const modalHeaderElement = modalElement.querySelector('.modal-header');

        if (modalTitleElement) modalTitleElement.textContent = title;

        if (modalBodyElement) {
            if (typeof message === 'object' && message.html) {
                modalBodyElement.innerHTML = message.html;
            } else {
                modalBodyElement.textContent = String(message);
            }
        }

        if (modalHeaderElement) {
            modalHeaderElement.className = 'modal-header text-white'; // Reset classes
            let headerBgClass = 'bg-primary';
            switch (type) {
                case 'success':
                    headerBgClass = 'bg-success';
                    break;
                case 'error':
                case 'validation_error':
                    headerBgClass = 'bg-danger';
                    break;
                case 'warning':
                    headerBgClass = 'bg-warning';
                    modalHeaderElement.classList.remove('text-white');
                    break;
                case 'info':
                default:
                    headerBgClass = 'bg-info';
                    modalHeaderElement.classList.remove('text-white');
                    break;
            }
            modalHeaderElement.classList.add(headerBgClass);
        }

        try {
            const appModalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            appModalInstance.show();
        } catch (e) {
            console.error("Lỗi khi hiển thị Bootstrap modal: ", e);
            alert(`${title}: ${message.html || message}`);
        }
    };

    /**
     * A.4. Hiển thị lỗi validation trên một form AJAX.
     */
    window.displayValidationErrors = function (form, errors) {
        if (!form || !errors) return;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        for (const field in errors) {
            const inputField = form.querySelector(`[name="${field}"]`);
            if (inputField) {
                inputField.classList.add('is-invalid');
                const errorDiv = inputField.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.textContent = errors[field][0];
                }
            }
        }
    };

    /**
     * A.5. Gắn sự kiện submit AJAX cho một form.
     */
    window.setupAjaxForm = function (formId, successCallback = null, errorCallback = null) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            window.showAppLoader();
            try {
                const formData = new FormData(form);
                const httpMethod = (formData.get('_method') || form.method).toUpperCase();

                const response = await fetch(form.action, {
                    method: httpMethod,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        window.displayValidationErrors(form, result.errors);
                        window.showAppInfoModal('Vui lòng kiểm tra lại các trường dữ liệu.', 'validation_error', 'Lỗi Nhập Liệu');
                    } else {
                        throw new Error(result.message || 'Có lỗi không xác định xảy ra.');
                    }
                    if (errorCallback) errorCallback(result);
                    return;
                }

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
        if (typeof window.initializeAuthPages === 'function') {
            window.initializeAuthPages();
        }
        // Gọi hàm khởi tạo cho trang Tài khoản nếu tồn tại
        if (typeof window.initializeAccountPage === 'function') {
            window.initializeAccountPage();
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