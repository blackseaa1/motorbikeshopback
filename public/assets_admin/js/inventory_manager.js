/**
 * =================================================================================
 * inventory_manager.js
 * ---------------------------------------------------------------------------------
 * Quản lý các chức năng tương tác trên trang tồn kho (Inventory).
 * - Cập nhật số lượng sản phẩm trực tiếp qua AJAX.
 * - Hiển thị chi tiết sản phẩm trong modal.
 * =================================================================================
 */
window.initializeInventoryManager = (
    showAppLoader,
    hideAppLoader,
    showAppInfoModal,
    setupAjaxForm, // Not directly used here, but kept for consistency if needed
    clearValidationErrors // Not directly used here, but kept for consistency if needed
) => {
    'use strict';

    // A. UTILITIES & DOM CACHING
    const formatCurrency = (value) => {
        if (isNaN(value)) return '0 ₫';
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    };

    const $adminInventoryPage = $('#adminInventoryPage');
    const $viewProductDetailsModal = $('#viewProductDetailsModal');
    const $updateQuantityModal = $('#updateQuantityModal'); // Cache the update quantity modal
    const $newStockQuantityInput = $('#newStockQuantity'); // Cache the new quantity input in the modal

    // B. MAIN INITIALIZATION
    function initialize() {
        if (!$adminInventoryPage.length) return;

        // Setup for inline quantity controls (if they were present, currently removed from blade)
        setupQuantityControls();
        // Setup for product details modal
        setupViewDetailsModal();
        // Setup for update quantity modal
        setupUpdateQuantityModal();
    }

    // C. QUANTITY CONTROL LOGIC (for inline +/- buttons, currently not used in blade)
    // This function is kept for completeness but its event listeners are not active
    // because the corresponding HTML elements have been removed from inventory.blade.php.
    // If you re-introduce inline +/- buttons, uncomment the event listeners here.
    function setupQuantityControls() {
        // $adminInventoryPage.on('click', '.quantity-minus-btn', function() {
        //     const $input = $(this).siblings('.quantity-input');
        //     let currentValue = parseInt($input.val()) || 0;
        //     if (currentValue > 0) {
        //         $input.val(currentValue - 1).trigger('change');
        //     }
        // });

        // $adminInventoryPage.on('click', '.quantity-plus-btn', function() {
        //     const $input = $(this).siblings('.quantity-input');
        //     let currentValue = parseInt($input.val()) || 0;
        //     $input.val(currentValue + 1).trigger('change');
        // });

        // $adminInventoryPage.on('change', '.quantity-input', function() {
        //     const $input = $(this);
        //     const productId = $input.data('id');
        //     let newQuantity = parseInt($input.val());
        //     const currentStock = parseInt($input.data('current-stock'));
        //     const $statusMessage = $(`.stock-status-message[data-id="${productId}"]`);

        //     if (isNaN(newQuantity) || newQuantity < 0) {
        //         newQuantity = 0;
        //         $input.val(newQuantity);
        //     }

        //     if (newQuantity === currentStock) {
        //         $statusMessage.text('');
        //         return;
        //     }
        // });
    }

    /**
     * Sends an AJAX request to update product stock.
     * Cập nhật số lượng tồn kho sản phẩm qua AJAX.
     * @param {number} productId - ID của sản phẩm.
     * @param {number} newQuantity - Số lượng tồn kho mới.
     * @param {jQuery} $inlineInputRef - Tham chiếu đến phần tử input số lượng inline trên bảng (để cập nhật giá trị hiển thị).
     * @param {jQuery} $inlineStatusMessageRef - Tham chiếu đến phần tử hiển thị thông báo trạng thái inline.
     */
    async function updateProductStock(productId, newQuantity, $inlineInputRef, $inlineStatusMessageRef) {
        showAppLoader();
        // Cập nhật trạng thái hiển thị inline trên bảng
        $inlineStatusMessageRef.removeClass('text-success text-danger').text('Đang cập nhật...');
        $('#updateQuantityError').text(''); // Xóa lỗi cũ trong modal

        try {
            const response = await fetch(`/admin/product-management/api/products/${productId}/update-stock`, {
                method: 'PUT', // Sử dụng PUT cho cập nhật
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Lấy CSRF token từ meta tag
                },
                body: JSON.stringify({ stock_quantity: newQuantity })
            });

            const data = await response.json();

            if (response.ok) {
                // Cập nhật giá trị và data-current-stock của input inline trên bảng
                $inlineInputRef.data('current-stock', newQuantity);
                $inlineInputRef.val(newQuantity); // Cập nhật giá trị hiển thị trong input (nếu có)

                // Cập nhật số lượng hiển thị trong cột "Tồn Kho" (span.badge)
                $inlineInputRef.closest('td').find('.badge').text(newQuantity);

                $inlineStatusMessageRef.text('Đã cập nhật!').addClass('text-success');
                showAppInfoModal('Cập nhật tồn kho thành công!', 'Thành công', 'success');
                $updateQuantityModal.modal('hide'); // Đóng modal khi thành công
            } else {
                if (response.status === 422 && data.errors && data.errors.stock_quantity) {
                    $inlineStatusMessageRef.text(data.errors.stock_quantity[0]).addClass('text-danger');
                    $('#updateQuantityError').text(data.errors.stock_quantity[0]); // Hiển thị lỗi trong modal
                } else {
                    $inlineStatusMessageRef.text(data.message || 'Cập nhật thất bại!').addClass('text-danger');
                    $('#updateQuantityError').text(data.message || 'Cập nhật thất bại!'); // Hiển thị lỗi trong modal
                }
                // Khôi phục giá trị cũ trong input inline nếu cập nhật thất bại
                // $inlineInputRef.val($inlineInputRef.data('current-stock')); // Không cần thiết nếu không có input inline
            }
        } catch (error) {
            console.error('Lỗi cập nhật tồn kho:', error);
            $inlineStatusMessageRef.text('Lỗi kết nối!').addClass('text-danger');
            $('#updateQuantityError').text('Lỗi kết nối đến máy chủ!'); // Hiển thị lỗi trong modal
            // $inlineInputRef.val($inlineInputRef.data('current-stock')); // Không cần thiết nếu không có input inline
        } finally {
            hideAppLoader();
            setTimeout(() => $inlineStatusMessageRef.text(''), 3000); // Xóa thông báo inline sau 3 giây
        }
    }

    // D. VIEW PRODUCT DETAILS MODAL LOGIC
    function setupViewDetailsModal() {
        $adminInventoryPage.on('click', '.view-product-details-btn', async function() {
            const productId = $(this).data('id');
            showAppLoader();
            $viewProductDetailsModal.modal('show'); // Hiển thị modal ngay lập tức để người dùng thấy trạng thái tải

            try {
                // Gọi API để lấy chi tiết sản phẩm
                const response = await fetch(`/admin/product-management/api/products/${productId}/details`);
                if (!response.ok) {
                    throw new Error('Không thể tải chi tiết sản phẩm. Mã lỗi: ' + response.status);
                }
                const product = await response.json();
                populateProductDetailsModal(product);
            } catch (error) {
                console.error('Lỗi tải chi tiết sản phẩm:', error);
                showAppInfoModal(error.message, 'Lỗi', 'error');
                $viewProductDetailsModal.modal('hide'); // Đóng modal nếu có lỗi
            } finally {
                hideAppLoader();
            }
        });
    }

    /**
     * Populates the product details modal with data.
     * Điền dữ liệu sản phẩm vào modal chi tiết.
     * @param {object} product - Đối tượng sản phẩm.
     */
    function populateProductDetailsModal(product) {
        if (!product) return;

        // Điền thông tin cơ bản
        $('#viewProductName').text(product.name);
        $('#viewProductId').text(product.id);
        $('#viewProductNameDetail').text(product.name);
        $('#viewProductPrice').text(formatCurrency(product.price));
        $('#viewProductStock').text(product.stock_quantity);
        $('#viewProductCategory').text(product.category ? product.category.name : 'N/A');
        $('#viewProductBrand').text(product.brand ? product.brand.name : 'N/A');
        $('#viewProductStatusBadge').html(`<span class="badge ${product.status_badge_class}">${product.status_text}</span>`);
        $('#viewProductImage').attr('src', product.thumbnail_url || NO_IMAGE_URL);
        $('#viewProductDescription').html(product.description || 'Không có mô tả.');
        $('#viewProductSpecifications').html(product.specifications || 'Không có thông số kỹ thuật.');

        // Điền thông tin dòng xe tương thích
        const $vehicleModelsContainer = $('#viewProductVehicleModels');
        $vehicleModelsContainer.empty(); // Xóa nội dung cũ
        if (product.vehicle_models && product.vehicle_models.length > 0) {
            product.vehicle_models.forEach(model => {
                const brandName = model.vehicle_brand ? model.vehicle_brand.name : '';
                $vehicleModelsContainer.append(`<span class="badge bg-secondary me-1 mb-1">${brandName} - ${model.name}</span>`);
            });
        } else {
            $vehicleModelsContainer.append('<span class="text-muted">Không có dòng xe tương thích.</span>');
        }

        // Cập nhật link "Chỉnh sửa đầy đủ" trong footer modal
        $('#viewProductEditLink').attr('href', `/admin/product-management/products?search=${product.id}`);
    }

    // E. UPDATE QUANTITY MODAL LOGIC
    function setupUpdateQuantityModal() {
        // Event listener for opening the update quantity modal
        $adminInventoryPage.on('click', '.open-update-quantity-modal-btn', function() {
            const productId = $(this).data('id');
            const $row = $(this).closest('tr');
            const productName = $row.find('td:nth-child(2)').text(); // Lấy tên sản phẩm từ cột thứ 2
            // Lấy số lượng tồn kho hiện tại đang hiển thị trên bảng
            const currentQuantity = parseInt($row.find('.badge.bg-danger').text()) || 0;

            // Điền dữ liệu vào modal
            $('#updateQuantityProductId').val(productId);
            $('#updateQuantityProductName').text(productName);
            $newStockQuantityInput.val(currentQuantity); // Đặt giá trị hiện tại vào input của modal
            $('#updateQuantityError').text(''); // Xóa lỗi cũ

            $updateQuantityModal.modal('show'); // Hiển thị modal
        });

        // Event listener for confirming update in the modal
        $('#confirmUpdateQuantityBtn').on('click', function() {
            const productId = $('#updateQuantityProductId').val();
            const newQuantity = parseInt($newStockQuantityInput.val());

            // Lấy tham chiếu đến phần tử hiển thị số lượng inline và thông báo trạng thái
            const $inlineQuantityBadge = $adminInventoryPage.find(`tr button.open-update-quantity-modal-btn[data-id="${productId}"]`).closest('tr').find('.badge.bg-danger');
            const $inlineStatusMessage = $adminInventoryPage.find(`tr button.open-update-quantity-modal-btn[data-id="${productId}"]`).closest('tr').find('.stock-status-message');

            // Validate input in modal before sending
            if (isNaN(newQuantity) || newQuantity < 0) {
                $('#updateQuantityError').text('Số lượng phải là một số nguyên không âm.');
                return;
            }

            // Gọi hàm cập nhật tồn kho, truyền các tham chiếu để cập nhật hiển thị
            updateProductStock(productId, newQuantity, $inlineQuantityBadge, $inlineStatusMessage);
        });
    }

    // F. KICKSTART THE SCRIPT
    initialize();
};
