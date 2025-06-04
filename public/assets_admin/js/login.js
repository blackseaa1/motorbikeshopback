/**
 * ===================================================================
 * login.js
 *
 * Xử lý logic cho trang đăng nhập admin, bao gồm cả AJAX request.
 * - Gửi yêu cầu đăng nhập.
 * - Hiển thị thông báo lỗi (ưu tiên Modal, fallback về Alert).
 * - Điều khiển lớp phủ tải (loading overlay).
 *
 * Cập nhật: [Ngày hiện tại] (Sử dụng global app loader)
 * ===================================================================
 */

(function () {
    'use strict';

    window.initializeLoginPage = function () {
        const loginForm = document.getElementById('loginForm');

        if (!loginForm) {
            // console.log("Login JS: Form đăng nhập không tìm thấy trên trang này.");
            return;
        }
        // console.log("Login JS: Khởi tạo trang đăng nhập.");

        const submitButton = loginForm.querySelector('button[type="submit"]');

        const errorModalElement = document.getElementById('loginErrorModal');
        let loginErrorModalInstance;
        if (errorModalElement && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
            try {
                loginErrorModalInstance = new bootstrap.Modal(errorModalElement);
            } catch (e) {
                console.error("Login JS: Không thể khởi tạo Bootstrap Modal cho loginErrorModal.", e);
                loginErrorModalInstance = null;
            }
        } else if (errorModalElement) {
            console.warn("Login JS: bootstrap.Modal không được định nghĩa. Modal lỗi sẽ không hoạt động.");
        }

        const loginErrorModalBody = document.getElementById('loginErrorModalBody');
        const errorAlertDiv = document.getElementById('loginErrorAlert');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const loginUrl = loginForm.dataset.action;

        // Tham chiếu đến loader cục bộ trên trang login (nếu có và là fallback)
        // Nếu admin_layout.js luôn được nạp, loader toàn cục sẽ được ưu tiên.
        const localLoadingOverlay = document.getElementById('loading-overlay');

        function showLoader() {
            if (typeof window.showAppLoader === 'function') {
                window.showAppLoader(); // Ưu tiên sử dụng loader toàn cục
            } else if (localLoadingOverlay) { // Fallback sử dụng loader cục bộ của trang login
                localLoadingOverlay.classList.add('active');
                console.warn("Login JS: hàm showAppLoader từ admin_layout.js không tồn tại, sử dụng loader cục bộ.");
            } else {
                console.warn("Login JS: hàm showAppLoader không tồn tại và không có loader cục bộ (#loading-overlay).");
            }
        }

        function hideLoader() {
            if (typeof window.hideAppLoader === 'function') {
                window.hideAppLoader(); // Ưu tiên sử dụng loader toàn cục
            } else if (localLoadingOverlay) { // Fallback sử dụng loader cục bộ
                localLoadingOverlay.classList.remove('active');
                console.warn("Login JS: hàm hideAppLoader từ admin_layout.js không tồn tại, sử dụng loader cục bộ.");
            } else {
                console.warn("Login JS: hàm hideAppLoader không tồn tại và không có loader cục bộ (#loading-overlay).");
            }
        }

        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (errorAlertDiv) {
                errorAlertDiv.classList.add('d-none');
            }

            submitButton.disabled = true;
            showLoader(); // Gọi hàm showLoader đã được cập nhật

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(loginUrl, {
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
                    window.location.href = result.redirect_url;
                    // Không gọi hideLoader() ở đây vì trang sẽ chuyển hướng
                    return;
                } else {
                    let errorMessage = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                    if (result.errors && result.errors.email && result.errors.email.length > 0) {
                        errorMessage = result.errors.email[0];
                    } else if (result.message) {
                        errorMessage = result.message;
                    }

                    if (loginErrorModalBody && loginErrorModalInstance) {
                        loginErrorModalBody.textContent = errorMessage;
                        loginErrorModalInstance.show();
                    } else if (errorAlertDiv) {
                        errorAlertDiv.textContent = errorMessage;
                        errorAlertDiv.classList.remove('d-none');
                    } else {
                        alert(errorMessage);
                    }
                }
            } catch (error) {
                console.error('Login request failed:', error);
                const failMessage = 'Không thể kết nối đến máy chủ hoặc có lỗi xảy ra. Vui lòng thử lại.';

                if (loginErrorModalBody && loginErrorModalInstance) {
                    loginErrorModalBody.textContent = failMessage;
                    loginErrorModalInstance.show();
                } else if (errorAlertDiv) {
                    errorAlertDiv.textContent = failMessage;
                    errorAlertDiv.classList.remove('d-none');
                } else {
                    alert(failMessage);
                }
            } finally {
                submitButton.disabled = false;
                hideLoader(); // Gọi hàm hideLoader đã được cập nhật
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initializeLoginPage);
    } else {
        window.initializeLoginPage();
    }

})();