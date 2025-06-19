/**
 * ===================================================================
 * cart.js
 *
 * Xử lý tất cả các tương tác liên quan đến giỏ hàng thông qua API.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Cập nhật số lượng hiển thị trên icon giỏ hàng ở header.
     * @param {number} count - Số lượng sản phẩm.
     */
    function updateCartBadge(count) {
        const cartCountBadge = document.getElementById('header-cart-count');
        if (!cartCountBadge) return;

        if (count > 0) {
            cartCountBadge.textContent = count;
            cartCountBadge.classList.remove('d-none');
        } else {
            cartCountBadge.classList.add('d-none');
        }
    }

    /**
     * Hàm xử lý khi nhấn nút "Thêm vào giỏ hàng".
     */
    async function handleAddToCart(event) {
        // Chỉ xử lý khi click vào nút có class 'add-to-cart-btn'
        if (!event.target.closest('.add-to-cart-btn')) {
            return;
        }
        event.preventDefault();

        const button = event.target.closest('.add-to-cart-btn');
        const productId = button.dataset.productId;
        if (!productId) {
            console.error('Không tìm thấy product ID trên nút.');
            return;
        }

        // Tìm input số lượng liên quan đến nút này
        const quantityInput = document.getElementById(`quantity-input-${productId}`);
        const quantity = quantityInput ? quantityInput.value : 1;

        window.showAppLoader(); // Hiện loading overlay

        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            const response = await fetch('/api/customer/cart/add', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (!response.ok) {
                // Ném lỗi để block catch xử lý
                throw new Error(result.message || 'Không thể thêm sản phẩm vào giỏ.');
            }

            // Nếu thành công
            window.showAppInfoModal(result.message, 'success');
            updateCartBadge(result.cart_item_count);

        } catch (error) {
            console.error('Lỗi khi thêm vào giỏ:', error);
            window.showAppInfoModal(error.message, 'error');
        } finally {
            window.hideAppLoader(); // Luôn ẩn loading overlay
        }
    }
    
    /**
     * Hàm lấy thông tin giỏ hàng ban đầu khi tải trang.
     */
    async function fetchInitialCartInfo() {
        try {
            const response = await fetch('/api/customer/cart/info', {
                 headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) return;
            const result = await response.json();
            updateCartBadge(result.item_count);
        } catch (error) {
            console.error('Không thể lấy thông tin giỏ hàng ban đầu:', error);
        }
    }

    /**
     * Đăng ký các trình xử lý sự kiện.
     */
    window.initializeCartHandler = function() {
        document.body.addEventListener('click', handleAddToCart);
        fetchInitialCartInfo(); // Lấy thông tin giỏ hàng khi trang được tải
    };

})();
