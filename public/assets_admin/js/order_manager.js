/**
 * =================================================================================
 * order_manager.js - PHIÊN BẢN SỬA LỖI HOÀN CHỈNH
 * ---------------------------------------------------------------------------------
 * - Tái cấu trúc luồng tính toán để loại bỏ lỗi đồng bộ hóa (race condition).
 * - Sử dụng một hàm tính toán tổng thể duy nhất (calculateAndUpdateAll).
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

    // A. UTILITIES & DOM CACHING
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

    // B. MAIN INITIALIZATION
    function initialize() {
        if (!$('#adminOrdersPage').length) return;
        initializeCreateModal();
        initializeUpdateModal();
        initializeViewModal();
        initializeDeleteModal();
    }

    // C. CREATE ORDER MODAL LOGIC
    function initializeCreateModal() {
        $createModal.on('show.bs.modal', resetCreateForm);
        $('#add-product-row-btn').on('click', addProductRow);

        // Gán các sự kiện
        $productItemsContainer.on('click', '.remove-product-item', handleRemoveRow);
        $productItemsContainer.on('change', '.product-select', handleProductSelectChange);
        $productItemsContainer.on('change keyup', '.quantity-input', () => calculateAndUpdateAll()); // Chỉ gọi hàm tính toán tổng

        $createForm.find('input[name="customer_type"]').on('change', handleCustomerTypeChange);
        $customerSelect.on('changed.bs.select', (e) => handleCustomerSelect($(e.currentTarget).val()));
        $('#btn-show-new-address-form').on('click', () => toggleNewAddressForm(true));
        $('#btn-cancel-new-address').on('click', () => toggleNewAddressForm(false));
        setupDependentDropdowns('new_province_id', 'new_district_id', 'new_ward_id');

        // Các dropdown này cũng sẽ gọi hàm tính toán tổng
        $('#delivery_service_id_create, #promotion_id_create').on('change', () => calculateAndUpdateAll());

        setupAjaxForm('createOrderForm', 'createOrderModal');
    }

    function resetCreateForm() {
        clearValidationErrors($createForm[0]);
        $createForm[0].reset();
        $customerSelect.selectpicker('val', '');
        $('#customerTypeExisting').prop('checked', true).trigger('change');
        $productItemsContainer.empty();
        addProductRow();
        calculateAndUpdateAll();
    }

    function addProductRow() {
        const rowIndex = Date.now();
        const template = document.getElementById('product-row-template');
        if (!template) return;

        const newRowHtml = template.innerHTML.replace(/NEW_ROW_INDEX/g, rowIndex);
        $productItemsContainer.append(newRowHtml);

        const newSelect = $productItemsContainer.find(`[data-row-index="${rowIndex}"] .selectpicker`);
        if (newSelect.length && typeof $.fn.selectpicker === 'function') {
            newSelect.selectpicker('render');
        }
    }

    function handleRemoveRow(event) {
        $(event.currentTarget).closest('.product-item-row').remove();
        calculateAndUpdateAll();
    }

    function handleProductSelectChange(event) {
        const $select = $(event.currentTarget);
        const $row = $select.closest('.product-item-row');
        const $selectedOption = $select.find('option:selected');
        const imageUrl = $selectedOption.data('image-url') || "{{ asset('assets_admin/images/no-image.png') }}";
        const stock = parseInt($selectedOption.data('stock')) || 0;
        const $quantityInput = $row.find('.quantity-input');

        $row.find('.product-image').attr('src', imageUrl);
        $quantityInput.attr('max', stock);

        // Logic quan trọng: Tự đặt lại số lượng
        if (parseInt($quantityInput.val()) < 1 || isNaN(parseInt($quantityInput.val()))) {
            $quantityInput.val(1);
        }
        if (parseInt($quantityInput.val()) > stock) {
            $quantityInput.val(stock);
        }

        // Luôn gọi hàm tính toán tổng thể sau mỗi thay đổi
        calculateAndUpdateAll();
    }

    /**
     * [HÀM MỚI] Đây là hàm tính toán tổng thể duy nhất, là nguồn chân lý.
     * Nó quét toàn bộ form, tính toán lại mọi thứ và cập nhật giao diện.
     */
    /**
    * [HÀM ĐÃ SỬA LỖI] Đây là hàm tính toán tổng thể duy nhất.
    * Đã loại bỏ hoàn toàn logic xóa và tạo input ẩn nguy hiểm.
    */
    function calculateAndUpdateAll() {
        let orderSubtotal = 0;

        // 1. Quét qua từng dòng sản phẩm để TÍNH TOÁN và CẬP NHẬT GIAO DIỆN
        $productItemsContainer.find('.product-item-row').each(function () {
            const $row = $(this);
            const $productSelect = $row.find('.product-select');
            const $quantityInput = $row.find('.quantity-input');
            const $selectedOption = $productSelect.find('option:selected');

            const productId = $productSelect.val();
            const quantity = parseInt($quantityInput.val()) || 0;
            const price = parseFloat($selectedOption.data('price')) || 0;

            // Tính thành tiền cho từng dòng và cập nhật giao diện của dòng đó
            const rowSubtotal = price * quantity;
            $row.find('.product-subtotal-value').text(formatCurrency(rowSubtotal));

            // Nếu dòng này hợp lệ, cộng vào tổng
            if (productId && quantity > 0) {
                orderSubtotal += rowSubtotal;
            }
        });

        // 2. Tính toán Phí vận chuyển, Khuyến mãi
        const deliverySelect = document.getElementById('delivery_service_id_create');
        const shippingFee = parseFloat(deliverySelect.options[deliverySelect.selectedIndex]?.dataset.fee || 0);

        const promoSelect = document.getElementById('promotion_id_create');
        const promoOption = promoSelect.options[promoSelect.selectedIndex];
        let discount = 0;
        if (promoOption && promoOption.value) {
            const promoType = promoOption.dataset.type;
            const promoValue = parseFloat(promoOption.dataset.value) || 0;
            if (promoType === 'percentage') {
                discount = (orderSubtotal * promoValue) / 100;
            } else {
                discount = promoValue;
            }
        }
        if (isNaN(discount)) discount = 0;

        // 3. Tính tổng cuối cùng
        const grandTotal = orderSubtotal + shippingFee - discount;

        // 4. Cập nhật giao diện tóm tắt đơn hàng
        $('#summary-subtotal').text(formatCurrency(orderSubtotal));
        $('#summary-shipping').text(formatCurrency(shippingFee));
        $('#summary-discount').text(`-${formatCurrency(discount)}`);
        $('#summary-grand-total').text(formatCurrency(grandTotal > 0 ? grandTotal : 0));
    }

    // D. CÁC HÀM KHÁC (GIỮ NGUYÊN)
    // Các hàm này không thay đổi so với phiên bản trước
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
        let currentOrderData = null;
        $viewModal.on('show.bs.modal', async (event) => {
            const orderId = event.relatedTarget.dataset.id;
            $('#viewModalOrderIdStrong').text(orderId);
            showAppLoader();
            try {
                const response = await fetch(`/admin/sales/orders/${orderId}`);
                if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng.');
                const order = await response.json();
                currentOrderData = order;
                populateViewModal(order);
            } catch (error) {
                showAppInfoModal(error.message, 'error');
                $viewModal.modal('hide');
            } finally {
                hideAppLoader();
            }
        });
        $('#printOrderBtn').off('click').on('click', () => {
            if (currentOrderData) {
                populateAndPrintInvoice(currentOrderData);
            } else {
                showAppInfoModal('Không có dữ liệu đơn hàng để in.', 'error');
            }
        });
        $('#editOrderFromViewBtn').on('click', () => {
            const orderId = $('#viewModalOrderIdStrong').text();
            $(`.update-order-btn[data-id="${orderId}"]`).trigger('click');
            $viewModal.modal('hide');
        });
    }

    function populateAndPrintInvoice(order) {
        $('#print-invoice-id').text(order.id);
        $('#print-invoice-date').text(new Date(order.created_at).toLocaleDateString('vi-VN'));
        $('#print-invoice-status').text(order.status_text);
        $('#print-customer-name').text(order.shipping_name);
        $('#print-customer-phone').text(order.shipping_phone);
        $('#print-customer-email').text(order.shipping_email || 'N/A');
        const fullAddress = `${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}`;
        $('#print-customer-address').text(fullAddress);
        const itemsHtml = order.items.map(item => `<tr class="item"><td>${item.product.name}</td><td style="text-align: center;">${item.quantity}</td><td class="text-right">${formatCurrency(item.price * item.quantity)}</td></tr>`).join('');
        $('#print-items-body').html(itemsHtml);
        $('#print-subtotal').text(formatCurrency(order.subtotal));
        $('#print-shipping').text(formatCurrency(order.shipping_fee));
        $('#print-discount').text(`-${formatCurrency(order.discount_amount)}`);
        $('#print-grand-total').text(formatCurrency(order.total_price));
        const printContents = document.getElementById('invoice-print-template').innerHTML;
        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
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
        const itemsHtml = order.items.map(item => `<tr><td>${item.product.name}</td><td>${item.quantity}</td><td>${formatCurrency(item.price)}</td><td class="text-end">${formatCurrency(item.price * item.quantity)}</td></tr>`).join('');
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

    // F. KICKSTART THE SCRIPT
    initialize();
};