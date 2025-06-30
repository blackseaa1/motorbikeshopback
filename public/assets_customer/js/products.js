// products.js
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
        const reviewFormContainer = document.getElementById('review-form-container'); // Assuming a container for the form
        const reviewStatusMessage = document.getElementById('review-status-message'); // Assuming an element to display messages

        if (reviewForm) {
            // Check review status on page load (e.g., if admin rejected a previous review)
            const currentReviewStatus = reviewForm.dataset.reviewStatus; // Assuming data-review-status attribute

            // Ẩn form và hiển thị thông báo nếu đã có đánh giá (pending, rejected, approved)
            if (currentReviewStatus) { // Nếu có bất kỳ trạng thái nào (không phải rỗng)
                if (reviewFormContainer) {
                    reviewFormContainer.style.display = 'none'; // Ẩn form
                }
                if (reviewStatusMessage) {
                    let messageHtml = '';
                    if (currentReviewStatus === 'rejected') {
                        messageHtml = '<div class="alert alert-warning">Đánh giá trước đó của bạn đã bị từ chối. Vui lòng liên hệ bộ phận hỗ trợ nếu bạn muốn gửi lại đánh giá.</div>';
                    } else if (currentReviewStatus === 'pending') {
                        messageHtml = '<div class="alert alert-info">Đánh giá của bạn đang chờ phê duyệt. Vui lòng đợi quản trị viên xem xét.</div>';
                    } else if (currentReviewStatus === 'approved') { // Thêm trạng thái đã duyệt
                        messageHtml = '<div class="alert alert-success">Bạn đã gửi đánh giá cho sản phẩm này. Cảm ơn bạn!</div>';
                    } else { // Các trạng thái khác (nếu có)
                        messageHtml = '<div class="alert alert-info">Bạn đã gửi đánh giá cho sản phẩm này.</div>';
                    }
                    reviewStatusMessage.innerHTML = messageHtml;
                    reviewStatusMessage.style.display = 'block';
                }
            } else {
                // Hiển thị form nếu chưa có đánh giá nào được gửi
                if (reviewFormContainer) {
                    reviewFormContainer.style.display = 'block';
                }
                if (reviewStatusMessage) {
                    reviewStatusMessage.style.display = 'none';
                }
            }


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

                            // Khi gửi đánh giá thành công, ẩn form và hiển thị thông báo chờ duyệt
                            if (reviewFormContainer) {
                                reviewFormContainer.style.display = 'none';
                            }
                            if (reviewStatusMessage) {
                                reviewStatusMessage.innerHTML = '<div class="alert alert-info">Đánh giá của bạn đã được gửi và đang chờ phê duyệt.</div>';
                                reviewStatusMessage.style.display = 'block';
                            }

                        } else if (status === 422) {
                            // Lỗi validation
                            let errorMessages = [];
                            for (const field in body.errors) {
                                body.errors[field].forEach(error => {
                                    errorMessages.push(error);
                                });
                            }
                            // Join all error messages with newline characters
                            const finalErrorMessage = 'Lỗi Dữ Liệu!\n' + errorMessages.join('\n');
                            window.showAppInfoModal(finalErrorMessage, 'error', 'Lỗi Dữ Liệu!');
                        } else {
                            // Các lỗi khác
                            window.showAppInfoModal(body.message || 'Có lỗi không xác định xảy ra.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Đây là lỗi khi không thể kết nối đến máy chủ
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