/**
 * =================================================================================
 * order_manager.js - PHIÊN BẢN API-DRIVEN
 * ---------------------------------------------------------------------------------
 * - Tái cấu trúc để tải danh sách sản phẩm động từ API /api/products/all-for-order.
 * - Loại bỏ sự phụ thuộc vào biến allProductsForJs được truyền từ Blade/PHP.
 * - Tối ưu hóa hiệu năng tải trang ban đầu.
 * - Giữ nguyên luồng tính toán tổng đơn hàng đã ổn định.
 * =================================================================================
 */
window.initializeOrderManager = (
    showAppLoader,
    hideAppLoader,
    showAppInfoModal,
    setupAjaxForm,
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
    const NO_IMAGE_URL = '/assets_admin/images/no-image.png'; // URL ảnh mặc định

    // MỚI: Biến state để lưu trữ dữ liệu sản phẩm và HTML options
    let allProducts = [];
    let productOptionsHtml = ''; // Sẽ được tạo khi dữ liệu được tải thành công

    // B. MAIN INITIALIZATION
    function initialize() {
        if (!$('#adminOrdersPage').length) return;

        // MỚI: Tải dữ liệu sản phẩm ngay khi trang được khởi tạo
        fetchAllProducts();

        initializeCreateModal();
        initializeUpdateModal();
        initializeViewModal();
        initializeDeleteModal();
    }

    // MỚI: Hàm tải danh sách sản phẩm từ API
    async function fetchAllProducts() {
        // Chỉ tải một lần duy nhất
        if (allProducts.length > 0) return;

        try {
            const response = await fetch('/api/products/all-for-order');
            if (!response.ok) {
                throw new Error('Không thể tải danh sách sản phẩm từ server.');
            }
            allProducts = await response.json();
            buildProductOptionsHtml(); // Tạo HTML cho select options sau khi có dữ liệu
        } catch (error) {
            console.error('Lỗi khi tải sản phẩm:', error);
            showAppInfoModal('Không thể tải được danh sách sản phẩm. Vui lòng tải lại trang.', 'Lỗi nghiêm trọng', 'error');
            // Nếu lỗi, tạo một option thông báo lỗi
            productOptionsHtml = '<option value="">Lỗi tải sản phẩm</option>';
        }
    }

    // MỚI: Hàm tạo chuỗi HTML <option> cho select sản phẩm
    function buildProductOptionsHtml() {
        let options = '<option value="" selected>Chọn sản phẩm...</option>'; // Placeholder mặc định
        if (allProducts && allProducts.length > 0) {
            options += allProducts.map(product => {
                const imageUrl = product.thumbnail_url || NO_IMAGE_URL;
                return `<option 
                                value="${product.id}" 
                                data-price="${product.price}" 
                                data-stock="${product.stock_quantity}" 
                                data-image-url="${imageUrl}">
                                ${product.name}
                            </option>`;
            }).join('');
        }
        productOptionsHtml = options;
    }


    // C. CREATE ORDER MODAL LOGIC
    function initializeCreateModal() {
        $createModal.on('show.bs.modal', resetCreateForm);
        $('#add-product-row-btn').on('click', addProductRow);

        $productItemsContainer.on('click', '.remove-product-item', handleRemoveRow);
        $productItemsContainer.on('change', '.product-select', handleProductSelectChange);
        $productItemsContainer.on('change keyup', '.quantity-input', () => calculateAndUpdateAll());

        $createForm.find('input[name="customer_type"]').on('change', handleCustomerTypeChange);
        $customerSelect.on('changed.bs.select', (e) => handleCustomerSelect($(e.currentTarget).val()));
        $('#btn-show-new-address-form').on('click', () => toggleNewAddressForm(true));
        $('#btn-cancel-new-address').on('click', () => toggleNewAddressForm(false));
        setupDependentDropdowns('new_province_id', 'new_district_id', 'new_ward_id');
        setupDependentDropdowns('guest_province_id', 'guest_district_id', 'guest_ward_id');

        $('#delivery_service_id_create, #promotion_id_create').on('change', () => calculateAndUpdateAll());

        setupAjaxForm('createOrderForm', 'createOrderModal');
    }

    function resetCreateForm() {
        clearValidationErrors($createForm[0]);
        $createForm[0].reset();
        $customerSelect.selectpicker('val', '');
        $('#delivery_service_id_create').val('');
        $('#promotion_id_create').val('');
        $('#status_create').val('pending');
        $('#customerTypeExisting').prop('checked', true).trigger('change');
        $productItemsContainer.empty();
        addProductRow();
        calculateAndUpdateAll();
    }

    // SỬA: Hàm addProductRow giờ sẽ dùng productOptionsHtml đã được tạo sẵn
    function addProductRow() {
        const rowIndex = Date.now();
        const template = document.getElementById('product-row-template');
        if (!template) return;

        // Lấy HTML của template và thay thế index
        const newRowHtml = template.innerHTML.replace(/NEW_ROW_INDEX/g, rowIndex);
        const $newRow = $(newRowHtml); // Chuyển thành jQuery object

        // Tìm thẻ select và điền các options đã được tạo sẵn vào
        const $select = $newRow.find('.product-select');
        $select.html(productOptionsHtml);

        // Thêm dòng mới vào container
        $productItemsContainer.append($newRow);

        // Khởi tạo selectpicker cho thẻ select mới
        if (typeof $.fn.selectpicker === 'function') {
            $select.selectpicker('render');
        }

        calculateAndUpdateAll();
    }

    function handleRemoveRow(event) {
        $(event.currentTarget).closest('.product-item-row').remove();
        calculateAndUpdateAll();
    }

    // SỬA: Thay thế asset helper của Blade bằng biến NO_IMAGE_URL
    function handleProductSelectChange(event) {
        const $select = $(event.currentTarget);
        const $row = $select.closest('.product-item-row');
        const $selectedOption = $select.find('option:selected');
        const imageUrl = $selectedOption.data('image-url') || NO_IMAGE_URL;
        const stock = parseInt($selectedOption.data('stock')) || 0;
        const $quantityInput = $row.find('.quantity-input');

        $row.find('.product-image').attr('src', imageUrl);
        $quantityInput.attr('max', stock);

        if (parseInt($quantityInput.val()) < 1 || isNaN(parseInt($quantityInput.val()))) {
            $quantityInput.val(1);
        }
        if (stock > 0 && parseInt($quantityInput.val()) > stock) {
            $quantityInput.val(stock);
        }

        calculateAndUpdateAll();
    }

    /**
     * Hàm tính toán tổng thể duy nhất (giữ nguyên, đã hoạt động tốt).
     */
    function calculateAndUpdateAll() {
        let orderSubtotal = 0;

        $productItemsContainer.find('.product-item-row').each(function () {
            const $row = $(this);
            const $productSelect = $row.find('.product-select');
            const $quantityInput = $row.find('.quantity-input');
            const $selectedOption = $productSelect.find('option:selected');

            const productId = $productSelect.val();
            const quantity = parseInt($quantityInput.val()) || 0;
            const price = parseFloat($selectedOption.data('price')) || 0;

            const rowSubtotal = price * quantity;
            $row.find('.product-subtotal-value').text(formatCurrency(rowSubtotal));

            if (productId && quantity > 0) {
                orderSubtotal += rowSubtotal;
            }
        });

        const deliverySelect = document.getElementById('delivery_service_id_create');
        const shippingFee = parseFloat(deliverySelect.options[deliverySelect.selectedIndex]?.dataset.fee || 0);

        const promoSelect = document.getElementById('promotion_id_create');
        const promoOption = promoSelect.options[promoSelect.selectedIndex];
        let discount = 0;
        if (promoOption && promoOption.value) {
            const promoType = promoOption.dataset.type;
            const promoValue = parseFloat(promoOption.dataset.value) || 0;

            if (promoType === 'percentage') {
                discount = (orderSubtotal > 0 ? (orderSubtotal * promoValue) / 100 : 0);
            } else {
                discount = promoValue;
            }
            discount = Math.min(discount, orderSubtotal + shippingFee);
        }

        const grandTotal = Math.max(orderSubtotal + shippingFee - discount, 0);

        $('#summary-subtotal').text(formatCurrency(orderSubtotal));
        $('#summary-shipping').text(formatCurrency(shippingFee));

        // Sửa lỗi nhỏ: Lấy đúng element chứa discount để ẩn/hiện
        const $discountDisplay = $('#summary-discount');
        $discountDisplay.text(`-${formatCurrency(discount)}`);
        $discountDisplay.closest('p').toggleClass('d-none', discount <= 0); // Giả sử discount nằm trong <p>

        $('#summary-grand-total').text(formatCurrency(grandTotal));
    }


    // --- CÁC HÀM KHÁC GIỮ NGUYÊN VÌ ĐÃ HOẠT ĐỘNG TỐT ---

    function handleCustomerTypeChange() {
        const isExisting = $('#customerTypeExisting').is(':checked');
        $('#existing_customer_block').toggle(isExisting);
        $('#guest_customer_block').toggle(!isExisting);

        if (isExisting) {
            toggleNewAddressForm(false);
            $('#addressListContainer').html('<p class="text-muted">Vui lòng chọn khách hàng để xem địa chỉ.</p>');
            $customerSelect.selectpicker('val', '');
        } else {
            $('#shipping_address_option_create').val('new');
        }
        clearValidationErrors($createForm[0]);
        calculateAndUpdateAll();
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
            // SỬA: Thay thế URL cũ bằng URL mới trong admin route group
            const response = await fetch(`/admin/api/customers/${customerId}/addresses`);
            if (!response.ok) throw new Error('Không thể tải địa chỉ của khách hàng.');
            const addresses = await response.json();
            renderAddressList(addresses, customerId);
        } catch (error) {
            $addressContainer.html(`<p class="text-danger">${error.message}</p>`);
        }
    }

    function renderAddressList(addresses, customerId) {
        const $container = $('#addressListContainer');
        const $newAddressName = $('#new_shipping_name');
        const $newAddressPhone = $('#new_shipping_phone');

        const customerOption = $(`#customer_id_create option[value="${customerId}"]`);
        const customerName = customerOption.text();
        // Sửa: Lấy email từ data-subtext
        const customerEmail = customerOption.data('subtext');

        $newAddressName.val(customerName);
        $newAddressPhone.val(''); // Reset phone

        if (!addresses || addresses.length === 0) {
            $container.html('<p class="text-muted">Khách hàng này chưa có địa chỉ. Vui lòng thêm địa chỉ mới.</p>');
            toggleNewAddressForm(true);
            return;
        }
        const addressesHtml = addresses.map((addr, index) => {
            const isChecked = addr.is_default || index === 0;
            return `
            <div class="form-check address-item p-2 border-bottom">
                <input class="form-check-input" type="radio" name="shipping_address_id" id="addr_${addr.id}" value="${addr.id}" ${isChecked ? 'checked' : ''}>
                <label class="form-check-label w-100" for="addr_${addr.id}">
                    <strong>${addr.full_name}</strong> - ${addr.phone} ${addr.is_default ? '<span class="badge bg-success ms-1">Mặc định</span>' : ''}<br>
                    <small class="text-muted">${addr.address_line}, ${addr.ward.name}, ${addr.district.name}, ${addr.province.name}</small>
                </label>
            </div>`;
        }).join('');
        $container.html(addressesHtml);
    }

    function toggleNewAddressForm(show) {
        $('#new_address_form').toggleClass('d-none', !show);
        $('#existing_address_block').toggle(!show);
        $('#shipping_address_option_create').val(show ? 'new' : 'existing');
        if (!show && $('#addressListContainer .form-check-input').length > 0) {
            $('#addressListContainer .form-check-input').first().prop('checked', true);
        }
        // Không cần clear validation ở đây
    }

    function initializeUpdateModal() {
        $updateModal.on('show.bs.modal', async (event) => {
            const button = event.relatedTarget;
            if (!button) return;
            const orderId = button.dataset.id;

            const form = document.getElementById('updateOrderForm');
            form.action = `/admin/sales/orders/${orderId}`;
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
        if (!order) return;
        $('#update_order_status').val(order.status);
        $('#update_delivery_service_id').val(order.delivery_service_id);
        $('#update_notes').val(order.notes || '');

        const customerInfo = order.customer
            ? `KH: ${order.customer.name} (#${order.customer.id})`
            : `Khách vãng lai: ${order.shipping_name}`;
        $('#update-customer-info').text(customerInfo);

        const fullAddress = (order.shipping_address_line && order.ward && order.district && order.province)
            ? `${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}`
            : 'Địa chỉ không đầy đủ';
        $('#update-shipping-address').text(fullAddress);

        $('#update-order-subtotal').text(formatCurrency(order.subtotal));
        $('#update-order-shipping-fee').text(formatCurrency(order.shipping_fee));
        $('#update-order-discount').text(`-${formatCurrency(order.discount_amount)}`);
        $('#update-order-grand-total').text(formatCurrency(order.total_price));
    }

    function initializeViewModal() {
        let currentOrderData = null;
        $viewModal.on('show.bs.modal', async (event) => {
            const button = event.relatedTarget;
            if (!button) return;
            const orderId = button.dataset.id;

            $('#viewModalOrderIdStrong').text(orderId);
            $('#order-view-content').addClass('d-none');
            showAppLoader();
            try {
                const response = await fetch(`/admin/sales/orders/${orderId}`);
                if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng.');
                currentOrderData = await response.json();
                populateViewModal(currentOrderData);
                $('#order-view-content').removeClass('d-none');
            } catch (error) {
                showAppInfoModal(error.message, 'error');
                currentOrderData = null;
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
            if (currentOrderData) {
                $viewModal.modal('hide');
                const updateBtn = $(`<button type="button" class="d-none" data-bs-toggle="modal" data-bs-target="#updateOrderModal" data-id="${currentOrderData.id}"></button>`);
                $('body').append(updateBtn);
                updateBtn.trigger('click');
                updateBtn.remove();
            }
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
        const fullAddress = (order.shipping_address_line && order.ward && order.district && order.province)
            ? `${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}`
            : 'Địa chỉ không đầy đủ';
        $('#viewDetailOrderFullAddress').text(fullAddress);
        $('#viewDetailOrderPaymentMethod').text(order.payment_method ? order.payment_method.toUpperCase() : 'N/A');
        $('#viewDetailOrderDeliveryService').text(order.delivery_service ? order.delivery_service.name : 'N/A');
        $('#viewDetailOrderPromotionCode').text(order.promotion ? `${order.promotion.code}` : 'Không có');
        $('#viewDetailOrderNotes').text(order.notes || 'Không có ghi chú');
        $('#viewDetailOrderCreatedByAdmin').text(order.created_by_admin ? order.created_by_admin.name : 'Khách hàng tự đặt');

        const itemsHtml = order.items && order.items.length > 0
            ? order.items.map(item => `<tr><td>${item.product ? item.product.name : 'Sản phẩm đã bị xóa'}</td><td>${item.quantity}</td><td>${formatCurrency(item.price)}</td><td class="text-end">${formatCurrency(item.price * item.quantity)}</td></tr>`).join('')
            : '<tr><td colspan="4" class="text-center">Không có sản phẩm.</td></tr>';
        $('#viewOrderItemsBody').html(itemsHtml);

        $('#viewOrderSubtotal').text(formatCurrency(order.subtotal));
        $('#viewOrderShippingFee').text(formatCurrency(order.shipping_fee));
        $('#viewOrderDiscount').text(`-${formatCurrency(order.discount_amount)}`);
        $('#viewOrderGrandTotal').text(formatCurrency(order.total_price));
    }

    function populateAndPrintInvoice(order) {
        const invoiceWindow = window.open('', '_blank');
        invoiceWindow.document.write(`
            <!DOCTYPE html>
            <html lang="vi">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Hóa Đơn Đặt Hàng #${order.id}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; }
                    .invoice-header, .invoice-footer { text-align: center; margin-bottom: 20px; }
                    .invoice-header h1 { font-size: 24px; margin-bottom: 10px; }
                    .invoice-details table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    .invoice-details table td { padding: 5px; vertical-align: top; }
                    .invoice-items table { width: 100%; border-collapse: collapse; }
                    .invoice-items th, .invoice-items td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
                    .invoice-items th { background-color: #f8f9fa; }
                    .text-end { text-align: right; }
                    .mt-4 { margin-top: 1.5rem; }
                    .mb-4 { margin-bottom: 1.5rem; }
                    .fw-bold { font-weight: bold; }
                    @page { size: A4; margin: 15mm; }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <div class="invoice-header">
                        <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
                        <p><strong>Mã Đơn Hàng: #${order.id}</strong></p>
                        <p>Ngày Tạo: ${new Date(order.created_at).toLocaleString('vi-VN')}</p>
                    </div>

                    <div class="invoice-details mb-4">
                        <div class="row">
                            <div class="col-6">
                                <p class="fw-bold">Thông tin khách hàng:</p>
                                <p><strong>Tên:</strong> ${order.shipping_name}</p>
                                <p><strong>SĐT:</strong> ${order.shipping_phone}</p>
                                <p><strong>Email:</strong> ${order.shipping_email || 'N/A'}</p>
                                <p><strong>Địa chỉ:</strong> ${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}</p>
                            </div>
                            <div class="col-6">
                                <p class="fw-bold">Thông tin đơn hàng:</p>
                                <p><strong>Trạng thái:</strong> <span class="badge bg-info">${order.status_text}</span></p>
                                <p><strong>Phương thức thanh toán:</strong> ${order.payment_method ? order.payment_method.toUpperCase() : 'N/A'}</p>
                                <p><strong>Dịch vụ vận chuyển:</strong> ${order.delivery_service ? order.delivery_service.name : 'N/A'}</p>
                                <p><strong>Mã khuyến mãi:</strong> ${order.promotion ? order.promotion.code : 'Không có'}</p>
                                <p><strong>Ghi chú:</strong> ${order.notes || 'Không có'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-items mb-4">
                        <p class="fw-bold">Chi tiết sản phẩm:</p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items && order.items.length > 0
                ? order.items.map(item => `
                                        <tr>
                                            <td>${item.product ? item.product.name : 'Sản phẩm đã bị xóa'}</td>
                                            <td>${item.quantity}</td>
                                            <td>${formatCurrency(item.price)}</td>
                                            <td class="text-end">${formatCurrency(item.price * item.quantity)}</td>
                                        </tr>`).join('')
                : '<tr><td colspan="4" class="text-center">Không có sản phẩm.</td></tr>'
            }
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Tổng phụ:</td>
                                        <td class="text-end">${formatCurrency(order.subtotal)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phí vận chuyển:</td>
                                        <td class="text-end">${formatCurrency(order.shipping_fee)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Giảm giá:</td>
                                        <td class="text-end">-${formatCurrency(order.discount_amount)}</td>
                                    </tr>
                                    <tr class="fw-bold fs-5">
                                        <td>Tổng cộng:</td>
                                        <td class="text-end">${formatCurrency(order.total_price)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="invoice-footer mt-4">
                        <p>Xin chân thành cảm ơn quý khách!</p>
                        <p>Trân trọng,</p>
                        <p>Đội ngũ ${window.APP_NAME || 'Your Company'}</p>
                    </div>
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        };
                    };
                </script>
            </body>
            </html>
        `);
        invoiceWindow.document.close();
    }

    function initializeDeleteModal() {
        $deleteModal.on('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            if (!button) return;
            const orderId = button.dataset.id;
            document.getElementById('deleteOrderForm').action = `/admin/sales/orders/${orderId}`;
            $('#delete-order-id').text(orderId);
            $('#delete_password').val('');
            clearValidationErrors(document.getElementById('deleteOrderForm'));
        });
        setupAjaxForm('deleteOrderForm', 'deleteOrderModal');
    }

    function setupDependentDropdowns(provinceId, districtId, wardId) {
        const $province = $(`#${provinceId}`);
        const $district = $(`#${districtId}`);
        const $ward = $(`#${wardId}`);

        $province.on('change', async function () {
            const provinceVal = $(this).val();
            $district.prop('disabled', true).html('<option value="">Đang tải...</option>');
            $ward.prop('disabled', true).html('<option value="">Chọn Phường/Xã</option>');

            if (!provinceVal) {
                $district.html('<option value="">Chọn Quận/Huyện</option>').prop('disabled', true);
                return;
            }

            try {
                const response = await fetch(`/api/provinces/${provinceVal}/districts`);
                const districts = await response.json();
                let districtOptions = '<option value="">Chọn Quận/Huyện</option>';
                districts.forEach(d => {
                    districtOptions += `<option value="${d.id}">${d.name}</option>`;
                });
                $district.html(districtOptions).prop('disabled', false);
            } catch (e) {
                $district.html('<option value="">Lỗi tải</option>');
            }
        });

        $district.on('change', async function () {
            const districtVal = $(this).val();
            $ward.prop('disabled', true).html('<option value="">Đang tải...</option>');

            if (!districtVal) {
                $ward.html('<option value="">Chọn Phường/Xã</option>').prop('disabled', true);
                return;
            }
            try {
                const response = await fetch(`/api/districts/${districtVal}/wards`);
                const wards = await response.json();
                let wardOptions = '<option value="">Chọn Phường/Xã</option>';
                wards.forEach(w => {
                    wardOptions += `<option value="${w.id}">${w.name}</option>`;
                });
                $ward.html(wardOptions).prop('disabled', false);
            } catch (e) {
                $ward.html('<option value="">Lỗi tải</option>');
            }
        });
    }

    // F. KICKSTART THE SCRIPT
    initialize();
};