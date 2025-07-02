// public/assets_admin/js/order_manager.js

/**
 * =================================================================================
 * order_manager.js - PHIÊN BẢN API-DRIVEN HOÀN CHỈNH VỚI DEBUGGING CẢI TIẾN
 * ---------------------------------------------------------------------------------
 * - Tái cấu trúc để tải danh sách sản phẩm động từ API /api/products/all-for-order.
 * - Loại bỏ sự phụ thuộc vào biến allProductsForJs được truyền từ Blade/PHP.
 * - Tối ưu hóa hiệu năng tải trang ban đầu.
 * - Sử dụng API backend để tính toán tổng đơn hàng chi tiết.
 * - Khắc phục các vấn đề đồng bộ hóa với Bootstrap-select.
 * - Thêm các log debug chi tiết để theo dõi luồng dữ liệu, đặc biệt là product_id.
 * - Áp dụng setTimeout cho việc gọi calculateAndUpdateAll từ handleProductSelectChange
 * để đảm bảo đồng bộ hóa giá trị select sau khi Bootstrap-select cập nhật DOM.
 * - Thêm logic hiển thị giá tiền sản phẩm trên từng dòng và cập nhật khi thay đổi số lượng.
 * - Cải thiện giao diện và chức năng nút tăng/giảm số lượng (chặn ở 1, thông báo tồn kho).
 * - Tối ưu hóa khởi tạo selectpicker để tránh khởi tạo nhiều lần.
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
    const $customerSelect = $('#customer_id_create'); // Select for existing customers
    const $productItemsContainer = $('#product-items-container');
    const NO_IMAGE_URL = '/assets_admin/images/no-image.png'; // URL ảnh mặc định

    // Biến state để lưu trữ dữ liệu sản phẩm và HTML options
    let allProducts = [];
    let productOptionsHtml = ''; // Sẽ được tạo khi dữ liệu được tải thành công
    let isCalculatingSummary = false; // Biến cờ để ngăn các yêu cầu API chồngF chéo

    // Khai báo phiên bản debounce của calculateAndUpdateAll (chỉ dùng cho các sự kiện khác ngoài product-select change)
    const calculateAndUpdateAllDebounced = debounce(calculateAndUpdateAll, 250); // Tăng độ trễ lên 250ms

    // B. MAIN INITIALIZATION
    function initialize() {
        if (!$('#adminOrdersPage').length) return;

        // Tải dữ liệu sản phẩm ngay khi trang được khởi tạo
        fetchAllProducts();

        initializeCreateModal();
        initializeUpdateModal();
        initializeViewModal();
        initializeDeleteModal();
    }

    // Hàm tải danh sách sản phẩm từ API
    async function fetchAllProducts() {
        // Chỉ tải một lần duy nhất
        if (allProducts.length > 0) return;

        try {
            console.log("Fetching all products for order creation...");
            const response = await fetch('/api/products/all-for-order');
            if (!response.ok) {
                throw new Error('Không thể tải danh sách sản phẩm từ server.');
            }
            allProducts = await response.json();
            buildProductOptionsHtml(); // Tạo HTML cho select options sau khi có dữ liệu
            console.log("Products fetched successfully.", allProducts);

            // ĐÃ XÓA: KHÔNG THÊM HÀNG SẢN PHẨM BAN ĐẦU TẠI ĐÂY NỮA.
            // Việc thêm hàng ban đầu sẽ được quản lý hoàn toàn bởi resetCreateForm khi modal mở.
            // if ($createModal.hasClass('show') && $productItemsContainer.is(':empty')) {
            //     console.log("Modal is open and container is empty, adding initial product row after product fetch.");
            //     addProductRow();
            // }

        } catch (error) {
            console.error('Lỗi khi tải sản phẩm:', error);
            showAppInfoModal('Không thể tải được danh sách sản phẩm. Vui lòng tải lại trang.', 'Lỗi nghiêm trọng', 'error');
            // Nếu lỗi, tạo một option thông báo lỗi
            productOptionsHtml = '<option value="">Lỗi tải sản phẩm</option>';
        }
    }

    // Hàm tạo chuỗi HTML <option> cho select sản phẩm
    function buildProductOptionsHtml() {
        let options = '<option value="">Chọn sản phẩm...</option>';
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
        console.log("Generated productOptionsHtml length:", productOptionsHtml.length);
    }


    // C. CREATE ORDER MODAL LOGIC
    function initializeCreateModal() {
        $createModal.on('show.bs.modal', resetCreateForm);
        $('#add-product-row-btn').on('click', addProductRow);

        $productItemsContainer.on('click', '.remove-product-item', handleRemoveRow);
        // Sử dụng sự kiện 'changed.bs.select' của Bootstrap-select
        $productItemsContainer.on('changed.bs.select', '.product-select', handleProductSelectChange);
        $productItemsContainer.on('change keyup', '.quantity-input', (e) => handleQuantityChange($(e.currentTarget)));
        $productItemsContainer.on('click', '.quantity-plus-btn', (e) => handleQuantityButtonClick($(e.currentTarget), 1));
        $productItemsContainer.on('click', '.quantity-minus-btn', (e) => handleQuantityButtonClick($(e.currentTarget), -1));

        // Event listener for customer type radio buttons
        $createForm.find('input[name="customer_type"]').on('change', handleCustomerTypeChange);
        // Event listener for existing customer selectpicker
        $customerSelect.on('changed.bs.select', (e) => handleCustomerSelect($(e.currentTarget).val()));

        // Setup dependent dropdowns for guest/unified address fields
        setupDependentDropdowns('guest_province_id', 'guest_district_id', 'guest_ward_id');

        // Event listeners for summary calculation (API-driven)
        $('#delivery_service_id_create').on('change', calculateAndUpdateAllDebounced); // Vẫn dùng debounce ở đây
        $('#apply-promo-btn-create').on('click', calculateAndUpdateAll);
        $('#clear-promo-btn-create').on('click', clearPromotionCode);
        $('#promotion_code_create').on('input', toggleClearPromoButton);

        setupAjaxForm('createOrderForm', 'createOrderModal');

        // THÊM LOGIC XÓA KHOẢNG TRẮNG ĐẦU VÀO CHO guest_name
        const guestNameInput = document.getElementById('guest_name');
        if (guestNameInput) {
            guestNameInput.addEventListener('input', function () {
                this.value = this.value.replace(/^\s+/, ''); // Xóa khoảng trắng đầu
            });
        }
    }

    function resetCreateForm() {
        console.log("Resetting Create Order Form...");
        clearValidationErrors($createForm[0]);
        $createForm[0].reset();
        $customerSelect.selectpicker('val', '');
        $('#delivery_service_id_create').val('');
        $('#promotion_code_create').val('');
        $('#promotion_id_for_form').val('');
        $('#promo-feedback-create').html('');
        $('#clear-promo-btn-create').addClass('d-none');
        $('#status_create').val('pending');

        // Reset customer type to existing and trigger change to set initial state
        $('#customerTypeExisting').prop('checked', true).trigger('change');

        // CHỈ THÊM HÀNG SẢN PHẨM BAN ĐẦU TẠI ĐÂY KHI MODAL ĐƯỢC RESET VÀ OPTIONS ĐÃ SẴN RÀNG
        $productItemsContainer.empty();
        if (productOptionsHtml) { // Đảm bảo options đã được tải trước khi thêm hàng
            addProductRow();
        } else {
            console.log("Product options not yet loaded when resetting form. Initial product row will be added once products are fetched.");
            // addProductRow sẽ được gọi từ fetchAllProducts nếu modal đang mở.
        }


        // Reset guest/unified address fields
        $('#guest_name').val('');
        $('#guest_phone').val('');
        $('#guest_email').val('');
        $('#guest_province_id').val('').trigger('change'); // Trigger change để reset districts/wards
        $('#guest_district_id').html('<option value="">Chọn Quận/Huyện</option>').prop('disabled', true);
        $('#guest_ward_id').html('<option value="">Chọn Phường/Xã</option>').prop('disabled', true);
        $('#guest_address_line').val('');
    }

    function addProductRow() {
        console.log("Adding new product row...");
        const rowIndex = Date.now();
        const template = document.getElementById('product-row-template');
        if (!template) {
            console.error("Product row template not found!");
            return;
        }

        const newRowHtml = template.innerHTML.replace(/NEW_ROW_INDEX/g, rowIndex);
        const $newRow = $(newRowHtml);

        const $select = $newRow.find('.product-select');
        // QUAN TRỌNG: Xóa tất cả các option cũ trước khi điền lại để tránh lặp dữ liệu
        $select.empty().html(productOptionsHtml); // Điền options vào select

        $productItemsContainer.append($newRow);

        if (typeof $.fn.selectpicker === 'function') {
            // Luôn khởi tạo selectpicker cho phần tử SELECT MỚI NÀY
            // Kiểm tra !$select.data('selectpicker') chỉ có ý nghĩa nếu phần tử đó có thể đã được khởi tạo trước đó
            // trong ngữ cảnh khác. Trong addProductRow, nó luôn là một phần tử mới.
            $select.selectpicker(); // Khởi tạo selectpicker cho phần tử <select> mới được thêm vào DOM
            console.log(`Product selectpicker initialized and refreshed for new row ${rowIndex}.`);

            // Không cần gọi riêng 'refresh' nếu nó là khởi tạo lần đầu cho phần tử đó.
            // selectpicker() khi khởi tạo sẽ tự động render.
            // Nếu có trường hợp cần thay đổi options sau khi đã khởi tạo, mới dùng refresh.
            // Ở đây, options được điền trước khi selectpicker() được gọi.
        } else {
            console.warn("Bootstrap-select is not initialized. Product dropdown might not work correctly.");
        }

        // Cập nhật trạng thái nút +/- ban đầu
        const $quantityInput = $newRow.find('.quantity-input');
        updateQuantityButtons($quantityInput);

        calculateAndUpdateAll(); // Called directly for immediate initial calculation
    }
    function handleRemoveRow(event) {
        console.log("Removing product row.");
        $(event.currentTarget).closest('.product-item-row').remove();
        calculateAndUpdateAll(); // Called directly for immediate update
    }

    function handleProductSelectChange(event) {
        const $select = $(event.currentTarget);
        const productId = $select.val(); // Lấy giá trị từ selectpicker.
        console.log("Selected Product ID from select (inside handleProductSelectChange):", productId);

        const $row = $select.closest('.product-item-row');
        const $selectedOption = $select.find('option:selected');
        const imageUrl = $selectedOption.data('image-url') || NO_IMAGE_URL;
        const stock = parseInt($selectedOption.data('stock')) || 0;
        const price = parseFloat($selectedOption.data('price')) || 0;
        const $quantityInput = $row.find('.quantity-input');
        const $productSubtotalValue = $row.find('.product-subtotal-value');
        const $stockInfoMessage = $row.find('.stock-info-message'); // Lấy phần tử thông báo tồn kho

        $row.find('.product-image').attr('src', imageUrl);
        $quantityInput.attr('max', stock); // Cập nhật max attribute

        let currentQuantity = parseInt($quantityInput.val()) || 1;
        currentQuantity = Math.max(1, currentQuantity);
        if (stock > 0) {
            currentQuantity = Math.min(currentQuantity, stock);
        }
        $quantityInput.val(currentQuantity);

        $productSubtotalValue.text(formatCurrency(price * currentQuantity));

        // Cập nhật trạng thái nút +/- và thông báo tồn kho
        updateQuantityButtons($quantityInput);
        updateStockInfoMessage($quantityInput, stock);


        // KHÔNG GỌI $select.selectpicker('refresh'); ở đây. (ĐÃ XÓA)
        // THAY THẾ LỜI GỌI DEBOUNCED BẰNG setTimeout TRỰC TIẾP (ĐÃ XÓA)
        // Đảm bảo calculateAndUpdateAll chạy sau khi DOM đã được cập nhật
        setTimeout(() => {
            calculateAndUpdateAll();
        }, 300); // Tăng độ trễ lên 300ms, có thể tăng lên 500ms nếu cần
        // Điều này sẽ cho Bootstrap-select đủ thời gian để cập nhật <select> gốc.
    }

    function handleQuantityChange($input) {
        let value = parseInt($input.val()) || 0;
        const max = parseInt($input.attr('max')) || 0;
        const $row = $input.closest('.product-item-row');
        const $select = $row.find('.product-select');
        const $selectedOption = $select.find('option:selected');
        const price = parseFloat($selectedOption.data('price')) || 0;
        const stock = parseInt($selectedOption.data('stock')) || 0; // Lấy stock
        const $productSubtotalValue = $row.find('.product-subtotal-value');
        const $stockInfoMessage = $row.find('.stock-info-message'); // Lấy phần tử thông báo tồn kho

        value = Math.max(1, value); // Chặn số lượng tối thiểu là 1
        if (max > 0) {
            value = Math.min(value, max); // Chặn số lượng không vượt quá tồn kho
        }
        $input.val(value);

        $productSubtotalValue.text(formatCurrency(price * value));

        // Cập nhật trạng thái nút +/- và thông báo tồn kho
        updateQuantityButtons($input);
        updateStockInfoMessage($input, stock);

        calculateAndUpdateAll(); // Called directly for immediate update
    }

    function handleQuantityButtonClick($button, change) {
        const $quantityInput = $button.siblings('.quantity-input');
        let currentQuantity = parseInt($quantityInput.val()) || 0;

        currentQuantity += change;
        $quantityInput.val(currentQuantity); // Cập nhật giá trị input trước khi gọi handleQuantityChange
        handleQuantityChange($quantityInput); // Gọi hàm xử lý thay đổi số lượng
    }

    // Hàm cập nhật trạng thái nút +/-
    function updateQuantityButtons($quantityInput) {
        const currentQuantity = parseInt($quantityInput.val()) || 0;
        const maxStock = parseInt($quantityInput.attr('max')) || 0;
        const $row = $quantityInput.closest('.product-item-row');
        const $minusBtn = $row.find('.quantity-minus-btn');
        const $plusBtn = $row.find('.quantity-plus-btn');

        // Vô hiệu hóa nút trừ nếu số lượng là 1
        if (currentQuantity <= 1) {
            $minusBtn.prop('disabled', true);
        } else {
            $minusBtn.prop('disabled', false);
        }

        // Vô hiệu hóa nút cộng nếu số lượng đạt tồn kho tối đa
        if (maxStock > 0 && currentQuantity >= maxStock) {
            $plusBtn.prop('disabled', true);
        } else {
            $plusBtn.prop('disabled', false);
        }
    }

    // Hàm cập nhật thông báo tồn kho
    function updateStockInfoMessage($quantityInput, stock) {
        const currentQuantity = parseInt($quantityInput.val()) || 0;
        const $stockInfoMessage = $quantityInput.closest('.product-item-row').find('.stock-info-message');

        if (stock === 0) {
            $stockInfoMessage.text('Hết hàng!').removeClass('text-info').addClass('text-danger');
        } else if (currentQuantity >= stock) {
            $stockInfoMessage.text(`Còn lại: ${stock}`).removeClass('text-info').addClass('text-warning');
        } else {
            $stockInfoMessage.text('').removeClass('text-danger text-warning').addClass('text-info');
        }
    }


    // Debounce function to limit API calls (especially for input/change events)
    function debounce(func, delay) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    // Async function to calculate and update all summary fields via API
    async function calculateAndUpdateAll() {
        if (isCalculatingSummary) {
            console.log("Calculation already in progress, skipping.");
            return; // Prevent multiple concurrent requests
        }
        isCalculatingSummary = true;
        showAppLoader();
        clearValidationErrors($('#createOrderForm')[0]); // Clear old validation errors

        const orderItems = [];
        $productItemsContainer.find('.product-item-row').each(function (index) {
            const $row = $(this);
            // SỬA ĐỔI QUAN TRỌNG: Đảm bảo tìm phần tử <select> gốc
            const $productSelectElement = $row.find('select.product-select'); // CHỈ ĐỊNH RÕ LÀ THẺ <select>

            // Kiểm tra nếu không tìm thấy phần tử select gốc (tránh lỗi nếu DOM chưa sẵn sàng)
            if ($productSelectElement.length === 0) {
                console.warn(`DEBUG: No <select.product-select> found for row ${index}. Skipping item.`);
                return; // Bỏ qua mục này nếu không tìm thấy select gốc
            }

            const productId = $productSelectElement.val(); // Lấy giá trị từ select gốc
            const quantity = parseInt($row.find('.quantity-input').val()) || 0;

            // DEBUG LOGS QUAN TRỌNG:
            console.log(`DEBUG: Row ${index} - Found Select Element:`, $productSelectElement[0]); // Log phần tử DOM gốc
            console.log(`DEBUG: Row ${index} - Product ID from Select (jQuery .val()): '${productId}'`); // Giá trị từ jQuery .val()
            console.log(`DEBUG: Row ${index} - Product ID from Select (DOM .value property): '${$productSelectElement[0].value}'`); // Giá trị từ thuộc tính DOM .value
            console.log(`DEBUG: Row ${index} - Quantity: ${quantity}`);

            // LUÔN THÊM MỤC VÀO, KỂ CẢ KHI product_id HOẶC quantity KHÔNG HỢP LỆ
            // Điều này cho phép backend validation bắt lỗi và trả về phản hồi chi tiết.
            orderItems.push({
                product_id: productId, // Sẽ là "" nếu chưa chọn
                quantity: quantity
            });
        });

        const deliveryServiceId = $('#delivery_service_id_create').val();
        const promotionCode = $('#promotion_code_create').val();

        const payload = {
            items: orderItems,
            delivery_service_id: deliveryServiceId,
            promotion_code: promotionCode
        };

        console.log('Payload sent to calculate-summary API:', payload); // DEBUG: Final payload check

        try {
            const response = await fetch('/admin/sales/orders/calculate-summary', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (response.ok && data.success) {
                const summary = data.summary;
                $('#summary-subtotal').text(formatCurrency(summary.subtotal));
                $('#summary-shipping').text(formatCurrency(summary.shipping_fee));
                $('#summary-discount').text(`-${formatCurrency(summary.discount_amount)}`);
                $('#summary-grand-total').text(formatCurrency(summary.total_price));

                // Toggle discount row visibility
                $('#summary-discount-row').toggleClass('d-none', summary.discount_amount <= 0);

                // Update hidden promotion_id_for_form
                $('#promotion_id_for_form').val(summary.promotion_id || '');

                // Update promotion feedback
                const promoFeedbackEl = $('#promo-feedback-create');
                if (summary.promo_error) {
                    promoFeedbackEl.html(`<div class='text-danger small mt-1'>${summary.promo_error}</div>`);
                    $('#clear-promo-btn-create').removeClass('d-none'); // Show clear button on error
                } else if (summary.promotion_info && summary.promotion_info.code) {
                    promoFeedbackEl.html(`<div class='text-success small mt-1'>Đã áp dụng mã: <strong>${summary.promotion_info.code}</strong></div>`);
                    $('#clear-promo-btn-create').removeClass('d-none'); // Show clear button if applied
                } else {
                    promoFeedbackEl.html('');
                    if (!$('#promotion_code_create').val()) {
                        $('#clear-promo-btn-create').addClass('d-none');
                    }
                }

                // Clear specific item validation errors if calculation successful
                $productItemsContainer.find('.invalid-feedback[data-field^="items."]').text('').hide();

            } else {
                // Handle validation errors from API or general errors
                const errors = data.errors;
                console.warn('API calculation failed. Errors:', errors);
                if (errors) {
                    // Clear all previous item errors
                    $productItemsContainer.find('.invalid-feedback[data-field^="items."]').text('').hide();
                    $('#error-items').text('').hide(); // Clear general items error

                    // Display general items error if present
                    if (errors.items) {
                        $('#error-items').text(errors.items[0]).show();
                    }

                    // Display specific item errors
                    for (const key in errors) {
                        if (key.startsWith('items.') && (key.endsWith('.product_id') || key.endsWith('.quantity'))) {
                            const errorField = $(`[data-field="${key}"]`);
                            if (errorField.length) {
                                errorField.text(errors[key][0]).show();
                            }
                        }
                    }

                    if (errors.promotion_code) {
                        $('#promo-feedback-create').html(`<div class='text-danger small mt-1'>${errors.promotion_code[0]}</div>`);
                        $('#clear-promo-btn-create').removeClass('d-none');
                    }
                } else {
                    showAppInfoModal(data.message || 'Đã có lỗi xảy ra khi tính toán tóm tắt đơn hàng.', 'Lỗi', 'error');
                }
                // Reset summary and hidden promo ID on error
                $('#summary-subtotal').text(formatCurrency(0));
                $('#summary-shipping').text(formatCurrency(0));
                $('#summary-discount').text(`-${formatCurrency(0)}`);
                $('#summary-grand-total').text(formatCurrency(0));
                $('#summary-discount-row').addClass('d-none');
                $('#promotion_id_for_form').val('');
            }
        } catch (error) {
            console.error('Error updating order summary:', error);
            showAppInfoModal('Không thể cập nhật tóm tắt đơn hàng. Vui lòng kiểm tra kết nối.', 'Lỗi', 'error');
            // Reset summary and hidden promo ID on network error
            $('#summary-subtotal').text(formatCurrency(0));
            $('#summary-shipping').text(formatCurrency(0));
            $('#summary-discount').text(`-${formatCurrency(0)}`);
            $('#summary-grand-total').text(formatCurrency(0));
            $('#summary-discount-row').addClass('d-none');
            $('#promotion_id_for_form').val('');
        } finally {
            hideAppLoader();
            isCalculatingSummary = false;
        }
    }


    function handleCustomerTypeChange() {
        const isExisting = $('#customerTypeExisting').is(':checked');
        $('#existing_customer_block').toggle(isExisting);
        // guest_customer_block luôn hiển thị vì nó là nơi nhập địa chỉ thống nhất
        $('#guest_customer_block').toggle(true);

        if (isExisting) {
            // Khi chuyển sang khách hàng có sẵn, xóa các trường khách vãng lai để chuẩn bị điền từ data khách hàng
            $('#guest_name').val('');
            $('#guest_phone').val('');
            $('#guest_email').val('');
            $('#guest_province_id').val('').trigger('change'); // Trigger change để reset districts/wards
            $('#guest_district_id').html('<option value="">Chọn Quận/Huyện</option>').prop('disabled', true);
            $('#guest_ward_id').html('<option value="">Chọn Phường/Xã</option>').prop('disabled', true);
            $('#guest_address_line').val('');
            $customerSelect.selectpicker('val', ''); // Đảm bảo select khách hàng có sẵn được reset
        } else {
            // Khi chuyển sang khách vãng lai, đảm bảo select khách hàng có sẵn được xóa
            $customerSelect.selectpicker('val', '');
        }
        clearValidationErrors($createForm[0]);
        calculateAndUpdateAll();
    }

    async function handleCustomerSelect(customerId) {
        const $guestName = $('#guest_name');
        const $guestPhone = $('#guest_phone');
        const $guestEmail = $('#guest_email');
        const $guestProvinceSelect = $('#guest_province_id');
        const $guestDistrictSelect = $('#guest_district_id');
        const $guestWardSelect = $('#guest_ward_id');
        const $guestAddressLine = $('#guest_address_line');

        // Luôn xóa các trường khách vãng lai trước khi điền
        $guestName.val('');
        $guestPhone.val('');
        $guestEmail.val('');
        $guestProvinceSelect.val('').trigger('change'); // Trigger change để reset districts/wards
        $guestDistrictSelect.html('<option value="">Chọn Quận/Huyện</option>').prop('disabled', true);
        $guestWardSelect.html('<option value="">Chọn Phường/Xã</option>').prop('disabled', true);
        $guestAddressLine.val('');

        if (!customerId) {
            // Nếu không có khách hàng nào được chọn (ví dụ: dropdown bị xóa), chỉ cần xóa các trường và thoát.
            return;
        }

        showAppLoader();
        try {
            const response = await fetch(`/admin/api/customers/${customerId}/addresses`);
            if (!response.ok) throw new Error('Không thể tải địa chỉ của khách hàng.');
            const addresses = await response.json();

            // Populate guest fields with customer's info and default address
            const customerOption = $(`#customer_id_create option[value="${customerId}"]`);
            // SỬA ĐỔI QUAN TRỌNG: Thêm .trim() để loại bỏ khoảng trắng thừa
            $guestName.val(customerOption.text().trim()); // Tên khách hàng
            $guestEmail.val(customerOption.data('subtext') ? customerOption.data('subtext').trim() : ''); // Email khách hàng (cũng trim nếu có)
            console.log("DEBUG: Customer Name after trim:", customerOption.text().trim());

            if (addresses && addresses.length > 0) {
                // SỬA LỖI: defaultAddress.is_default phải là addr.is_default
                const defaultAddress = addresses.find(addr => addr.is_default) || addresses[0];

                $guestPhone.val(defaultAddress.phone || ''); // Đảm bảo không undefined
                $guestAddressLine.val(defaultAddress.address_line || ''); // Đảm bảo không undefined
                console.log("DEBUG: Default Address Line from API:", defaultAddress.address_line); // DEBUGGING

                // Logic để điền Tỉnh/Thành, Quận/Huyện, Phường/Xã
                $guestProvinceSelect.val(defaultAddress.province_id).trigger('change');

                let districtLoaded = false;
                const checkAndSetDistrict = () => {
                    // Kiểm tra xem option của district đã được load vào dropdown chưa
                    if ($guestDistrictSelect.find(`option[value="${defaultAddress.district_id}"]`).length) {
                        $guestDistrictSelect.val(defaultAddress.district_id);
                        districtLoaded = true;
                        $guestDistrictSelect.trigger('change'); // Trigger change để load wards
                    } else {
                        setTimeout(checkAndSetDistrict, 100); // Thử lại sau 100ms
                    }
                };

                const checkAndSetWard = () => {
                    // Kiểm tra xem option của ward đã được load vào dropdown chưa
                    if ($guestWardSelect.find(`option[value="${defaultAddress.ward_id}"]`).length) {
                        $guestWardSelect.val(defaultAddress.ward_id);
                    } else {
                        setTimeout(checkAndSetWard, 100); // Thử lại sau 100ms
                    }
                };

                checkAndSetDistrict(); // Bắt đầu kiểm tra và set district
                // Chỉ bắt đầu kiểm tra và set ward sau khi district đã được load
                const interval = setInterval(() => {
                    if (districtLoaded) {
                        clearInterval(interval);
                        checkAndSetWard();
                    }
                }, 50);

            } else {
                showAppInfoModal('Khách hàng này chưa có địa chỉ mặc định. Vui lòng nhập địa chỉ mới.', 'Thông báo');
            }
        } catch (error) {
            console.error("Error fetching customer addresses:", error); // DEBUGGING
            showAppInfoModal(error.message, 'Lỗi');
        } finally {
            hideAppLoader();
        }
        calculateAndUpdateAll();
    }

    // Function to clear promotion code
    function clearPromotionCode() {
        $('#promotion_code_create').val('');
        $('#clear-promo-btn-create').addClass('d-none');
        $('#promo-feedback-create').html('');
        calculateAndUpdateAll(); // Re-calculate after clearing promo
    }

    // Function to toggle clear promo button visibility
    function toggleClearPromoButton() {
        const promoCode = $('#promotion_code_create').val();
        // Show clear button if there's any text, or if there was a promo error displayed
        const hasPromoFeedback = $('#promo-feedback-create').text().trim() !== '';
        $('#clear-promo-btn-create').toggleClass('d-none', !(promoCode || hasPromoFeedback));
        if (!promoCode && !hasPromoFeedback) {
            $('#promo-feedback-create').html(''); // Clear feedback if input is empty and no error was shown
        }
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
            : `Khách vãng lai: ${order.guest_name}`; // Use guest_name for consistency
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
        $('#viewDetailCustomerName').text(order.guest_name); // Use guest_name
        $('#viewDetailCustomerPhone').text(order.guest_phone); // Use guest_phone
        $('#viewDetailCustomerEmail').text(order.guest_email || 'N/A'); // Use guest_email
        const fullAddress = (order.shipping_address_line && order.ward && order.district && order.province)
            ? `${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}`
            : 'Địa chỉ không đầy đủ';
        $('#viewDetailOrderFullAddress').text(fullAddress);
        $('#viewDetailOrderPaymentMethod').text(order.payment_method ? order.payment_method.name : 'N/A');
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
                                <p><strong>Tên:</strong> ${order.guest_name}</p>
                                <p><strong>SĐT:</strong> ${order.guest_phone}</p>
                                <p><strong>Email:</strong> ${order.guest_email || 'N/A'}</p>
                                <p><strong>Địa chỉ:</strong> ${order.shipping_address_line}, ${order.ward.name}, ${order.district.name}, ${order.province.name}</p>
                            </div>
                            <div class="col-6">
                                <p class="fw-bold">Thông tin đơn hàng:</p>
                                <p><strong>Trạng thái:</strong> <span class="badge bg-info">${order.status_text}</span></p>
                                <p><strong>Phương thức thanh toán:</strong> ${order.payment_method ? order.payment_method.name : 'N/A'}</p>
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
                        <p>Đội ngũ ${window.APP_NAME || 'Thanhdoshop'}</p>
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
                console.error("Error loading districts:", e);
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
                console.error("Error loading wards:", e);
                $ward.html('<option value="">Lỗi tải</option>');
            }
        });
    }

    // F. KICKSTART THE SCRIPT
    initialize();
};
