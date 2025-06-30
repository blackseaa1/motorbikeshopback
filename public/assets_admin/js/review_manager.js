/**
 * ===================================================================
 * review_manager.js
 * Xử lý JavaScript cho trang quản lý Đánh giá sản phẩm (Reviews).
 * Tích hợp AJAX cho các thao tác và hỗ trợ phân trang động.
 * Tham khảo từ brand_manager.js.
 * ===================================================================
 */

let isReviewManagerInitialized = false; // Flag để ngăn khởi tạo nhiều lần

document.addEventListener('DOMContentLoaded', function () {
    // Chỉ khởi tạo nếu chưa được khởi tạo
    if (isReviewManagerInitialized) {
        console.log("Review Manager đã được khởi tạo trước đó. Bỏ qua khởi tạo lại.");
        return;
    }
    isReviewManagerInitialized = true;
    console.log("Khởi tạo JS cho trang Quản lý Đánh giá...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // Lấy các hàm helper toàn cục (showAppLoader, hideAppLoader, showToast)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Fallback đơn giản nếu showToast không được định nghĩa toàn cục
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`);
            return;
        }

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    // --- Xử lý modal xem chi tiết đánh giá (View Review Modal) ---
    const viewReviewModalElement = document.getElementById('viewReviewModal');
    if (viewReviewModalElement) {
        viewReviewModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Nút kích hoạt modal

            // Lấy dữ liệu từ data attributes của nút
            const reviewId = button.dataset.id;
            const customerName = button.dataset.customerName;
            const customerEmail = button.dataset.customerEmail;
            const productName = button.dataset.productName;
            const rating = button.dataset.rating;
            const comment = button.dataset.comment;
            const status = button.dataset.status;
            const createdAt = button.dataset.createdAt;
            const productLink = button.dataset.productLink;

            // Đổ dữ liệu vào các phần tử trong modal
            viewReviewModalElement.querySelector('#viewReviewId').textContent = reviewId;
            viewReviewModalElement.querySelector('#viewReviewCustomer').textContent = `${customerName} (${customerEmail})`;
            
            const productLinkElement = viewReviewModalElement.querySelector('#viewReviewProductLink');
            productLinkElement.textContent = productName;
            productLinkElement.href = productLink;

            const ratingHtml = Array(parseInt(rating)).fill('<i class="bi bi-star-fill text-warning"></i>').join('') +
                               Array(5 - parseInt(rating)).fill('<i class="bi bi-star text-warning"></i>').join('');
            viewReviewModalElement.querySelector('#viewReviewRating').innerHTML = ratingHtml;
            
            viewReviewModalElement.querySelector('#viewReviewComment').textContent = comment;
            
            const statusSpan = viewReviewModalElement.querySelector('#viewReviewStatus');
            statusSpan.className = ''; // Clear existing classes
            if (status === 'pending') {
                statusSpan.innerHTML = '<span class="badge bg-warning">Đang chờ</span>';
            } else if (status === 'approved') {
                statusSpan.innerHTML = '<span class="badge bg-success">Đã duyệt</span>';
            } else { // rejected
                statusSpan.innerHTML = '<span class="badge bg-danger">Đã từ chối</span>';
            }

            viewReviewModalElement.querySelector('#viewReviewCreatedAt').textContent = createdAt;
        });
    }

    // --- Xử lý modal xác nhận xóa đánh giá (Delete Review Modal) ---
    const deleteReviewModalElement = document.getElementById('deleteReviewModal');
    if (deleteReviewModalElement) {
        const deleteReviewForm = deleteReviewModalElement.querySelector('#deleteReviewForm');
        const customerNameSpan = deleteReviewModalElement.querySelector('#customerNameForDelete');
        const productNameSpan = deleteReviewModalElement.querySelector('#productNameForDelete');
        const submitButtonDelete = deleteReviewForm ? deleteReviewForm.querySelector('button[type="submit"]') : null;

        deleteReviewModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Nút kích hoạt modal
            if (!button) return;

            // Lấy dữ liệu từ data attributes của nút
            const reviewId = button.dataset.id;
            const deleteUrl = button.dataset.url;
            const customerName = button.dataset.customerName;
            const productName = button.dataset.productName;

            // Đặt action cho form xóa
            if (deleteReviewForm) deleteReviewForm.action = deleteUrl;
            
            // Hiển thị thông tin trong modal
            if (customerNameSpan) customerNameSpan.textContent = customerName;
            if (productNameSpan) productNameSpan.textContent = productName;

            // Reset trạng thái nút submit
            if (submitButtonDelete) {
                submitButtonDelete.disabled = false;
                submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
            }
        });

        // Xử lý sự kiện submit form xóa
        if (deleteReviewForm && submitButtonDelete) {
            deleteReviewForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const url = this.action;
                const formData = new FormData(this); // Lấy dữ liệu form (bao gồm _token và _method)

                showAppLoader();
                submitButtonDelete.disabled = true;
                submitButtonDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xóa...';

                try {
                    const response = await fetch(url, {
                        method: 'POST', // Luôn là POST với FormData, Laravel sẽ đọc _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (response.ok) { // Status code 2xx
                        const modalInstance = bootstrap.Modal.getInstance(deleteReviewModalElement);
                        if (modalInstance) modalInstance.hide();
                        showToast(result.message, 'success');
                        // Tải lại trang hoặc xóa hàng khỏi DOM sau khi xóa thành công
                        setTimeout(() => window.location.reload(), 1000);
                    } else { // Các lỗi khác (ví dụ: 403 Forbidden, 404 Not Found)
                        showToast(result.message || `Lỗi HTTP: ${response.status}`, 'error');
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa đánh giá:', error);
                    showToast('Có lỗi xảy ra trong quá trình xử lý xóa.', 'error');
                } finally {
                    hideAppLoader();
                    submitButtonDelete.disabled = false;
                    submitButtonDelete.innerHTML = 'Xóa Vĩnh Viễn';
                }
            });
        }
    }

    // --- Xử lý nút cập nhật trạng thái đánh giá (Update Status Button) ---
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('.update-review-status-btn');
        if (!button) return;

        event.preventDefault(); // Ngăn chặn hành động mặc định của thẻ <a>

        const reviewId = button.dataset.id;
        const url = button.dataset.url;
        const newStatus = button.dataset.status;

        showAppLoader();
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json' // Quan trọng khi gửi JSON body
                },
                body: JSON.stringify({ status: newStatus }) // Gửi trạng thái mới dưới dạng JSON
            });

            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorResult.message || `Lỗi HTTP: ${response.status}`);
            }
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Cập nhật trạng thái thành công.', 'success');
                // Cập nhật trạng thái hiển thị trên bảng
                const statusCell = document.getElementById(`review-status-${reviewId}`);
                if (statusCell) {
                    let badgeClass = '';
                    if (result.new_status === 'pending') {
                        badgeClass = 'bg-warning';
                    } else if (result.new_status === 'approved') {
                        badgeClass = 'bg-success';
                    } else { // rejected
                        badgeClass = 'bg-danger';
                    }
                    statusCell.innerHTML = `<span class="badge ${badgeClass}">${result.status_text}</span>`;
                }
            } else {
                throw new Error(result.message || 'Có lỗi khi cập nhật trạng thái.');
            }
        } catch (error) {
            console.error('Lỗi khi thay đổi trạng thái đánh giá:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    });

    // --- Xử lý phân trang (Pagination) ---
    // Sử dụng event delegation để bắt click vào các link phân trang
    document.body.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('.pagination a');
        if (!paginationLink) return;

        event.preventDefault(); // Ngăn chặn chuyển hướng mặc định của link

        const url = new URL(paginationLink.href);
        // Có thể thêm logic để giữ lại các tham số tìm kiếm, lọc, sắp xếp hiện có
        // Tuy nhiên, vì Blade paginate() và withQueryString() đã làm điều này,
        // chúng ta chỉ cần chuyển hướng trình duyệt đến URL mới.
        window.location.href = url.toString();
    });

    console.log("Module Quản lý Đánh giá đã được khởi tạo thành công.");
});
