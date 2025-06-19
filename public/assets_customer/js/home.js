/**
 * ===================================================================
 * home.js
 *
 * Xử lý logic cho trang chủ (home.blade.php).
 * Được gọi bởi "nhạc trưởng" customer_layout.js.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Khởi tạo các thành phần trên trang chủ.
     * Hàm này được `customer_layout.js` gọi thông qua cơ chế @push.
     */
    window.initializeHomePage = function () {
        window.showAppLoader();

        try {
            console.log('Khởi tạo kịch bản cho trang chủ...');

            const mainCarouselEl = document.getElementById('mainCarousel');
            if (mainCarouselEl) {
                new bootstrap.Carousel(mainCarouselEl, {
                    interval: 5000,
                    wrap: true
                });
            }

            const categoryCarouselDesktopEl = document.getElementById('categoryCarouselDesktop');
            if (categoryCarouselDesktopEl) {
                new bootstrap.Carousel(categoryCarouselDesktopEl, {
                    interval: false
                });
            }

            const categoryCarouselMobileEl = document.getElementById('categoryCarouselMobile');
            if (categoryCarouselMobileEl) {
                new bootstrap.Carousel(categoryCarouselMobileEl, {
                    interval: false
                });
            }

            console.log('Các carousel trên trang chủ đã được khởi tạo.');
        } catch (error) {
            console.error('Lỗi khi khởi tạo trang chủ:', error);
            window.showAppInfoModal('Đã xảy ra lỗi khi tải trang chủ. Vui lòng thử lại sau.', 'error', 'Lỗi hệ thống');
        } finally {
            window.hideAppLoader();
        }
    };

})();
