/**
 * =================================================================================
 * order_manager.js - PHIÊN BẢN HOÀN CHỈNH (SINGLE MODAL)
 * ---------------------------------------------------------------------------------
 * - Script này quản lý toàn bộ chức năng trang đơn hàng trong một modal duy nhất.
 * - Loại bỏ quy trình 2 modal, tích hợp phần chọn sản phẩm có hình ảnh trực tiếp.
 * - Tự động cập nhật tổng tiền mỗi khi có thay đổi về sản phẩm hoặc vận chuyển.
 * - Tương thích với: jQuery, Bootstrap 5, Bootstrap Select.
 * =================================================================================
 */
window.initializeOrderManager = (
    showAppLoader,
    hideAppLoader,
    showAppInfoModal,
    setupAjaxForm,
    displayValidationErrors,
    clearValidationErrors
) => {
    'use strict';

    // =========================================================================
    // A. UTILITIES & DOM CACHING
    // =========================================================================
    const formatCurrency = (value) => {
        if (isNaN(value)) return '0 ₫';
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    };

    const $createModal = $('#createOrderModal');
    const $updateModal = $('#updateOrderModal');
    const $deleteModal = $('#deleteOrderModal');
    const $viewModal = $('#viewOrderModal');
    const $createForm = $('#createOrderForm');
    const $customerSelect = $('#customer_id_create');
    const $productItemsContainer = $('#product-items-container');

    // =========================================================================
    // B. MAIN INITIALIZATION
    // =========================================================================
    function initialize() {
        if (!$createModal.length) return;
        initializeCreateModal();
        initializeUpdateModal();
        initializeViewModal();
        initializeDeleteModal();
    }

    // =========================================================================
    // C. CREATE ORDER MODAL LOGIC (Single Modal Flow)
    // =========================================================================
    function initializeCreateModal() {
        // Sự kiện chính
        $createModal.on('show.bs.modal', resetCreateForm);
        $('#add-product-row-btn').on('click', addProductRow);

        // Event Delegation cho các hành động trên dòng sản phẩm
        $productItemsContainer.on('click', '.remove-product-item', handleRemoveRow);
        $productItemsContainer.on('change', '.product-select', handleProductSelectChange);
        $productItemsContainer.on('change keyup', '.quantity-input', handleQuantityOrPriceChange);

        // Listener cho các thành phần khác của form
        $createForm.find('input[name="customer_type"]').on('change', handleCustomerTypeChange);
        $customerSelect.on('changed.bs.select', (e) => handleCustomerSelect($(e.currentTarget).val()));
        $('#btn-show-new-address-form').on('click', () => toggleNewAddressForm(true));
        $('#btn-cancel-new-address').on('click', () => toggleNewAddressForm(false));
        setupDependentDropdowns('new_province_id', 'new_district_id', 'new_ward_id');
        $('#delivery_service_id_create, #promotion_id_create').on('change', updateOrderSummary);

        setupAjaxForm('createOrderForm', 'createOrderModal');
    }

    function resetCreateForm() {
        clearValidationErrors($createForm[0]);
        $createForm[0].reset();
        $customerSelect.selectpicker('val', '');
        $('#customerTypeExisting').prop('checked', true).trigger('change');
        $productItemsContainer.empty();
        addProductRow(); // Luôn có ít nhất một dòng khi mở modal
        updateOrderSummary();
    }

    function addProductRow() {
        const rowIndex = Date.now(); // Dùng timestamp để đảm bảo index luôn là duy nhất
        const newRowHtml = document.getElementById('product-row-template').innerHTML.replace(/NEW_ROW_INDEX/g, rowIndex);
        $productItemsContainer.append(newRowHtml);
        // Khởi tạo Bootstrap Select cho dòng mới
        $productItemsContainer.find(`[data-row-index="${rowIndex}"] .selectpicker`).selectpicker('render');
    }

    function handleRemoveRow(event) {
        $(event.currentTarget).closest('.product-item-row').remove();
        updateOrderSummary(); // Tính lại tổng tiền sau khi xóa
    }

    function handleProductSelectChange(event) {
        const $select = $(event.currentTarget);
        const $row = $select.closest('.product-item-row');
        const $selectedOption = $select.find('option:selected');
        const $image = $row.find('.product-image');
        
        const $quantityInput = $row.find('.quantity-input');

        const imageUrl = $selectedOption.data('image-url');
        const stock = parseInt($selectedOption.data('stock')) || 0;

        $image.attr('src', imageUrl);
        $quantityInput.attr('max', stock);

        // Nếu số lượng hiện tại lớn hơn tồn kho mới, điều chỉnh lại
        if (parseInt($quantityInput.val()) > stock) {
            $quantityInput.val(stock);
        }

        updateRowSubtotal($select);
    }

    function handleQuantityOrPriceChange(event) {
        updateRowSubtotal($(event.currentTarget));
    }

    function updateRowSubtotal($elementInRow) {
        const $row = $elementInRow.closest('.product-item-row');
        const $selectedOption = $row.find('.product-select option:selected');
        const quantity = parseInt($row.find('.quantity-input').val()) || 0;
        const price = parseFloat($selectedOption.data('price')) || 0;
        const subtotal = price * quantity;
        $row.find('.product-subtotal-value').text(formatCurrency(subtotal));
        // Sau khi cập nhật thành tiền của dòng, cập nhật tổng tiền của cả đơn hàng
        updateOrderSummary();
    }

    function updateOrderSummary() {
        let currentOrderItems = [];
        let subtotal = 0;

        // Đọc trực tiếp từ DOM để xây dựng state sản phẩm
        $productItemsContainer.find('.product-item-row').each(function () {
            const $row = $(this);
            const $productSelect = $row.find('.product-select');
            const $quantityInput = $row.find('.quantity-input');
            const $selectedOption = $productSelect.find('option:selected');
            const productId = $productSelect.val();
            const quantity = parseInt($quantityInput.val());

            if (productId && quantity > 0) {
                const price = parseFloat($selectedOption.data('price')) || 0;
                subtotal += price * quantity;
                currentOrderItems.push({ id: productId, quantity: quantity });
            }
        });

        // Đồng bộ state vừa đọc vào các input ẩn để gửi đi
        $createForm.find('input[name^="items["]').remove();
        currentOrderItems.forEach((item, index) => {
            $createForm.append(`<input type="hidden" name="items[${index}][product_id]" value="${item.id}">`);
            $createForm.append(`<input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`);
        });

        const deliverySelect = document.getElementById('delivery_service_id_create');
        const shippingFee = parseFloat(deliverySelect.options[deliverySelect.selectedIndex]?.dataset.fee || 0);

        const promoSelect = document.getElementById('promotion_id_create');
        const promoOption = promoSelect.options[promoSelect.selectedIndex];
        let discount = 0;
        if (promoOption && promoOption.value) {
            const promoType = promoOption.dataset.type;
            const promoValue = parseFloat(promoOption.dataset.value);
            if (promoType === 'percentage') discount = (subtotal * promoValue) / 100;
            else discount = promoValue;
        }

        const grandTotal = subtotal + shippingFee - discount;

        $('#summary-subtotal').text(formatCurrency(subtotal));
        $('#summary-shipping').text(formatCurrency(shippingFee));
        $('#summary-discount').text(`-${formatCurrency(discount)}`);
        $('#summary-grand-total').text(formatCurrency(grandTotal > 0 ? grandTotal : 0));
    }


    // =========================================================================
    // D. OTHER MODALS & HELPER FUNCTIONS (Hoàn chỉnh)
    // =========================================================================

    function handleCustomerTypeChange() {
        const isExisting = document.getElementById('customerTypeExisting').checked;
        $('#existingCustomerBlock').toggle(isExisting);
        $('#existingAddressBlock').toggle(isExisting);
        $('#newAddressBlock').toggleClass('d-none', isExisting);
        $('#newAddressBlock .guest-only-field').toggle(!isExisting);
        document.getElementById('shipping_address_option_create').value = isExisting ? 'existing' : 'new';

        if (isExisting) {
            toggleNewAddressForm(false);
            $('#addressListContainer').html('<p class="text-muted">Vui lòng chọn khách hàng để xem địa chỉ.</p>');
        } else {
            $('#guest_name').on('input', function () { $('#new_shipping_name').val($(this).val()); });
            $('#guest_phone').on('input', function () { $('#new_shipping_phone').val($(this).val()); });
        }
    }

    async function handleCustomerSelect(customerId) {
        const $addressContainer = $('#addressListContainer');
        $addressContainer.html('<p class="text-muted">Đang tải địa chỉ...</p>');
        toggleNewAddressForm(false);

        if (!customerId) {
            $addressContainer.html('<p class="text-muted">Vui lòng chọn khách hàng để xem địa chỉ.</p>');
            return;
        }
        try {
            const response = await fetch(`/admin/api/customers/${customerId}/addresses`);
            if (!response.ok) throw new Error('Không thể tải địa chỉ.');
            const addresses = await response.json();
            renderAddressList(addresses);
        } catch (error) {
            $addressContainer.html(`<p class="text-danger">${error.message}</p>`);
        }
    }

    function renderAddressList(addresses) {
        const $container = $('#addressListContainer');
        if (!addresses || addresses.length === 0) {
            $container.html('<p class="text-muted">Khách hàng này chưa có địa chỉ. Vui lòng thêm địa chỉ mới.</p>');
            toggleNewAddressForm(true);
            return;
        }
        const addressesHtml = addresses.map((addr, index) => `
            <div class="form-check address-item">
                <input class="form-check-input" type="radio" name="shipping_address_id" id="addr_${addr.id}" value="${addr.id}" ${addr.is_default || index === 0 ? 'checked' : ''}>
                <label class="form-check-label" for="addr_${addr.id}">
                    <strong>${addr.full_name}</strong> - ${addr.phone} ${addr.is_default ? '<span class="badge bg-success ms-1">Mặc định</span>' : ''}<br>
                    <small>${addr.address_line}, ${addr.ward.name}, ${addr.district.name}, ${addr.province.name}</small>
                </label>
            </div>`).join('');
        $container.html(addressesHtml);
    }

    function toggleNewAddressForm(show) {
        $('#newAddressBlock').toggleClass('d-none', !show);
        $('#existingAddressBlock').toggle(!show);
        document.getElementById('shipping_address_option_create').value = show ? 'new' : 'existing';
    }

    function initializeUpdateModal() {
        $updateModal.on('show.bs.modal', async (event) => {
            const orderId = event.relatedTarget.dataset.id;
            document.getElementById('updateOrderForm').action = `/admin/sales/orders/${orderId}`;
            $('#update_order_id').val(orderId);
            $('#update-order-id').text(orderId);
            showAppLoader();
            try {
                const response = await fetch(`/admin/sales/orders/${orderId}`);
                if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng.');
                const order = await response.json();
                populateUpdateForm(order);
            } catch (error) {
                showAppInfoModal(error.message, 'error');
                $updateModal.modal('hide');
            } finally {
                hideAppLoader();
            }
        });
        setupAjaxForm('updateOrderForm', 'updateOrderModal');
    }

    function populateUpdateForm(order) {
        $('#update_order_status').val(order.status);
        $('#update_delivery_service_id').selectpicker('val', order.delivery_service_id);
        $('#update_notes').val(order.notes || '');
        const customerInfo = order.customer ? `KH: ${order.customer.name} (#${order.customer.id})` : `Khách vãng lai: ${order.shipping_name}`;
        $('#update-customer-info').text(customerInfo);
        const address = `${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}`;
        $('#update-shipping-address').text(address);
        $('#update-order-subtotal').text(formatCurrency(order.subtotal));
        $('#update-order-shipping-fee').text(formatCurrency(order.shipping_fee));
        $('#update-order-discount').text(`-${formatCurrency(order.discount_amount)}`);
        $('#update-order-grand-total').text(formatCurrency(order.total_price));
    }

    function initializeViewModal() {
        $viewModal.on('show.bs.modal', async (event) => {
            const orderId = event.relatedTarget.dataset.id;
            $('#viewModalOrderIdStrong').text(orderId);
            showAppLoader();
            try {
                const response = await fetch(`/admin/sales/orders/${orderId}`);
                if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng.');
                const order = await response.json();
                populateViewModal(order);
            } catch (error) {
                showAppInfoModal(error.message, 'error');
                $viewModal.modal('hide');
            } finally {
                hideAppLoader();
            }
        });
        $('#editOrderFromViewBtn').on('click', () => {
            const orderId = $('#viewModalOrderIdStrong').text();
            $(`.update-order-btn[data-id="${orderId}"]`).trigger('click');
            $viewModal.modal('hide');
        });
    }

    function populateViewModal(order) {
        $('#viewDetailOrderId').text(`#${order.id}`);
        $('#viewDetailOrderCreatedAt').text(new Date(order.created_at).toLocaleString('vi-VN'));
        $('#viewDetailOrderStatusBadge').html(`<span class="badge ${order.status_badge_class}">${order.status_text}</span>`);
        $('#viewDetailCustomerType').text(order.customer_id ? 'Khách hàng có tài khoản' : 'Khách vãng lai');
        $('#viewDetailCustomerName').text(order.shipping_name);
        $('#viewDetailCustomerPhone').text(order.shipping_phone);
        $('#viewDetailCustomerEmail').text(order.shipping_email || 'N/A');
        $('#viewDetailOrderFullAddress').text(`${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}`);
        $('#viewDetailOrderPaymentMethod').text(order.payment_method.toUpperCase());
        $('#viewDetailOrderDeliveryService').text(order.delivery_service.name);
        $('#viewDetailOrderPromotionCode').text(order.promotion ? `${order.promotion.code}` : 'Không có');
        $('#viewDetailOrderNotes').text(order.notes || 'Không có ghi chú');
        $('#viewDetailOrderCreatedByAdmin').text(order.created_by_admin ? order.created_by_admin.name : 'Khách hàng tự đặt');
        const itemsHtml = order.items.map(item => `
            <tr>
                <td>${item.product.name}</td>
                <td>${item.quantity}</td>
                <td>${formatCurrency(item.price)}</td>
                <td class="text-end">${formatCurrency(item.price * item.quantity)}</td>
            </tr>`).join('');
        $('#viewOrderItemsBody').html(itemsHtml);
        $('#viewOrderSubtotal').text(formatCurrency(order.subtotal));
        $('#viewOrderShippingFee').text(formatCurrency(order.shipping_fee));
        $('#viewOrderDiscount').text(`-${formatCurrency(order.discount_amount)}`);
        $('#viewOrderGrandTotal').text(formatCurrency(order.total_price));
    }

    function initializeDeleteModal() {
        $deleteModal.on('show.bs.modal', (event) => {
            const orderId = event.relatedTarget.dataset.id;
            document.getElementById('deleteOrderForm').action = `/admin/sales/orders/${orderId}`;
            $('#delete-order-id').text(orderId);
        });
        setupAjaxForm('deleteOrderForm', 'deleteOrderModal');
    }

    function setupDependentDropdowns(provinceId, districtId, wardId) {
        const $province = $(`#${provinceId}`);
        const $district = $(`#${districtId}`);
        const $ward = $(`#${wardId}`);
        $province.on('change', async function () {
            const province = $(this).val();
            $district.prop('disabled', true).html('<option value="">Đang tải...</option>');
            $ward.prop('disabled', true).html('<option value="">Chọn Phường/Xã</option>');
            if (!province) { $district.html('<option value="">Chọn Quận/Huyện</option>'); return; }
            const response = await fetch(`/api/provinces/${province}/districts`);
            const districts = await response.json();
            $district.html('<option value="">Chọn Quận/Huyện</option>');
            districts.forEach(d => $district.append(new Option(d.name, d.id)));
            $district.prop('disabled', false);
        });
        $district.on('change', async function () {
            const district = $(this).val();
            $ward.prop('disabled', true).html('<option value="">Đang tải...</option>');
            if (!district) { $ward.html('<option value="">Chọn Phường/Xã</option>'); return; }
            const response = await fetch(`/api/districts/${district}/wards`);
            const wards = await response.json();
            $ward.html('<option value="">Chọn Phường/Xã</option>');
            wards.forEach(w => $ward.append(new Option(w.name, w.id)));
            $ward.prop('disabled', false);
        });
    }

    // =========================================================================
    // F. KICKSTART THE SCRIPT
    // =========================================================================
    initialize();
};