/**
 * =================================================================================
 * inventory_manager.js
 * ---------------------------------------------------------------------------------
 * Quản lý các chức năng tương tác trên trang tồn kho (Inventory).
 * - Cập nhật số lượng sản phẩm trực tiếp qua AJAX.
 * - Hiển thị chi tiết sản phẩm trong modal.
 * - Tải dữ liệu bảng và phân trang bằng AJAX.
 * =================================================================================
 */
window.initializeInventoryManager = (
    showAppLoader,
    hideAppLoader,
    showAppInfoModal
    // setupAjaxForm, // Not directly used here, but kept for consistency if needed
    // clearValidationErrors // Not directly used here, but kept for consistency if needed
) => {
    'use strict';

    // A. UTILITIES & DOM CACHING
    const formatCurrency = (value) => {
        if (isNaN(value)) return '0 ₫';
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    };

    // Kiểm tra và khởi tạo jQuery trước khi sử dụng
    if (typeof jQuery === 'undefined') {
        console.error('Lỗi: jQuery không được tải. inventory_manager.js sẽ không hoạt động.');
        return;
    }
    console.log('jQuery đã được tải.');


    const $adminInventoryPage = $('#adminInventoryPage');
    if ($adminInventoryPage.length === 0) {
        console.error('Lỗi: Không tìm thấy phần tử #adminInventoryPage. Script sẽ dừng.');
        return;
    }
    console.log('Đã tìm thấy #adminInventoryPage.');

    const $viewProductDetailsModal = $('#viewProductDetailsModal');
    const $updateQuantityModal = $('#updateQuantityModal'); // Cache the update quantity modal
    const $newStockQuantityInput = $('#newStockQuantity'); // Cache the new quantity input in the modal

    // Lấy CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token! Script sẽ dừng.');
        return;
    }
    console.log('CSRF Token đã được tìm thấy.');


    // Cache các phần tử bảng và phân trang
    const inventoryTableBody = document.getElementById('inventory-table-body');
    if (!inventoryTableBody) {
        console.error('Lỗi: Không tìm thấy phần tử #inventory-table-body. Script sẽ dừng.');
        return;
    }
    console.log('Đã tìm thấy #inventory-table-body.');

    let inventoryPaginationLinksContainer = document.getElementById('inventory-pagination-links'); // Biến này sẽ được cập nhật lại

    // B. MAIN INITIALIZATION
    function initialize() {
        // Kiểm tra lại lần nữa để đảm bảo các phần tử quan trọng nhất đã có
        if (!$adminInventoryPage.length || !inventoryTableBody) {
            console.error('Khởi tạo thất bại: Một số phần tử DOM cần thiết không có.');
            return;
        }
        console.log('Bắt đầu khởi tạo các chức năng quản lý tồn kho.');

        // Setup for product details modal
        setupViewDetailsModal();
        // Setup for update quantity modal
        setupUpdateQuantityModal();

        // Tải dữ liệu tồn kho ban đầu bằng AJAX
        loadInventoryTable(1);
    }

    // C. AJAX TABLE LOADING & PAGINATION
    /**
     * Lấy số trang hiện tại từ phân trang.
     */
    function getCurrentPage() {
        if (inventoryPaginationLinksContainer) {
            const activePageLink = inventoryPaginationLinksContainer.querySelector('.page-item.active .page-link');
            if (activePageLink) {
                return parseInt(activePageLink.textContent, 10);
            }
        }
        return 1;
    }

    /**
     * Cập nhật nội dung bảng và liên kết phân trang.
     * @param {string} tableRowsHtml - HTML cho các hàng của bảng.
     * @param {string} paginationLinksHtml - HTML cho các liên kết phân trang.
     */
    function updateInventoryTableContent(tableRowsHtml, paginationLinksHtml) {
        console.log('Cập nhật nội dung bảng tồn kho...');
        inventoryTableBody.innerHTML = tableRowsHtml || `
            <tr id="no-low-stock-products-row">
                <td colspan="7" class="text-center">
                    <div class="alert alert-info mb-0">Không có sản phẩm nào sắp hết hàng.</div>
                </td>
            </tr>`;

        const cardBody = document.querySelector('#adminInventoryPage .card-body');
        if (!cardBody) {
            console.error("Card body not found. Cannot update pagination.");
        } else {
            // Xóa phần tử phân trang hiện có nếu nó tồn tại
            let existingPaginationDiv = cardBody.querySelector('#inventory-pagination-links');
            if (existingPaginationDiv) {
                existingPaginationDiv.remove();
                console.log('Đã xóa phần tử phân trang cũ.');
            }

            // Nếu có HTML phân trang mới, tạo và thêm phần tử mới
            if (paginationLinksHtml && paginationLinksHtml.trim() !== '') {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = paginationLinksHtml;
                const newPaginationDiv = tempDiv.querySelector('#inventory-pagination-links');

                if (newPaginationDiv) {
                    cardBody.appendChild(newPaginationDiv);
                    console.log('Đã thêm phần tử phân trang mới.');
                } else {
                    console.warn("Pagination HTML returned by server did not contain #inventory-pagination-links div.");
                }
            } else {
                 console.log('Không có HTML phân trang mới để thêm.');
            }
        }

        // Cập nhật biến toàn cục để trỏ đến phần tử mới (hoặc null nếu không có)
        inventoryPaginationLinksContainer = document.getElementById('inventory-pagination-links');
        if (inventoryPaginationLinksContainer) {
            console.log('Đã cập nhật tham chiếu paginationLinksContainer.');
        } else {
            console.log('Không tìm thấy paginationLinksContainer sau cập nhật.');
        }
        attachPaginationListeners(); // Gắn lại listeners cho các liên kết phân trang mới
    }

    /**
     * Tải dữ liệu tồn kho từ server bằng AJAX và cập nhật bảng.
     * @param {number} page - Số trang muốn tải.
     */
    async function loadInventoryTable(page = 1) {
        showAppLoader();
        console.log(`Đang tải dữ liệu tồn kho cho trang ${page}...`);
        try {
            const urlParams = new URLSearchParams();
            urlParams.append('page', page);

            const url = `/admin/product-management/inventory?${urlParams.toString()}`;
            console.log(`Fetching from URL: ${url}`);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            console.log(`Phản hồi Fetch Status: ${response.status}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
            }
            const data = await response.json();
            console.log('Dữ liệu tồn kho nhận được:', data);
            updateInventoryTableContent(data.table_rows, data.pagination_links);
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu tồn kho:', error);
            showAppInfoModal('Không thể tải dữ liệu tồn kho. Vui lòng thử lại.', 'Lỗi', 'error');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Gắn lại event listeners cho các liên kết phân trang sau khi DOM được cập nhật.
     */
    function attachPaginationListeners() {
        console.log('Gắn lại listeners cho phân trang.');
        if (inventoryPaginationLinksContainer) {
            inventoryPaginationLinksContainer.removeEventListener('click', handlePaginationClick);
            inventoryPaginationLinksContainer.addEventListener('click', handlePaginationClick);
            console.log('Listeners phân trang đã được gắn.');
        } else {
            console.log('Không có container phân trang để gắn listeners.');
        }
    }

    /**
     * Xử lý sự kiện click trên các liên kết phân trang.
     * @param {Event} event - Sự kiện click.
     */
    function handlePaginationClick(event) {
        const link = event.target.closest('.pagination a');
        if (link) {
            event.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            if (page) {
                console.log(`Chuyển trang phân trang đến trang: ${page}`);
                loadInventoryTable(page);
            }
        }
    }


    // D. PRODUCT STOCK UPDATE LOGIC
    /**
     * Sends an AJAX request to update product stock.
     * Cập nhật số lượng tồn kho sản phẩm qua AJAX.
     * @param {number} productId - ID của sản phẩm.
     * @param {number} newQuantity - Số lượng tồn kho mới.
     * @param {jQuery} $inlineQuantityBadgeRef - Tham chiếu đến phần tử hiển thị số lượng badge trên bảng.
     */
    async function updateProductStock(productId, newQuantity, $inlineQuantityBadgeRef) {
        showAppLoader();
        $('#updateQuantityError').text(''); // Xóa lỗi cũ trong modal

        try {
            console.log(`Đang gửi yêu cầu cập nhật tồn kho cho Product ID: ${productId}, Số lượng mới: ${newQuantity}`);
            const response = await fetch(`/admin/product-management/api/products/${productId}/update-stock`, {
                method: 'PUT', // Đã thay đổi từ POST sang PUT trực tiếp
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // Lấy CSRF token từ biến đã cache
                },
                body: JSON.stringify({
                    stock_quantity: newQuantity
                    // Loại bỏ _method: 'PUT' vì đã dùng method: 'PUT' trực tiếp
                })
            });

            const data = await response.json();
            console.log('Phản hồi từ server (cập nhật tồn kho):', data);

            if (response.ok) {
                showAppInfoModal('Cập nhật tồn kho thành công!', 'Thành công', 'success');
                $updateQuantityModal.modal('hide'); // Đóng modal khi thành công
                loadInventoryTable(getCurrentPage()); // Tải lại bảng để phản ánh thay đổi
            } else {
                if (response.status === 422 && data.errors && data.errors.stock_quantity) {
                    $('#updateQuantityError').text(data.errors.stock_quantity[0]); // Hiển thị lỗi trong modal
                } else {
                    $('#updateQuantityError').text(data.message || 'Cập nhật thất bại!'); // Hiển thị lỗi trong modal
                }
            }
        } catch (error) {
            console.error('Lỗi cập nhật tồn kho:', error);
            $('#updateQuantityError').text('Lỗi kết nối đến máy chủ!'); // Hiển thị lỗi trong modal
        } finally {
            hideAppLoader();
        }
    }

    // E. VIEW PRODUCT DETAILS MODAL LOGIC
    function setupViewDetailsModal() {
        console.log('Thiết lập trình xử lý sự kiện cho nút "Xem chi tiết".');
        $adminInventoryPage.on('click', '.view-product-details-btn', async function() {
            console.log('Nút "Xem chi tiết" được click.');
            const productId = $(this).data('id');
            console.log(`Product ID từ data-id: ${productId}`);
            showAppLoader();
            $viewProductDetailsModal.modal('show'); // Hiển thị modal ngay lập tức để người dùng thấy trạng thái tải

            try {
                // Gọi API để lấy chi tiết sản phẩm
                const response = await fetch(`/admin/product-management/api/products/${productId}/details`);
                console.log(`Phản hồi Fetch Status (chi tiết sản phẩm): ${response.status}`);
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                    throw new Error(errorData.message || `Không thể tải chi tiết sản phẩm. Mã lỗi: ${response.status}`);
                }
                const product = await response.json();
                console.log('Dữ liệu chi tiết sản phẩm nhận được:', product);
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
        console.log('Điền dữ liệu vào modal chi tiết sản phẩm.');
        if (!product) {
            console.warn('Không có dữ liệu sản phẩm để điền vào modal.');
            return;
        }

        // Điền thông tin cơ bản
        $('#viewProductName').text(product.name || 'N/A');
        $('#viewProductId').text(product.id || 'N/A');
        $('#viewProductNameDetail').text(product.name || 'N/A');
        $('#viewProductPrice').text(formatCurrency(product.price) || '0 ₫');
        $('#viewProductStock').text(product.stock_quantity || '0');
        $('#viewProductCategory').text(product.category ? product.category.name : 'N/A');
        $('#viewProductBrand').text(product.brand ? product.brand.name : 'N/A');
        $('#viewProductStatusBadge').html(`<span class="badge ${product.status_badge_class}">${product.status_text || 'N/A'}</span>`);
        $('#viewProductImage').attr('src', product.thumbnail_url || 'https://placehold.co/300x300/EFEFEF/AAAAAA&text=Product'); // Sử dụng placeholder nếu không có ảnh
        $('#viewProductDescription').html(product.description || 'Không có mô tả.');
        $('#viewProductSpecifications').html(product.specifications || 'Không có thông số kỹ thuật.');

        // Điền thông tin dòng xe tương thích
        const $vehicleModelsContainer = $('#viewProductVehicleModels');
        $vehicleModelsContainer.empty(); // Xóa nội dung cũ
        if (product.vehicle_models && product.vehicle_models.length > 0) {
            console.log('Đang điền dòng xe tương thích.');
            product.vehicle_models.forEach(model => {
                const brandName = model.vehicle_brand ? model.vehicle_brand.name : '';
                $vehicleModelsContainer.append(`<span class="badge bg-secondary me-1 mb-1">${brandName} - ${model.name} (${model.year})</span>`);
            });
        } else {
            $vehicleModelsContainer.append('<span class="text-muted">Không có dòng xe tương thích.</span>');
            console.log('Không có dòng xe tương thích.');
        }

        // Cập nhật link "Chỉnh sửa đầy đủ" trong footer modal
        // Nút này dùng để điều hướng người dùng đến trang quản lý sản phẩm chính
        // để thực hiện chỉnh sửa chi tiết sản phẩm. Nó không mở một modal chỉnh sửa tại chỗ.
        const editLink = `/admin/product-management/products?search=${product.id}`;
        $('#viewProductEditLink').attr('href', editLink);
        console.log(`Link chỉnh sửa đầy đủ được đặt thành: ${editLink}`);
    }

    // F. UPDATE QUANTITY MODAL LOGIC
    function setupUpdateQuantityModal() {
        console.log('Thiết lập trình xử lý sự kiện cho nút "Cập nhật số lượng".');
        // Event listener for opening the update quantity modal
        $adminInventoryPage.on('click', '.open-update-quantity-modal-btn', function() {
            console.log('Nút "Cập nhật số lượng" được click.');
            const productId = $(this).data('id');
            const $row = $(this).closest('tr');
            const productName = $row.find('td:nth-child(2)').text(); // Lấy tên sản phẩm từ cột thứ 2
            // Lấy số lượng tồn kho hiện tại đang hiển thị trên bảng
            const currentQuantity = parseInt($row.find('.badge.bg-danger').text()) || 0;
            console.log(`Product ID: ${productId}, Product Name: ${productName}, Current Quantity: ${currentQuantity}`);

            // Điền dữ liệu vào modal
            $('#updateQuantityProductId').val(productId);
            $('#updateQuantityProductName').text(productName);
            $newStockQuantityInput.val(currentQuantity); // Đặt giá trị hiện tại vào input của modal
            $('#updateQuantityError').text(''); // Xóa lỗi cũ

            $updateQuantityModal.modal('show'); // Hiển thị modal
        });

        // Event listener for confirming update in the modal
        $('#confirmUpdateQuantityBtn').on('click', function() {
            console.log('Nút "Lưu thay đổi" trong modal cập nhật số lượng được click.');
            const productId = $('#updateQuantityProductId').val();
            const newQuantity = parseInt($newStockQuantityInput.val());

            // Lấy tham chiếu đến phần tử hiển thị số lượng inline (badge)
            const $inlineQuantityBadge = $adminInventoryPage.find(`tr button.open-update-quantity-modal-btn[data-id="${productId}"]`).closest('tr').find('.badge.bg-danger');

            // Validate input in modal before sending
            if (isNaN(newQuantity) || newQuantity < 0) {
                $('#updateQuantityError').text('Số lượng phải là một số nguyên không âm.');
                console.warn('Validation failed: New quantity is not a non-negative number.');
                return;
            }

            // Gọi hàm cập nhật tồn kho, truyền các tham chiếu để cập nhật hiển thị
            updateProductStock(productId, newQuantity, $inlineQuantityBadge);
        });
    }

    // G. KICKSTART THE SCRIPT
    initialize();
};
