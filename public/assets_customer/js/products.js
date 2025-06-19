/**
 * ===================================================================
 * products.js
 *
 * Xử lý logic cho trang chi tiết sản phẩm.
 * - Thư viện ảnh (Image Gallery).
 * - Gửi đánh giá bằng AJAX.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Khởi tạo các thành phần trên trang chi tiết sản phẩm.
     */
    function initializeProductDetailPage() {
        console.log('Khởi tạo kịch bản cho trang chi tiết sản phẩm...');

        // Chỉ chạy script nếu đang ở đúng trang (có element với id này)
        if (!document.getElementById('product-detail-page')) return;

        // 1. Xử lý thư viện ảnh
        const mainImage = document.getElementById('main-product-image');
        const galleryContainer = document.getElementById('product-image-gallery');

        if (mainImage && galleryContainer) {
            galleryContainer.addEventListener('click', function (e) {
                // Sử dụng event delegation
                const thumbnailLink = e.target.closest('a');
                if (thumbnailLink && thumbnailLink.dataset.imageUrl) {
                    e.preventDefault();
                    mainImage.src = thumbnailLink.dataset.imageUrl;
                }
            });
        }

        // 2. Xử lý gửi Review bằng AJAX
        const reviewForm = document.getElementById('review-form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function (e) {
                e.preventDefault();
                window.showAppLoader(); // Hiện loading overlay

                const formData = new FormData(this);
                const url = this.action;

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => {
                        // Chuyển response thành JSON, ngay cả khi bị lỗi validation (422)
                        return response.json().then(data => ({ status: response.status, body: data }));
                    })
                    .then(({ status, body }) => {
                        if (status === 200 && body.success) {
                            // Thành công
                            window.showAppInfoModal(body.message, 'success');
                            reviewForm.reset();
                            // Reset lại các ngôi sao
                            document.querySelectorAll('.rating-stars label').forEach(label => label.style.color = '#ddd');

                        } else if (status === 422) {
                            // Lỗi validation
                            let errorHtml = '<ul class="mb-0 text-start ps-3">';
                            for (const field in body.errors) {
                                body.errors[field].forEach(error => {
                                    errorHtml += `<li>${error}</li>`;
                                });
                            }
                            errorHtml += '</ul>';
                            window.showAppInfoModal({ html: errorHtml }, 'validation_error', 'Lỗi Dữ Liệu!');
                        } else {
                            // Các lỗi khác
                            window.showAppInfoModal(body.message || 'Có lỗi không xác định xảy ra.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.showAppInfoModal('Không thể kết nối đến máy chủ, vui lòng thử lại sau.', 'error');
                    })
                    .finally(() => {
                        window.hideAppLoader(); // Luôn ẩn loading overlay
                    });
            });
        }
    }

    // Đưa hàm khởi tạo ra global scope để "nhạc trưởng" có thể gọi
    window.initializeProductDetailPage = initializeProductDetailPage;

})();