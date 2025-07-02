// File: public/assets_admin/js/inventory_manager.js

/**
 * =================================================================================
 * inventory_manager.js
 * ---------------------------------------------------------------------------------
 * Quản lý các chức năng tương tác trên trang tồn kho (Inventory).\r
 * - Cập nhật số lượng sản phẩm trực tiếp qua AJAX.\r
 * - Hiển thị chi tiết sản phẩm trong modal.\r
 * - Tải dữ liệu bảng và phân trang bằng AJAX.\r
 * =================================================================================\r
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
    const $updateProductModal = $('#updateProductModal'); // Cache the update product (full edit) modal

    // THÊM CÁC PHẦN TỬ MỚI VÀO BỘ NHỚ CACHE
    const inventorySearchInput = document.getElementById('inventorySearchInput');
    const inventorySearchBtn = document.getElementById('inventorySearchBtn');
    const inventoryCategoryFilter = document.getElementById('inventoryCategoryFilter');
    const inventoryBrandFilter = document.getElementById('inventoryBrandFilter');
    const inventoryClearFiltersBtn = document.getElementById('inventoryClearFiltersBtn');


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
        // Setup for update product (full edit) form submission [new]
        setupUpdateProductForm(); // <-- Gọi hàm thiết lập form cập nhật

        // Setup event listeners for search and filter [new]
        setupSearchAndFilterListeners();

        // Tải dữ liệu tồn kho ban đầu bằng AJAX
        loadInventoryTable(1);
    }

    // --- CÁC HÀM XỬ LÝ TÌM KIẾM VÀ LỌC MỚI ---
    function setupSearchAndFilterListeners() {
        if (inventorySearchBtn) {
            inventorySearchBtn.addEventListener('click', () => loadInventoryTable(1));
        }
        if (inventorySearchInput) {
            inventorySearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadInventoryTable(1);
                }
            });
        }
        if (inventoryCategoryFilter) {
            inventoryCategoryFilter.addEventListener('change', () => loadInventoryTable(1));
        }
        if (inventoryBrandFilter) {
            inventoryBrandFilter.addEventListener('change', () => loadInventoryTable(1));
        }
        if (inventoryClearFiltersBtn) {
            inventoryClearFiltersBtn.addEventListener('click', () => {
                if (inventorySearchInput) inventorySearchInput.value = '';
                if (inventoryCategoryFilter) inventoryCategoryFilter.value = '';
                if (inventoryBrandFilter) inventoryBrandFilter.value = '';
                loadInventoryTable(1);
            });
        }
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

            // THÊM THAM SỐ TÌM KIẾM VÀ LỌC VÀO URLSearchParams
            const currentSearchQuery = inventorySearchInput ? inventorySearchInput.value : '';
            const currentCategoryFilter = inventoryCategoryFilter ? inventoryCategoryFilter.value : '';
            const currentBrandFilter = inventoryBrandFilter ? inventoryBrandFilter.value : '';

            if (currentSearchQuery) urlParams.append('search', currentSearchQuery);
            if (currentCategoryFilter) urlParams.append('category_id', currentCategoryFilter);
            if (currentBrandFilter) urlParams.append('brand_id', currentBrandFilter);


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
                console.error('Lỗi phản hồi tải tồn kho:', errorData);
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
            console.log(`Phản hồi Cập nhật Tồn kho Status: ${response.status}`);

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
                    console.error('Lỗi phản hồi tải chi tiết sản phẩm:', errorData);
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

        // Bổ sung: Xử lý nút "Chỉnh sửa đầy đủ" trong modal chi tiết
        $viewProductDetailsModal.on('click', '.open-full-edit-product-modal-btn', async function() {
            const productId = $('#viewProductId').text(); // Lấy product ID từ modal chi tiết hiện tại
            console.log(`Nút "Chỉnh sửa đầy đủ" được click từ modal chi tiết. Product ID: ${productId}`);
            
            // Đóng modal chi tiết trước khi mở modal chỉnh sửa
            $viewProductDetailsModal.modal('hide'); 

            // Gọi hàm để mở và điền dữ liệu vào modal chỉnh sửa sản phẩm
            await openUpdateProductModal(productId);
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
    }

    /**
     * Khởi tạo SelectPicker cho các select element trong phạm vi cụ thể.
     * Hủy các instance cũ trước khi khởi tạo lại để tránh lỗi.
     * (Đây là phiên bản đã điều chỉnh từ product_management.js để phù hợp với inventory_manager.js)
     */
    const initializeSelectPickersInScope = (scope = document) => { // Đổi tên để tránh nhầm lẫn nếu có initializeSelectPickers toàn cục khác
        try {
            const $pickers = $(scope).find('.selectpicker');
            if ($pickers.length === 0) return;

            $pickers.each(function() {
                const $this = $(this);
                // Kiểm tra xem đã có data selectpicker chưa, nếu có thì hủy trước
                if ($this.data('selectpicker')) {
                    $this.selectpicker('destroy');
                }
            });

            // Khởi tạo selectpicker
            $pickers.selectpicker({
                liveSearch: true,
                width: '100%',
                noneSelectedText: 'Chưa chọn mục nào',
                actionsBox: true,
                selectAllText: 'Chọn tất cả',
                deselectAllText: 'Bỏ chọn tất cả',
                size: 10,
                dropupAuto: false
            });
            $pickers.selectpicker('render');
            console.log('Selectpickers trong phạm vi đã được khởi tạo/làm mới.');
        } catch (error) {
            console.error('Lỗi khi khởi tạo selectpicker trong phạm vi:', error);
        }
    };


    /**
     * Hàm mới để mở và điền dữ liệu vào modal chỉnh sửa sản phẩm.
     * Hàm này sẽ tương tự như cách product_management.js xử lý việc mở modal chỉnh sửa.
     * Bạn cần đảm bảo #updateProductModal có sẵn trong DOM (đã include trong inventory.blade.php).
     */
    async function openUpdateProductModal(productId) {
        showAppLoader();
        // $updateProductModal đã được khai báo ở đầu tệp
        try {
            // Xóa các lỗi validation cũ và trạng thái is-invalid
            $updateProductModal.find('.invalid-feedback').remove();
            $updateProductModal.find('.is-invalid').removeClass('is-invalid');
            $updateProductModal.find('input[type="file"]').val(''); // Clear file inputs

            // Lấy chi tiết sản phẩm
            const response = await fetch(`/admin/product-management/api/products/${productId}/details`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorData.message || `Không thể tải chi tiết sản phẩm để chỉnh sửa. Mã lỗi: ${response.status}`);
            }
            const product = await response.json();
            console.log('Dữ liệu sản phẩm để chỉnh sửa nhận được:', product);

            // Quan trọng: Khởi tạo/làm mới selectpickers TRƯỚC khi đặt giá trị
            // Sử dụng setTimeout để đảm bảo DOM được cập nhật và selectpicker sẵn sàng.
            // Gọi initializeSelectPickersInScope cho modal này.
            setTimeout(() => {
                initializeSelectPickersInScope($updateProductModal); // [new]
            }, 0);


            // Điền dữ liệu vào form của modal chỉnh sửa
            $('#updateProductId').val(product.id); // SỬA: Đảm bảo ID này tồn tại trong update_product.blade.php
            $('#productNameUpdate').val(product.name); // SỬA ID
            $('#productDescriptionUpdate').val(product.description); // SỬA ID
            $('#productPriceUpdate').val(product.price); // SỬA ID
            $('#productStockUpdate').val(product.stock_quantity); // SỬA ID
            $('#productMaterialUpdate').val(product.material); // SỬA ID
            $('#productColorUpdate').val(product.color); // SỬA ID
            $('#productSpecificationsUpdate').val(product.specifications); // SỬA ID

            // Chọn danh mục và thương hiệu (sử dụng trigger('change') và .selectpicker('refresh') nếu là Select2 hoặc tương tự)
            $('#productCategoryUpdate').val(product.category_id); // SỬA ID
            if ($.fn.selectpicker) { // Kiểm tra nếu Bootstrap-select đang được sử dụng
                $('#productCategoryUpdate').selectpicker('refresh');
            } else if ($.fn.select2) { // Nếu là Select2
                $('#productCategoryUpdate').trigger('change');
            }
            
            $('#productBrandUpdate').val(product.brand_id); // SỬA ID
            if ($.fn.selectpicker) { //
                $('#productBrandUpdate').selectpicker('refresh');
            } else if ($.fn.select2) { //
                $('#productBrandUpdate').trigger('change');
            }

            // Xử lý checkbox trạng thái
            if (product.status === 'active') {
                $('#productIsActiveUpdate').prop('checked', true); // SỬA ID
            } else {
                $('#productIsActiveUpdate').prop('checked', false); // SỬA ID
            }

            // Xử lý các dòng xe tương thích (select multiple, giả định Select2 hoặc selectpicker)
            const selectedVehicleModels = product.vehicle_models ? product.vehicle_models.map(model => model.id.toString()) : [];
            $('#productVehicleModelsUpdate').val(selectedVehicleModels); // SỬA ID
            if ($.fn.selectpicker) { //
                $('#productVehicleModelsUpdate').selectpicker('refresh');
            } else if ($.fn.select2) { //
                $('#productVehicleModelsUpdate').trigger('change');
            }

            // Hiển thị hình ảnh hiện có
            const $existingImagesContainer = $('#productImagesPreviewUpdate'); // SỬA ID
            $existingImagesContainer.empty();
            if (product.images && product.images.length > 0) {
                product.images.forEach(image => {
                    const imageUrl = image.image_url.startsWith('http') ? image.image_url : `/storage/${image.image_url}`;
                    $existingImagesContainer.append(`
                        <div class="col-md-3 mb-2 existing-image-item" data-id="${image.id}">
                            <img src="${imageUrl}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger remove-existing-image-btn" data-id="${image.id}">X</button>
                            <input type="hidden" name="existing_images[]" value="${image.id}">
                        </div>
                    `);
                });
            } else {
                $existingImagesContainer.append('<p>Không có hình ảnh hiện có.</p>');
            }
            
            // Cập nhật action của form để trỏ đến endpoint update đúng với ID sản phẩm
            // Giả định route có dạng /admin/product-management/products/{product} và method PUT
            $('#updateProductForm').attr('action', `/admin/product-management/products/${productId}`);


            // Mở modal
            $updateProductModal.modal('show');
            console.log('Đã mở modal chỉnh sửa sản phẩm.');

        } catch (error) {
            console.error('Lỗi khi mở modal chỉnh sửa sản phẩm:', error);
            showAppInfoModal(error.message, 'Lỗi', 'error');
        } finally {
            hideAppLoader();
        }
    }

    // G. SETUP UPDATE PRODUCT FORM SUBMISSION [new function]
    function setupUpdateProductForm() {
        console.log('Thiết lập trình xử lý gửi form cho modal cập nhật sản phẩm.');

        $('#updateProductForm').on('submit', async function(event) {
            event.preventDefault(); // Ngăn chặn hành vi gửi form mặc định của trình duyệt
            showAppLoader();
            
            // Xóa các thông báo lỗi validation cũ
            $updateProductModal.find('.invalid-feedback').text('');
            $updateProductModal.find('.form-control').removeClass('is-invalid');
            
            const form = this;
            const formData = new FormData(form);
            const productId = $('#updateProductId').val(); // Lấy ID sản phẩm từ trường ẩn

            // Thêm _method=PUT vào FormData vì Laravel cần nó cho phương thức PUT/PATCH qua form POST
            formData.append('_method', 'PUT');

            const actionUrl = $(form).attr('action'); // Lấy URL từ thuộc tính action của form

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST', // Luôn là POST khi dùng FormData với _method=PUT
                    headers: {
                        'X-CSRF-TOKEN': csrfToken // Đảm bảo CSRF token được gửi
                    },
                    body: formData // FormData tự động thiết lập Content-Type là multipart/form-data
                });

                const data = await response.json();
                console.log('Phản hồi từ server (cập nhật sản phẩm):', data);
                console.log(`Phản hồi Cập nhật Sản phẩm Status: ${response.status}`);

                if (response.ok) { // Nếu phản hồi thành công (2xx)
                    showAppInfoModal(data.message || 'Cập nhật sản phẩm thành công!', 'Thành công', 'success');
                    $updateProductModal.modal('hide'); // Đóng modal
                    loadInventoryTable(getCurrentPage()); // Tải lại bảng để phản ánh thay đổi
                } else if (response.status === 422) { // Lỗi validation
                    console.error('Lỗi validation:', data.errors);
                    // Hiển thị lỗi validation trên form
                    for (const fieldName in data.errors) {
                        const errorMessage = data.errors[fieldName][0];
                        const inputElement = $(`#${fieldName}Update`); // Giả định ID input là fieldName + 'Update'
                        
                        // Xử lý các trường Selectpicker/Select2
                        if (inputElement.hasClass('selectpicker') || inputElement.hasClass('select2')) {
                            inputElement.closest('.form-group, .mb-3').find('.invalid-feedback').text(errorMessage).show();
                            inputElement.closest('.form-group, .mb-3').find('.form-control, .bootstrap-select, .select2-container').addClass('is-invalid');
                        } else {
                            inputElement.addClass('is-invalid');
                            inputElement.next('.invalid-feedback').text(errorMessage);
                        }

                        // Xử lý product_images nếu có lỗi
                        if (fieldName.startsWith('product_images')) {
                            $('#productImagesUpdate').addClass('is-invalid');
                            $('#productImagesUpdate').next('.invalid-feedback').text(errorMessage);
                        }
                    }
                    showAppInfoModal('Vui lòng kiểm tra lại thông tin bạn đã nhập.', 'Lỗi nhập liệu', 'error');
                } else { // Các lỗi server khác (5xx)
                    showAppInfoModal(data.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.', 'Lỗi', 'error');
                }
            } catch (error) {
                console.error('Lỗi khi gửi form cập nhật sản phẩm:', error);
                showAppInfoModal('Lỗi kết nối đến máy chủ! Vui lòng thử lại.', 'Lỗi kết nối', 'error');
            } finally {
                hideAppLoader();
                if (submitButton) {
                    submitButton.disabled = false;
                    if (formId === 'createProductForm') submitButton.innerHTML = 'Lưu Sản phẩm';
                    else if (formId === 'updateProductForm') submitButton.innerHTML = 'Lưu thay đổi';
                    else if (formId === 'bulkDeleteProductsForm') submitButton.innerHTML = 'Xác nhận Xóa mềm';
                    else if (formId === 'bulkForceDeleteProductsForm') submitButton.innerHTML = 'Xóa Vĩnh viễn';
                    else if (formId === 'bulkRestoreProductsForm') submitButton.innerHTML = 'Xác nhận Khôi phục';
                    else if (formId === 'bulkToggleStatusProductsForm') submitButton.innerHTML = 'Xác nhận';
                }
            }
        });

        // Event listener để xử lý nút xóa ảnh hiện có trong modal update product
        // (Nếu logic này không nằm trong product_management.js đang được tải)
        $updateProductModal.on('click', '.remove-existing-image-btn', function() {
            const $imageItem = $(this).closest('.existing-image-item');
            const imageIdToRemove = $(this).data('id');
            // Gỡ bỏ phần tử ảnh khỏi DOM
            $imageItem.remove();
            console.log(`Đã xóa ảnh với ID: ${imageIdToRemove} khỏi hiển thị. ` +
                        `Hình ảnh này sẽ không được gửi trong mảng 'existing_images[]' khi form được submit.`);
            // Backend sẽ so sánh 'existing_images[]' với các ảnh hiện có để xác định ảnh nào bị xóa.
        });
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