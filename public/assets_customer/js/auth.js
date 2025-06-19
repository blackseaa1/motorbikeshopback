/**
 * ===================================================================
 * auth.js - PHIÊN BẢN HOÀN CHỈNH
 *
 * - Xử lý logic cho các trang Đăng nhập, Đăng ký.
 * - THÊM MỚI: Xử lý logic cho trang Bắt buộc đổi mật khẩu.
 * - Tích hợp các hàm kiểm tra độ mạnh mật khẩu.
 * - CẬP NHẬT: Xử lý đăng nhập qua AJAX với modal lỗi và loader.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Hàm kiểm tra độ mạnh mật khẩu real-time
     */
    function setupPasswordStrengthChecker() {
        const passwordInput = document.getElementById('password');
        const criteriaList = document.getElementById('password-strength-criteria');
        if (!passwordInput || !criteriaList) return;

        const criteriaItems = criteriaList.querySelectorAll('li');
        passwordInput.addEventListener('focus', () => {
            criteriaList.style.display = 'block';
        });
        passwordInput.addEventListener('keyup', () => {
            criteriaItems.forEach(item => {
                item.classList.toggle('valid', new RegExp(item.dataset.regex).test(passwordInput.value));
            });
        });
    }

    /**
     * Hàm kiểm tra mật khẩu xác nhận có khớp không
     */
    function setupPasswordConfirmationChecker() {
        const passwordInput = document.getElementById('password');
        const confirmationInput = document.getElementById('password_confirmation');
        if (!passwordInput || !confirmationInput) return;

        const errorElement = confirmationInput.parentElement.querySelector('.invalid-feedback');

        const validate = () => {
            const mismatch = confirmationInput.value.length > 0 && passwordInput.value !== confirmationInput.value;
            confirmationInput.classList.toggle('is-invalid', mismatch);
            if (errorElement) {
                errorElement.textContent = mismatch ? 'Mật khẩu xác nhận không khớp.' : '';
            }
        };

        confirmationInput.addEventListener('keyup', validate);
        passwordInput.addEventListener('keyup', validate);
    }

    /**
     * Hàm xử lý form AJAX chung
     */
    function setupAjaxForm(formId, successCallback) {
        const form = document.getElementById(formId);
        if (!form) return;

        const submitButton = form.querySelector('button[type="submit"]');
        const errorModalElement = document.getElementById('errorModal');
        let errorModalInstance;
        if (errorModalElement && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
            try {
                errorModalInstance = new bootstrap.Modal(errorModalElement);
            } catch (e) {
                console.error("Auth JS: Không thể khởi tạo Bootstrap Modal cho errorModal.", e);
                errorModalInstance = null;
            }
        } else if (errorModalElement) {
            console.warn("Auth JS: bootstrap.Modal không được định nghĩa. Modal lỗi sẽ không hoạt động.");
        }

        const errorModalBody = document.getElementById('errorMessage');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const actionUrl = form.dataset.action || form.action;

        function showLoader() {
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader();
            } else {
                console.warn("Auth JS: Hàm showAppLoader không tồn tại.");
            }
        }

        function hideLoader() {
            if (typeof window.hideAppLoader === 'function') {
                window.hideAppLoader();
            } else {
                console.warn("Auth JS: Hàm hideAppLoader không tồn tại.");
            }
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            submitButton.disabled = true;
            showLoader();

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    successCallback(result);
                } else {
                    let errorMessage = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                    if (result.errors && result.errors.email && result.errors.email.length > 0) {
                        errorMessage = result.errors.email[0];
                    } else if (result.message) {
                        errorMessage = result.message;
                    }

                    if (errorModalBody && errorModalInstance) {
                        errorModalBody.textContent = errorMessage;
                        errorModalInstance.show();
                    } else {
                        alert(errorMessage);
                    }
                }
            } catch (error) {
                console.error('Auth request failed:', error);
                const failMessage = 'Không thể kết nối đến máy chủ hoặc có lỗi xảy ra. Vui lòng thử lại.';

                if (errorModalBody && errorModalInstance) {
                    errorModalBody.textContent = failMessage;
                    errorModalInstance.show();
                } else {
                    alert(failMessage);
                }
            } finally {
                submitButton.disabled = false;
                hideLoader();
            }
        });
    }

    /**
     * Hàm khởi tạo chính, được gọi bởi `customer_layout.js`
     */
    window.initializeAuthPages = function () {
        // 1. Xử lý form đăng nhập
        if (document.getElementById('login-form')) {
            setupAjaxForm('login-form', (result) => {
                window.showAppInfoModal(result.message, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 1000);
            });
        }

        // 2. Xử lý form đăng ký
        if (document.getElementById('register-form')) {
            setupPasswordStrengthChecker();
            setupPasswordConfirmationChecker();
            setupAjaxForm('register-form', (result) => {
                window.showAppInfoModal(result.message, 'success', 'Hoàn tất');
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 1500);
            });
        }

        // 3. Xử lý form bắt buộc đổi mật khẩu
        if (document.getElementById('force-password-change-form')) {
            setupPasswordStrengthChecker();
            setupPasswordConfirmationChecker();
            setupAjaxForm('force-password-change-form', (result) => {
                window.showAppInfoModal(result.message, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 1500);
            });
        }
    };

})();