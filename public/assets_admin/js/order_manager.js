/**
 * ===================================================================
 * order_manager.js
 *
 * Xử lý JavaScript cho trang quản lý Đơn hàng (Thêm, Sửa, Xóa, Xem, Cập nhật trạng thái).
 * Tích hợp với OrderController.php và các file Blade (orders, view_order_modal, etc.).
 * Sử dụng các hàm toàn cục từ admin_layout.js và các route từ web.php.
 * Cập nhật để kiểm tra trang và xử lý lỗi DOM.
 * ===================================================================
 */

function initializeOrderManager(showAppLoader, hideAppLoader, showAppInfoModal, setupAjaxForm, displayValidationErrors, clearValidationErrors) {
    // Kiểm tra xem trang có phải là trang đơn hàng không
    if (!document.querySelector('body[data-page="orders"]')) {
        console.log('Không phải trang quản lý đơn hàng. Bỏ qua khởi tạo order_manager.js.');
        return;
    }

    console.log("Khởi tạo JS cho trang Quản lý Đơn hàng...");

    // --- KHAI BÁO BIẾN & LẤY ELEMENTS ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    const orderTableBody = document.getElementById('order-table-body');
    const createOrderModalEl = document.getElementById('createOrderModal');
    const updateOrderModalEl = document.getElementById('updateOrderModal');
    const viewOrderModalEl = document.getElementById('viewOrderModal');
    const deleteOrderModalEl = document.getElementById('deleteOrderModal');

    // Kiểm tra từng phần tử và ghi log chi tiết
    const missingElements = [];
    if (!orderTableBody) missingElements.push('#order-table-body');
    if (!createOrderModalEl) missingElements.push('#createOrderModal');
    if (!updateOrderModalEl) missingElements.push('#updateOrderModal');
    if (!viewOrderModalEl) missingElements.push('#viewOrderModal');
    if (!deleteOrderModalEl) missingElements.push('#deleteOrderModal');

    if (missingElements.length > 0) {
        console.error(`Các phần tử thiếu trong DOM: ${missingElements.join(', ')}. Script sẽ không chạy.`);
        return;
    }

    // Khởi tạo các đối tượng Modal của Bootstrap
    const createModal = new bootstrap.Modal(createOrderModalEl);
    const updateModal = new bootstrap.Modal(updateOrderModalEl);
    const viewModal = new bootstrap.Modal(viewOrderModalEl);
    const deleteModal = new bootstrap.Modal(deleteOrderModalEl);

    // --- HÀM TIỆN ÍCH (HELPER FUNCTIONS) ---

    /**
     * Định dạng ngày giờ sang định dạng dd/mm/yyyy, hh:mm:ss.
     * @param {string} dateString - Chuỗi ngày giờ.
     * @returns {string} - Chuỗi định dạng hoặc chuỗi gốc nếu lỗi.
     */
    function formatLocaleDateTime(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Ho_Chi_Minh'
            });
        } catch (e) {
            console.error('Lỗi định dạng ngày:', e);
            return dateString;
        }
    }

    /**
     * Định dạng số tiền sang định dạng VNĐ.
     * @param {number} amount - Số tiền.
     * @returns {string} - Chuỗi định dạng tiền tệ.
     */
    function formatCurrency(amount) {
        return Number(amount || 0).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    }

    /**
     * Tải danh sách quận/huyện theo tỉnh/thành.
     * @param {string} provinceId - ID tỉnh/thành.
     * @param {HTMLSelectElement} districtSelect - Dropdown quận/huyện.
     * @param {string|null} selectedDistrictId - ID quận/huyện được chọn.
     */
    async function fetchDistricts(provinceId, districtSelect, selectedDistrictId = null) {
        if (!provinceId) {
            districtSelect.innerHTML = '<option value="">-- Chọn Tỉnh/Thành trước --</option>';
            districtSelect.disabled = true;
            return;
        }
        showAppLoader();
        districtSelect.disabled = true;
        districtSelect.innerHTML = '<option value="">Đang tải...</option>';
        try {
            const response = await fetch(`/api/provinces/${provinceId}/districts`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error(`Lỗi tải quận/huyện: ${response.statusText}`);
            const districts = await response.json();
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            districts.forEach(district => {
                const option = new Option(district.name, district.id, false, district.id == selectedDistrictId);
                districtSelect.add(option);
            });
            districtSelect.disabled = false;
            $(districtSelect).selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi fetchDistricts:', error);
            districtSelect.innerHTML = '<option value="">Lỗi tải Quận/Huyện</option>';
            showAppInfoModal('Không thể tải danh sách quận/huyện.', 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Tải danh sách phường/xã theo quận/huyện.
     * @param {string} districtId - ID quận/huyện.
     * @param {HTMLSelectElement} wardSelect - Dropdown phường/xã.
     * @param {string|null} selectedWardId - ID phường/xã được chọn.
     */
    async function fetchWards(districtId, wardSelect, selectedWardId = null) {
        if (!districtId) {
            wardSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện trước --</option>';
            wardSelect.disabled = true;
            return;
        }
        showAppLoader();
        wardSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">Đang tải...</option>';
        try {
            const response = await fetch(`/api/districts/${districtId}/wards`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error(`Lỗi tải phường/xã: ${response.statusText}`);
            const wards = await response.json();
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wards.forEach(ward => {
                const option = new Option(ward.name, ward.id, false, ward.id == selectedWardId);
                wardSelect.add(option);
            });
            wardSelect.disabled = false;
            $(wardSelect).selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi fetchWards:', error);
            wardSelect.innerHTML = '<option value="">Lỗi tải Phường/Xã</option>';
            showAppInfoModal('Không thể tải danh sách phường/xã.', 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Tải danh sách địa chỉ của khách hàng.
     * @param {string} customerId - ID khách hàng.
     * @param {HTMLSelectElement} addressSelect - Dropdown địa chỉ.
     * @param {string|null} selectedAddressId - ID địa chỉ được chọn.
     */
    async function fetchCustomerAddresses(customerId, addressSelect, selectedAddressId = null) {
        if (!customerId) {
            addressSelect.innerHTML = '<option value="">-- Chọn khách hàng trước --</option>';
            addressSelect.disabled = true;
            $(addressSelect).selectpicker('refresh');
            return;
        }
        showAppLoader();
        addressSelect.disabled = true;
        addressSelect.innerHTML = '<option value="">Đang tải...</option>';
        try {
            const response = await fetch(`/admin/sales/orders/get-customer-addresses?customer_id=${customerId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error(`Lỗi tải địa chỉ khách hàng: ${response.statusText}`);
            const addresses = await response.json();
            addressSelect.innerHTML = '<option value="">-- Chọn địa chỉ --</option>';
            if (addresses.length === 0) {
                addressSelect.innerHTML = '<option value="">Khách hàng chưa có địa chỉ</option>';
            } else {
                addresses.forEach(address => {
                    const displayText = `${address.address_line}, ${address.ward_name}, ${address.district_name}, ${address.province_name}`;
                    const option = new Option(displayText, address.id, false, address.id == selectedAddressId);
                    addressSelect.add(option);
                });
            }
            addressSelect.disabled = false;
            $(addressSelect).selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi fetchCustomerAddresses:', error);
            addressSelect.innerHTML = '<option value="">Lỗi tải địa chỉ</option>';
            showAppInfoModal('Không thể tải danh sách địa chỉ khách hàng.', 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Lấy thông tin sản phẩm qua API.
     * @param {string} productId - ID sản phẩm.
     * @returns {Object|null} - Thông tin sản phẩm hoặc null nếu lỗi.
     */
    async function fetchProductDetails(productId) {
        showAppLoader();
        try {
            const response = await fetch(`/admin/sales/orders/get-product-details?product_id=${productId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error(`Lỗi tải chi tiết sản phẩm: ${response.statusText}`);
            return await response.json();
        } catch (error) {
            console.error('Lỗi fetchProductDetails:', error);
            showAppInfoModal('Không thể tải thông tin sản phẩm.', 'error', 'Lỗi Hệ thống');
            return null;
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Khởi tạo SelectPicker cho các dropdown.
     */
    function initializeSelectPickers() {
        try {
            $('.selectpicker').selectpicker({
                liveSearch: true,
                width: '100%',
                noneSelectedText: 'Chưa chọn mục nào'
            }).selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi khởi tạo SelectPicker:', error);
            showAppInfoModal('Lỗi khởi tạo giao diện dropdown.', 'error', 'Lỗi Hệ thống');
        }
    }

    // --- XỬ LÝ MODAL TẠO ĐƠN HÀNG ---

    /**
     * Cập nhật giao diện form tạo đơn hàng dựa trên loại khách hàng.
     */
    function updateCreateFormUI() {
        const customerType = createOrderModalEl.querySelector('#customerType')?.value;
        const existingCustomerFields = createOrderModalEl.querySelector('#existingCustomerFields');
        const guestFields = createOrderModalEl.querySelector('#guestFields');
        const addressSelectWrapper = createOrderModalEl.querySelector('#addressSelectWrapper');
        const newAddressFields = createOrderModalEl.querySelector('#newAddressFields');

        if (!customerType || !existingCustomerFields || !guestFields || !addressSelectWrapper || !newAddressFields) {
            console.warn('Một hoặc nhiều trường form tạo đơn hàng không tồn tại.');
            return;
        }

        existingCustomerFields.classList.toggle('d-none', customerType !== 'existing');
        guestFields.classList.toggle('d-none', customerType !== 'guest');
        addressSelectWrapper.classList.toggle('d-none', customerType !== 'existing');
        newAddressFields.classList.toggle('d-none', customerType !== 'existing');

        if (customerType === 'existing') {
            const customerId = createOrderModalEl.querySelector('#customerId')?.value;
            const addressSelect = createOrderModalEl.querySelector('#shippingAddressId');
            if (customerId && addressSelect) {
                fetchCustomerAddresses(customerId, addressSelect);
            } else if (addressSelect) {
                addressSelect.innerHTML = '<option value="">-- Chọn khách hàng trước --</option>';
                addressSelect.disabled = true;
                $(addressSelect).selectpicker('refresh');
            }
        }
    }

    /**
     * Thêm sản phẩm vào danh sách trong form tạo đơn hàng.
     */
    async function addProductItem(productSelect, quantityInput, index) {
        const productId = productSelect.value;
        const quantity = quantityInput.value;
        if (!productId || !quantity) {
            showAppInfoModal('Vui lòng chọn sản phẩm và số lượng.', 'warning', 'Thiếu thông tin');
            return;
        }

        const product = await fetchProductDetails(productId);
        if (!product) return;

        const productList = createOrderModalEl.querySelector('#productList');
        const itemRow = document.createElement('div');
        itemRow.className = 'product-item-row row mb-2 align-items-center';
        itemRow.dataset.itemId = index;
        itemRow.innerHTML = `
            <div class="col-5">
                <input type="hidden" name="items[${index}][product_id]" value="${productId}">
                <span>${product.name}</span>
                <div class="text-danger small product-id-error" data-field="items.${index}.product_id"></div>
            </div>
            <div class="col-3">
                <input type="hidden" name="items[${index}][quantity]" value="${quantity}">
                <span>${quantity}</span>
                <div class="text-danger small quantity-error" data-field="items.${index}.quantity"></div>
            </div>
            <div class="col-3">${formatCurrency(product.price * quantity)}</div>
            <div class="col-1">
                <button type="button" class="btn btn-danger btn-sm remove-product-item">X</button>
            </div>
        `;
        productList.appendChild(itemRow);

        productSelect.value = '';
        quantityInput.value = '';
        $(productSelect).selectpicker('refresh');
        productSelect.focus();
        updateOrderSummary();
    }

    /**
     * Tính toán và cập nhật tổng giá trị đơn hàng.
     */
    async function updateOrderSummary() {
        const productList = createOrderModalEl.querySelector('#productList');
        const deliveryServiceSelect = createOrderModalEl.querySelector('#deliveryServiceId');
        const promotionSelect = createOrderModalEl.querySelector('#promotionId');
        let subtotal = 0;

        const productPromises = [];
        productList.querySelectorAll('.product-item-row').forEach(row => {
            const productId = row.querySelector('input[name^="items"][name$="[product_id]"]').value;
            const quantity = parseInt(row.querySelector('input[name^="items"][name$="[quantity]"]').value);
            productPromises.push(fetchProductDetails(productId).then(product => {
                if (product) {
                    subtotal += product.price * quantity;
                }
            }));
        });

        await Promise.all(productPromises);

        const shippingFee = parseFloat(deliveryServiceSelect.selectedOptions[0]?.dataset.fee || 0);
        const discount = promotionSelect.value ? parseFloat(promotionSelect.selectedOptions[0]?.dataset.discount || 0) * subtotal / 100 : 0;
        const total = subtotal + shippingFee - discount;

        createOrderModalEl.querySelector('#subtotal').textContent = formatCurrency(subtotal);
        createOrderModalEl.querySelector('#shippingFee').textContent = formatCurrency(shippingFee);
        createOrderModalEl.querySelector('#discount').textContent = formatCurrency(discount);
        createOrderModalEl.querySelector('#total').textContent = formatCurrency(total);
    }

    // --- XỬ LÝ MODAL XEM CHI TIẾT ---

    async function handleShowViewModal(orderId) {
        console.log(`Tải chi tiết đơn hàng ID: ${orderId}`); // Thêm log
        showAppLoader();
        try {
            const response = await fetch(`/admin/sales/orders/${orderId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error(`Lỗi tải chi tiết đơn hàng: ${response.statusText}`);

            const order = await response.json();
            console.log('Dữ liệu đơn hàng:', order); // Thêm log

            // Điền dữ liệu vào phần thông tin chi tiết
            const setTextContent = (selector, value) => {
                const element = viewOrderModalEl.querySelector(selector);
                if (element) element.textContent = value || 'N/A';
                else console.warn(`Phần tử ${selector} không tồn tại trong viewOrderModal`);
            };

            setTextContent('#orderIdView', order.id);
            setTextContent('#orderCustomerType', order.customer ? 'Khách hàng hiện có' : 'Khách vãng lai');
            setTextContent('#orderCustomerView', order.customer ? order.customer.name : order.guest_name);
            setTextContent('#orderAddressView', order.full_address);
            setTextContent('#orderPhoneView', order.customer ? order.customer.phone : order.guest_phone);
            setTextContent('#orderEmailView', order.customer ? order.customer.email : order.guest_email);
            setTextContent('#orderDeliveryServiceView', order.delivery_service?.name);
            setTextContent('#orderPaymentMethodView', order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'VNPay');
            setTextContent('#orderSubtotalView', formatCurrency(order.subtotal));
            setTextContent('#orderShippingFeeView', formatCurrency(order.shipping_fee));
            setTextContent('#orderDiscountView', formatCurrency(order.discount_amount));
            setTextContent('#orderTotalView', formatCurrency(order.total_price));
            setTextContent('#orderNotesView', order.notes);
            setTextContent('#orderCreatedAtView', formatLocaleDateTime(order.created_at));
            setTextContent('#orderCreatedByView', order.created_by_admin?.name);
            setTextContent('#orderPromotionView', order.promotion?.code || 'Không có');

            const statusView = viewOrderModalEl.querySelector('#orderStatusView');
            if (statusView) {
                statusView.innerHTML = `<span class="badge ${order.status_badge_class || 'bg-secondary'}">${order.status_text || order.status}</span>`;
            }

            const productList = viewOrderModalEl.querySelector('#orderItemsView');
            if (productList) {
                productList.innerHTML = '';
                (order.items || []).forEach(item => {
                    productList.innerHTML += `
                        <tr>
                            <td><img src="${item.product.images[0]?.image_full_url || '/placeholder.jpg'}" alt="${item.product.name}" width="50"></td>
                            <td>${item.product.name || 'N/A'}</td>
                            <td>${item.quantity || 0}</td>
                            <td>${formatCurrency(item.price)}</td>
                            <td>${formatCurrency(item.price * item.quantity)}</td>
                        </tr>
                    `;
                });
            }

            // Điền dữ liệu vào phần hóa đơn
            setTextContent('#invoiceOrderId', order.id);
            setTextContent('#invoiceCreatedAt', formatLocaleDateTime(order.created_at));
            setTextContent('#invoiceStatus', order.status_text || order.status);
            setTextContent('#invoiceCustomerName', order.customer ? order.customer.name : order.guest_name);
            setTextContent('#invoiceCustomerPhone', order.customer ? order.customer.phone : order.guest_phone);
            setTextContent('#invoiceCustomerEmail', order.customer ? order.customer.email : order.guest_email);
            setTextContent('#invoiceCustomerAddress', order.full_address);
            setTextContent('#invoiceSubtotal', formatCurrency(order.subtotal));
            setTextContent('#invoiceShippingFee', formatCurrency(order.shipping_fee));
            setTextContent('#invoiceDiscount', formatCurrency(order.discount_amount));
            setTextContent('#invoiceTotal', formatCurrency(order.total_price));

            const invoiceProductList = viewOrderModalEl.querySelector('#invoiceItems');
            if (invoiceProductList) {
                invoiceProductList.innerHTML = '';
                (order.items || []).forEach(item => {
                    invoiceProductList.innerHTML += `
                        <tr>
                            <td>${item.product.name || 'N/A'}</td>
                            <td>${item.quantity || 0}</td>
                            <td>${formatCurrency(item.price)}</td>
                        </tr>
                    `;
                });
            }

            // Gán orderId cho nút chỉnh sửa
            const editButton = viewOrderModalEl.querySelector('#editOrderFromViewBtn');
            if (editButton) {
                editButton.dataset.orderId = order.id;
            }

            viewModal.show();
        } catch (error) {
            console.error('Lỗi handleShowViewModal:', error);
            showAppInfoModal(`Không thể tải chi tiết đơn hàng: ${error.message}`, 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    }

    // --- XỬ LÝ MODAL CẬP NHẬT ---

    async function handleShowUpdateModal(orderId) {
        console.log(`Tải dữ liệu sửa đơn hàng ID: ${orderId}`); // Thêm log
        showAppLoader();
        try {
            const response = await fetch(`/admin/sales/orders/${orderId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error(`Lỗi tải dữ liệu đơn hàng: ${response.statusText}`);
            const order = await response.json();

            const form = updateOrderModalEl.querySelector('#updateOrderForm');
            if (!form) throw new Error('Form cập nhật không tồn tại');

            form.action = `/admin/sales/orders/${orderId}`;
            const deliveryServiceSelect = form.querySelector('#deliveryServiceIdUpdate');
            const notesInput = form.querySelector('#notesUpdate');
            const statusSelect = form.querySelector('#orderStatusUpdate');

            if (deliveryServiceSelect) deliveryServiceSelect.value = order.delivery_service_id || '';
            if (notesInput) notesInput.value = order.notes || '';
            if (statusSelect) statusSelect.value = order.status || '';

            $('#updateOrderForm .selectpicker').selectpicker('val', order.delivery_service_id || '').selectpicker('refresh');
            updateModal.show();
        } catch (error) {
            console.error('Lỗi handleShowUpdateModal:', error);
            showAppInfoModal(`Không thể tải dữ liệu để sửa đơn hàng: ${error.message}`, 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    }

    // --- XỬ LÝ MODAL XÓA ---

    function handleShowDeleteModal(orderId, orderCode) {
        console.log(`Mở modal xóa đơn hàng ID: ${orderId}`); // Thêm log
        const form = deleteOrderModalEl.querySelector('#deleteOrderForm');
        if (!form) {
            console.error('Form xóa đơn hàng không tồn tại');
            return;
        }
        form.action = `/admin/sales/orders/${orderId}`;
        const codeDisplay = deleteOrderModalEl.querySelector('#orderCodeToDelete');
        if (codeDisplay) {
            codeDisplay.textContent = orderCode || `DH-${orderId}`;
        }
        deleteModal.show();
    }

    // --- XỬ LÝ CẬP NHẬT TRẠNG THÁI ---

    async function handleUpdateStatus(orderId, status) {
        console.log(`Cập nhật trạng thái đơn hàng ID: ${orderId}, trạng thái: ${status}`); // Thêm log
        showAppLoader();
        try {
            const response = await fetch(`/admin/sales/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || `Lỗi cập nhật trạng thái: ${response.statusText}`);
            showAppInfoModal(result.message || 'Cập nhật trạng thái thành công.', 'success', 'Thành công');
            setTimeout(() => {
                if (typeof Turbo !== 'undefined') {
                    Turbo.visit(window.location.href, { action: 'replace' });
                } else {
                    window.location.reload();
                }
            }, 1200);
        } catch (error) {
            console.error('Lỗi handleUpdateStatus:', error);
            showAppInfoModal(`Lỗi cập nhật trạng thái: ${error.message}`, 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    }

    // --- GẮN KẾT SỰ KIỆN ---

    function setupEventListeners() {
        // Sự kiện click trên bảng đơn hàng
        orderTableBody.addEventListener('click', async function (event) {
            const button = event.target.closest('.btn-action');
            if (!button) return;

            const orderId = button.dataset.id;
            const orderCode = button.dataset.code;
            console.log(`Click nút hành động: ${button.className}, orderId: ${orderId}`); // Thêm log

            if (button.classList.contains('btn-view')) {
                await handleShowViewModal(orderId);
            } else if (button.classList.contains('btn-edit')) {
                await handleShowUpdateModal(orderId);
            } else if (button.classList.contains('btn-delete')) {
                handleShowDeleteModal(orderId, orderCode);
            }
        });

        // Sự kiện thay đổi trạng thái
        orderTableBody.addEventListener('change', function (event) {
            const select = event.target.closest('.status-select');
            if (!select) return;
            const orderId = select.dataset.id;
            const newStatus = select.value;
            handleUpdateStatus(orderId, newStatus);
        });

        // Sự kiện modal tạo đơn hàng
        createOrderModalEl.addEventListener('shown.bs.modal', () => {
            initializeSelectPickers();
            updateCreateFormUI();
            updateOrderSummary();
        });

        createOrderModalEl.addEventListener('hidden.bs.modal', () => {
            const form = createOrderModalEl.querySelector('#createOrderForm');
            if (form) {
                form.reset();
                clearValidationErrors(form);
                const productList = createOrderModalEl.querySelector('#productList');
                if (productList) productList.innerHTML = '';
                $('#createOrderForm .selectpicker').selectpicker('val', '').selectpicker('refresh');
            }
        });

        // Sự kiện thay đổi loại khách hàng
        const customerTypeSelect = createOrderModalEl.querySelector('#customerType');
        if (customerTypeSelect) {
            customerTypeSelect.addEventListener('change', updateCreateFormUI);
        }

        // Sự kiện thay đổi khách hàng
        const customerIdSelect = createOrderModalEl.querySelector('#customerId');
        if (customerIdSelect) {
            customerIdSelect.addEventListener('change', function () {
                const customerId = this.value;
                const addressSelect = createOrderModalEl.querySelector('#shippingAddressId');
                if (addressSelect) {
                    fetchCustomerAddresses(customerId, addressSelect);
                }
            });
        }

        // Sự kiện thay đổi tỉnh/thành (khách vãng lai)
        const guestProvinceSelect = createOrderModalEl.querySelector('#guestProvinceId');
        if (guestProvinceSelect) {
            guestProvinceSelect.addEventListener('change', function () {
                const provinceId = this.value;
                const districtSelect = createOrderModalEl.querySelector('#guestDistrictId');
                const wardSelect = createOrderModalEl.querySelector('#guestWardId');
                if (districtSelect) {
                    fetchDistricts(provinceId, districtSelect);
                }
                if (wardSelect) {
                    wardSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện trước --</option>';
                    $(wardSelect).selectpicker('refresh');
                }
            });
        }

        // Sự kiện thay đổi quận/huyện (khách vãng lai)
        const guestDistrictSelect = createOrderModalEl.querySelector('#guestDistrictId');
        if (guestDistrictSelect) {
            guestDistrictSelect.addEventListener('change', function () {
                const districtId = this.value;
                const wardSelect = createOrderModalEl.querySelector('#guestWardId');
                if (wardSelect) {
                    fetchWards(districtId, wardSelect);
                }
            });
        }

        // Sự kiện thay đổi tỉnh/thành (địa chỉ mới)
        const newProvinceSelect = createOrderModalEl.querySelector('#newProvinceId');
        if (newProvinceSelect) {
            newProvinceSelect.addEventListener('change', function () {
                const provinceId = this.value;
                const districtSelect = createOrderModalEl.querySelector('#newDistrictId');
                const wardSelect = createOrderModalEl.querySelector('#newWardId');
                if (districtSelect) {
                    fetchDistricts(provinceId, districtSelect);
                }
                if (wardSelect) {
                    wardSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện trước --</option>';
                    $(wardSelect).selectpicker('refresh');
                }
            });
        }

        // Sự kiện thay đổi quận/huyện (địa chỉ mới)
        const newDistrictSelect = createOrderModalEl.querySelector('#newDistrictId');
        if (newDistrictSelect) {
            newDistrictSelect.addEventListener('change', function () {
                const districtId = this.value;
                const wardSelect = createOrderModalEl.querySelector('#newWardId');
                if (wardSelect) {
                    fetchWards(districtId, wardSelect);
                }
            });
        }

        // Sự kiện thêm sản phẩm
        const addProductBtn = createOrderModalEl.querySelector('#addProductBtn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', async () => {
                const productSelect = createOrderModalEl.querySelector('#productId');
                const quantityInput = createOrderModalEl.querySelector('#productQuantity');
                const productList = createOrderModalEl.querySelector('#productList');
                const index = productList.querySelectorAll('.product-item-row').length;
                await addProductItem(productSelect, quantityInput, index);
            });
        }

        // Sự kiện xóa sản phẩm
        createOrderModalEl.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-product-item')) {
                event.target.closest('.product-item-row').remove();
                updateOrderSummary();
            }
        });

        // Sự kiện thay đổi dịch vụ vận chuyển hoặc khuyến mãi
        const deliveryServiceSelect = createOrderModalEl.querySelector('#deliveryServiceId');
        const promotionSelect = createOrderModalEl.querySelector('#promotionId');
        if (deliveryServiceSelect) {
            deliveryServiceSelect.addEventListener('change', updateOrderSummary);
        }
        if (promotionSelect) {
            promotionSelect.addEventListener('change', updateOrderSummary);
        }

        // Sự kiện chuyển từ modal xem sang modal sửa
        const editFromViewBtn = viewOrderModalEl.querySelector('#editOrderFromViewBtn');
        if (editFromViewBtn) {
            editFromViewBtn.addEventListener('click', async () => {
                const orderId = editFromViewBtn.dataset.orderId;
                if (orderId) {
                    viewModal.hide();
                    await handleShowUpdateModal(orderId);
                }
            });
        }
    }

    // --- THIẾT LẬP AJAX FORM ---

    function setupAjaxForms() {
        if (!setupAjaxForm) {
            console.error('Hàm setupAjaxForm không tồn tại!');
            return;
        }

        const reloadPage = () => setTimeout(() => {
            if (typeof Turbo !== 'undefined') {
                Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                window.location.reload();
            }
        }, 1200);

        setupAjaxForm('createOrderForm', 'createOrderModal', reloadPage);
        setupAjaxForm('updateOrderForm', 'updateOrderModal', reloadPage);
        setupAjaxForm('deleteOrderForm', 'deleteOrderModal', reloadPage);
    }

    // --- KHỞI TẠO ---

    initializeSelectPickers();
    setupEventListeners();
    setupAjaxForms();

    console.log("JS cho trang Quản lý Đơn hàng đã được khởi tạo thành công.");
}