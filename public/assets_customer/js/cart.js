(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let isProcessing = false;

    /**
     * HÀM 1: Dựng lại HTML cho mini-cart trên header.
     * (Cập nhật để hiển thị tổng tiền một cách linh hoạt)
     */
    function renderHeaderCart(data) {
        const countBadge = document.getElementById('header-cart-count');
        const itemsContainer = document.getElementById('header-cart-items-container');
        const cartFooter = document.getElementById('header-cart-footer');
        const cartTotalEl = document.getElementById('header-cart-total');

        if (!countBadge || !itemsContainer || !cartFooter || !cartTotalEl) return;

        const count = data.count || 0;

        // === SỬA ĐỔI LOGIC HIỂN THỊ TỔNG TIỀN ===
        // Ưu tiên hiển thị grand_total (tổng cuối cùng) nếu có.
        // Nếu không, sẽ hiển thị subtotal (tạm tính).
        const totalToShow = data.subtotal ?? 0;

        countBadge.textContent = count;
        countBadge.classList.toggle('d-none', count === 0);

        if (data.items && data.items.length > 0) {
            let itemsHtml = '<ul class="list-group list-group-flush">';
            data.items.forEach(item => {
                const price = item.product ? Number(item.product.price) : 0;
                itemsHtml += `
                    <li class="list-group-item d-flex align-items-center" data-product-id="${item.product_id}">
                        <img src="${item.product?.thumbnail_url || ''}" alt="${item.product?.name || ''}" class="me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <a href="/products/${item.product_id}" class="fw-bold text-dark text-decoration-none small">${item.product?.name || 'Sản phẩm không xác định'}</a>
                            <div class="d-flex align-items-center mt-2">
                                <input type="number" class="form-control form-control-sm cart-quantity-input" value="${item.quantity}" min="1" style="width: 65px;" aria-label="Số lượng">
                                <span class="ms-2 text-muted small">${price.toLocaleString('vi-VN')} ₫</span>
                            </div>
                        </div>
                        <button class="btn btn-sm text-danger remove-from-cart-btn" data-product-id="${item.product_id}" aria-label="Xóa"><i class="bi bi-trash"></i></button>
                    </li>
                `;
            });
            itemsHtml += '</ul>';
            itemsContainer.innerHTML = itemsHtml;
            cartTotalEl.textContent = `${totalToShow.toLocaleString('vi-VN')} ₫`;
            cartFooter.classList.remove('d-none');
        } else {
            itemsContainer.innerHTML = '<div class="p-4 text-center text-muted">Giỏ hàng của bạn đang trống</div>';
            cartFooter.classList.add('d-none');
        }
    }

    /**
     * === HÀM 2: SỬA LỖI - Dựng lại toàn bộ giao diện trang /cart ===
     * (Cập nhật theo góp ý của bạn)
     */
    function renderCartPage(data) {
        const itemsContainer = document.getElementById('cart-items-container');
        if (!itemsContainer) return; // Chỉ chạy nếu đang ở trang cart

        // Dựng lại danh sách sản phẩm
        if (data.items && data.items.length > 0) {
            let itemsHtml = '';
            data.items.forEach(item => {
                const price = item.product ? Number(item.product.price) : 0;
                itemsHtml += `
                    <div class="card mb-3 cart-item" data-product-id="${item.product_id}">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap">
                                <img src="${item.product.thumbnail_url}" alt="${item.product.name}" style="width: 100px; height: 100px; object-fit: cover;" class="me-3 mb-2 mb-md-0">
                                <div class="ms-md-3 me-md-auto flex-grow-1">
                                    <h5 class="mb-1"><a href="/products/${item.product_id}" class="text-dark text-decoration-none">${item.product.name}</a></h5>
                                    <p class="mb-1 text-muted">${price.toLocaleString('vi-VN')} ₫</p>
                                </div>
                                <div class="d-flex align-items-center mt-2 mt-md-0">
                                    <input type="number" class="form-control form-control-sm cart-quantity-input" value="${item.quantity}" min="1" style="width: 70px;" aria-label="Số lượng">
                                    <button class="btn btn-sm btn-outline-danger ms-3 remove-from-cart-btn" data-product-id="${item.product_id}" aria-label="Xóa"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            itemsContainer.innerHTML = itemsHtml;
        } else {
            // Nếu giỏ hàng trống, hiển thị thông báo
            itemsContainer.innerHTML = '<div class="alert alert-info">Giỏ hàng của bạn đang trống. <a href="/products" class="alert-link">Tiếp tục mua sắm</a>.</div>';
        }

        // === SỬA ĐỔI QUAN TRỌNG: LUÔN CẬP NHẬT KHU VỰC SUMMARY ===
        // Lấy các element trong tóm tắt đơn hàng
        const subtotalEl = document.getElementById('cart-subtotal');
        const shippingFeeEl = document.getElementById('cart-shipping-fee');
        const discountEl = document.getElementById('cart-discount');
        const discountRowEl = document.getElementById('discount-row');
        const grandTotalEl = document.getElementById('cart-grand-total');

        // Lấy giá trị từ data, mặc định là 0 nếu không có
        const subtotal = data.subtotal || 0;
        const shippingFee = data.shipping_fee || 0;
        const discountAmount = data.discount_amount || 0;
        const grandTotal = data.grand_total || 0;

        // Cập nhật các giá trị. Khi giỏ hàng trống, các giá trị này sẽ là 0.
        if (subtotalEl) subtotalEl.textContent = `${subtotal.toLocaleString('vi-VN')} ₫`;
        if (shippingFeeEl) shippingFeeEl.textContent = `${shippingFee.toLocaleString('vi-VN')} ₫`;
        if (discountEl) discountEl.textContent = `-${discountAmount.toLocaleString('vi-VN')} ₫`;
        if (grandTotalEl) grandTotalEl.textContent = `${grandTotal.toLocaleString('vi-VN')} ₫`;

        // Ẩn dòng giảm giá nếu không có giảm giá
        if (discountRowEl) discountRowEl.classList.toggle('d-none', discountAmount <= 0);
    }

    /**
     * HÀM 3: Cập nhật tóm tắt đơn hàng (SỬA LỖI NaN)
     */
    function updateCartSummary(data) {
        const subtotal = data.subtotal || 0;
        const shippingFee = data.shipping_fee || 0;
        const discountAmount = data.discount_amount || 0;
        const grandTotal = data.grand_total || 0;

        const subtotalEl = document.getElementById('cart-subtotal');
        const shippingFeeEl = document.getElementById('cart-shipping-fee');
        const discountEl = document.getElementById('cart-discount');
        const grandTotalEl = document.getElementById('cart-grand-total');
        const discountRow = document.getElementById('discount-row');
        const promoFeedback = document.getElementById('promo-feedback');

        // KIỂM TRA: Chỉ thực thi nếu tất cả các element cần thiết tồn tại
        if (!subtotalEl || !shippingFeeEl || !discountEl || !grandTotalEl || !discountRow || !promoFeedback) return;

        subtotalEl.textContent = `${subtotal.toLocaleString('vi-VN')} ₫`;
        shippingFeeEl.textContent = `${shippingFee.toLocaleString('vi-VN')} ₫`;
        discountEl.textContent = `-${discountAmount.toLocaleString('vi-VN')} ₫`;
        grandTotalEl.textContent = `${grandTotal.toLocaleString('vi-VN')} ₫`;

        discountRow.classList.toggle('d-none', discountAmount <= 0);

        if (data.promotion_info && data.promotion_info.code) {
            promoFeedback.innerHTML = `<div class='text-success small mt-1'>Đã áp dụng mã: <strong>${data.promotion_info.code}</strong></div>`;
        } else if (data.promo_error) {
            promoFeedback.innerHTML = `<div class='text-danger small mt-1'>${data.promo_error}</div>`;
        } else {
            promoFeedback.innerHTML = '';
        }
    }

    /**
     * HÀM 4: Hàm AJAX trung tâm, được cập nhật để xử lý thông báo và summary
     */
    async function sendCartRequest(url, body, isSummaryUpdate = false, successMessage = null) {
        if (isProcessing) return;
        isProcessing = true;
        window.showAppLoader();

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(body)
            });
            const data = await response.json();
            if (!response.ok) {
                if (response.status === 422 && data.errors?.promotion_code) {
                    const promoFeedback = document.getElementById('promo-feedback');
                    if (promoFeedback) {
                        promoFeedback.innerHTML = `<div class='text-danger small mt-1'>${data.errors.promotion_code[0]}</div>`;
                    }
                    return;
                }
                let errorMessage = data.errors?.stock?.[0] || data.message || 'Có lỗi xảy ra.';
                if (data.errors && !data.errors.promotion_code && !data.errors.stock) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                throw new Error(errorMessage);
            }

            // Cập nhật giao diện khi thành công
            if (isSummaryUpdate) {
                updateCartSummary(data);
            } else {
                renderHeaderCart(data);
                if (window.location.pathname === '/cart') {
                    renderCartPage(data);
                    updateCartSummary(data);
                }
            }

            if (successMessage) {
                window.showAppInfoModal(successMessage, 'success', 'Thành công!');
            }

        } catch (error) {
            window.showAppInfoModal(error.message, 'error');
        } finally {
            window.hideAppLoader();
            isProcessing = false;
        }
    }

    /**
     * HÀM 5: KHỞI TẠO CÁC TRÌNH LẮNG NGHE SỰ KIỆN.
     */
    window.initializeCartHandler = function () {
        // Tải thông tin giỏ hàng ban đầu
        fetch('/api/cart', { headers: { 'Accept': 'application/json' } })
            .then(res => res.ok ? res.json() : Promise.reject('Failed to load cart'))
            .then(data => {
                renderHeaderCart(data);
                if (window.location.pathname === '/cart') {
                    renderCartPage(data);
                    updateCartSummary(data);
                }
            })
            .catch(err => console.error("Không thể tải giỏ hàng ban đầu:", err));

        // Sửa lỗi dropdown tự tắt
        const cartDropdownToggle = document.getElementById('cartDropdown');
        if (cartDropdownToggle) {
            new bootstrap.Dropdown(cartDropdownToggle, { autoClose: 'outside' });
        }

        // Khởi tạo bootstrap-select (cần jQuery)
        if ($('.selectpicker').length) {
            $('.selectpicker').selectpicker();
        }

        // HÀM MỚI: Gửi yêu cầu cập nhật tóm tắt giỏ hàng
        function triggerSummaryUpdate() {
            const deliveryId = $('#delivery_service_id').val();
            const promoCode = document.getElementById('promotion_code')?.value;

            const payload = {};
            if (deliveryId) payload.delivery_service_id = deliveryId;
            if (promoCode !== undefined) payload.promotion_code = promoCode;

            sendCartRequest('/api/cart/update-summary', payload, true);
        }

        // Lắng nghe sự kiện click trên toàn trang
        document.body.addEventListener('click', e => {
            const target = e.target;
            const addToCartBtn = target.closest('.add-to-cart-btn');
            const removeFromCartBtn = target.closest('.remove-from-cart-btn');

            if (addToCartBtn) {
                e.preventDefault();
                const productId = addToCartBtn.dataset.productId;
                const quantityInput = document.getElementById('quantity');
                const quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;
                sendCartRequest('/api/cart/add', { product_id: productId, quantity: quantity }, false, 'Đã thêm sản phẩm vào giỏ hàng!');
            }

            if (removeFromCartBtn) {
                e.preventDefault();
                const productId = removeFromCartBtn.dataset.productId;
                if (productId) {
                    sendCartRequest('/api/cart/remove', { product_id: productId });
                }
            }
        });

        // Lắng nghe sự kiện thay đổi số lượng và các sự kiện mới
        document.body.addEventListener('change', e => {
            const target = e.target;
            const quantityInput = target.closest('.cart-quantity-input');
            if (quantityInput) {
                const productId = quantityInput.closest('[data-product-id]').dataset.productId;
                const quantity = parseInt(quantityInput.value, 10);
                sendCartRequest('/api/cart/update', { product_id: productId, quantity: quantity });
            }
        });

        // SỬA ĐỔI: Lắng nghe sự kiện của bootstrap-select
        $('#delivery_service_id').on('changed.bs.select', function () {
            triggerSummaryUpdate();
        });

        // Khi bấm nút áp dụng mã giảm giá
        $('#apply-promo-btn').on('click', function () {
            triggerSummaryUpdate();
        });
    }
})();