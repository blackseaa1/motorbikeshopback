/**
 * ===================================================================
 * account.js
 *
 * Xử lý logic cho trang quản lý tài khoản người dùng (profile).
 * Được gọi bởi "nhạc trưởng" customer_layout.js.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Khởi tạo các form trên trang tài khoản.
     * Hàm này được `customer_layout.js` gọi thông qua cơ chế @push.
     */
    window.initializeAccountPage = function () {
        // 1. Thiết lập cho form cập nhật thông tin cá nhân
        const profileForm = document.getElementById('profile-update-form');
        if (profileForm) {
            window.setupAjaxForm('profile-update-form', (result, form) => {
                // Cập nhật thành công, hiển thị thông báo
                window.showAppInfoModal(result.message, 'success', 'Thành công');

                // Tùy chọn: Cập nhật tên người dùng trên header mà không cần reload
                const userNameOnHeader = document.querySelector('#navbarDropdown');
                const newName = form.querySelector('[name="name"]').value;
                if (userNameOnHeader && newName) {
                    // Cập nhật tên hiển thị ở nhiều nơi nếu có
                }
            });
        }

        // 2. Thiết lập cho form đổi mật khẩu
        const passwordForm = document.getElementById('password-update-form');
        if (passwordForm) {
            window.setupAjaxForm('password-update-form', (result, form) => {
                // Cập nhật thành công, hiển thị thông báo và xóa trắng form
                window.showAppInfoModal(result.message, 'success', 'Thành công');
                form.reset();
            });
        }
    };
})();