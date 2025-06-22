(function () {
    'use strict';

    let _globalHelpers; // Để lưu trữ các hàm helper toàn cục

    // Hàm tiện ích để định dạng tiền tệ
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    // Hàm tiện ích để cập nhật tổng tiền
    function updateOrderSummaryDetails(formId) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Form with ID ${formId} not found.`);
            return;
        }

        let subtotal = 0;
        form.querySelectorAll('.product-item-row').forEach(row => {
            const priceInput = row.querySelector('.product-price');
            const quantityInput = row.querySelector('.product-quantity');
            if (priceInput && quantityInput) {
                const price = parseFloat(priceInput.value) || 0;
                const quantity = parseInt(quantityInput.value) || 0;
                subtotal += (price * quantity);
            }
        });

        const shippingFeeSelect = form.querySelector('[name="delivery_service_id"]');
        let shippingFee = 0;
        if (shippingFeeSelect) {
            const selectedOption = shippingFeeSelect.options[shippingFeeSelect.selectedIndex];
            shippingFee = parseFloat(selectedOption?.dataset.shippingFee || 0);
        }

        const promotionSelect = form.querySelector('[name="promotion_id"]');
        let discountAmount = 0;
        if (promotionSelect) {
            const selectedOption = promotionSelect.options[promotionSelect.selectedIndex];
            const discountPercentage = parseFloat(selectedOption?.dataset.discountPercentage || 0);
            discountAmount = (subtotal * discountPercentage) / 100;
            if (discountAmount > subtotal) { // Giảm giá không được lớn hơn tổng phụ
                discountAmount = subtotal;
            }
        }

        const grandTotal = subtotal + shippingFee - discountAmount;

        // Sửa đổi dòng này để tạo ID đúng định dạng (ví dụ: create-order-subtotal)
        const baseId = formId.replace('OrderForm', '-order');

        form.querySelector(`#${baseId}-subtotal`).textContent = formatCurrency(subtotal);
        form.querySelector(`#${baseId}-shipping-fee`).textContent = formatCurrency(shippingFee);
        form.querySelector(`#${baseId}-discount`).textContent = `-${formatCurrency(discountAmount)}`;
        form.querySelector(`#${baseId}-grand-total`).textContent = formatCurrency(grandTotal);
    }

    // Hàm tiện ích để thêm hàng sản phẩm vào form tạo/cập nhật
    function addProductItemRow(containerId, productsData, item = null) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const itemId = item ? item.product_id : Date.now(); // Sử dụng product_id làm ID nếu có sẵn, hoặc timestamp
        const productId = item ? item.product_id : '';
        const quantity = item ? item.quantity : 1;
        const price = item ? item.price : 0;
        const productName = item ? item.product.name : 'Chọn sản phẩm...';
        const productStock = item ? item.product.stock_quantity : 0;

        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-3', 'product-item-row');
        newRow.dataset.itemId = itemId;

        newRow.innerHTML = `
            <div class="col-md-6 mb-2">
                <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                <select class="form-select selectpicker product-select" data-live-search="true" name="items[${itemId}][product_id]" title="Chọn sản phẩm...">
                    <option value="">Chọn sản phẩm...</option>
                    ${productsData.map(p => `
                        <option value="${p.id}"
                            data-product-price="${p.price}"
                            data-product-stock="${p.stock_quantity}"
                            ${p.id == productId ? 'selected' : ''}>
                            ${p.name} (SL: ${p.stock_quantity}, Giá: ${formatCurrency(p.price)})
                        </option>
                    `).join('')}
                </select>
                <div class="text-danger product-id-error mt-1"></div>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                <input type="number" class="form-control product-quantity" name="items[${itemId}][quantity]" value="${quantity}" min="1" required>
                <div class="text-danger quantity-error mt-1"></div>
                <small class="text-muted product-stock-info">Tồn kho: ${productStock}</small>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Đơn giá</label>
                <input type="text" class="form-control product-price" name="items[${itemId}][price]" value="${price}" readonly>
            </div>
            <div class="col-md-1 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-product-item-btn">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);

        // Khởi tạo selectpicker cho hàng mới
        $(newRow).find('.selectpicker').selectpicker('refresh');

        // Gắn sự kiện thay đổi cho select sản phẩm và input số lượng
        const productSelect = newRow.querySelector('.product-select');
        const quantityInput = newRow.querySelector('.product-quantity');
        const priceInput = newRow.querySelector('.product-price');
        const stockInfo = newRow.querySelector('.product-stock-info');

        if (productSelect) {
            $(productSelect).on('changed.bs.select', function() {
                const selectedOption = this.options[this.selectedIndex];
                const selectedPrice = parseFloat(selectedOption?.dataset.productPrice || 0);
                const selectedStock = parseInt(selectedOption?.dataset.productStock || 0);

                if (priceInput) priceInput.value = selectedPrice;
                if (stockInfo) stockInfo.textContent = `Tồn kho: ${selectedStock}`;
                if (quantityInput) quantityInput.max = selectedStock; // Cập nhật max attribute
                if (quantityInput && parseInt(quantityInput.value) > selectedStock) {
                    quantityInput.value = selectedStock > 0 ? selectedStock : 1; // Điều chỉnh số lượng nếu vượt quá tồn kho
                }
                updateOrderSummaryDetails(container.closest('form').id);
            });
            // Kích hoạt sự kiện change ban đầu nếu có item được truyền vào (để điền giá và stock)
            if (item) {
                 $(productSelect).trigger('changed.bs.select');
            }
        }

        if (quantityInput) {
            quantityInput.addEventListener('input', () => {
                updateOrderSummaryDetails(container.closest('form').id);
            });
        }

        // Gắn sự kiện xóa
        newRow.querySelector('.remove-product-item-btn').addEventListener('click', function() {
            newRow.remove();
            updateOrderSummaryDetails(container.closest('form').id);
        });
    }

    // Hàm tiện ích để điền dropdown địa chỉ (quận/huyện, phường/xã)
    function populateDropdown(selectElement, items, placeholder) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            selectElement.appendChild(option);
        });
        $(selectElement).selectpicker('refresh');
        selectElement.disabled = false;
    }

    // ===============================================================
    // CÁC HÀM KHỞI TẠO MODAL
    // ===============================================================

    function setupCreateOrderModal(globalHelpers, customers, products, deliveryServices, promotions, provinces, initialOrderStatuses) {
        const modalElement = document.getElementById('createOrderModal');
        if (!modalElement) return;

        const form = document.getElementById('createOrderForm');
        const customerTypeRadios = form.querySelectorAll('input[name="customer_type"]');
        const existingCustomerFields = document.getElementById('existingCustomerFields');
        const guestCustomerFields = document.getElementById('guestCustomerFields');
        const customerIdSelect = document.getElementById('customer_id');
        const shippingAddressSelect = document.getElementById('shipping_address_id');
        const addProductItemBtn = document.getElementById('addProductItemBtn');
        const productItemsContainer = document.getElementById('productItemsContainer');

        const provinceSelectCreate = document.getElementById('province_id_create');
        const districtSelectCreate = document.getElementById('district_id_create');
        const wardSelectCreate = document.getElementById('ward_id_create');

        // Hàm để chuyển đổi hiển thị các trường khách hàng
        function toggleCustomerFields() {
            const selectedType = form.querySelector('input[name="customer_type"]:checked').value;
            if (selectedType === 'existing') {
                existingCustomerFields.classList.remove('d-none');
                guestCustomerFields.classList.add('d-none');
                customerIdSelect.setAttribute('required', 'required');
                shippingAddressSelect.setAttribute('required', 'required');
                // Remove required for guest fields
                form.querySelectorAll('#guestCustomerFields input, #guestCustomerFields select').forEach(el => {
                    el.removeAttribute('required');
                });
            } else {
                existingCustomerFields.classList.add('d-none');
                guestCustomerFields.classList.remove('d-none');
                customerIdSelect.removeAttribute('required');
                shippingAddressSelect.removeAttribute('required');
                // Add required for guest fields
                form.querySelectorAll('#guestCustomerFields input, #guestCustomerFields select').forEach(el => {
                    if (el.id !== 'guest_email' && el.id !== 'guest_phone') { // Email/phone can be optional
                        el.setAttribute('required', 'required');
                    }
                });
            }
            $(customerIdSelect).selectpicker('refresh');
            $(shippingAddressSelect).selectpicker('refresh');
            $(provinceSelectCreate).selectpicker('refresh');
            $(districtSelectCreate).selectpicker('refresh');
            $(wardSelectCreate).selectpicker('refresh');
        }

        customerTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleCustomerFields);
        });

        // Tải địa chỉ giao hàng khi chọn khách hàng
        $(customerIdSelect).on('changed.bs.select', async function() {
            const customerId = this.value;
            shippingAddressSelect.innerHTML = '<option value="">Đang tải địa chỉ...</option>';
            $(shippingAddressSelect).selectpicker('refresh');
            shippingAddressSelect.disabled = true;

            if (!customerId) {
                shippingAddressSelect.innerHTML = '<option value="">Chọn địa chỉ...</option>';
                $(shippingAddressSelect).selectpicker('refresh');
                shippingAddressSelect.disabled = false;
                return;
            }

            try {
                _globalHelpers.showAppLoader();
                const response = await fetch(`/admin/sales/orders/get-customer-addresses?customer_id=${customerId}`);
                const addresses = await response.json();
                shippingAddressSelect.innerHTML = '<option value="">Chọn địa chỉ...</option>';
                addresses.forEach(addr => {
                    const option = document.createElement('option');
                    option.value = addr.id;
                    option.textContent = `${addr.address_line}, ${addr.ward.name}, ${addr.district.name}, ${addr.province.name}`;
                    shippingAddressSelect.appendChild(option);
                });
                $(shippingAddressSelect).selectpicker('refresh');
                shippingAddressSelect.disabled = false;
            } catch (error) {
                console.error('Lỗi khi tải địa chỉ khách hàng:', error);
                _globalHelpers.showAppInfoModal('Lỗi khi tải địa chỉ khách hàng.', 'error');
            } finally {
                _globalHelpers.hideAppLoader();
            }
        });

        // Xử lý các dropdown địa lý cho khách vãng lai
        $(provinceSelectCreate).on('changed.bs.select', async function() {
            const provinceId = this.value;
            districtSelectCreate.innerHTML = '<option value="">Đang tải...</option>';
            $(districtSelectCreate).selectpicker('refresh');
            districtSelectCreate.disabled = true;
            wardSelectCreate.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            $(wardSelectCreate).selectpicker('refresh');
            wardSelectCreate.disabled = true;

            if (!provinceId) {
                populateDropdown(districtSelectCreate, [], 'Chọn Quận/Huyện');
                populateDropdown(wardSelectCreate, [], 'Chọn Phường/Xã');
                return;
            }

            try {
                const response = await fetch(`/admin/sales/orders/get-districts?province_id=${provinceId}`);
                const districts = await response.json();
                populateDropdown(districtSelectCreate, districts, 'Chọn Quận/Huyện');
            } catch (error) {
                console.error('Error loading districts:', error);
                _globalHelpers.showAppInfoModal('Lỗi khi tải danh sách quận/huyện.', 'error');
            }
        });

        $(districtSelectCreate).on('changed.bs.select', async function() {
            const districtId = this.value;
            wardSelectCreate.innerHTML = '<option value="">Đang tải...</option>';
            $(wardSelectCreate).selectpicker('refresh');
            wardSelectCreate.disabled = true;

            if (!districtId) {
                populateDropdown(wardSelectCreate, [], 'Chọn Phường/Xã');
                return;
            }

            try {
                const response = await fetch(`/admin/sales/orders/get-wards?district_id=${districtId}`);
                const wards = await response.json();
                populateDropdown(wardSelectCreate, wards, 'Chọn Phường/Xã');
            } catch (error) {
                console.error('Error loading wards:', error);
                _globalHelpers.showAppInfoModal('Lỗi khi tải danh sách phường/xã.', 'error');
            }
        });

        // Nút thêm sản phẩm
        addProductItemBtn.addEventListener('click', () => addProductItemRow('productItemsContainer', products));

        // Initial add product item row if there's no item (for empty form)
        if (productItemsContainer.children.length === 0) {
             addProductItemRow('productItemsContainer', products);
        }

        // Cập nhật tổng tiền khi thay đổi dịch vụ vận chuyển hoặc khuyến mãi
        const deliveryServiceSelect = form.querySelector('#delivery_service_id_create');
        const promotionSelect = form.querySelector('#promotion_id_create');

        if (deliveryServiceSelect) {
            $(deliveryServiceSelect).on('changed.bs.select', () => updateOrderSummaryDetails('createOrderForm'));
        }
        if (promotionSelect) {
            $(promotionSelect).on('changed.bs.select', () => updateOrderSummaryDetails('createOrderForm'));
        }

        // Gọi lại toggle để áp dụng trạng thái ban đầu và xử lý required attributes
        toggleCustomerFields();
        updateOrderSummaryDetails('createOrderForm'); // Tính toán lần đầu

        // Gắn AJAX form submission
        globalHelpers.setupAjaxForm('createOrderForm', 'createOrderModal', (result) => {
            globalHelpers.showAppInfoModal(result.message, 'success', 'Thành công');
            // Tải lại trang hoặc cập nhật bảng nếu cần
            setTimeout(() => window.location.reload(), 1200);
        });

        // Xử lý khi modal đóng để reset form và lỗi
        modalElement.addEventListener('hidden.bs.modal', function () {
            form.reset();
            _globalHelpers.clearValidationErrors(form);
            // Clear product items
            productItemsContainer.innerHTML = '';
            addProductItemRow('productItemsContainer', products); // Add one empty row back
            // Reset selectpickers
            $(form).find('.selectpicker').selectpicker('val', '');
            $(form).find('.selectpicker').selectpicker('refresh');
            // Reset customer type
            document.getElementById('existingCustomer').checked = true;
            toggleCustomerFields();
            updateOrderSummaryDetails('createOrderForm'); // Reset summary
        });
    }

    function setupViewOrderModal(globalHelpers) {
        const modalElement = document.getElementById('viewOrderModal');
        if (!modalElement) return;

        const printOrderBtn = document.getElementById('printOrderBtn');
        const editOrderFromViewBtn = document.getElementById('editOrderFromViewBtn');

        // Lắng nghe sự kiện mở modal
        modalElement.addEventListener('show.bs.modal', async function (event) {
            const button = event.relatedTarget;
            const orderId = button.dataset.id;
            const viewModalOrderIdStrong = document.getElementById('viewModalOrderIdStrong');
            if (viewModalOrderIdStrong) viewModalOrderIdStrong.textContent = orderId;

            globalHelpers.showAppLoader();
            try {
                const response = await fetch(`/admin/sales/orders/${orderId}`);
                if (!response.ok) {
                    throw new Error('Không thể tải chi tiết đơn hàng.');
                }
                const data = await response.json();
                const order = data.order;
                const customerName = data.customer_name; // Sử dụng customer_name từ accessor
                const shippingAddressFull = data.shipping_address_full; // Sử dụng full_shipping_address từ accessor
                const createdByAdminName = data.created_by_admin_name;

                // Điền thông tin vào phần view_order_modal.blade.php
                document.getElementById('viewDetailOrderId').textContent = order.id;
                document.getElementById('viewDetailOrderCreatedAt').textContent = new Date(order.created_at).toLocaleString('vi-VN');
                document.getElementById('viewDetailOrderStatusBadge').innerHTML = `<span class="badge ${order.status_badge_class}">${order.status_text}</span>`;
                document.getElementById('viewDetailCustomerType').textContent = order.customer_id ? 'Khách hàng có sẵn' : 'Khách vãng lai';
                document.getElementById('viewDetailCustomerName').textContent = customerName;
                document.getElementById('viewDetailCustomerPhone').textContent = order.customer?.phone || order.guest_phone || 'N/A';
                document.getElementById('viewDetailCustomerEmail').textContent = order.customer?.email || order.guest_email || 'N/A';
                document.getElementById('viewDetailOrderFullAddress').textContent = shippingAddressFull;
                document.getElementById('viewDetailOrderPaymentMethod').textContent = order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản ngân hàng';
                document.getElementById('viewDetailOrderDeliveryService').textContent = order.delivery_service?.name || 'N/A';
                document.getElementById('viewDetailOrderPromotionCode').textContent = order.promotion?.code || 'Không áp dụng';
                document.getElementById('viewDetailOrderNotes').textContent = order.notes || 'Không có';
                document.getElementById('viewDetailOrderCreatedByAdmin').textContent = createdByAdminName;

                // Chi tiết sản phẩm
                const itemsBody = document.getElementById('viewOrderItemsBody');
                itemsBody.innerHTML = '';
                order.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.product.name}</td>
                        <td>${item.quantity}</td>
                        <td>${formatCurrency(item.price)}</td>
                        <td class="text-end">${formatCurrency(item.quantity * item.price)}</td>
                    `;
                    itemsBody.appendChild(row);
                });

                document.getElementById('viewOrderSubtotal').textContent = formatCurrency(order.subtotal);
                document.getElementById('viewOrderShippingFee').textContent = formatCurrency(order.shipping_fee);
                document.getElementById('viewOrderDiscount').textContent = `-${formatCurrency(order.discount_amount)}`;
                document.getElementById('viewOrderGrandTotal').textContent = formatCurrency(order.total_price);

                // Setup print data
                document.getElementById('print-invoice-id').textContent = order.id;
                document.getElementById('print-invoice-date').textContent = new Date(order.created_at).toLocaleDateString('vi-VN');
                document.getElementById('print-invoice-status').textContent = order.status_text;
                document.getElementById('print-customer-name').textContent = customerName;
                document.getElementById('print-customer-phone').textContent = order.customer?.phone || order.guest_phone || '';
                document.getElementById('print-customer-email').textContent = order.customer?.email || order.guest_email || '';
                document.getElementById('print-customer-address').textContent = shippingAddressFull;

                const printItemsBody = document.getElementById('print-items-body');
                printItemsBody.innerHTML = '';
                order.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.classList.add('item');
                    row.innerHTML = `
                        <td>${item.product.name}</td>
                        <td style="text-align: center;">${item.quantity}</td>
                        <td class="text-right">${formatCurrency(item.price)}</td>
                    `;
                    printItemsBody.appendChild(row);
                });

                document.getElementById('print-subtotal').textContent = formatCurrency(order.subtotal);
                document.getElementById('print-shipping').textContent = formatCurrency(order.shipping_fee);
                document.getElementById('print-discount').textContent = `-${formatCurrency(order.discount_amount)}`;
                document.getElementById('print-grand-total').textContent = formatCurrency(order.total_price);


                // Gắn ID đơn hàng vào nút "Chỉnh sửa"
                editOrderFromViewBtn.dataset.id = order.id;

            } catch (error) {
                console.error('Lỗi khi hiển thị chi tiết đơn hàng:', error);
                globalHelpers.showAppInfoModal(error.message, 'error', 'Lỗi');
            } finally {
                globalHelpers.hideAppLoader();
            }
        });

        // Xử lý nút in hóa đơn
        if (printOrderBtn) {
            printOrderBtn.addEventListener('click', function() {
                const viewContent = document.getElementById('order-view-content');
                const invoiceTemplate = document.getElementById('invoice-print-template');

                if (viewContent && invoiceTemplate) {
                    // Ẩn nội dung modal thông thường, hiển thị template hóa đơn
                    viewContent.style.display = 'none';
                    invoiceTemplate.style.display = 'block';

                    // Ẩn footer modal
                    const modalFooter = modalElement.querySelector('.modal-footer');
                    if (modalFooter) modalFooter.style.display = 'none';

                    // Lấy nội dung hóa đơn để in
                    const printableContent = invoiceTemplate.outerHTML;

                    // Tạo cửa sổ in mới
                    const printWindow = window.open('', '', 'height=800,width=800');
                    printWindow.document.write('<html><head><title>Hóa Đơn</title>');
                    // Thêm CSS cho hóa đơn
                    printWindow.document.write(`
                        <style>
                            body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
                            .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 14px; line-height: 24px; color: #555; }
                            .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
                            .invoice-box table td { padding: 8px 10px; vertical-align: top; }
                            .invoice-box table tr.top table td { padding-bottom: 20px; }
                            .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; }
                            .invoice-box table tr.information table td { padding-bottom: 30px; }
                            .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; padding: 10px; }
                            .invoice-box table tr.details td { padding-bottom: 20px; }
                            .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
                            .invoice-box table tr.item.last td { border-bottom: none; }
                            .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
                            .text-right { text-align: right; }
                            .text-center { text-align: center; }
                            .invoice-company-logo { width: 100px; max-width: 300px; } /* Điều chỉnh kích thước logo */
                            .invoice-footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #777; }
                            @media print {
                                body { -webkit-print-color-adjust: exact; }
                                .modal-backdrop { display: none !important; }
                                .modal-dialog { display: block !important; margin: 0 auto; }
                            }
                        </style>
                    `);
                    printWindow.document.write('</head><body>');
                    printWindow.document.write(printableContent);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.focus();

                    // Đợi hình ảnh tải xong trước khi in
                    printWindow.onload = function() {
                        const images = printWindow.document.querySelectorAll('img');
                        let imagesLoaded = 0;
                        const totalImages = images.length;

                        if (totalImages === 0) {
                            printWindow.print();
                            printWindow.close();
                        } else {
                            images.forEach(img => {
                                if (img.complete) {
                                    imagesLoaded++;
                                } else {
                                    img.onload = () => {
                                        imagesLoaded++;
                                        if (imagesLoaded === totalImages) {
                                            printWindow.print();
                                            printWindow.close();
                                        }
                                    };
                                    img.onerror = () => {
                                        imagesLoaded++; // Vẫn tăng nếu lỗi để không bị kẹt
                                        console.warn("Lỗi tải ảnh khi in: " + img.src);
                                        if (imagesLoaded === totalImages) {
                                            printWindow.print();
                                            printWindow.close();
                                        }
                                    };
                                }
                            });
                            if (imagesLoaded === totalImages) { // Trường hợp tất cả ảnh đã tải xong ngay lập tức
                                printWindow.print();
                                printWindow.close();
                            }
                        }
                    };

                    // Khi đóng cửa sổ in, khôi phục lại trạng thái modal
                    printWindow.onbeforeunload = () => {
                        viewContent.style.display = 'block';
                        invoiceTemplate.style.display = 'none';
                        if (modalFooter) modalFooter.style.display = 'flex'; // Hiển thị lại footer
                    };
                }
            });
        }


        // Xử lý nút chỉnh sửa từ modal xem
        if (editOrderFromViewBtn) {
            editOrderFromViewBtn.addEventListener('click', function() {
                const orderId = this.dataset.id;
                const viewModal = bootstrap.Modal.getInstance(modalElement);
                if (viewModal) viewModal.hide(); // Đóng modal xem

                // Mở modal cập nhật
                const updateModalElement = document.getElementById('updateOrderModal');
                const updateModal = new bootstrap.Modal(updateModalElement);
                updateModal.show(this); // Truyền nút kích hoạt để modal update có thể lấy data-id
            });
        }
    }

    function setupUpdateOrderModal(globalHelpers, orderStatuses, deliveryServices, allProducts) {
        const modalElement = document.getElementById('updateOrderModal');
        if (!modalElement) return;

        const form = document.getElementById('updateOrderForm');
        const updateOrderIdInput = document.getElementById('update_order_id');
        const updateOrderStatusSelect = document.getElementById('update_order_status');
        const updateDeliveryServiceSelect = document.getElementById('update_delivery_service_id');
        const updateNotesTextarea = document.getElementById('update_notes');

        // Lắng nghe sự kiện mở modal
        modalElement.addEventListener('show.bs.modal', async function (event) {
            const button = event.relatedTarget;
            const orderId = button.dataset.id;
            updateOrderIdInput.value = orderId; // Set hidden order ID

            globalHelpers.showAppLoader();
            try {
                const response = await fetch(`/admin/sales/orders/${orderId}`);
                if (!response.ok) {
                    throw new Error('Không thể tải chi tiết đơn hàng để cập nhật.');
                }
                const data = await response.json();
                const order = data.order;
                const customerName = data.customer_name;
                const shippingAddressFull = data.shipping_address_full;

                // Điền thông tin vào form
                document.getElementById('update_customer_info').textContent = `${customerName} (${order.customer?.email || order.guest_email || 'N/A'}) - ${order.customer?.phone || order.guest_phone || 'N/A'}`;
                document.getElementById('update_shipping_address').textContent = shippingAddressFull;

                // Set selected values for dropdowns
                $(updateOrderStatusSelect).selectpicker('val', order.status);
                $(updateDeliveryServiceSelect).selectpicker('val', order.delivery_service_id);
                updateNotesTextarea.value = order.notes || '';

                // Cập nhật chi tiết tài chính
                document.getElementById('update-order-subtotal').textContent = formatCurrency(order.subtotal);
                document.getElementById('update-order-shipping-fee').textContent = formatCurrency(order.shipping_fee);
                document.getElementById('update-order-discount').textContent = `-${formatCurrency(order.discount_amount)}`;
                document.getElementById('update-order-grand-total').textContent = formatCurrency(order.total_price);

            } catch (error) {
                console.error('Lỗi khi tải dữ liệu đơn hàng để cập nhật:', error);
                globalHelpers.showAppInfoModal(error.message, 'error', 'Lỗi');
            } finally {
                globalHelpers.hideAppLoader();
            }
        });

        // Gắn AJAX form submission
        globalHelpers.setupAjaxForm('updateOrderForm', 'updateOrderModal', (result) => {
            globalHelpers.showAppInfoModal(result.message, 'success', 'Thành công');
            setTimeout(() => window.location.reload(), 1200);
        });

        // Xử lý khi modal đóng để reset form và lỗi
        modalElement.addEventListener('hidden.bs.modal', function () {
            form.reset();
            _globalHelpers.clearValidationErrors(form);
            $(form).find('.selectpicker').selectpicker('val', ''); // Clear selected values
            $(form).find('.selectpicker').selectpicker('refresh'); // Refresh to show placeholder
        });
    }

    function setupDeleteOrderModal(globalHelpers) {
        const modalElement = document.getElementById('deleteOrderModal');
        if (!modalElement) return;

        const form = document.getElementById('deleteOrderForm');
        const deleteOrderNameElement = document.getElementById('deleteOrderName');
        const adminPasswordInput = document.getElementById('adminPasswordDeleteOrder');


        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.dataset.id;
            const orderName = `Đơn hàng #${orderId}`; // Chỉ hiện ID, tên không cần thiết
            deleteOrderNameElement.textContent = orderName;
            form.action = `/admin/sales/orders/${orderId}`; // Đặt action của form
        });

        // Gắn AJAX form submission
        globalHelpers.setupAjaxForm('deleteOrderForm', 'deleteOrderModal', (result) => {
            globalHelpers.showAppInfoModal(result.message, 'success', 'Thành công');
            setTimeout(() => window.location.reload(), 1200);
        });

        // Xử lý khi modal đóng để reset form và lỗi
        modalElement.addEventListener('hidden.bs.modal', function () {
            form.reset();
            _globalHelpers.clearValidationErrors(form);
            if (adminPasswordInput) adminPasswordInput.value = ''; // Clear password field
        });
    }

    // ===============================================================
    // HÀM KHỞI TẠO TỔNG QUAN CHO TRANG ORDERS
    // ===============================================================

    window.initializeOrderManager = function (showAppLoader, hideAppLoader, showAppInfoModal, setupAjaxForm, displayValidationErrors, clearValidationErrors) {
        _globalHelpers = { showAppLoader, hideAppLoader, showAppInfoModal, setupAjaxForm, displayValidationErrors, clearValidationErrors };

        console.log("Initializing Order Manager...");

        // Load data for modals (customers, products, delivery services, promotions, provinces)
        // This data is usually passed from the Blade view, but for JS-driven modals,
        // we might fetch it via AJAX or ensure it's available globally.
        // For now, let's assume it's available via global variables set in the Blade view.
        const customers = window.pageData.customers || [];
        const products = window.pageData.products || []; // All products for selection
        const deliveryServices = window.pageData.deliveryServices || [];
        const promotions = window.pageData.promotions || [];
        const provinces = window.pageData.provinces || [];
        const initialOrderStatuses = window.pageData.initialOrderStatuses || [];
        const orderStatuses = window.pageData.orderStatuses || [];


        // Khởi tạo Bootstrap Select cho tất cả các selectpicker
        try {
            $('.selectpicker').selectpicker();
        } catch (error) {
            console.error('Bootstrap Select initialization failed in Order Manager:', error);
        }

        // Setup individual modals
        setupCreateOrderModal(_globalHelpers, customers, products, deliveryServices, promotions, provinces, initialOrderStatuses);
        setupViewOrderModal(_globalHelpers);
        setupUpdateOrderModal(_globalHelpers, orderStatuses, deliveryServices, products); // Pass allProducts for product selection
        setupDeleteOrderModal(_globalHelpers);
    };

})();