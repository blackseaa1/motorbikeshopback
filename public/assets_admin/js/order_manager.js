/**
 * ===================================================================
 * order_manager.js
 * Xử lý JavaScript cho trang quản lý Đơn hàng.
 * ===================================================================
 */

// BƯỚC 1: Thêm một biến cờ (flag) để kiểm tra
if (typeof window.orderManagerInitialized === 'undefined') {
    window.orderManagerInitialized = false;
}

function initializeOrderManager() {
    // BƯỚC 2: Kiểm tra ngay khi bắt đầu hàm
    // Nếu đã khởi tạo rồi, thì thoát ngay lập tức
    if (window.orderManagerInitialized) {
        return;
    }

    const pageContainer = document.getElementById('adminOrdersPage');
    if (!pageContainer) return;

    console.log("Khởi tạo JS cho trang Quản lý Đơn hàng...");

    const allProducts = JSON.parse(pageContainer.dataset.products || '[]');
    const allPromotions = JSON.parse(pageContainer.dataset.promotions || '[]');
    const allCustomers = JSON.parse(pageContainer.dataset.customers || '[]');
    const allProvinces = JSON.parse(pageContainer.dataset.provinces || '[]');
    const allDeliveryServices = JSON.parse(pageContainer.dataset.deliveryServices || '[]');

    const hasValidationErrors = pageContainer.dataset.errors === 'true';
    const formMarker = pageContainer.dataset.formMarker || null;

    if (!Array.isArray(allProducts)) {
        console.error("Dữ liệu sản phẩm (data-products) không hợp lệ.", allProducts);
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // --- GLOBAL HELPERS (from admin_layout.js, ensuring they are available) ---
    // Make sure these functions are defined globally or passed into this scope if needed.
    // Assuming admin_layout.js is loaded before order_manager.js and defines these globally.
    const showAppLoader = window.showAppLoader;
    const hideAppLoader = window.hideAppLoader;
    const showAppInfoModal = window.showAppInfoModal;

    if (!showAppLoader || !hideAppLoader || !showAppInfoModal) {
        console.error('Các hàm trợ giúp toàn cục (showAppLoader, hideAppLoader, showAppInfoModal) chưa được định nghĩa.');
        return;
    }

    // --- CÁC BIẾN CHO MODAL TẠO & CẬP NHẬT ---
    let productItemIndex = 0; // Để theo dõi số lượng dòng sản phẩm trong modal tạo/cập nhật
    const removedItemIds = new Set(); // Để lưu ID các order_item bị xóa khi chỉnh sửa

    // ===================================================================
    // FUNCTIONS
    // ===================================================================

    /**
     * Định dạng số thành tiền tệ Việt Nam.
     * @param {number} amount
     * @returns {string}
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    /**
     * Thêm một dòng sản phẩm mới vào modal tạo/cập nhật.
     * @param {HTMLElement} container - Container để thêm dòng sản phẩm vào (e.g., product_items_container_modal, product_items_container_update)
     * @param {Array} products - Danh sách sản phẩm
     * @param {Object} itemData - Dữ liệu sản phẩm (optional)
     * @param {string} formIdentifier - Identifier của form (để xử lý lỗi validation)
     */
    function addProductItem(container, products, itemData = {}, formIdentifier = 'create_order_form') {
        const newIndex = productItemIndex++;
        const productOptions = products.map(p => `
            <option value="${p.id}" data-price="${p.price}" data-stock="${p.stock_quantity}" ${itemData.product_id == p.id ? 'selected' : ''}>
                ${p.name} (Tồn: ${p.stock_quantity})
            </option>
        `).join('');

        const orderItemIdInput = itemData.order_item_id ? `<input type="hidden" name="items[${newIndex}][order_item_id]" value="${itemData.order_item_id}">` : '';
        const quantityValue = itemData.quantity ?? 1;

        const rowHtml = `
            <div class="card card-body mb-2 product-item-row-modal" data-row-index="${newIndex}">
                <div class="row align-items-center">
                    ${orderItemIdInput}
                    <div class="col-md-5">
                        <label for="product_id_${newIndex}" class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker product-select ${hasValidationErrors && window.LaravelErrors && window.LaravelErrors[`items.${newIndex}.product_id`] ? 'is-invalid' : ''}"
                            data-live-search="true" id="product_id_${newIndex}" name="items[${newIndex}][product_id]" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            ${productOptions}
                        </select>
                        ${hasValidationErrors && window.LaravelErrors && window.LaravelErrors[`items.${newIndex}.product_id`] ? `<div class="text-danger mt-1">${window.LaravelErrors[`items.${newIndex}.product_id`][0]}</div>` : ''}
                    </div>
                    <div class="col-md-3">
                        <label for="quantity_${newIndex}" class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" class="form-control quantity-input ${hasValidationErrors && window.LaravelErrors && window.LaravelErrors[`items.${newIndex}.quantity`] ? 'is-invalid' : ''}"
                            id="quantity_${newIndex}" name="items[${newIndex}][quantity]" min="1" value="${quantityValue}" required>
                        ${hasValidationErrors && window.LaravelErrors && window.LaravelErrors[`items.${newIndex}.quantity`] ? `<div class="text-danger mt-1">${window.LaravelErrors[`items.${newIndex}.quantity`][0]}</div>` : ''}
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Thành tiền</label>
                        <p class="form-control-plaintext product-subtotal-display">
                            <span class="product-subtotal-value">0</span> ₫
                        </p>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-product-item" title="Xóa sản phẩm">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        const newRow = container.querySelector(`[data-row-index="${newIndex}"]`);
        const productSelect = newRow.querySelector('.product-select');
        const quantityInput = newRow.querySelector('.quantity-input');
        const removeButton = newRow.querySelector('.remove-product-item');

        // Initialize selectpicker for the new dropdown
        $(productSelect).selectpicker(); // Khởi tạo selectpicker cho phần tử mới

        // Attach event listeners for the new row
        productSelect.addEventListener('change', () => handleProductChange(newRow, allProducts, formIdentifier));
        quantityInput.addEventListener('input', () => handleQuantityChange(newRow, allProducts, formIdentifier));
        removeButton.addEventListener('click', () => removeProductItem(newRow, formIdentifier));

        // Calculate initial subtotal for the new row
        calculateRowSubtotal(newRow, allProducts);
        // Recalculate full order summary
        updateSummary(formIdentifier);
    }

    /**
     * Cập nhật thành tiền cho một dòng sản phẩm và tổng cộng của đơn hàng.
     * @param {HTMLElement} row - Dòng sản phẩm HTML element.
     * @param {Array} products - Danh sách sản phẩm.
     */
    function calculateRowSubtotal(row, products) {
        const productId = row.querySelector('.product-select').value;
        const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
        const product = products.find(p => p.id == productId);
        const price = product ? parseFloat(product.price) : 0;
        const subtotal = price * quantity;
        row.querySelector('.product-subtotal-value').textContent = new Intl.NumberFormat('vi-VN').format(subtotal);
    }

    /**
     * Xử lý khi sản phẩm trong một dòng được thay đổi.
     * @param {HTMLElement} row - Dòng sản phẩm HTML element.
     * @param {Array} products - Danh sách sản phẩm.
     * @param {string} formIdentifier - Identifier của form.
     */
    function handleProductChange(row, products, formIdentifier) {
        const productId = row.querySelector('.product-select').value;
        const product = products.find(p => p.id == productId);
        const quantityInput = row.querySelector('.quantity-input');

        // Cập nhật stock_quantity trong option của selectpicker nếu cần (đã có trong data-stock)
        // và reset số lượng về 1 khi đổi sản phẩm
        if (product) {
            quantityInput.value = 1;
            // Clear any previous validation errors for this row if product changed
            row.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            row.querySelectorAll('.text-danger.mt-1').forEach(el => el.remove());
        } else {
            quantityInput.value = 0;
        }

        calculateRowSubtotal(row, products);
        updateSummary(formIdentifier);
    }

    /**
     * Xử lý khi số lượng sản phẩm thay đổi.
     * @param {HTMLElement} row - Dòng sản phẩm HTML element.
     * @param {Array} products - Danh sách sản phẩm.
     * @param {string} formIdentifier - Identifier của form.
     */
    function handleQuantityChange(row, products, formIdentifier) {
        const productId = row.querySelector('.product-select').value;
        const quantityInput = row.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value) || 0;
        const product = products.find(p => p.id == productId);

        if (product && quantity > product.stock_quantity) {
            // Hiển thị cảnh báo hoặc tự động điều chỉnh số lượng
            // Ví dụ: quantityInput.value = product.stock_quantity;
            // showAppInfoModal(`Số lượng sản phẩm "${product.name}" trong kho chỉ còn ${product.stock_quantity}.`, 'warning');
        }

        calculateRowSubtotal(row, products);
        updateSummary(formIdentifier);
    }

    /**
     * Xóa một dòng sản phẩm khỏi modal.
     * @param {HTMLElement} row - Dòng sản phẩm HTML element.
     * @param {string} formIdentifier - Identifier của form.
     */
    function removeProductItem(row, formIdentifier) {
        const orderItemId = row.querySelector('input[name$="[order_item_id]"]')?.value;
        if (orderItemId) {
            // If it's an existing order item, add its ID to the removed items set
            removedItemIds.add(orderItemId);
            // Append a hidden input to the form to signal backend about removal
            const container = formIdentifier === 'create_order_form' ? document.getElementById('removed_items_container') : document.getElementById('removed_items_container_update');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'removed_items[]';
            hiddenInput.value = orderItemId;
            container.appendChild(hiddenInput);
        }
        row.remove();
        updateSummary(formIdentifier);
    }

    /**
     * Cập nhật tóm tắt đơn hàng (tổng phụ, phí vận chuyển, giảm giá, tổng cộng).
     * @param {string} formIdentifier - Identifier của form ('create_order_form' or 'update_order_form')
     */
    function updateSummary(formIdentifier) {
        let subtotal = 0;
        let shippingFee = 0;
        let discountAmount = 0;

        const productItemsContainer = formIdentifier === 'create_order_form' ?
            document.getElementById('product_items_container_modal') :
            document.getElementById('product_items_container_update');

        productItemsContainer.querySelectorAll('.product-item-row-modal').forEach(row => {
            const productId = row.querySelector('.product-select').value;
            const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
            const product = allProducts.find(p => p.id == productId);
            const price = product ? parseFloat(product.price) : 0;
            subtotal += price * quantity;
        });

        // Get shipping fee
        const deliveryServiceSelect = formIdentifier === 'create_order_form' ?
            document.getElementById('delivery_service_id') :
            document.getElementById('delivery_service_id_update');
        const selectedDeliveryServiceOption = deliveryServiceSelect.options[deliveryServiceSelect.selectedIndex];
        if (selectedDeliveryServiceOption) {
            shippingFee = parseFloat(selectedDeliveryServiceOption.dataset.shippingFee || 0);
        }

        // Get promotion discount
        const promotionSelect = formIdentifier === 'create_order_form' ?
            document.getElementById('promotion_id') :
            document.getElementById('promotion_id_update');
        const selectedPromotionOption = promotionSelect.options[promotionSelect.selectedIndex];
        let discountPercentage = 0;
        if (selectedPromotionOption && selectedPromotionOption.value) {
            discountPercentage = parseFloat(selectedPromotionOption.dataset.discountPercent || 0);
            discountAmount = subtotal * (discountPercentage / 100);
        }

        const grandTotal = Math.max(0, subtotal - discountAmount + shippingFee);

        // Update display elements
        const subtotalElem = formIdentifier === 'create_order_form' ? document.getElementById('create-order-subtotal') : document.getElementById('update-order-subtotal');
        const shippingFeeElem = formIdentifier === 'create_order_form' ? document.getElementById('create-order-shipping-fee') : document.getElementById('update-order-shipping-fee');
        const discountAmountElem = formIdentifier === 'create_order_form' ? document.getElementById('create-order-discount-amount') : document.getElementById('update-order-discount-amount');
        const discountRowElem = formIdentifier === 'create_order_form' ? document.getElementById('create-order-discount-row') : document.getElementById('update-order-discount-row');
        const grandTotalElem = formIdentifier === 'create_order_form' ? document.getElementById('create-order-grand-total') : document.getElementById('update-order-grand-total');

        subtotalElem.textContent = formatCurrency(subtotal);
        shippingFeeElem.textContent = formatCurrency(shippingFee);
        discountAmountElem.textContent = `-${formatCurrency(discountAmount)}`;

        if (discountAmount > 0) {
            discountRowElem.classList.remove('d-none');
        } else {
            discountRowElem.classList.add('d-none');
        }
        grandTotalElem.textContent = formatCurrency(grandTotal);
    }

    /**
     * Điền dữ liệu đơn hàng vào modal chỉnh sửa.
     * @param {Object} order - Dữ liệu đơn hàng.
     */
    async function fillUpdateModal(order) {
        const updateOrderModal = document.getElementById('updateOrderModal');
        const updateOrderForm = document.getElementById('updateOrderForm');

        updateOrderForm.action = `/admin/sales/orders/${order.id}`; // Cập nhật action URL
        document.getElementById('updateModalOrderIdStrong').textContent = order.id;

        // Reset form
        updateOrderForm.reset();
        removedItemIds.clear(); // Clear removed items set for new update session
        document.getElementById('removed_items_container_update').innerHTML = ''; // Clear hidden inputs

        // Fill customer and guest info
        const customerIdSelect = document.getElementById('customer_id_update');
        const guestInfoFields = document.getElementById('guest_info_fields_update');
        const customerAddressFields = document.getElementById('customer_address_fields_update');

        if (order.customer_id) {
            // Registered customer
            $(customerIdSelect).val(order.customer_id).selectpicker('refresh');
            guestInfoFields.classList.add('d-none');
            customerAddressFields.classList.remove('d-none');

            // Load customer addresses for the selected customer
            await populateCustomerAddresses(order.customer_id, document.getElementById('customer_shipping_address_id_update'), order.shipping_address_id); // Pass shipping_address_id if available on order
            // If order has a specific address_line, update the guest address fields for view purposes (even if guest fields are hidden)
            // Or just update the selected address in the dropdown.
            if (order.shipping_address_id) {
                $(document.getElementById('customer_shipping_address_id_update')).val(order.shipping_address_id).selectpicker('refresh');
            } else {
                // If no specific address_id is saved on order, use the order's explicit address fields
                // This might happen if the customer's default address was used but not linked by ID.
                document.getElementById('guest_name_update').value = order.guest_name || order.customer.name;
                document.getElementById('guest_email_update').value = order.guest_email || order.customer.email;
                document.getElementById('guest_phone_update').value = order.guest_phone || order.customer.phone;
                document.getElementById('shipping_address_line_update').value = order.shipping_address_line;

                await populateProvinces(document.getElementById('province_id_update'), order.province_id);
                if (order.province_id) await populateDistricts(order.province_id, document.getElementById('district_id_update'), order.district_id);
                if (order.district_id) await populateWards(order.district_id, document.getElementById('ward_id_update'), order.ward_id);

                guestInfoFields.classList.remove('d-none'); // Show guest fields as fallback for display
                customerAddressFields.classList.add('d-none');
            }
        } else {
            // Guest customer
            $(customerIdSelect).val('').selectpicker('refresh');
            guestInfoFields.classList.remove('d-none');
            customerAddressFields.classList.add('d-none');

            document.getElementById('guest_name_update').value = order.guest_name;
            document.getElementById('guest_email_update').value = order.guest_email;
            document.getElementById('guest_phone_update').value = order.guest_phone;
            document.getElementById('shipping_address_line_update').value = order.shipping_address_line;

            await populateProvinces(document.getElementById('province_id_update'), order.province_id);
            if (order.province_id) await populateDistricts(order.province_id, document.getElementById('district_id_update'), order.district_id);
            if (order.district_id) await populateWards(order.district_id, document.getElementById('ward_id_update'), order.ward_id);
        }

        // Fill other order details
        $(document.getElementById('status_update')).val(order.status).selectpicker('refresh');
        $(document.getElementById('payment_method_update')).val(order.payment_method).selectpicker('refresh');
        $(document.getElementById('delivery_service_id_update')).val(order.delivery_service_id).selectpicker('refresh');
        $(document.getElementById('promotion_id_update')).val(order.promotion_id || '').selectpicker('refresh');
        document.getElementById('notes_update').value = order.notes || '';

        // Fill order items
        const productItemsContainerUpdate = document.getElementById('product_items_container_update');
        productItemsContainerUpdate.innerHTML = ''; // Clear existing items
        productItemIndex = 0; // Reset index for update modal

        order.items.forEach(item => {
            addProductItem(productItemsContainerUpdate, allProducts, {
                product_id: item.product_id,
                quantity: item.quantity,
                order_item_id: item.id // Pass the order_item_id
            }, 'update_order_form');
        });

        // If there are no items, add an empty row
        if (order.items.length === 0) {
            addProductItem(productItemsContainerUpdate, allProducts, {}, 'update_order_form');
        }

        updateSummary('update_order_form'); // Update summary after filling all details
    }

    /**
     * Điền dữ liệu đơn hàng vào modal xem chi tiết.
     * @param {Object} order - Dữ liệu đơn hàng.
     */
    function fillViewModal(order) {
        document.getElementById('viewModalOrderIdStrong').textContent = order.id;

        // General Info
        document.getElementById('viewDetailOrderId').textContent = order.id;
        document.getElementById('viewDetailOrderCreatedAt').textContent = new Date(order.created_at).toLocaleString('vi-VN');
        const statusBadge = document.getElementById('viewDetailOrderStatusBadge');
        statusBadge.textContent = order.status_text;
        statusBadge.className = `badge ${order.status_badge_class}`;

        // Customer Info
        document.getElementById('viewDetailOrderCustomerType').textContent = order.customer_id ? 'Khách hàng đăng ký' : 'Khách hàng vãng lai';
        document.getElementById('viewDetailOrderCustomerName').textContent = order.customer_name;
        document.getElementById('viewDetailOrderCustomerEmail').textContent = order.guest_email || (order.customer ? order.customer.email : 'N/A');
        document.getElementById('viewDetailOrderCustomerPhone').textContent = order.guest_phone || (order.customer ? order.customer.phone : 'N/A');
        document.getElementById('viewDetailOrderCreatedBy').textContent = order.created_by_admin?.name || 'Hệ thống/Khách hàng';

        // Shipping Info
        document.getElementById('viewDetailOrderShippingAddress').textContent = order.shipping_address_full;
        document.getElementById('viewDetailOrderPaymentMethod').textContent = order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' :
            (order.payment_method === 'bank_transfer' ? 'Chuyển khoản ngân hàng' :
                (order.payment_method === 'online_payment' ? 'Thanh toán online' : order.payment_method));
        document.getElementById('viewDetailOrderDeliveryService').textContent = order.delivery_service?.name || 'N/A';
        document.getElementById('viewDetailOrderNotes').textContent = order.notes || 'Không có';

        // Order Items
        const orderItemsContainer = document.getElementById('viewDetailOrderItems');
        orderItemsContainer.innerHTML = '';
        order.items.forEach(item => {
            const row = `
                <tr>
                    <td>${item.product?.name || 'Sản phẩm đã xóa'}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">${formatCurrency(item.price)}</td>
                    <td class="text-end">${formatCurrency(item.subtotal)}</td>
                </tr>
            `;
            orderItemsContainer.insertAdjacentHTML('beforeend', row);
        });

        // Summary
        document.getElementById('viewDetailOrderSubtotal').textContent = formatCurrency(order.subtotal);
        document.getElementById('viewDetailOrderShippingFee').textContent = formatCurrency(order.shipping_fee);
        document.getElementById('viewDetailOrderDiscount').textContent = `-${formatCurrency(order.discount_amount)}`;
        const viewDetailDiscountRow = document.getElementById('view-detail-discount-row');
        if (order.promotion_id && order.discount_amount > 0) {
            viewDetailDiscountRow.classList.remove('d-none');
            document.getElementById('viewDetailOrderPromotionCode').textContent = `(${order.promotion?.code || 'N/A'})`;
        } else {
            viewDetailDiscountRow.classList.add('d-none');
            document.getElementById('viewDetailOrderPromotionCode').textContent = '';
        }
        document.getElementById('viewDetailOrderGrandTotal').textContent = formatCurrency(order.grand_total);


        // Fill print invoice area
        document.getElementById('print-order-id').textContent = order.id;
        document.getElementById('print-created-at').textContent = new Date(order.created_at).toLocaleString('vi-VN');
        document.getElementById('print-status').textContent = order.status_text;
        document.getElementById('print-customer-name').textContent = order.customer_name;
        document.getElementById('print-customer-email').textContent = order.guest_email || (order.customer ? order.customer.email : 'N/A');
        document.getElementById('print-customer-phone').textContent = order.guest_phone || (order.customer ? order.customer.phone : 'N/A');
        document.getElementById('print-shipping-address-full').textContent = order.shipping_address_full;
        document.getElementById('print-payment-method').textContent = document.getElementById('viewDetailOrderPaymentMethod').textContent; // Re-use formatted text
        document.getElementById('print-delivery-service').textContent = order.delivery_service?.name || 'N/A';
        document.getElementById('print-notes').textContent = order.notes || 'Không có';

        const printOrderItemsContainer = document.getElementById('print-order-items');
        printOrderItemsContainer.innerHTML = '';
        order.items.forEach(item => {
            const row = `
                <tr>
                    <td style="border: 1px solid #eee; padding: 8px;">${item.product?.name || 'Sản phẩm đã xóa'}</td>
                    <td style="border: 1px solid #eee; padding: 8px; text-align: center;">${item.quantity}</td>
                    <td style="border: 1px solid #eee; padding: 8px; text-align: right;">${formatCurrency(item.price)}</td>
                    <td style="border: 1px solid #eee; padding: 8px; text-align: right;">${formatCurrency(item.subtotal)}</td>
                </tr>
            `;
            printOrderItemsContainer.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('print-subtotal').textContent = formatCurrency(order.subtotal);
        document.getElementById('print-shipping').textContent = formatCurrency(order.shipping_fee);
        document.getElementById('print-discount').textContent = `-${formatCurrency(order.discount_amount)}`;
        const printDiscountRow = document.getElementById('print-discount-row');
        if (order.promotion_id && order.discount_amount > 0) {
            printDiscountRow.classList.remove('d-none');
            document.getElementById('print-promotion-code').textContent = order.promotion?.code || 'N/A';
        } else {
            printDiscountRow.classList.add('d-none');
            document.getElementById('print-promotion-code').textContent = '';
        }
        document.getElementById('print-grand-total').textContent = formatCurrency(order.grand_total);

    }

    /**
     * Tải danh sách quận/huyện dựa trên ID tỉnh/thành.
     * @param {number} provinceId
     * @param {HTMLElement} districtSelectElement
     * @param {number} selectedId
     */
    async function populateDistricts(provinceId, districtSelectElement, selectedId = null) {
        districtSelectElement.innerHTML = '<option value="">-- Đang tải Quận/Huyện --</option>';
        $(districtSelectElement).selectpicker('refresh');
        if (!provinceId) {
            districtSelectElement.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            $(districtSelectElement).selectpicker('refresh');
            return;
        }
        try {
            const response = await fetch(`/api/provinces/${provinceId}/districts`);
            const districts = await response.json();
            districtSelectElement.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            districts.forEach(district => {
                const option = new Option(district.name, district.id);
                districtSelectElement.add(option);
            });
            $(districtSelectElement).val(selectedId).selectpicker('refresh');
            $(districtSelectElement).trigger('changed.bs.select'); // Trigger change event for wards
        } catch (error) {
            console.error('Lỗi khi tải danh sách quận/huyện:', error);
            districtSelectElement.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            $(districtSelectElement).selectpicker('refresh');
        }
    }

    /**
     * Tải danh sách phường/xã dựa trên ID quận/huyện.
     * @param {number} districtId
     * @param {HTMLElement} wardSelectElement
     * @param {number} selectedId
     */
    async function populateWards(districtId, wardSelectElement, selectedId = null) {
        wardSelectElement.innerHTML = '<option value="">-- Đang tải Phường/Xã --</option>';
        $(wardSelectElement).selectpicker('refresh');
        if (!districtId) {
            wardSelectElement.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            $(wardSelectElement).selectpicker('refresh');
            return;
        }
        try {
            const response = await fetch(`/api/districts/${districtId}/wards`);
            const wards = await response.json();
            wardSelectElement.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wards.forEach(ward => {
                const option = new Option(ward.name, ward.id);
                wardSelectElement.add(option);
            });
            $(wardSelectElement).val(selectedId).selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi khi tải danh sách phường/xã:', error);
            wardSelectElement.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            $(wardSelectElement).selectpicker('refresh');
        }
    }

    /**
     * Điền các option cho select Tỉnh/Thành phố.
     * @param {HTMLElement} provinceSelectElement
     * @param {number} selectedId
     */
    function populateProvinces(provinceSelectElement, selectedId = null) {
        provinceSelectElement.innerHTML = '<option value="">-- Chọn Tỉnh/Thành --</option>';
        allProvinces.forEach(province => {
            const option = new Option(province.name, province.id);
            provinceSelectElement.add(option);
        });
        $(provinceSelectElement).val(selectedId).selectpicker('refresh');
    }

    /**
     * Điền các option cho select Khách hàng.
     * @param {HTMLElement} customerSelectElement
     * @param {number} selectedId
     */
    function populateCustomers(customerSelectElement, selectedId = null) {
        customerSelectElement.innerHTML = '<option value="">-- Khách hàng vãng lai --</option>';
        allCustomers.forEach(customer => {
            const option = new Option(`${customer.name} (${customer.email || customer.phone})`, customer.id);
            customerSelectElement.add(option);
        });
        $(customerSelectElement).val(selectedId).selectpicker('refresh');
    }

    /**
     * Tải và điền danh sách địa chỉ của một khách hàng.
     * @param {number} customerId
     * @param {HTMLElement} addressSelectElement
     * @param {number} selectedAddressId
     */
    async function populateCustomerAddresses(customerId, addressSelectElement, selectedAddressId = null) {
        addressSelectElement.innerHTML = '<option value="">-- Đang tải địa chỉ --</option>';
        $(addressSelectElement).selectpicker('refresh');

        if (!customerId) {
            addressSelectElement.innerHTML = '<option value="">-- Chọn địa chỉ --</option>';
            $(addressSelectElement).selectpicker('refresh');
            return;
        }

        try {
            const response = await fetch(`/api/customer/${customerId}/addresses`); // You might need to define this API route
            const addresses = await response.json();
            addressSelectElement.innerHTML = '<option value="">-- Chọn địa chỉ --</option>';
            addresses.forEach(address => {
                const option = new Option(`${address.address_line}, ${address.ward?.name}, ${address.district?.name}, ${address.province?.name}`, address.id);
                addressSelectElement.add(option);
            });
            $(addressSelectElement).val(selectedAddressId).selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi khi tải địa chỉ khách hàng:', error);
            addressSelectElement.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            $(addressSelectElement).selectpicker('refresh');
        }
    }


    /**
     * Xử lý hiển thị/ẩn thông tin khách vãng lai/địa chỉ khách hàng đăng ký.
     * @param {HTMLElement} customerSelect - Selectbox chọn khách hàng
     * @param {HTMLElement} guestFieldsContainer - Container chứa các trường khách vãng lai
     * @param {HTMLElement} customerAddressContainer - Container chứa select địa chỉ của khách hàng đăng ký
     * @param {HTMLElement} customerShippingAddressSelect - Selectbox chọn địa chỉ của khách hàng đăng ký
     * @param {string} formIdentifier - 'create_order_form' or 'update_order_form'
     */
    async function handleCustomerSelectChange(customerSelect, guestFieldsContainer, customerAddressContainer, customerShippingAddressSelect, formIdentifier) {
        const customerId = customerSelect.value;

        if (customerId) {
            guestFieldsContainer.classList.add('d-none');
            customerAddressContainer.classList.remove('d-none');
            await populateCustomerAddresses(customerId, customerShippingAddressSelect);
        } else {
            guestFieldsContainer.classList.remove('d-none');
            customerAddressContainer.classList.add('d-none');
            // Reset guest address fields when switching back to guest
            const provinceSelect = formIdentifier === 'create_order_form' ? document.getElementById('province_id') : document.getElementById('province_id_update');
            const districtSelect = formIdentifier === 'create_order_form' ? document.getElementById('district_id') : document.getElementById('district_id_update');
            const wardSelect = formIdentifier === 'create_order_form' ? document.getElementById('ward_id') : document.getElementById('ward_id_update');
            const shippingAddressLineInput = formIdentifier === 'create_order_form' ? document.getElementById('shipping_address_line') : document.getElementById('shipping_address_line_update');

            populateProvinces(provinceSelect);
            populateDistricts(null, districtSelect);
            populateWards(null, wardSelect);
            shippingAddressLineInput.value = '';
        }
    }


    /**
     * Hàm in hóa đơn.
     */
    function printOrder() {
        const orderViewContent = document.getElementById('order-view-content');
        const invoicePrintArea = document.getElementById('invoice-print-area');

        // Hide normal view, show print area
        orderViewContent.classList.add('d-none');
        invoicePrintArea.classList.remove('d-none');

        // Delay print to ensure DOM is ready for print
        setTimeout(() => {
            window.print();
            // Restore normal view after printing
            orderViewContent.classList.remove('d-none');
            invoicePrintArea.classList.add('d-none');
        }, 500);
    }

    /**
     * Xử lý lỗi validation từ Laravel và hiển thị trong modal.
     * @param {Object} errors - Đối tượng lỗi từ Laravel.
     * @param {string} formIdentifier - 'create_order_form' or 'update_order_form'
     */
    function displayValidationErrors(errors, formIdentifier) {
        // Clear previous errors
        document.querySelectorAll(`#${formIdentifier === 'create_order_form' ? 'createOrderModal' : 'updateOrderModal'} .is-invalid`).forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll(`#${formIdentifier === 'create_order_form' ? 'createOrderModal' : 'updateOrderModal'} .text-danger.mt-1`).forEach(el => el.remove());

        for (const [key, value] of Object.entries(errors)) {
            let inputElement = null;
            if (key.startsWith('items.')) {
                // Handle nested item errors
                const parts = key.split('.'); // e.g., items.0.product_id
                const index = parts[1];
                const field = parts[2];
                const row = document.querySelector(`#${formIdentifier === 'create_order_form' ? 'product_items_container_modal' : 'product_items_container_update'} [data-row-index="${index}"]`);
                if (row) {
                    if (field === 'product_id') {
                        inputElement = row.querySelector('.product-select');
                    } else if (field === 'quantity') {
                        inputElement = row.querySelector('.quantity-input');
                    }
                }
            } else {
                inputElement = document.getElementById(`${key}${formIdentifier === 'create_order_form' ? '' : '_update'}`);
            }

            if (inputElement) {
                inputElement.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.classList.add('text-danger', 'mt-1');
                errorDiv.textContent = value[0]; // First error message
                inputElement.parentNode.appendChild(errorDiv);

                // For selectpicker, also add is-invalid to the button
                if ($(inputElement).hasClass('selectpicker')) {
                    const button = $(inputElement).next('.bootstrap-select').find('button');
                    button.addClass('is-invalid');
                }
            }
        }
    }

    /**
     * Thiết lập các Event Listener.
     */
    function setupEventListeners() {
        // Init selectpickers
        $('.selectpicker').selectpicker();

        // --- CREATE ORDER MODAL ---
        const createOrderModal = document.getElementById('createOrderModal');
        const createOrderForm = document.getElementById('createOrderForm');
        const addProductItemBtn = document.getElementById('add_product_item_modal');
        const createProductItemsContainer = document.getElementById('product_items_container_modal');

        const createCustomerIdSelect = document.getElementById('customer_id');
        const createGuestInfoFields = document.getElementById('guest_info_fields');
        const createCustomerAddressFields = document.getElementById('customer_address_fields');
        const createCustomerShippingAddressSelect = document.getElementById('customer_shipping_address_id');

        const createProvinceSelect = document.getElementById('province_id');
        const createDistrictSelect = document.getElementById('district_id');
        const createWardSelect = document.getElementById('ward_id');

        // Populate initial data for create modal
        populateCustomers(createCustomerIdSelect);
        populateProvinces(createProvinceSelect);

        // Handle customer selection change (toggle guest/registered customer address fields)
        $(createCustomerIdSelect).on('changed.bs.select', function () {
            handleCustomerSelectChange(this, createGuestInfoFields, createCustomerAddressFields, createCustomerShippingAddressSelect, 'create_order_form');
        });

        // Handle province/district change for guest customer address
        $(createProvinceSelect).on('changed.bs.select', function () {
            populateDistricts(this.value, createDistrictSelect);
            populateWards(null, createWardSelect); // Clear wards when province changes
        });
        $(createDistrictSelect).on('changed.bs.select', function () {
            populateWards(this.value, createWardSelect);
        });

        addProductItemBtn.addEventListener('click', () => addProductItem(createProductItemsContainer, allProducts, {}, 'create_order_form'));

        // Handle changes within the create order form to update summary
        $(createOrderForm).on('change', '.product-select, .quantity-input', function () {
            const row = this.closest('.product-item-row-modal');
            if (row) {
                calculateRowSubtotal(row, allProducts);
            }
            updateSummary('create_order_form');
        });
        $(createOrderModal).on('changed.bs.select', '#delivery_service_id, #promotion_id', function () {
            updateSummary('create_order_form');
        });


        // Handle validation errors on page load for create modal
        if (hasValidationErrors && formMarker === 'create_order_form') {
            $('#createOrderModal').modal('show');
            displayValidationErrors(window.LaravelErrors, 'create_order_form');
            // Re-populate product items if there were old inputs for them
            if (oldInputs && oldInputs.items) {
                createProductItemsContainer.innerHTML = ''; // Clear initial empty row
                productItemIndex = 0; // Reset index for re-populating
                oldInputs.items.forEach((item, index) => {
                    addProductItem(createProductItemsContainer, allProducts, item, 'create_order_form');
                });
            }
            // Re-populate geography dropdowns if there were old inputs
            if (oldInputs.province_id) {
                populateProvinces(createProvinceSelect, oldInputs.province_id);
                if (oldInputs.district_id) {
                    populateDistricts(oldInputs.province_id, createDistrictSelect, oldInputs.district_id);
                    if (oldInputs.ward_id) {
                        populateWards(oldInputs.district_id, createWardSelect, oldInputs.ward_id);
                    }
                }
            }
            // Handle customer type selection persistence after validation error
            if (oldInputs.customer_id) {
                $(createCustomerIdSelect).val(oldInputs.customer_id).selectpicker('refresh');
                handleCustomerSelectChange(createCustomerIdSelect, createGuestInfoFields, createCustomerAddressFields, createCustomerShippingAddressSelect, 'create_order_form');
                if (oldInputs.shipping_address_id) {
                    populateCustomerAddresses(oldInputs.customer_id, createCustomerShippingAddressSelect, oldInputs.shipping_address_id);
                }
            } else {
                // For guest, ensure fields are visible
                createGuestInfoFields.classList.remove('d-none');
                createCustomerAddressFields.classList.add('d-none');
            }
            updateSummary('create_order_form'); // Final summary update for old inputs
        } else {
            // For initial load, always add one product item to create modal
            addProductItem(createProductItemsContainer, allProducts, {}, 'create_order_form');
        }


        // --- UPDATE ORDER MODAL ---
        const updateOrderModal = document.getElementById('updateOrderModal');
        const updateOrderForm = document.getElementById('updateOrderForm');
        const addProductItemUpdateBtn = document.getElementById('add_product_item_update_btn');
        const updateProductItemsContainer = document.getElementById('product_items_container_update');

        const updateCustomerIdSelect = document.getElementById('customer_id_update');
        const updateGuestInfoFields = document.getElementById('guest_info_fields_update');
        const updateCustomerAddressFields = document.getElementById('customer_address_fields_update');
        const updateCustomerShippingAddressSelect = document.getElementById('customer_shipping_address_id_update');

        const updateProvinceSelect = document.getElementById('province_id_update');
        const updateDistrictSelect = document.getElementById('district_id_update');
        const updateWardSelect = document.getElementById('ward_id_update');


        // Populate initial data for update modal dropdowns (customers, provinces)
        populateCustomers(updateCustomerIdSelect);
        populateProvinces(updateProvinceSelect);

        // Handle customer selection change (toggle guest/registered customer address fields)
        $(updateCustomerIdSelect).on('changed.bs.select', function () {
            handleCustomerSelectChange(this, updateGuestInfoFields, updateCustomerAddressFields, updateCustomerShippingAddressSelect, 'update_order_form');
        });

        // Handle province/district change for guest customer address in update modal
        $(updateProvinceSelect).on('changed.bs.select', function () {
            populateDistricts(this.value, updateDistrictSelect);
            populateWards(null, updateWardSelect);
        });
        $(updateDistrictSelect).on('changed.bs.select', function () {
            populateWards(this.value, updateWardSelect);
        });

        addProductItemUpdateBtn.addEventListener('click', () => addProductItem(updateProductItemsContainer, allProducts, {}, 'update_order_form'));

        // Handle changes within the update order form to update summary
        $(updateOrderForm).on('change', '.product-select, .quantity-input', function () {
            const row = this.closest('.product-item-row-modal');
            if (row) {
                calculateRowSubtotal(row, allProducts);
            }
            updateSummary('update_order_form');
        });
        $(updateOrderModal).on('changed.bs.select', '#delivery_service_id_update, #promotion_id_update', function () {
            updateSummary('update_order_form');
        });


        // Handle view button click
        document.querySelectorAll('.view-order-btn').forEach(button => {
            button.addEventListener('click', async function () {
                const orderId = this.dataset.id;
                const fetchUrl = this.dataset.viewUrl;
                showAppLoader();
                try {
                    const response = await fetch(fetchUrl);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    fillViewModal(data.order);
                    $('#viewOrderModal').modal('show');
                } catch (error) {
                    console.error('Error fetching order details:', error);
                    showAppInfoModal('Không thể tải chi tiết đơn hàng.', 'error');
                } finally {
                    hideAppLoader();
                }
            });
        });

        // Handle print button click
        document.getElementById('printOrderBtn').addEventListener('click', printOrder);

        // Handle edit from view modal button click
        document.getElementById('editOrderFromViewBtn').addEventListener('click', function () {
            const orderId = document.getElementById('viewDetailOrderId').textContent;
            // Fetch the order data again for safety and fill update modal
            const fetchUrl = `/admin/sales/orders/${orderId}`;
            showAppLoader();
            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    $('#viewOrderModal').modal('hide'); // Hide view modal first
                    fillUpdateModal(data.order);
                    $('#updateOrderModal').modal('show');
                })
                .catch(error => {
                    console.error('Error fetching order details for update:', error);
                    showAppInfoModal('Không thể tải dữ liệu để chỉnh sửa đơn hàng.', 'error');
                })
                .finally(() => {
                    hideAppLoader();
                });
        });

        // Handle update button click
        document.querySelectorAll('.update-order-btn').forEach(button => {
            button.addEventListener('click', async function () {
                const orderId = this.dataset.id;
                const fetchUrl = this.dataset.fetchUrl; // API endpoint to fetch order data
                showAppLoader();
                try {
                    const response = await fetch(fetchUrl);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    fillUpdateModal(data.order);
                    $('#updateOrderModal').modal('show');
                } catch (error) {
                    console.error('Error fetching order details for update:', error);
                    showAppInfoModal('Không thể tải dữ liệu đơn hàng để chỉnh sửa.', 'error');
                } finally {
                    hideAppLoader();
                }
            });
        });

        // Handle validation errors on page load for update modal
        if (hasValidationErrors && formMarker === 'update_order_form' && window.LaravelOldInputForUpdate) {
            $('#updateOrderModal').modal('show');
            displayValidationErrors(window.LaravelErrors, 'update_order_form');
            const oldOrder = window.LaravelOldInputForUpdate; // This would need to be passed from controller if validation fails
            // Re-fill update modal with old input values (this requires careful handling from Laravel backend)
            // For simplicity, we assume 'oldInputs' might contain enough info to reconstruct the state.
            // A more robust solution would be to pass the entire 'order' object back to the view
            // and reconstruct the modal using that data and the old input overrides.
            // For now, if validation fails, the user will see the errors but the previous data might not be fully restored
            // unless you have a mechanism to pass back the "original" order object + "old" inputs.
            // For now, just display errors.
        }

        // Handle delete button click
        document.querySelectorAll('.delete-order-btn').forEach(button => {
            button.addEventListener('click', function () {
                const orderId = this.dataset.id;
                const orderName = this.dataset.name;
                const deleteUrl = this.dataset.deleteUrl;

                document.getElementById('deleteOrderName').textContent = orderName;
                const deleteForm = document.getElementById('deleteOrderForm');
                deleteForm.action = deleteUrl;

                // Clear password input and errors on modal open
                const adminPasswordInput = document.getElementById('adminPasswordDeleteOrder');
                if (adminPasswordInput) {
                    adminPasswordInput.value = '';
                    adminPasswordInput.classList.remove('is-invalid');
                    const errorDiv = adminPasswordInput.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('text-danger')) {
                        errorDiv.remove();
                    }
                }
            });
        });

        // Handle approve order button click
        document.querySelectorAll('.approve-order-btn').forEach(button => {
            button.addEventListener('click', async function () {
                const orderId = this.dataset.id;
                const orderName = this.dataset.name;
                const approveUrl = this.dataset.approveUrl;

                if (confirm(`Bạn có chắc chắn muốn DUYỆT ${orderName} không?`)) {
                    showAppLoader();
                    try {
                        const response = await fetch(approveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            showAppInfoModal(data.message, 'success');
                            // Reload page or update table row
                            window.location.reload();
                        } else {
                            showAppInfoModal(data.message || 'Có lỗi xảy ra khi duyệt đơn hàng.', 'error');
                            if (data.errors) {
                                console.error('Validation errors:', data.errors);
                            }
                        }
                    } catch (error) {
                        console.error('Lỗi duyệt đơn hàng:', error);
                        showAppInfoModal('Lỗi kết nối máy chủ hoặc lỗi không xác định khi duyệt đơn hàng.', 'error');
                    } finally {
                        hideAppLoader();
                    }
                }
            });
        });

        // Re-initialize selectpickers when modals are shown (important for dynamic content)
        $('#createOrderModal').on('shown.bs.modal', function () {
            $('.selectpicker', this).selectpicker('refresh');
            updateSummary('create_order_form'); // Initial summary calculation when modal opens
        });

        $('#updateOrderModal').on('shown.bs.modal', function () {
            $('.selectpicker', this).selectpicker('refresh');
            updateSummary('update_order_form'); // Initial summary calculation when modal opens
        });

        // Handle case where form has errors on page load and was for update modal
        if (hasValidationErrors && formMarker === 'update_order_form') {
            const originalOrderId = pageContainer.dataset.originalOrderId; // You need to pass this from controller
            if (originalOrderId) {
                // Re-fetch the original order data and then overlay with old inputs
                fetch(`/admin/sales/orders/${originalOrderId}`)
                    .then(response => response.json())
                    .then(data => {
                        const order = data.order;
                        // Populate modal with original order data first
                        fillUpdateModal(order);
                        // Then override with old input values
                        const oldInputs = window.LaravelOldInputForUpdate; // This would need to be a global variable or passed via data-attribute
                        if (oldInputs) {
                            // Example: fill some basic fields
                            document.getElementById('guest_name_update').value = oldInputs.guest_name || '';
                            document.getElementById('guest_email_update').value = oldInputs.guest_email || '';
                            document.getElementById('guest_phone_update').value = oldInputs.guest_phone || '';
                            document.getElementById('shipping_address_line_update').value = oldInputs.shipping_address_line || '';
                            document.getElementById('notes_update').value = oldInputs.notes || '';

                            // Update selectpickers
                            $(document.getElementById('status_update')).val(oldInputs.status).selectpicker('refresh');
                            $(document.getElementById('payment_method_update')).val(oldInputs.payment_method).selectpicker('refresh');
                            $(document.getElementById('delivery_service_id_update')).val(oldInputs.delivery_service_id).selectpicker('refresh');
                            $(document.getElementById('promotion_id_update')).val(oldInputs.promotion_id || '').selectpicker('refresh');

                            // Re-handle customer type selection and addresses if customer_id was changed
                            if (oldInputs.customer_id) {
                                $(updateCustomerIdSelect).val(oldInputs.customer_id).selectpicker('refresh');
                                handleCustomerSelectChange(updateCustomerIdSelect, updateGuestInfoFields, updateCustomerAddressFields, updateCustomerShippingAddressSelect, 'update_order_form');
                                if (oldInputs.shipping_address_id) {
                                    populateCustomerAddresses(oldInputs.customer_id, updateCustomerShippingAddressSelect, oldInputs.shipping_address_id);
                                }
                            } else {
                                // For guest, ensure fields are visible
                                updateGuestInfoFields.classList.remove('d-none');
                                updateCustomerAddressFields.classList.add('d-none');
                                if (oldInputs.province_id) {
                                    populateProvinces(updateProvinceSelect, oldInputs.province_id);
                                    if (oldInputs.district_id) {
                                        populateDistricts(oldInputs.province_id, updateDistrictSelect, oldInputs.district_id);
                                        if (oldInputs.ward_id) {
                                            populateWards(oldInputs.district_id, updateWardSelect, oldInputs.ward_id);
                                        }
                                    }
                                }
                            }

                            // Reconstruct product items from old input
                            if (oldInputs.items) {
                                updateProductItemsContainer.innerHTML = '';
                                productItemIndex = 0;
                                oldInputs.items.forEach((item) => {
                                    addProductItem(updateProductItemsContainer, allProducts, item, 'update_order_form');
                                });
                            }
                            updateSummary('update_order_form'); // Final summary update for old inputs
                        }
                        displayValidationErrors(window.LaravelErrors, 'update_order_form');
                        $('#updateOrderModal').modal('show');
                    })
                    .catch(error => console.error('Error re-filling update modal after validation failure:', error));
            }
        }
    }

    // --- KHỞI CHẠY ---
    setupEventListeners();

    // BƯỚC 3: Đặt cờ thành true ở cuối hàm để đánh dấu đã khởi tạo
    window.orderManagerInitialized = true;

    console.log("JS cho quản lý Đơn hàng đã được khởi tạo thành công.");
}


// Gọi hàm chính
document.addEventListener('DOMContentLoaded', initializeOrderManager);

// Re-run initialization on Turbo/Turbolinks load if used
document.addEventListener('turbo:load', function () {
    // Đảm bảo initializeOrderManager chỉ chạy một lần duy nhất
    // trong suốt vòng đời của Turbo Drive hoặc DOMContentLoaded.
    // Loại bỏ việc reset cờ để không khởi tạo lại quá nhiều.
    initializeOrderManager();
});