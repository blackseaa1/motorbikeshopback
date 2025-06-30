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
            if (currentReviewStatus === 'rejected') {
                if (reviewFormContainer) {
                    reviewFormContainer.style.display = 'none'; // Hide the form
                }
                if (reviewStatusMessage) {
                    reviewStatusMessage.innerHTML = '<div class="alert alert-warning">Đánh giá trước đó của bạn đã bị từ chối. Vui lòng liên hệ bộ phận hỗ trợ nếu bạn muốn gửi lại đánh giá.</div>';
                    reviewStatusMessage.style.display = 'block';
                }
            } else if (currentReviewStatus === 'pending') {
                if (reviewFormContainer) {
                    reviewFormContainer.style.display = 'none'; // Hide the form
                }
                if (reviewStatusMessage) {
                    reviewStatusMessage.innerHTML = '<div class="alert alert-info">Đánh giá của bạn đang chờ phê duyệt. Vui lòng đợi quản trị viên xem xét.</div>';
                    reviewStatusMessage.style.display = 'block';
                }
            } else {
                // Show the form if no special status or status allows submission
                if (reviewFormContainer) {
                    reviewFormContainer.style.display = 'block';
                }
                if (reviewStatusMessage) {
                    reviewStatusMessage.style.display = 'none';
                }
            }


            reviewForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Client-side validation for phone number (assuming an input with name="phone_number")
                const phoneNumberInput = this.querySelector('input[name="phone_number"]');
                if (phoneNumberInput) {
                    const phoneNumber = phoneNumberInput.value.trim();
                    if (phoneNumber.length !== 10 || !/^\d{10}$/.test(phoneNumber)) {
                        // Changed message to "Bạn cần nhập đủ 10 kí tự"
                        window.showAppInfoModal('Bạn cần nhập đủ 10 kí tự.', 'error', 'Lỗi Nhập Liệu!');
                        return; // Stop submission
                    }
                }

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

                            // If a new review is submitted successfully, you might want to hide the form
                            // and show a pending message, or refresh the review list.
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
                                // Check for specific phone number error, if backend sends it
                                if (field === 'phone_number') { // Use the actual name of your phone number input field
                                    errorMessages.push(`Số điện thoại: ${body.errors[field].join(', ')}`);
                                } else {
                                    body.errors[field].forEach(error => {
                                        errorMessages.push(error);
                                    });
                                }
                            }
                            // Join all error messages with newline characters
                            const finalErrorMessage = 'Lỗi Dữ Liệu!\n' + errorMessages.join('\n');
                            window.showAppInfoModal(finalErrorMessage, 'error', 'Lỗi Dữ Liệu!'); // Changed type to 'error' and passed a plain string
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