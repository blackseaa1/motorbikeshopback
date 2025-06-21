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
    // THÊM MỚI: Đọc dữ liệu khuyến mãi từ dataset
    const allPromotions = JSON.parse(pageContainer.dataset.promotions || '[]');

    const hasValidationErrors = pageContainer.dataset.errors === 'true';
    const formMarker = pageContainer.dataset.formMarker || null;

    if (!Array.isArray(allProducts)) {
        console.error("Dữ liệu sản phẩm (data-products) không hợp lệ.", allProducts);
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
    }

    const { showAppLoader, hideAppLoader, showAppInfoModal, setupAjaxForm, displayValidationErrors } = window;

    const createOrderModalEl = document.getElementById('createOrderModal');
    const viewOrderModalEl = document.getElementById('viewOrderModal');
    const updateOrderModalEl = document.getElementById('updateOrderModal');
    const deleteOrderModalEl = document.getElementById('deleteOrderModal');

    const createOrderForm = document.getElementById('createOrderForm');
    const updateOrderForm = document.getElementById('updateOrderForm');
    const deleteOrderForm = document.getElementById('deleteOrderForm');

    const ordersTableBody = document.getElementById('orders-table-body');
    const productItemsContainer = document.getElementById('product_items_container_modal');
    let productItemCounter = productItemsContainer ? productItemsContainer.querySelectorAll('.product-item-row-modal').length : 0;


    // --- HÀM TRỢ GIÚP ---

    function clearValidationErrors(form) {
        if (!form) return;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    const refreshSelectPickers = () => {
        if ($.fn.selectpicker) {
            $('.selectpicker').selectpicker('render');
        }
    };

    /**
     * Quản lý việc hiển thị các trường nhập địa chỉ.
     * @param {string} formId - ID của form ('createOrderForm' hoặc 'updateOrderForm')
     * @param {boolean} showSelect - True để hiện dropdown địa chỉ, false để hiện các trường nhập tay.
     */
    function toggleAddressFields(formId, showSelect) {
        const form = document.getElementById(formId);
        if (!form) return;

        const addressSelectGroup = form.querySelector('.customer-address-select-group'); // Thay đổi ID nếu cần
        const manualAddressFields = form.querySelector('.manual-address-fields-group'); // Thay đổi ID nếu cần

        if (addressSelectGroup) addressSelectGroup.style.display = showSelect ? 'block' : 'none';
        if (manualAddressFields) manualAddressFields.style.display = showSelect ? 'none' : 'block';

        // Nếu chuyển sang hiện trường nhập tay, reset và vô hiệu hóa dropdown địa chỉ
        if (!showSelect && form.querySelector('.customer-address-select')) {
            $(form.querySelector('.customer-address-select')).val('').selectpicker('refresh');
        }
    }


    /**
     * NÂNG CẤP: Hàm tạo dòng sản phẩm có hình ảnh cho modal UPDATE
     */
    const createUpdateProductRow = (item = {}) => {
        const itemIndex = Date.now() + Math.random();
        const productData = item.product || allProducts.find(p => p.id == item.product_id) || {};

        const productsOptions = allProducts.map(product =>
            `<option value="${product.id}" data-price="${product.price}" data-stock="${product.stock_quantity}" ${item.product_id == product.id ? 'selected' : ''}>
                ${product.name} (Kho: ${product.stock_quantity})
            </option>`
        ).join('');

        const newRowHtml = `
            <div class="row product-item-row-update mb-3 align-items-center" data-index="${itemIndex}">
                <input type="hidden" name="items[${itemIndex}][order_item_id]" value="${item.id || ''}">
                <div class="col-md-1 text-center">
                    <img src="${productData.thumbnail_url || 'https://placehold.co/70x70/EFEFEF/AAAAAA&text=No+Image'}"
                         class="img-fluid rounded product-thumbnail-update"
                         alt="Ảnh sản phẩm" style="width: 60px; height: 60px; object-fit: cover;">
                </div>
                <div class="col-md-6">
                    <select name="items[${itemIndex}][product_id]" class="form-select selectpicker product-select-update" data-live-search="true" data-width="100%" title="-- Chọn sản phẩm --">
                        ${productsOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Số lượng" min="1" value="${item.quantity || 1}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-product-item-update-btn w-100"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
        $('#product_items_container_update').append(newRowHtml);
    };

    // Tìm đến hàm addProductItem(context = 'create', item = null)
    function addProductItem(context = 'create', item = null) {
        // ... (các dòng khai báo biến container, index, ... giữ nguyên)
        if (!productItemsContainer) return;
        const itemIndex = Date.now() + Math.random();
        const productsOptions = allProducts.map(product =>
            // SỬA ĐỔI QUAN TRỌNG: Sử dụng 'p.thumbnail_url' thay vì 'p.image_url'
            `<option value="${product.id}" data-price="${product.price}" data-stock="${product.stock_quantity}">${product.name} (Kho: ${product.stock_quantity})</option>`
        ).join('');

        const newRowHtml = `
            <div class="product-item-row-modal mb-2" data-index="${itemIndex}">
                <select name="product_ids[]" class="form-select selectpicker" data-live-search="true" data-width="100%">
                    <option value="">-- Chọn sản phẩm --</option>
                    ${productsOptions}
                </select>
                <input type="number" name="quantities[]" class="form-control product-quantity" placeholder="SL" min="1" value="1">
                <button type="button" class="btn btn-danger remove-product-item-modal"><i class="bi bi-trash"></i></button>
            </div>
        `;
        $(productItemsContainer).append(newRowHtml);
        productItemCounter++;
    }


    // --- CÁC HÀM XỬ LÝ LOGIC ---

    function toggleCustomerFields() {
        const existingFields = document.getElementById('existing_customer_fields_modal');
        const guestFields = document.getElementById('guest_customer_fields_modal');
        if (!existingFields || !guestFields) return;

        if (document.getElementById('customer_type_existing_modal').checked) {
            existingFields.style.display = 'block';
            guestFields.style.display = 'none';
        } else {
            existingFields.style.display = 'none';
            guestFields.style.display = 'flex';
        }
        refreshSelectPickers();
    }

    async function loadDistricts(provinceId, selectedDistrictId = null) {
        const districtSelect = $('#guest_district_id_modal');
        const wardSelect = $('#guest_ward_id_modal');
        districtSelect.empty().append('<option value="">-- Chọn Quận/Huyện --</option>');
        wardSelect.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
        refreshSelectPickers();
        if (provinceId) {
            try {
                const response = await fetch(`/api/provinces/${provinceId}/districts`);
                const data = await response.json();
                $.each(data, (id, name) => districtSelect.append(new Option(name, id)));
                if (selectedDistrictId) districtSelect.val(selectedDistrictId);
                refreshSelectPickers();
            } catch (error) { console.error('Lỗi khi tải quận/huyện:', error); }
        }
    }

    async function loadWards(districtId, selectedWardId = null) {
        const wardSelect = $('#guest_ward_id_modal');
        wardSelect.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
        refreshSelectPickers();
        if (districtId) {
            try {
                const response = await fetch(`/api/districts/${districtId}/wards`);
                const data = await response.json();
                $.each(data, (id, name) => wardSelect.append(new Option(name, id)));
                if (selectedWardId) wardSelect.val(selectedWardId);
                refreshSelectPickers();
            } catch (error) { console.error('Lỗi khi tải phường/xã:', error); }
        }
    }

    async function loadDistrictsForUpdate(provinceId, selectedDistrictId = null) {
        const districtSelect = $('#district_id_update');
        const wardSelect = $('#ward_id_update');
        districtSelect.empty().append('<option value="">-- Chọn Quận/Huyện --</option>');
        wardSelect.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
        if (provinceId) {
            try {
                const response = await fetch(`/api/provinces/${provinceId}/districts`);
                const data = await response.json();
                $.each(data, (id, name) => districtSelect.append(new Option(name, id)));
                districtSelect.val(selectedDistrictId);
            } catch (error) { console.error('Lỗi khi tải quận/huyện cho modal update:', error); }
        }
        districtSelect.selectpicker('render');
        wardSelect.selectpicker('render');
    }

    async function loadWardsForUpdate(districtId, selectedWardId = null) {
        const wardSelect = $('#ward_id_update');
        wardSelect.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
        if (districtId) {
            try {
                const response = await fetch(`/api/districts/${districtId}/wards`);
                const data = await response.json();
                $.each(data, (id, name) => wardSelect.append(new Option(name, id)));
                wardSelect.val(selectedWardId);
            } catch (error) { console.error('Lỗi khi tải phường/xã cho modal update:', error); }
        }
        wardSelect.selectpicker('render');
    }

    // Function to add a product item to the modal (used by both create and update)
    // This is the function being modified in the prompt
    // function addProductItem() is defined above and the modification is applied there.


    // --- CÁC HÀM HIỂN THỊ MODAL ---

    async function handleShowViewModal(orderId, fetchUrl) {
        if (!viewOrderModalEl) return;
        showAppLoader();
        try {
            const response = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng.');
            const order = await response.json();

            viewOrderModalEl.querySelector('#viewModalOrderIdStrong').textContent = `#${order.id}`;
            viewOrderModalEl.querySelector('#viewDetailOrderId').textContent = `#${order.id}`;
            viewOrderModalEl.querySelector('#viewDetailOrderCreatedAt').textContent = new Date(order.created_at).toLocaleString('vi-VN');
            viewOrderModalEl.querySelector('#viewDetailOrderStatusBadge').innerHTML = `<span class="badge ${order.status_badge_class}">${order.status_text}</span>`;

            viewOrderModalEl.querySelector('#viewDetailCustomerType').textContent = order.customer_id ? 'Khách hàng hiện có' : 'Khách vãng lai';
            viewOrderModalEl.querySelector('#viewDetailCustomerName').textContent = order.customer_name;
            viewOrderModalEl.querySelector('#viewDetailCustomerEmail').textContent = order.guest_email || (order.customer ? order.customer.email : 'N/A');
            viewOrderModalEl.querySelector('#viewDetailCustomerPhone').textContent = order.guest_phone || (order.customer ? order.customer.phone : 'N/A');
            viewOrderModalEl.querySelector('#viewDetailOrderFullAddress').textContent = order.full_address;

            viewOrderModalEl.querySelector('#viewDetailOrderPaymentMethod').textContent = order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'VNPAY';
            viewOrderModalEl.querySelector('#viewDetailOrderDeliveryService').textContent = order.delivery_service ? order.delivery_service.name : 'N/A';
            viewOrderModalEl.querySelector('#viewDetailOrderPromotionCode').textContent = order.promotion ? order.promotion.code : 'Không áp dụng';
            viewOrderModalEl.querySelector('#viewDetailOrderNotes').textContent = order.notes || 'Không có ghi chú';
            viewOrderModalEl.querySelector('#viewDetailOrderCreatedByAdmin').textContent = order.created_by_admin ? order.created_by_admin.name : 'N/A';

            const orderItemsBody = viewOrderModalEl.querySelector('#viewOrderItemsBody');
            orderItemsBody.innerHTML = '';
            order.items.forEach(item => {
                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${item.product?.thumbnail_url || 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=No+Image'}" alt="${item.product?.name || 'Sản phẩm'}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                <span>${item.product?.name || 'Sản phẩm không tồn tại'}</span>
                            </div>
                        </td>
                        <td>${item.quantity}</td>
                        <td>${(item.price || 0).toLocaleString('vi-VN')} ₫</td>
                        <td class="text-end">${((item.quantity || 0) * (item.price || 0)).toLocaleString('vi-VN')} ₫</td>
                    </tr>
                `;
                orderItemsBody.insertAdjacentHTML('beforeend', row);
            });

            viewOrderModalEl.querySelector('#viewOrderSubtotal').textContent = `${order.subtotal.toLocaleString('vi-VN')} ₫`;
            viewOrderModalEl.querySelector('#viewOrderShippingFee').textContent = `${(order.delivery_service?.shipping_fee || 0).toLocaleString('vi-VN')} ₫`;
            viewOrderModalEl.querySelector('#viewOrderDiscount').textContent = `-${order.discount_amount.toLocaleString('vi-VN')} ₫`;
            viewOrderModalEl.querySelector('#viewOrderGrandTotal').textContent = order.formatted_total_price;

            const populatePrintTemplate = (order) => {
                document.getElementById('print-invoice-id').textContent = order.id;
                document.getElementById('print-invoice-date').textContent = new Date(order.created_at).toLocaleDateString('vi-VN');
                document.getElementById('print-invoice-status').textContent = order.status_text;

                document.getElementById('print-customer-name').textContent = order.customer_name;
                document.getElementById('print-customer-phone').textContent = order.guest_phone || (order.customer ? order.customer.phone : 'N/A');
                document.getElementById('print-customer-email').textContent = order.guest_email || (order.customer ? order.customer.email : 'N/A');
                document.getElementById('print-customer-address').textContent = order.full_address;

                const itemsBody = document.getElementById('print-items-body');
                itemsBody.innerHTML = '';
                order.items.forEach(item => {
                    const row = `
                        <tr class="item ${order.items.indexOf(item) === order.items.length - 1 ? 'last' : ''}">
                            <td>${item.product ? item.product.name : 'Sản phẩm không tồn tại'}</td>
                            <td style="text-align: center;">${item.quantity}</td>
                            <td class="text-right">${(item.price * item.quantity).toLocaleString('vi-VN')} ₫</td>
                        </tr>
                    `;
                    itemsBody.insertAdjacentHTML('beforeend', row);
                });

                document.getElementById('print-subtotal').textContent = `${order.subtotal.toLocaleString('vi-VN')} ₫`;
                document.getElementById('print-shipping').textContent = `${(order.delivery_service?.shipping_fee || 0).toLocaleString('vi-VN')} ₫`;
                document.getElementById('print-discount').textContent = `-${order.discount_amount.toLocaleString('vi-VN')} ₫`;
                document.getElementById('print-grand-total').textContent = order.formatted_total_price;
            };

            populatePrintTemplate(order);


            const editFromViewBtn = viewOrderModalEl.querySelector('#editOrderFromViewBtn');
            if (editFromViewBtn) {
                const originalButton = ordersTableBody.querySelector(`.edit-order-btn[data-id="${order.id}"]`);
                if (originalButton) {
                    editFromViewBtn.dataset.id = order.id;
                    editFromViewBtn.dataset.url = originalButton.dataset.url;
                    editFromViewBtn.dataset.updateUrl = originalButton.dataset.updateUrl;

                    editFromViewBtn.onclick = function () {
                        const viewModalInstance = bootstrap.Modal.getInstance(viewOrderModalEl);
                        if (viewModalInstance) viewModalInstance.hide();
                        handleShowUpdateModal(this.dataset.id, this.dataset.url, this.dataset.updateUrl);
                    };
                }
            }

            const modalInstance = bootstrap.Modal.getInstance(viewOrderModalEl) || new bootstrap.Modal(viewOrderModalEl);
            modalInstance.show();
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu đơn hàng để xem:', error);
            showAppInfoModal('Lỗi khi tải dữ liệu đơn hàng: ' + error.message, 'error', 'Lỗi tải dữ liệu');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * SỬA LỖI & NÂNG CẤP: Hàm hiển thị modal update
     */
    async function handleShowUpdateModal(orderId, fetchUrl, updateUrl) {
        if (!updateOrderModalEl || !updateOrderForm) return;
        showAppLoader();
        try {
            clearValidationErrors(updateOrderForm);
            updateOrderForm.reset();
            $('#product_items_container_update, #removed_items_container_update').empty();
            $('.selectpicker').val('default').selectpicker('render');

            const response = await fetch(fetchUrl, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error((await response.json()).message || 'Không thể tải dữ liệu.');

            const res = await response.json();
            if (!res.success) throw new Error(res.message);
            const order = res.order;

            updateOrderForm.action = updateUrl;
            $('#updateModalOrderIdStrong').text(`#${order.id}`);

            const isEditable = !['approved', 'completed', 'cancelled', 'returned', 'failed'].includes(order.status);

            $('#guest_name_update').val(order.guest_name || order.customer?.name || '');
            $('#guest_phone_update').val(order.guest_phone || order.customer?.phone || '');
            $('#guest_email_update').val(order.guest_email || order.customer?.email || '');
            $('#shipping_address_line_update').val(order.shipping_address_line);
            $('#payment_method_update').val(order.payment_method);
            $('#status_update').val(order.status);
            $('#notes_update').val(order.notes || '');

            $('#delivery_service_id_update').val(order.delivery_service_id).selectpicker('render');
            // Điền dropdown khuyến mãi
            populatePromotionSelect($('#promotion_id_update'), res.promotions || [], order.promotion_id);

            // Logic địa chỉ và khuyến mãi cho update modal
            if (order.customer_id && res.addresses && res.addresses.length > 0) {
                populateAddressSelect($('#customer_address_id_update'), res.addresses, order.customer_address_id);
                toggleAddressFields('updateOrderForm', true);
            } else {
                toggleAddressFields('updateOrderForm', false);
                $('#province_id_update').val(order.province_id).selectpicker('render');
                if (order.province_id) {
                    await loadDistrictsForUpdate(order.province_id, order.district_id);
                }
                if (order.district_id) {
                    await loadWardsForUpdate(order.district_id, order.ward_id);
                }
            }

            order.items.forEach(item => createUpdateProductRow(item));
            $('.selectpicker').selectpicker('render');

            $(updateOrderForm).find('input, select, textarea, button').prop('disabled', !isEditable);
            $('#saveUpdateOrderBtn, [data-bs-dismiss="modal"]').prop('disabled', false);

            const modalInstance = new bootstrap.Modal(updateOrderModalEl);
            modalInstance.show();
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu đơn hàng để chỉnh sửa:', error);
            showAppInfoModal(error.message, 'error', 'Lỗi');
        } finally {
            hideAppLoader();
        }
    }

    function handleShowConfirmDeleteModal(orderId, orderName, deleteUrl) {
        if (!deleteOrderModalEl || !deleteOrderForm) return;
        clearValidationErrors(deleteOrderForm);
        deleteOrderForm.action = deleteUrl;
        deleteOrderModalEl.querySelector('#deleteOrderName').textContent = `#${orderId} - ${orderName}`;

        const modalInstance = bootstrap.Modal.getInstance(deleteOrderModalEl) || new bootstrap.Modal(deleteOrderModalEl);
        modalInstance.show();
    }

    // --- THIẾT LẬP BAN ĐẦU & GẮN EVENT LISTENERS ---

    function setupEventListeners() {
        if (ordersTableBody) {
            ordersTableBody.addEventListener('click', async (event) => {
                const button = event.target.closest('button');
                if (!button) return;
                event.preventDefault();

                const { id, name, url, updateUrl, deleteUrl } = button.dataset;

                if (button.classList.contains('view-order-btn')) await handleShowViewModal(id, url);
                else if (button.classList.contains('edit-order-btn')) await handleShowUpdateModal(id, url, updateUrl);
                else if (button.classList.contains('delete-order-btn')) handleShowConfirmDeleteModal(id, name, deleteUrl);
            });
        }

        if (viewOrderModalEl) {
            viewOrderModalEl.addEventListener('click', function (event) {
                const button = event.target.closest('button');
                if (!button) return;

                if (button.id === 'printOrderBtn') {
                    const body = document.body;

                    body.classList.add('is-printing');
                    window.print();

                    window.addEventListener('afterprint', () => {
                        body.classList.remove('is-printing');
                    }, { once: true });
                    setTimeout(() => {
                        body.classList.remove('is-printing');
                    }, 1000);
                }
            });
        }

        if (updateOrderModalEl) {
            $('#add_product_item_update_btn').on('click', () => {
                createUpdateProductRow();
                $('.selectpicker').selectpicker('render');
            });

            $('#product_items_container_update').on('click', '.remove-product-item-update-btn', function () {
                const row = $(this).closest('.product-item-row-update');
                const orderItemId = row.find('input[type="hidden"]').val();
                if (orderItemId) {
                    $('#removed_items_container_update').append(`<input type="hidden" name="removed_item_ids[]" value="${orderItemId}">`);
                }
                row.remove();
            });

            $('#product_items_container_update').on('changed.bs.select', '.product-select-update', function () {
                const selectedProductId = $(this).val();
                const row = $(this).closest('.product-item-row-update');
                const imgTag = row.find('.product-thumbnail-update');
                const selectedProduct = allProducts.find(p => p.id == selectedProductId);
                imgTag.attr('src', selectedProduct?.thumbnail_url || 'https://placehold.co/70x70/EFEFEF/AAAAAA&text=No+Image');
            });

            // Sự kiện thay đổi địa chỉ cho modal update khi chọn địa chỉ của khách hàng
            $('#customer_address_id_update').on('changed.bs.select', async function () {
                const selectedAddressId = $(this).val();
                if (selectedAddressId) {
                    showAppLoader();
                    try {
                        const response = await fetch(`/api/customer-addresses/${selectedAddressId}`);
                        const address = await response.json();
                        if (address) {
                            $('#shipping_address_line_update').val(address.address_line);
                            $('#province_id_update').val(address.province_id).selectpicker('render');
                            await loadDistrictsForUpdate(address.province_id, address.district_id);
                            await loadWardsForUpdate(address.district_id, address.ward_id);
                        }
                    } catch (error) {
                        console.error('Lỗi khi tải chi tiết địa chỉ khách hàng:', error);
                        showAppInfoModal('Lỗi', 'Không thể tải chi tiết địa chỉ.');
                    } finally {
                        hideAppLoader();
                    }
                } else {
                    $('#shipping_address_line_update').val('');
                    $('#province_id_update').val('').selectpicker('render');
                    $('#district_id_update').empty().append('<option value="">-- Chọn Quận/Huyện --</option>').selectpicker('render');
                    $('#ward_id_update').empty().append('<option value="">-- Chọn Phường/Xã --</option>').selectpicker('render');
                }
            });


            // Sự kiện thay đổi địa chỉ cho khách vãng lai trong modal update
            $('#province_id_update').on('changed.bs.select', function () { loadDistrictsForUpdate($(this).val()); });
            $('#district_id_update').on('changed.bs.select', function () { loadWardsForUpdate($(this).val()); });
        }

        if (createOrderModalEl) {
            const customerTypeRadios = createOrderModalEl.querySelectorAll('input[name="customer_type"]');
            const addProductItemButton = createOrderModalEl.querySelector('#add_product_item_modal');
            const guestProvinceSelect = $('#guest_province_id_modal');
            const guestDistrictSelect = $('#guest_district_id_modal');

            customerTypeRadios.forEach(radio => radio.addEventListener('change', function () {
                toggleCustomerFields();
                if (this.value === 'existing') {
                    toggleAddressFields('createOrderForm', false);
                    $('#customer_id').trigger('changed.bs.select');
                } else {
                    toggleAddressFields('createOrderForm', false);
                }
            }));

            if (addProductItemButton) addProductItemButton.addEventListener('click', addProductItem);

            $(productItemsContainer).on('click', '.remove-product-item-modal', function () {
                if ($(productItemsContainer).find('.product-item-row-modal').length > 1) {
                    $(this).closest('.product-item-row-modal').remove();
                } else {
                    showAppInfoModal('Phải có ít nhất một sản phẩm trong đơn hàng.', 'warning');
                }
            });

            guestProvinceSelect.on('changed.bs.select', function () { loadDistricts($(this).val()); });
            guestDistrictSelect.on('changed.bs.select', function () { loadWards($(this).val()); });

            const customerSelectForCreate = document.getElementById('customer_id');
            const createFormAddressSelect = document.getElementById('customer_address_id');

            if (customerSelectForCreate) {
                $(customerSelectForCreate).on('changed.bs.select', async function () {
                    const customerId = this.value;
                    toggleAddressFields('createOrderForm', false);
                    createFormAddressSelect.innerHTML = '<option value="">Chọn địa chỉ</option>';
                    $(createFormAddressSelect).selectpicker('refresh');

                    if (customerId) {
                        showAppLoader();
                        try {
                            const response = await fetch(`/admin/customers/${customerId}/data`);
                            const res = await response.json();
                            if (res.success && res.addresses && res.addresses.length > 0) {
                                populateAddressSelect(createFormAddressSelect, res.addresses, res.default_address_id);
                                toggleAddressFields('createOrderForm', true);
                            } else {
                                toggleAddressFields('createOrderForm', false);
                            }
                        } catch (error) {
                            console.error('Lỗi khi lấy địa chỉ khách hàng:', error);
                        } finally {
                            hideAppLoader();
                        }
                    } else {
                        toggleAddressFields('createOrderForm', false);
                    }
                });
            }

            createOrderModalEl.addEventListener('shown.bs.modal', () => {
                const promotionSelect = document.getElementById('promotion_id');
                if (promotionSelect) {
                    populatePromotionSelect(promotionSelect, allPromotions, '');
                }
                document.getElementById('customer_type_guest_modal').checked = true;
                toggleCustomerFields();
                toggleAddressFields('createOrderForm', false);
            });
        }

        const reloadCallback = () => setTimeout(() => window.location.reload(), 1200);

        if (createOrderForm) setupAjaxForm('createOrderForm', 'createOrderModal', reloadCallback, (res) => {
            if (res.errors) displayValidationErrors(createOrderForm, res.errors)
        });
        if (updateOrderForm) setupAjaxForm('updateOrderForm', 'updateOrderModal', reloadCallback, (res) => {
            if (res.errors) displayValidationErrors(updateOrderForm, res.errors)
        });
        if (deleteOrderForm) setupAjaxForm('deleteOrderForm', 'deleteOrderModal', reloadCallback, (res) => {
            if (res.errors) displayValidationErrors(deleteOrderForm, res.errors)
        });

        if (createOrderModalEl && (productItemsContainer.querySelectorAll('.product-item-row-modal').length === 0 || hasValidationErrors)) {
            createOrderModalEl.addEventListener('shown.bs.modal', () => {
                if (productItemsContainer.querySelectorAll('.product-item-row-modal').length === 0) {
                    addProductItem();
                }
            }, { once: true });

            if (hasValidationErrors && formMarker === 'create_order_form' && productItemsContainer.querySelectorAll('.product-item-row-modal').length === 0) {
                addProductItem();
            }
        }
    }

    function populateAddressSelect(selectElement, addresses, selectedId) {
        selectElement.innerHTML = '<option value="">-- Chọn địa chỉ --</option>';
        if (Array.isArray(addresses)) {
            addresses.forEach(address => {
                const option = new Option(`${address.address_line}, ${address.ward?.name}, ${address.district?.name}, ${address.province?.name}`, address.id);
                selectElement.add(option);
            });
        }
        selectElement.value = selectedId || '';
        $(selectElement).selectpicker('refresh');
    }

    function populatePromotionSelect(selectElement, promotions, selectedId) {
        selectElement.innerHTML = '<option value="">Không áp dụng</option>';
        if (Array.isArray(promotions)) {
            promotions.forEach(promo => {
                const option = new Option(`${promo.code} - ${promo.name}`, promo.id);
                selectElement.add(option);
            });
        }
        selectElement.value = selectedId || '';
        $(selectElement).selectpicker('refresh');
    }

    // --- KHỞI CHẠY ---
    setupEventListeners();

    // BƯỚC 3: Đặt cờ thành true ở cuối hàm để đánh dấu đã khởi tạo
    window.orderManagerInitialized = true;

    console.log("JS cho quản lý Đơn hàng đã được khởi tạo thành công.");
}

// Gọi hàm chính
document.addEventListener('DOMContentLoaded', initializeOrderManager);