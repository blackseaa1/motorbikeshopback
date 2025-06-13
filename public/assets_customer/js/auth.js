/**
 * ===================================================================
 * auth.js
 *
 * Xử lý logic cho các trang Đăng nhập và Đăng ký.
 * Được gọi bởi "nhạc trưởng" customer_layout.js.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Khởi tạo các form trên trang xác thực (login, register).
     * Hàm này được `customer_layout.js` gọi thông qua cơ chế @push.
     */
    window.initializeAuthPages = function () {
        // 1. Thiết lập cho form đăng nhập
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            window.setupAjaxForm('login-form', (result, form) => {
                window.showAppInfoModal(result.message, 'success', 'Thành công');
                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    }
                }, 1000); // Chờ 1 giây để người dùng đọc thông báo
            });
        }

        // 2. Thiết lập cho form đăng ký
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            window.setupAjaxForm('register-form', (result, form) => {
                window.showAppInfoModal(result.message, 'success', 'Hoàn tất');
                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    }
                }, 1500); // Chờ 1.5 giây để người dùng đọc thông báo
            });
        }
    };
})();