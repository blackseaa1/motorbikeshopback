/**
 * ===================================================================
 * review_manager.js
 * Xử lý JavaScript cho trang quản lý Đánh giá sản phẩm (Reviews).
 * Tích hợp AJAX cho các thao tác và hỗ trợ phân trang động.
 * ===================================================================
 */

// Đặt hàm khởi tạo vào phạm vi toàn cục để admin_layout.js có thể truy cập
window.initializeReviewsPage = function () {
    console.log("Initializing JS for Review Management Page...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Serious Error: CSRF Token not found!');
        return;
    }

    // Get global helper functions (showAppLoader, hideAppLoader, showToast)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader (fallback)');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader (fallback)');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Simple fallback if showToast is not globally defined
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Cannot find .toast-container. Please add it to the main layout.');
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

    // --- Handle View Review Modal ---
    const viewReviewModalElement = document.getElementById('viewReviewModal');
    if (viewReviewModalElement) {
        viewReviewModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal

            // Get data from button's data attributes
            const customerId = button.dataset.customerId;
            const productId = button.dataset.productId;
            const customerName = button.dataset.customerName;
            const customerEmail = button.dataset.customerEmail;
            const productName = button.dataset.productName;
            const productLink = button.dataset.productLink;
            const rating = button.dataset.rating;
            const comment = button.dataset.comment;
            const status = button.dataset.status;
            const createdAt = button.dataset.createdAt;

            // Populate the modal fields
            viewReviewModalElement.querySelector('#viewReviewCustomerId').textContent = customerId;
            viewReviewModalElement.querySelector('#viewReviewProductId').textContent = productId;
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
                statusSpan.innerHTML = '<span class="badge bg-warning">Chờ duyệt</span>';
            } else if (status === 'approved') {
                statusSpan.innerHTML = '<span class="badge bg-success">Đã duyệt</span>';
            } else { // rejected
                statusSpan.innerHTML = '<span class="badge bg-danger">Từ chối</span>';
            }

            viewReviewModalElement.querySelector('#viewReviewCreatedAt').textContent = createdAt;
        });
    }

    // --- Handle Delete Review Modal ---
    const deleteReviewModalElement = document.getElementById('deleteReviewModal');
    if (deleteReviewModalElement) {
        const deleteReviewForm = deleteReviewModalElement.querySelector('#deleteReviewForm');
        const customerNameSpan = deleteReviewModalElement.querySelector('#customerNameForDelete');
        const productNameSpan = deleteReviewModalElement.querySelector('#productNameForDelete');
        const submitButtonDelete = deleteReviewForm ? deleteReviewForm.querySelector('button[type="submit"]') : null;

        deleteReviewModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            if (!button) return;

            // Get data from button's data attributes
            const customerId = button.dataset.customerId;
            const productId = button.dataset.productId;
            const deleteUrl = button.dataset.url;
            const customerName = button.dataset.customerName;
            const productName = button.dataset.productName;

            // Set the form action URL
            if (deleteReviewForm) deleteReviewForm.action = deleteUrl;

            // Add hidden inputs for composite primary key
            let customerIdInput = deleteReviewForm.querySelector('input[name="customer_id"]');
            let productIdInput = deleteReviewForm.querySelector('input[name="product_id"]');

            if (!customerIdInput) {
                customerIdInput = document.createElement('input');
                customerIdInput.type = 'hidden';
                customerIdInput.name = 'customer_id';
                deleteReviewForm.appendChild(customerIdInput);
            }
            if (!productIdInput) {
                productIdInput = document.createElement('input');
                productIdInput.type = 'hidden';
                productIdInput.name = 'product_id';
                deleteReviewForm.appendChild(productIdInput);
            }

            customerIdInput.value = customerId;
            productIdInput.value = productId;

            // Display information in the modal
            if (customerNameSpan) customerNameSpan.textContent = customerName;
            if (productNameSpan) productNameSpan.textContent = productName;

            // Reset submit button state
            if (submitButtonDelete) {
                submitButtonDelete.disabled = false;
                submitButtonDelete.innerHTML = '<i class="bi bi-trash-fill me-1"></i>Xóa Vĩnh Viễn';
            }
        });

        // Handle delete form submission
        if (deleteReviewForm && submitButtonDelete) {
            deleteReviewForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const url = this.action;
                const formData = new FormData(this);

                showAppLoader();
                submitButtonDelete.disabled = true;
                submitButtonDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xóa...';

                try {
                    const response = await fetch(url, {
                        method: 'POST', // Always POST with FormData, Laravel reads _method
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    const result = await response.json();

                    if (response.ok) { // Status code 2xx
                        const modalInstance = bootstrap.Modal.getInstance(deleteReviewModalElement);
                        if (modalInstance) modalInstance.hide();
                        showToast(result.message, 'success');
                        // Reload page or remove row from DOM after successful deletion
                        setTimeout(() => window.location.reload(), 1000);
                    } else { // Other errors (e.g., 403 Forbidden, 404 Not Found)
                        showToast(result.message || `HTTP Error: ${response.status}`, 'error');
                    }
                } catch (error) {
                    console.error('Error deleting review:', error);
                    showToast('An error occurred during deletion.', 'error');
                } finally {
                    hideAppLoader();
                    submitButtonDelete.disabled = false;
                    submitButtonDelete.innerHTML = '<i class="bi bi-trash-fill me-1"></i>Xóa Vĩnh Viễn';
                }
            });
        }
    }

    // --- Handle Update Review Status Button ---
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('.update-review-status-btn');
        if (!button) return;

        event.preventDefault(); // Prevent default link behavior

        const customerId = button.dataset.customerId;
        const productId = button.dataset.productId;
        const url = button.dataset.url;
        const newStatus = button.dataset.status;

        showAppLoader();
        try {
            const response = await fetch(url, {
                method: 'POST', // Your route is POST
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json' // Important when sending JSON body
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    product_id: productId,
                    status: newStatus
                })
            });

            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: 'Unknown error from server.' }));
                throw new Error(errorResult.message || `HTTP Error: ${response.status}`);
            }
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Status updated successfully.', 'success');
                // Update status display on the table
                const statusCell = document.getElementById(`review-status-${customerId}-${productId}`);
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
                } else {
                    // If specific cell not found, reload page to update the entire table
                    setTimeout(() => window.location.reload(), 500);
                }
            } else {
                throw new Error(result.message || 'Error updating status.');
            }
        } catch (error) {
            console.error('Error changing review status:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    });

    // --- Handle Pagination ---
    document.body.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('.pagination a');
        if (!paginationLink) return;

        event.preventDefault(); // Prevent default link navigation

        const url = new URL(paginationLink.href);
        // Since Blade's paginate() and withQueryString() already handle this,
        // we just need to navigate the browser to the new URL.
        window.location.href = url.toString();
    });

    console.log("Review Management Module initialized successfully.");
};

// Add this initializer to admin_layout.js's runPageSpecificInitializers
// (This part should be manually added to admin_layout.js, not in this file itself)
/*
// In public/assets_admin/js/admin_layout.js, inside runPageSpecificInitializers function:
if (typeof initializeReviewsPage === 'function' && document.getElementById('adminReviewsPage')) {
    initializeReviewsPage();
}
*/
