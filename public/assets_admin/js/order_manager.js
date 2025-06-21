/**
 * ===================================================================
 * order_manager.js
 * Xử lý JavaScript cho trang quản lý Đơn hàng, đặc biệt là form tạo đơn hàng.
 * Sử dụng Bootstrap Select thay vì Select2.
 * ===================================================================
 */

function initializeOrderManager(allProductsData, hasValidationErrors = false, formMarker = null) {
    // Chỉ chạy khi tìm thấy element đặc trưng của trang quản lý đơn hàng
    if (!document.getElementById('adminOrdersPage') && !document.getElementById('createOrderModal')) return;

    console.log("Khởi tạo JS cho trang Quản lý Đơn hàng...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // Lấy các hàm helper toàn cục từ admin_layout.js
    const showAppLoader = window.showAppLoader;
    const hideAppLoader = window.hideAppLoader;
    const showAppInfoModal = window.showAppInfoModal;
    const setupAjaxForm = window.setupAjaxForm;
    const displayValidationErrors = window.displayValidationErrors;

    // Lấy các element modal một lần
    const createOrderModalEl = document.getElementById('createOrderModal');
    const viewOrderModalEl = document.getElementById('viewOrderModal');
    const updateOrderModalEl = document.getElementById('updateOrderModal');
    const deleteOrderModalEl = document.getElementById('deleteOrderModal');

    // Các form trong modal
    const createOrderForm = document.getElementById('createOrderForm');
    const updateOrderForm = document.getElementById('updateOrderForm');
    const deleteOrderForm = document.getElementById('deleteOrderForm');

    // Container của bảng đơn hàng
    const ordersTableBody = document.getElementById('orders-table-body');
    if (!ordersTableBody) {
        console.warn('Không tìm thấy #orders-table-body. Một số chức năng có thể không hoạt động.');
    }

    const allProducts = allProductsData;


    // --- HÀM TRỢ GIÚP CHUNG ---

    function clearValidationErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    // --- CÁC HÀM XỬ LÝ SỰ KIỆN CHÍNH (click trên bảng) ---
    const setupEventListeners = () => {
        if (!ordersTableBody) return;
        ordersTableBody.addEventListener('click', async (event) => {
            const button = event.target.closest('button');
            if (!button) return;

            const orderId = button.dataset.id;
            const orderName = button.dataset.name;
            const fetchUrl = button.dataset.url;
            const updateUrl = button.dataset.updateUrl;
            const deleteUrl = button.dataset.deleteUrl;

            event.preventDefault();

            if (button.classList.contains('view-order-btn')) {
                await handleShowViewModal(orderId, fetchUrl);
            } else if (button.classList.contains('edit-order-btn')) {
                await handleShowUpdateModal(orderId, fetchUrl, updateUrl);
            } else if (button.classList.contains('delete-order-btn')) {
                handleShowConfirmDeleteModal(orderId, orderName, deleteUrl);
            }
        });
    };

    // --- CÁC HÀM HIỂN THỊ MODAL & ĐIỀN DỮ LIỆU ---

    async function handleShowViewModal(orderId, fetchUrl) {
        if (!viewOrderModalEl) return;
        showAppLoader();
        try {
            const response = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng.');
            const order = await response.json();

            // Điền dữ liệu vào modal Xem Chi tiết
            viewOrderModalEl.querySelector('#viewModalOrderIdStrong').textContent = `#${order.id}`;
            viewOrderModalEl.querySelector('#viewDetailOrderId').textContent = `#${order.id}`;
            viewOrderModalEl.querySelector('#viewDetailOrderCreatedAt').textContent = new Date(order.created_at).toLocaleString('vi-VN');

            const statusBadgeEl = viewOrderModalEl.querySelector('#viewDetailOrderStatusBadge');
            statusBadgeEl.innerHTML = `<span class="badge ${order.status_badge_class}">${order.status_text}</span>`;

            viewOrderModalEl.querySelector('#viewDetailOrderTotalPrice').textContent = order.formatted_total_price;
            viewOrderModalEl.querySelector('#viewDetailOrderPaymentMethod').textContent = order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'VNPAY';
            viewOrderModalEl.querySelector('#viewDetailOrderDeliveryService').textContent = order.delivery_service ? order.delivery_service.name : 'N/A';
            viewOrderModalEl.querySelector('#viewDetailOrderNotes').textContent = order.notes || 'Không có ghi chú';
            viewOrderModalEl.querySelector('#viewDetailOrderCreatedByAdmin').textContent = order.created_by_admin ? order.created_by_admin.name : 'N/A';

            viewOrderModalEl.querySelector('#viewDetailCustomerType').textContent = order.customer_id ? 'Khách hàng hiện có' : 'Khách vãng lai';
            viewOrderModalEl.querySelector('#viewDetailCustomerName').textContent = order.customer_name;
            viewOrderModalEl.querySelector('#viewDetailCustomerEmail').textContent = order.guest_email || (order.customer ? order.customer.email : 'N/A');
            viewOrderModalEl.querySelector('#viewDetailCustomerPhone').textContent = order.guest_phone || (order.customer ? order.customer.phone : 'N/A');
            viewOrderModalEl.querySelector('#viewDetailOrderFullAddress').textContent = order.full_address;

            if (order.promotion_id) {
                viewOrderModalEl.querySelector('#viewDetailOrderPromotionCode').textContent = order.promotion.code;
                viewOrderModalEl.querySelector('#viewDetailOrderDiscountAmount').textContent = `${order.formatted_discount}`;
            } else {
                viewOrderModalEl.querySelector('#viewDetailOrderPromotionCode').textContent = 'Không áp dụng';
                viewOrderModalEl.querySelector('#viewDetailOrderDiscountAmount').textContent = '0 ₫';
            }

            const orderItemsBody = viewOrderModalEl.querySelector('#viewOrderItemsBody');
            orderItemsBody.innerHTML = '';
            order.items.forEach(item => {
                const row = `
                    <tr>
                        <td>
                            <img src="${item.product?.thumbnail_url || 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=No+Image'}" alt="${item.product?.name || 'Sản phẩm'}" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>${item.product?.name || 'Sản phẩm không tồn tại'}</td>
                        <td>${item.quantity}</td>
                        <td>${(item.price || 0).toLocaleString('vi-VN')} ₫</td>
                        <td>${((item.quantity || 0) * (item.price || 0)).toLocaleString('vi-VN')} ₫</td>
                    </tr>
                `;
                orderItemsBody.insertAdjacentHTML('beforeend', row);
            });

            viewOrderModalEl.querySelector('#viewOrderSubtotal').textContent = `${order.subtotal.toLocaleString('vi-VN')} ₫`;
            viewOrderModalEl.querySelector('#viewOrderShippingFee').textContent = `${order.shipping_fee.toLocaleString('vi-VN')} ₫`;
            viewOrderModalEl.querySelector('#viewOrderDiscount').textContent = `-${order.discount_amount.toLocaleString('vi-VN')} ₫`;
            viewOrderModalEl.querySelector('#viewOrderGrandTotal').textContent = order.formatted_total_price;


            const editFromViewBtn = viewOrderModalEl.querySelector('#editOrderFromViewBtn');
            if (editFromViewBtn) {
                editFromViewBtn.dataset.id = order.id;
                editFromViewBtn.dataset.url = fetchUrl;
                const originalButton = event.target.closest('button.view-order-btn');
                editFromViewBtn.dataset.updateUrl = originalButton ? originalButton.dataset.updateUrl : updateUrl;

                editFromViewBtn.addEventListener('click', function () {
                    const viewModalInstance = bootstrap.Modal.getInstance(viewOrderModalEl);
                    if (viewModalInstance) viewModalInstance.hide();
                    handleShowUpdateModal(editFromViewBtn.dataset.id, editFromViewBtn.dataset.url, editFromViewBtn.dataset.updateUrl);
                });
            }

            const modalInstance = bootstrap.Modal.getInstance(viewOrderModalEl) || new bootstrap.Modal(viewOrderModalEl);
            modalInstance.show();
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu đơn hàng để xem:', error);
            showAppInfoModal(error.message, 'error', 'Lỗi tải dữ liệu');
        } finally {
            hideAppLoader();
        }
    }

    async function handleShowUpdateModal(orderId, fetchUrl, updateUrl) {
        if (!updateOrderModalEl) return;
        showAppLoader();
        try {
            clearValidationErrors(updateOrderForm);

            const response = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng để chỉnh sửa.');
            const order = await response.json();

            updateOrderForm.action = updateUrl;
            updateOrderModalEl.querySelector('#updateModalOrderIdStrong').textContent = `#${order.id}`;
            updateOrderModalEl.querySelector('#delivery_service_id_update').value = order.delivery_service_id;
            updateOrderModalEl.querySelector('#notes_update').value = order.notes || '';
            // SỬA ĐỔI: Điền trạng thái hiện tại của đơn hàng vào dropdown
            updateOrderModalEl.querySelector('#status_update').value = order.status;

            // SỬA ĐỔI: Vô hiệu hóa các trường nếu đơn hàng ĐÃ DUYỆT
            const isApproved = order.status === 'approved'; // Giả định 'approved' là hằng số trạng thái
            const inputFields = updateOrderModalEl.querySelectorAll('input, select, textarea');
            const saveButton = updateOrderForm.querySelector('button[type="submit"]');

            if (isApproved) {
                inputFields.forEach(field => field.disabled = true);
                if (saveButton) saveButton.disabled = true;
                showAppInfoModal('Đơn hàng đã duyệt không thể thay đổi thông tin.', 'warning', 'Cảnh báo');
            } else {
                inputFields.forEach(field => field.disabled = false);
                if (saveButton) saveButton.disabled = false;
            }

            $('.selectpicker').selectpicker('refresh'); // Cần refresh sau khi set giá trị và thay đổi disabled

            const modalInstance = bootstrap.Modal.getInstance(updateOrderModalEl) || new bootstrap.Modal(updateOrderModalEl);
            modalInstance.show();
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu đơn hàng để chỉnh sửa:', error);
            showAppInfoModal(error.message, 'error', 'Lỗi tải dữ liệu');
        } finally {
            hideAppLoader();
        }
    }

    function handleShowConfirmDeleteModal(orderId, orderName, deleteUrl) {
        if (!deleteOrderModalEl) return;
        clearValidationErrors(deleteOrderForm);
        deleteOrderForm.action = deleteUrl;
        deleteOrderModalEl.querySelector('#deleteOrderName').textContent = orderName;

        const modalInstance = bootstrap.Modal.getInstance(deleteOrderModalEl) || new bootstrap.Modal(deleteOrderModalEl);
        modalInstance.show();
    }


    // --- THIẾT LẬP BAN ĐẦU & GẮN FORMS AJAX ---

    const setupAllFormsAndEvents = () => {
        const reloadCallback = () => setTimeout(() => window.location.reload(), 1200);

        // Form Tạo mới đơn hàng
        if (createOrderForm) {
            setupAjaxForm('createOrderForm', 'createOrderModal', (result) => {
                showAppInfoModal(result.message, 'success', 'Tạo đơn hàng thành công');
                reloadCallback();
            }, (errorResult) => {
                if (errorResult.errors) {
                    displayValidationErrors(createOrderForm, errorResult.errors);
                } else {
                    showAppInfoModal(errorResult.message, 'error', 'Lỗi Tạo Đơn hàng');
                }
            });
        }

        // Form Cập nhật đơn hàng
        if (updateOrderForm) {
            setupAjaxForm('updateOrderForm', 'updateOrderModal', (result) => {
                showAppInfoModal(result.message, 'success', 'Cập nhật thành công');
                reloadCallback();
            }, (errorResult) => {
                if (errorResult.errors) {
                    displayValidationErrors(updateOrderForm, errorResult.errors);
                } else {
                    showAppInfoModal(errorResult.message, 'error', 'Lỗi Cập Nhật Đơn hàng');
                }
            });
        }

        // Form Xóa đơn hàng
        if (deleteOrderForm) {
            setupAjaxForm('deleteOrderForm', 'deleteOrderModal', (result) => {
                showAppInfoModal(result.message, 'success', 'Xóa thành công');
                reloadCallback();
            }, (errorResult) => {
                if (errorResult.errors) {
                    displayValidationErrors(deleteOrderForm, errorResult.errors);
                } else {
                    showAppInfoModal(errorResult.message, 'error', 'Lỗi Xóa Đơn hàng');
                }
            });
        }

        const customerTypeRadios = document.querySelectorAll('input[name="customer_type"]');
        const addProductItemButton = document.getElementById('add_product_item_modal');
        const productItemsContainer = document.getElementById('product_items_container_modal');
        const guestProvinceSelect = $('#guest_province_id_modal');
        const guestDistrictSelect = $('#guest_district_id_modal');

        customerTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleCustomerFields);
        });

        if (addProductItemButton) {
            addProductItemButton.addEventListener('click', addProductItem);
        }

        if (productItemsContainer) {
            $(productItemsContainer).on('click', '.remove-product-item-modal', function () {
                if ($(productItemsContainer).find('.product-item-row-modal').length > 1) {
                    $(this).closest('.product-item-row-modal').remove();
                } else {
                    window.showAppInfoModal('Phải có ít nhất một sản phẩm trong đơn hàng.', 'warning', 'Cảnh báo');
                }
            });
        }

        guestProvinceSelect.change(function () {
            loadDistricts($(this).val());
        });

        guestDistrictSelect.change(function () {
            loadWards($(this).val());
        });

        if (hasValidationErrors) {
            if (formMarker === 'create_order_form' && createOrderModalEl) {
                const modalInstance = new bootstrap.Modal(createOrderModalEl);
                modalInstance.show();
                const oldProvinceId = guestProvinceSelect.val();
                const oldDistrictId = '{{ old('guest_district_id') }}';
                const oldWardId = '{{ old('guest_ward_id') }}';

                if (oldProvinceId) {
                    loadDistricts(oldProvinceId, oldDistrictId);
                }
                if (oldDistrictId && !oldProvinceId) {
                    loadWards(oldDistrictId, oldWardId);
                } else if (oldDistrictId && oldProvinceId) {
                    guestDistrictSelect.one('loaded.bs.select', function () {
                        loadWards(oldDistrictId, oldWardId);
                    });
                }
                $('.product-item-row-modal .selectpicker').selectpicker('refresh');
            } else if (formMarker === 'update_order_form' && updateOrderModalEl) {
                const modalInstance = new bootstrap.Modal(updateOrderModalEl);
                modalInstance.show();
            } else if (formMarker === 'delete_order_form' && deleteOrderModalEl) {
                const modalInstance = new bootstrap.Modal(deleteOrderModalEl);
                modalInstance.show();
            }
        }


        function toggleCustomerFields() {
            const existingFields = document.getElementById('existing_customer_fields_modal');
            const guestFields = document.getElementById('guest_customer_fields_modal');
            if (document.getElementById('customer_type_existing_modal').checked) {
                existingFields.style.display = 'block';
                guestFields.style.display = 'none';
            } else {
                existingFields.style.display = 'none';
                guestFields.style.display = 'flex';
            }
            $('.selectpicker').selectpicker('refresh');
        }

        async function loadDistricts(provinceId, selectedDistrictId = null) {
            const districtSelect = $('#guest_district_id_modal');
            districtSelect.empty().append('<option value="">-- Chọn Quận/Huyện --</option>').selectpicker('refresh');
            $('#guest_ward_id_modal').empty().append('<option value="">-- Chọn Phường/Xã --</option>').selectpicker('refresh');

            if (provinceId) {
                try {
                    const response = await fetch(`/api/provinces/${provinceId}/districts`);
                    const data = await response.json();
                    $.each(data, function (id, name) {
                        districtSelect.append(new Option(name, id));
                    });
                    districtSelect.selectpicker('refresh');
                    if (selectedDistrictId) {
                        districtSelect.val(selectedDistrictId);
                        districtSelect.selectpicker('refresh');
                    }
                } catch (error) {
                    console.error('Lỗi khi tải quận/huyện:', error);
                }
            }
        }

        async function loadWards(districtId, selectedWardId = null) {
            const wardSelect = $('#guest_ward_id_modal');
            wardSelect.empty().append('<option value="">-- Chọn Phường/Xã --</option>').selectpicker('refresh');

            if (districtId) {
                try {
                    const response = await fetch(`/api/districts/${districtId}/wards`);
                    const data = await response.json();
                    $.each(data, function (id, name) {
                        wardSelect.append(new Option(name, id));
                    });
                    wardSelect.selectpicker('refresh');
                    if (selectedWardId) {
                        wardSelect.val(selectedWardId);
                        wardSelect.selectpicker('refresh');
                    }
                } catch (error) {
                    console.error('Lỗi khi tải phường/xã:', error);
                }
            }
        }

        let productItemCounter = productItemsContainer.querySelectorAll('.product-item-row-modal').length;
        if (productItemCounter === 0) {
            addProductItem();
        }

        function addProductItem() {
            const productsOptions = allProducts.map(product => `
                <option value="${product.id}" data-price="${product.price}" data-stock="${product.stock_quantity}">
                    ${product.name} (Kho: ${product.stock_quantity})
                </option>
            `).join('');

            const newRow = `
                <div class="product-item-row-modal" data-index="${productItemCounter}">
                    <select name="product_ids[]" class="form-select selectpicker product-select" data-live-search="true">
                        <option value="">-- Chọn sản phẩm --</option>
                        ${productsOptions}
                    </select>
                    <input type="number" name="quantities[]" class="form-control product-quantity" placeholder="Số lượng" min="1" value="1">
                    <button type="button" class="btn btn-danger remove-product-item-modal"><i class="bi bi-trash"></i></button>
                </div>
            `;
            $(productItemsContainer).append(newRow);
            $('.product-item-row-modal:last .selectpicker').selectpicker('refresh');
            productItemCounter++;
        }
    };


    setupAllFormsAndEvents();

    console.log("JS cho quản lý Đơn hàng đã được khởi tạo thành công.");
}