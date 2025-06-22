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
    const NO_IMAGE_URL = '/assets_admin/images/no_image.jpeg'; // Ensure this path is correct

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let isUpdatingSummary = false; // Flag to prevent multiple concurrent requests

    /**
     * Sends a request to the API to recalculate order totals and update the UI.
     */
    async function calculateAndUpdateAll() {
        if (isUpdatingSummary) return; // Prevent duplicate requests
        isUpdatingSummary = true;
        showAppLoader(); // Show loader

        try {
            const itemsData = [];
            $productItemsContainer.find('.product-item-row').each(function () {
                const $row = $(this);
                const $productSelect = $row.find('.product-select');
                const $quantityInput = $row.find('.quantity-input');
                const $priceInput = $row.find('.price-input'); // Added to get price for client-side subtotal

                const productId = $productSelect.val();
                const quantity = parseInt($quantityInput.val()) || 0;
                const price = parseFloat($priceInput.val()) || 0; // Get price from the readonly input

                // Only add products with a valid productId and quantity > 0 to the payload
                if (productId && quantity > 0) {
                    itemsData.push({
                        product_id: productId,
                        quantity: quantity
                    });
                }
                // Update client-side row subtotal immediately for responsiveness
                // This is a quick display, the final total comes from the server.
                const rowSubtotal = price * quantity;
                $row.find('.product-subtotal-value').text(formatCurrency(rowSubtotal));
            });

            const payload = {
                items: itemsData,
                delivery_service_id: $('#delivery_service_id_create').val() || null,
                promotion_id: $('#promotion_id_create').val() || null // Send promotion_id
            };

            const response = await fetch('/admin/sales/orders/calculate-summary', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                // Handle errors from the server (validation or other errors)
                let errorMessage = data.message || 'Có lỗi xảy ra khi tính toán.';
                if (data.errors) {
                    // If there are validation errors, display them in more detail
                    const errorMessages = Object.values(data.errors).flat();
                    errorMessage += '\n' + errorMessages.join('\n');
                    // You can optionally use `displayValidationErrors` here if you have such a function
                    // displayValidationErrors(document.getElementById('createOrderForm'), data.errors);
                }
                showAppInfoModal(errorMessage, 'Lỗi tính toán', 'error');
                // If you want to keep old values on error, you might not re-render the summary here
                return;
            }

            // Update the UI with data received from the server
            const summary = data.summary;
            $('#summary-subtotal').text(formatCurrency(summary.subtotal));
            $('#summary-shipping').text(formatCurrency(summary.shipping_fee));

            const $discountDisplay = $('#summary-discount');
            $discountDisplay.text(`-${formatCurrency(summary.discount_amount)}`);
            // Show/hide discount row based on whether there's an actual discount
            $discountDisplay.closest('p').toggleClass('d-none', (summary.discount_amount || 0) <= 0);

            $('#summary-grand-total').text(formatCurrency(summary.total_price));

        } catch (error) {
            console.error('Lỗi khi cập nhật tóm tắt đơn hàng:', error);
            showAppInfoModal('Không thể cập nhật tóm tắt đơn hàng. Vui lòng thử lại.', 'Lỗi', 'error');
        } finally {
            hideAppLoader();
            isUpdatingSummary = false;
        }
    }

    // B. EVENT HANDLERS FOR PRODUCT SELECTION AND QUANTITY CHANGES
    function addProductItemRow(product = null) {
        const uniqueId = Date.now(); // Simple unique ID for rows
        const rowHtml = `
            <div class="row product-item-row mb-3 border-bottom pb-3" data-id="${uniqueId}">
                <div class="col-md-5">
                    <label class="form-label">Sản phẩm</label>
                    <select class="form-control product-select" data-live-search="true" name="products[${uniqueId}][product_id]" required>
                        <option value="">Chọn sản phẩm</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Giá</label>
                    <input type="number" class="form-control price-input" name="products[${uniqueId}][price]" step="0.01" min="0" value="0" readonly required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Số lượng</label>
                    <input type="number" class="form-control quantity-input" name="products[${uniqueId}][quantity]" min="1" value="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Thành tiền</label>
                    <p class="form-control-plaintext product-subtotal-value">0 ₫</p>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-product-item">Xóa</button>
                </div>
            </div>
        `;
        const $row = $(rowHtml);
        $productItemsContainer.append($row);
        const $productSelect = $row.find('.product-select');

        // Populate product select options
        populateProductSelect($productSelect, product);

        // Refresh selectpicker after adding options
        $productSelect.selectpicker('refresh');

        // Add event listeners for the new row
        $productSelect.on('change', handleProductSelectChange);
        $row.find('.quantity-input').on('input', calculateAndUpdateAll);
        $row.find('.remove-product-item').on('click', function () {
            $(this).closest('.product-item-row').remove();
            calculateAndUpdateAll(); // Recalculate totals after removing an item
        });
    }

    async function populateProductSelect($selectElement, selectedProduct = null) {
        try {
            const response = await fetch('/api/products/all-for-order');
            if (!response.ok) {
                throw new Error('Failed to fetch products.');
            }
            const products = await response.json();
            let optionsHtml = '<option value="">Chọn sản phẩm</option>';
            products.forEach(p => {
                optionsHtml += `<option value="${p.id}" data-price="${p.price}" ${selectedProduct && selectedProduct.id == p.id ? 'selected' : ''}>
                                    ${p.name} (SL: ${p.stock_quantity}) - ${formatCurrency(p.price)}
                                </option>`;
            });
            $selectElement.html(optionsHtml);
            $selectElement.selectpicker('refresh');
            if (selectedProduct) {
                $selectElement.val(selectedProduct.id).change(); // Trigger change to update price/subtotal
            }
        } catch (error) {
            console.error('Error populating products:', error);
            $selectElement.html('<option value="">Lỗi tải sản phẩm</option>');
            $selectElement.selectpicker('refresh');
        }
    }

    function handleProductSelectChange() {
        const $row = $(this).closest('.product-item-row');
        const $selectedOption = $(this).find('option:selected');
        const price = parseFloat($selectedOption.data('price')) || 0;
        $row.find('.price-input').val(price);
        calculateAndUpdateAll(); // Recalculate totals when product or price changes
    }


    // C. INITIALIZATION OF MODALS
    function initializeCreateModal() {
        // Reset form and clear previous errors
        $createForm[0].reset();
        clearValidationErrors($createForm[0]);
        $productItemsContainer.empty(); // Clear product items
        addProductItemRow(); // Add an initial empty row

        // Re-initialize selectpickers for dynamically added elements
        $('.selectpicker').selectpicker();

        // Attach event listeners for calculating totals
        $('#delivery_service_id_create, #promotion_id_create').off('change').on('change', calculateAndUpdateAll); // Use .off().on() to prevent duplicate bindings
        $('#add-product-item').off('click').on('click', addProductItemRow); // Use .off().on() to prevent duplicate bindings

        // Initial calculation
        calculateAndUpdateAll();

        // Customer select change
        $customerSelect.off('change').on('change', async function () { // Use .off().on() to prevent duplicate bindings
            const customerId = $(this).val();
            const $addressSelect = $('#customer_address_id_create');
            $addressSelect.prop('disabled', true).html('<option value="">Đang tải...</option>');
            $addressSelect.selectpicker('refresh');

            if (!customerId) {
                $addressSelect.html('<option value="">Chọn Địa chỉ giao hàng</option>').prop('disabled', true);
                $addressSelect.selectpicker('refresh');
                return;
            }

            try {
                const response = await fetch(`/api/customers/${customerId}/addresses`);
                if (!response.ok) {
                    throw new Error('Failed to fetch customer addresses.');
                }
                const addresses = await response.json();
                let addressOptions = '<option value="">Chọn Địa chỉ giao hàng</option>';
                addresses.forEach(addr => {
                    addressOptions += `<option value="${addr.id}">${addr.address_line_1}, ${addr.ward_name}, ${addr.district_name}, ${addr.province_name}</option>`;
                });
                $addressSelect.html(addressOptions).prop('disabled', false);
            } catch (e) {
                console.error('Error fetching customer addresses:', e);
                $addressSelect.html('<option value="">Lỗi tải địa chỉ</option>');
            } finally {
                $addressSelect.selectpicker('refresh');
            }
        });
    }

    function initializeUpdateModal(orderData) {
        // This function would be called when the update modal is opened.
        // It should pre-populate the form with existing order data and then
        // set up event listeners similar to `initializeCreateModal`.

        // Example: Populating product items for an existing order
        $productItemsContainer.empty();
        if (orderData && orderData.order_items) {
            orderData.order_items.forEach(item => {
                addProductItemRow({ // Pass existing product data to pre-select
                    id: item.product_id,
                    name: item.product_name,
                    price: item.price,
                    stock_quantity: item.stock_quantity // Assuming this is available or fetched
                });
                const $lastRow = $productItemsContainer.find('.product-item-row').last();
                $lastRow.find('.product-select').val(item.product_id).selectpicker('refresh').change(); // Select product and trigger change
                $lastRow.find('.quantity-input').val(item.quantity);
                $lastRow.find('.price-input').val(item.price);
            });
        } else {
            addProductItemRow(); // Add an empty row if no items
        }

        // Pre-populate customer, delivery, promotion fields
        $('#customer_id_update').val(orderData.customer_id).selectpicker('refresh').change(); // Trigger change to load addresses
        $('#delivery_service_id_update').val(orderData.delivery_service_id).selectpicker('refresh');
        $('#promotion_id_update').val(orderData.promotion_id).selectpicker('refresh');
        $('#order_notes_update').val(orderData.notes);
        $('#status_update').val(orderData.status).selectpicker('refresh');

        // Re-attach event listeners for update modal
        $('#delivery_service_id_update, #promotion_id_update').off('change').on('change', calculateAndUpdateAll);
        $(document).off('input', '#updateOrderModal .quantity-input, #updateOrderModal .price-input').on('input', '#updateOrderModal .quantity-input, #updateOrderModal .price-input', calculateAndUpdateAll);
        $(document).off('change', '#updateOrderModal .product-select').on('change', '#updateOrderModal .product-select', handleProductSelectChange);
        $('#add-product-item-update').off('click').on('click', function () {
            addProductItemRow(); // Re-use add product row logic
        });

        calculateAndUpdateAll(); // Perform initial calculation for update modal
    }

    function initializeDeleteModal(orderId) {
        // Set the order ID for deletion
        $('#deleteOrderId').val(orderId);
    }

    function initializeViewModal(orderData) {
        // Populate view modal with order details
        $('#viewOrderId').text(orderData.id);
        $('#viewCustomerName').text(orderData.customer_name);
        $('#viewCustomerEmail').text(orderData.customer_email);
        $('#viewCustomerPhone').text(orderData.customer_phone);
        $('#viewShippingAddress').text(orderData.shipping_address);
        $('#viewDeliveryService').text(orderData.delivery_service_name || 'N/A');
        $('#viewPromotion').text(orderData.promotion_code ? `${orderData.promotion_code} (${orderData.promotion_discount_type === 'percentage' ? orderData.promotion_discount_value + '%' : formatCurrency(orderData.promotion_discount_value)})` : 'N/A');
        $('#viewOrderNotes').text(orderData.notes || 'Không có ghi chú');
        $('#viewOrderStatus').text(orderData.status_label);
        $('#viewSubtotal').text(formatCurrency(orderData.subtotal));
        $('#viewShippingFee').text(formatCurrency(orderData.shipping_fee));
        $('#viewDiscountAmount').text(`-${formatCurrency(orderData.discount_amount)}`);
        $('#viewGrandTotal').text(formatCurrency(orderData.total_price));
        $('#viewCreatedAt').text(new Date(orderData.created_at).toLocaleString());
        $('#viewUpdatedAt').text(new Date(orderData.updated_at).toLocaleString());

        const $viewOrderItemsContainer = $('#viewOrderItemsContainer');
        $viewOrderItemsContainer.empty();
        orderData.order_items.forEach(item => {
            $viewOrderItemsContainer.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${item.product_name} x ${item.quantity}
                    <span class="badge bg-primary rounded-pill">${formatCurrency(item.price * item.quantity)}</span>
                </li>
            `);
        });
    }


    // D. AJAX FORM SUBMISSION
    setupAjaxForm($createForm, {
        onSuccess: (response) => {
            showAppInfoModal(response.message, 'Thành công', 'success');
            $createModal.modal('hide');
            // Reload the page or update the table via DataTables API
            location.reload(); // Simple reload for now
        },
        onError: (errors) => {
            // displayValidationErrors($createForm[0], errors); // Uncomment if you have this function
            showAppInfoModal('Vui lòng kiểm tra lại thông tin đã nhập.', 'Lỗi Validation', 'error');
        }
    });

    // Event listener for update form submission (assuming a separate form for update)
    const $updateOrderForm = $('#updateOrderForm');
    if ($updateOrderForm.length) {
        setupAjaxForm($updateOrderForm, {
            onSuccess: (response) => {
                showAppInfoModal(response.message, 'Cập nhật thành công', 'success');
                $updateModal.modal('hide');
                location.reload(); // Reload to reflect changes
            },
            onError: (errors) => {
                showAppInfoModal('Vui lòng kiểm tra lại thông tin đã nhập khi cập nhật.', 'Lỗi Validation', 'error');
            }
        });
    }

    // Event listener for delete form submission
    const $deleteOrderForm = $('#deleteOrderForm');
    if ($deleteOrderForm.length) {
        setupAjaxForm($deleteOrderForm, {
            onSuccess: (response) => {
                showAppInfoModal(response.message, 'Xóa thành công', 'success');
                $deleteModal.modal('hide');
                location.reload();
            },
            onError: (errors) => {
                showAppInfoModal('Có lỗi xảy ra khi xóa đơn hàng.', 'Lỗi', 'error');
            }
        });
    }


    // E. GEOGRAPHY DROPDOWN LOGIC (for customer address in create/update modal)
    // This function will need to be called for both create and update modals
    // as customer address selection involves dynamic loading of districts and wards.
    function setupGeographyDropdowns(provinceIdSelector, districtIdSelector, wardIdSelector) {
        const $province = $(provinceIdSelector);
        const $district = $(districtIdSelector);
        const $ward = $(wardIdSelector);

        // Initial state
        // If no province is selected, disable district and ward
        if (!$province.val()) {
            $district.prop('disabled', true);
            $ward.prop('disabled', true);
        } else if (!$district.val()) { // If province is selected but no district, disable ward
            $ward.prop('disabled', true);
        }

        $province.on('change', async function () {
            const provinceVal = $(this).val();
            $district.prop('disabled', true).html('<option value="">Đang tải...</option>');
            $ward.prop('disabled', true).html('<option value="">Chọn Phường/Xã</option>'); // Reset ward too
            $district.selectpicker('refresh');
            $ward.selectpicker('refresh');

            if (!provinceVal) {
                $district.html('<option value="">Chọn Quận/Huyện</option>').prop('disabled', true);
                $district.selectpicker('refresh');
                return;
            }
            try {
                const response = await fetch(`/api/provinces/${provinceVal}/districts`);
                if (!response.ok) {
                    throw new Error('Failed to fetch districts.');
                }
                const districts = await response.json();
                let districtOptions = '<option value="">Chọn Quận/Huyện</option>';
                districts.forEach(d => {
                    districtOptions += `<option value=\"${d.id}\">${d.name}</option>`;
                });
                $district.html(districtOptions).prop('disabled', false);
            } catch (e) {
                console.error('Error fetching districts:', e);
                $district.html('<option value=\"\">Lỗi tải</option>');
            } finally {
                $district.selectpicker('refresh');
            }
        });

        $district.on('change', async function () {
            const districtVal = $(this).val();
            $ward.prop('disabled', true).html('<option value="">Đang tải...</option>');
            $ward.selectpicker('refresh');

            if (!districtVal) {
                $ward.html('<option value="">Chọn Phường/Xã</option>').prop('disabled', true);
                $ward.selectpicker('refresh');
                return;
            }
            try {
                const response = await fetch(`/api/districts/${districtVal}/wards`);
                if (!response.ok) {
                    throw new Error('Failed to fetch wards.');
                }
                const wards = await response.json();
                let wardOptions = '<option value="">Chọn Phường/Xã</option>';
                wards.forEach(w => {
                    wardOptions += `<option value=\"${w.id}\">${w.name}</option>`;
                });
                $ward.html(wardOptions).prop('disabled', false);
            } catch (e) {
                console.error('Error fetching wards:', e);
                $ward.html('<option value=\"\">Lỗi tải</option>');
            } finally {
                $ward.selectpicker('refresh');
            }
        });
    }

    // F. KICKSTART THE SCRIPT
    $(document).ready(function () {
        // Initialize create order modal when shown
        $createModal.on('show.bs.modal', initializeCreateModal);

        // Initialize update order modal when shown
        $updateModal.on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget); // Button that triggered the modal
            const orderJson = button.data('order'); // Extract info from data-* attributes
            const order = typeof orderJson === 'string' ? JSON.parse(orderJson) : orderJson;
            initializeUpdateModal(order);
        });

        // Initialize delete order modal when shown
        $deleteModal.on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data('id');
            initializeDeleteModal(orderId);
        });

        // Initialize view order modal when shown
        $viewModal.on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderJson = button.data('order');
            const order = typeof orderJson === 'string' ? JSON.parse(orderJson) : orderJson;
            initializeViewModal(order);
        });

        // General event listeners for dynamically added product rows in both create and update modals
        // These are delegated events, so they work for elements added after initial DOM load
        $(document).on('input', '.quantity-input, .price-input', function () {
            // Check if the input is within the create or update modal, then call calculateAndUpdateAll
            if ($(this).closest('#createOrderModal, #updateOrderModal').length) {
                calculateAndUpdateAll();
            }
        });
        $(document).on('change', '.product-select', function () {
            // Check if the select is within the create or update modal, then call handleProductSelectChange
            if ($(this).closest('#createOrderModal, #updateOrderModal').length) {
                handleProductSelectChange.call(this);
            }
        });

        // Setup geography dropdowns for create and update modals
        setupGeographyDropdowns('#province_id_create', '#district_id_create', '#ward_id_create');
        setupGeographyDropdowns('#province_id_update', '#district_id_update', '#ward_id_update');
    });
};