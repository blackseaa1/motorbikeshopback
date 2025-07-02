/**
 * ===================================================================
 * product_management.js
 * Xử lý JavaScript cho trang quản lý Sản phẩm (Thêm, Sửa, Xóa, Xem).
 * Phiên bản: Hoàn chỉnh, đã sửa lỗi route 405 và đảm bảo phương thức POST cho update.
 * Đã tích hợp thông báo Toast và định dạng tiền tệ (tương tự promotion_manager.js).
 * Đã thêm chức năng: Tìm kiếm, Lọc, Sắp xếp, Chọn tất cả, Hành động hàng loạt (xóa mềm, xóa vĩnh viễn, khôi phục, bật/tắt trạng thái).
 * ===================================================================
 */

// Biến toàn cục cho container phân trang, sẽ được gán giá trị trong initializeProductsPage
let paginationLinksContainer = null;

function initializeProductsPage() {
    console.log("Khởi tạo JS cho trang Sản phẩm...");

    // Lấy các element modal chính
    const createProductModalEl = document.getElementById('createProductModal');
    const updateProductModalEl = document.getElementById('updateProductModal');
    const viewProductModalEl = document.getElementById('viewProductModal');
    // Unified Modals
    const bulkDeleteProductsModalEl = document.getElementById('bulkDeleteProductsModal');
    const bulkForceDeleteProductsModalEl = document.getElementById('bulkForceDeleteProductsModal');
    const bulkRestoreProductsModalEl = document.getElementById('bulkRestoreProductsModal');
    const bulkToggleStatusProductsModalEl = document.getElementById('bulkToggleStatusProductsModal');

    const productTableBody = document.getElementById('product-table-body');
    // paginationLinksContainer được khai báo là biến toàn cục ở trên

    // Search, Filter, Sort elements
    const productSearchInput = document.getElementById('productSearchInput');
    const productSearchBtn = document.getElementById('productSearchBtn');
    const productFilterSelect = document.getElementById('productFilterSelect');
    const productSortSelect = document.getElementById('productSortSelect');

    // Bulk Action elements
    const selectAllProductsCheckbox = document.getElementById('selectAllProducts');
    const bulkActionsDropdownBtn = document.getElementById('bulkActionsDropdown');
    const selectedCountBulkSpan = document.getElementById('selectedCountBulk');
    const bulkToggleStatusBtn = document.getElementById('bulkToggleStatusBtn');
    const bulkRestoreBtn = document.getElementById('bulkRestoreBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkForceDeleteBtn = document.getElementById('bulkForceDeleteBtn');

    // Spans inside bulk modals for count
    const selectedProductsCountToggleModalSpan = document.getElementById('selectedProductsCountToggleModal');


    // Kiểm tra sự tồn tại của các element quan trọng
    if (!createProductModalEl || !updateProductModalEl || !viewProductModalEl ||
        !bulkDeleteProductsModalEl || !bulkForceDeleteProductsModalEl || !bulkRestoreProductsModalEl || !bulkToggleStatusProductsModalEl ||
        !productTableBody || !productSearchInput || !productSearchBtn || !productFilterSelect || !productSortSelect ||
        !selectAllProductsCheckbox || !bulkActionsDropdownBtn || !selectedCountBulkSpan ||
        !bulkToggleStatusBtn || !bulkRestoreBtn || !bulkDeleteBtn || !bulkForceDeleteBtn ||
        !selectedProductsCountToggleModalSpan
    ) {
        console.error('Một hoặc nhiều element quan trọng không tồn tại. Script sẽ không chạy.');
        return;
    }

    // Lấy CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // Lấy các hàm helper toàn cục từ admin_layout.js
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showAppInfoModal = typeof window.showAppInfoModal === 'function' ? window.showAppInfoModal : (msg, type) => alert(`${type}: ${msg}`);
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`);
            return;
        }

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    // Khởi tạo các đối tượng Modal của Bootstrap
    const createProductModal = new bootstrap.Modal(createProductModalEl);
    const updateProductModal = new bootstrap.Modal(updateProductModalEl);
    const viewProductModal = new bootstrap.Modal(viewProductModalEl);
    const bulkDeleteProductsModal = new bootstrap.Modal(bulkDeleteProductsModalEl);
    const bulkForceDeleteProductsModal = new bootstrap.Modal(bulkForceDeleteProductsModalEl);
    const bulkRestoreProductsModal = new bootstrap.Modal(bulkRestoreProductsModalEl);
    const bulkToggleStatusProductsModal = new bootstrap.Modal(bulkToggleStatusProductsModalEl);

    let selectedProductIds = new Set(); // Để lưu trữ các ID sản phẩm đã chọn


    // --- CÁC HÀM KHỞI TẠO & HỖ TRỢ ---

    /**
     * Khởi tạo SelectPicker cho các select element
     */
    const initializeSelectPickers = () => {
        try {
            const $pickers = $('.selectpicker');
            if ($pickers.length === 0) return;
            if ($pickers.data('selectpicker')) $pickers.selectpicker('destroy');
            $pickers.selectpicker({
                liveSearch: true,
                width: '100%',
                noneSelectedText: 'Chưa chọn mục nào',
                actionsBox: true,
                selectAllText: 'Chọn tất cả',
                deselectAllText: 'Bỏ chọn tất cả',
                size: 10, // Số lượng mục hiển thị trước khi cuộn
                dropupAuto: false // Vô hiệu hóa tự động "dropup" để giữ menu cuộn xuống
            });
            $pickers.selectpicker('render');
        } catch (error) {
            console.error('Lỗi khi khởi tạo selectpicker:', error);
        }
    };

    /**
     * Thiết lập preview hình ảnh khi chọn file
     * @param {HTMLInputElement} inputEl - Input file
     * @param {HTMLElement} previewContainerEl - Container cho preview
     */
    const setupImagePreviews = (inputEl, previewContainerEl) => {
        if (!inputEl || !previewContainerEl) return;
        inputEl.addEventListener('change', function (event) {
            previewContainerEl.querySelectorAll('.new-preview').forEach(el => el.remove());
            const files = event.target.files;
            if (!files) return;
            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewWrapper = document.createElement('div');
                    previewWrapper.className = 'img-preview-wrapper new-preview';
                    previewWrapper.innerHTML = `<img src="${e.target.result}" class="img-preview" alt="${file.name}"><button type="button" class="img-preview-remove" title="Xóa ảnh này">×</button>`;
                    previewContainerEl.appendChild(previewWrapper);
                };
                reader.readAsDataURL(file);
            }
        });
    };

    /**
     * Xóa các lỗi validation đang hiển thị trên form.
     * @param {HTMLElement} formElement - Form cần xóa lỗi.
     */
    function clearValidationErrors(formElement) {
        if (!formElement) return;
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        formElement.querySelectorAll('[id$="Error"]').forEach(el => { // Clear specific error divs (e.g., for password)
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    /**
     * Hiển thị lỗi validation từ phản hồi server dưới các trường input tương ứng.
     * @param {HTMLElement} formElement - Form đang có lỗi.
     * @param {object} errors - Đối tượng chứa các lỗi từ server (key: field_name, value: [error_message]).
     */
    function displayValidationErrors(formElement, errors) {
        clearValidationErrors(formElement);
        let firstErrorField = null;

        for (const fieldName in errors) {
            if (errors.hasOwnProperty(fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);
                let errorDiv = null;

                // Xử lý các trường hợp đặc biệt nếu name không khớp trực tiếp với ID
                if (!inputField) {
                    if (fieldName === 'category_id') {
                        inputField = formElement.querySelector('#productCategoryCreate') || formElement.querySelector('#productCategoryUpdate');
                    } else if (fieldName === 'brand_id') {
                        inputField = formElement.querySelector('#productBrandCreate') || formElement.querySelector('#productBrandUpdate');
                    } else if (fieldName === 'vehicle_model_ids') {
                        // For selectpickers, the error feedback might not be a direct sibling
                        inputField = formElement.querySelector('#productVehicleModelsCreate') || formElement.querySelector('#productVehicleModelsUpdate');
                        if (inputField) {
                            // Find the container, then the feedback div
                            errorDiv = inputField.closest('.mb-3')?.querySelector('.invalid-feedback');
                        }
                    } else if (fieldName === 'admin_password_confirm_delete') {
                        inputField = formElement.querySelector('#deleteProductsPassword') || formElement.querySelector('#forceDeleteProductsPassword') || formElement.querySelector('#bulkToggleStatusProductsPassword');
                        errorDiv = formElement.querySelector('#deleteProductsPasswordError') || formElement.querySelector('#forceDeleteProductsPasswordError') || formElement.querySelector('#bulkToggleStatusProductsPasswordError');
                    }
                }

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    if (!errorDiv) { // if errorDiv not already set by specific handling
                        errorDiv = inputField.nextElementSibling;
                    }

                    if (errorDiv && (errorDiv.classList.contains('invalid-feedback') || (errorDiv.id && errorDiv.id.endsWith('Error')))) {
                        errorDiv.textContent = errors[fieldName][0];
                        if (errorDiv.id && errorDiv.id.endsWith('Error')) { // For specific error divs that might be hidden by default
                            errorDiv.style.display = 'block';
                        }
                    } else {
                        console.warn(`Không tìm thấy div .invalid-feedback hoặc error cụ thể cho trường: ${fieldName}`);
                    }

                    if (!firstErrorField) {
                        firstErrorField = inputField;
                    }
                } else {
                    console.warn(`Không tìm thấy trường input cho lỗi: ${fieldName}`);
                }
            }
        }
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }


    /**
     * Định dạng số tiền theo chuẩn VNĐ khi người dùng nhập.
     * Hỗ trợ phần thập phân, tự động thêm dấu phân cách hàng nghìn.
     *
     * @param {HTMLInputElement} inputElement - Trường input cần định dạng.
     */
    function formatCurrencyInput(inputElement) {
        inputElement.addEventListener('input', function (e) {
            let raw = e.target.value;

            // Giữ vị trí con trỏ
            let caretPosition = e.target.selectionStart;

            // Xoá mọi ký tự ngoại trừ số và dấu phẩy (phần thập phân)
            raw = raw.replace(/[^0-9,]/g, '');

            // Tách phần nguyên và phần thập phân (nếu có)
            let parts = raw.split(',');
            let integerPart = parts[0];
            let decimalPart = parts[1] ? ',' + parts[1] : '';

            // Xoá dấu chấm cũ rồi thêm lại theo hàng nghìn
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            e.target.value = integerPart + decimalPart;

            // Cập nhật lại vị trí con trỏ tương đối
            e.target.setSelectionRange(e.target.value.length, e.target.value.length);
        });

        // Tuỳ chọn: chọn toàn bộ text khi focus để người dùng dễ gõ lại
        inputElement.addEventListener('focus', function (e) {
            e.target.select();
        });
    }

    /**
     * Chuyển đổi chuỗi số tiền định dạng VNĐ về số nguyên hoặc số thập phân.
     * @param {string} formattedValue - VD: "1.250.000,75"
     * @returns {string} - VD: "1250000.75"
     */
    function parseFormattedCurrency(formattedValue) {
        if (typeof formattedValue !== 'string') return formattedValue;
        return formattedValue.replace(/\./g, '').replace(',', '.'); // chuẩn hóa về số thực
    }

    /**
     * Reset form và các trạng thái khi modal đóng
     */
    const setupModalResets = () => {
        // Reset modal tạo mới
        createProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('createProductForm');
            form.reset();
            clearValidationErrors(form);
            document.getElementById('productImagesPreviewCreate').innerHTML = '';
            $('#createProductForm .selectpicker').selectpicker('val', '');
            $('#createProductForm .selectpicker').selectpicker('refresh');
        });
        const createImagesInput = document.getElementById('productImagesCreate');
        const createImagesPreview = document.getElementById('productImagesPreviewCreate');
        if (createImagesInput && createImagesPreview) setupImagePreviews(createImagesInput, createImagesPreview);

        // Reset modal cập nhật
        updateProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('updateProductForm');
            form.reset();
            clearValidationErrors(form);
            document.getElementById('productImagesPreviewUpdate').innerHTML = '';
            document.getElementById('productImagesUpdate').value = ''; // Clear file input
            $('#updateProductForm .selectpicker').selectpicker('val', '');
            $('#updateProductForm .selectpicker').selectpicker('refresh');
        });
        const updateImagesInput = document.getElementById('productImagesUpdate');
        const updateImagesPreview = document.getElementById('productImagesPreviewUpdate');
        if (updateImagesInput && updateImagesPreview) setupImagePreviews(updateImagesInput, updateImagesPreview);

         // Reset password and errors for deletion/bulk modals on show
        [bulkDeleteProductsModalEl, bulkForceDeleteProductsModalEl, bulkToggleStatusProductsModalEl, bulkRestoreProductsModalEl].forEach(modalEl => {
            modalEl.addEventListener('show.bs.modal', () => {
                const passwordInput = modalEl.querySelector('[name="admin_password_confirm_delete"]');
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.classList.remove('is-invalid');
                    const errorDiv = modalEl.querySelector(`#${passwordInput.id}Error`);
                    if (errorDiv) {
                        errorDiv.textContent = '';
                        errorDiv.style.display = 'none';
                    }
                }
            });
        });
    };

    // --- CÁC HÀM HIỂN THỊ MODAL ---

    /**
     * Hiển thị modal xem chi tiết sản phẩm
     * @param {number} productId - ID sản phẩm
     */
    const handleShowViewModal = async (productId) => {
        showAppLoader();
        try {
            const response = await fetch(`/admin/product-management/products/${productId}/details`);
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const product = await response.json();

            viewProductModalEl.querySelector('#productNameView').textContent = product.name || 'N/A';
            viewProductModalEl.querySelector('#productDescriptionView').textContent = product.description || 'Không có mô tả.';
            viewProductModalEl.querySelector('#productPriceView').textContent = product.formatted_price || '0';
            viewProductModalEl.querySelector('#productStockView').textContent = product.stock_quantity || '0';
            viewProductModalEl.querySelector('#productCategoryView').textContent = product.category ? product.category.name : 'N/A';
            viewProductModalEl.querySelector('#productBrandView').textContent = product.brand ? product.brand.name : 'N/A';
            viewProductModalEl.querySelector('#productMaterialView').textContent = product.material || 'N/A';
            viewProductModalEl.querySelector('#productColorView').textContent = product.color || 'N/A';
            viewProductModalEl.querySelector('#productSpecificationsView').textContent = product.specifications || 'Không có.';

            const statusSpan = viewProductModalEl.querySelector('#productStatusView');
            statusSpan.textContent = product.deleted_at ? 'Trong thùng rác' : product.status_text;
            statusSpan.className = `badge ${product.deleted_at ? 'bg-secondary' : product.status_badge_class}`;

            const vehicleModelsContainer = viewProductModalEl.querySelector('#productVehicleModelsView');
            vehicleModelsContainer.innerHTML = '';
            if (product.vehicle_models && product.vehicle_models.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-unstyled';
                product.vehicle_models.forEach(model => { list.innerHTML += `<li>- ${model.name} (${model.year})</li>`; });
                vehicleModelsContainer.appendChild(list);
            } else {
                vehicleModelsContainer.innerHTML = '<p class="text-muted">Không có dữ liệu.</p>';
            }

            const imagesContainer = viewProductModalEl.querySelector('#productImagesView');
            imagesContainer.innerHTML = '';
            if (product.images && product.images.length > 0) {
                product.images.forEach(image => { imagesContainer.innerHTML += `<img src="${image.image_full_url}" class="img-fluid rounded mb-2" alt="${product.name}">`; });
            } else {
                imagesContainer.innerHTML = '<p class="text-muted">Chưa có hình ảnh.</p>';
            }

            viewProductModalEl.querySelector('#editProductFromViewBtn').dataset.productId = product.id;
            viewProductModalEl.querySelector('#editProductFromViewBtn').style.display = product.deleted_at ? 'none' : 'inline-block';

            viewProductModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu xem chi tiết:', error);
            showAppInfoModal(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    };

    /**
     * Hiển thị modal cập nhật sản phẩm
     * @param {number} productId - ID sản phẩm
     */
    const handleShowUpdateModal = async (productId) => {
        showAppLoader();
        try {
            const response = await fetch(`/admin/product-management/products/${productId}/details`);
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const product = await response.json();

            const form = document.getElementById('updateProductForm');
            form.action = `/admin/product-management/products/${productId}`;

            form.querySelector('#productNameUpdate').value = product.name || '';
            form.querySelector('#productDescriptionUpdate').value = product.description || '';
            form.querySelector('#productPriceUpdate').value = product.price || 0;
            form.querySelector('#productStockUpdate').value = product.stock_quantity || 0;
            form.querySelector('#productMaterialUpdate').value = product.material || '';
            form.querySelector('#productColorUpdate').value = product.color || '';
            form.querySelector('#productSpecificationsUpdate').value = product.specifications || '';
            form.querySelector('#productIsActiveUpdate').checked = product.status === 'active';

            const vehicleModelIds = product.vehicle_models ? product.vehicle_models.map(model => String(model.id)) : [];
            updateProductModalEl.dataset.categoryId = product.category_id || '';
            updateProductModalEl.dataset.brandId = product.brand_id || '';
            updateProductModalEl.dataset.vehicleModelIds = JSON.stringify(vehicleModelIds);

            const previewContainer = document.getElementById('productImagesPreviewUpdate');
            previewContainer.innerHTML = '';
            if (product.images && product.images.length > 0) {
                product.images.forEach(image => {
                    previewContainer.innerHTML += `<div class="img-preview-wrapper existing-preview"><input type="hidden" name="existing_images[]" value="${image.id}"><img src="${image.image_full_url}" class="img-preview" alt=""><button type="button" class="img-preview-remove" title="Xóa ảnh này">×</button></div>`;
                });
            }

            // Apply currency format after setting value
            const priceUpdateInput = form.querySelector('#productPriceUpdate');
            if (priceUpdateInput) {
                priceUpdateInput.value = new Intl.NumberFormat('vi-VN').format(parseFloat(priceUpdateInput.value));
            }
            clearValidationErrors(form); // Clear errors before showing
            updateProductModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            showAppInfoModal(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error', 'Lỗi Hệ thống');
        } finally {
            hideAppLoader();
        }
    };

    /**
     * Hiển thị modal xác nhận xóa mềm hoặc hàng loạt xóa mềm.
     * @param {Array<string>|string} productIds - ID sản phẩm hoặc mảng các ID.
     * @param {string} productNameOrCount - Tên sản phẩm hoặc số lượng sản phẩm.
     * @param {boolean} isBulk - True nếu là xóa hàng loạt.
     */
    const handleShowDeleteModal = (productIds, productNameOrCount, isBulk = false) => {
        const form = document.getElementById('bulkDeleteProductsForm');
        const nameElement = document.getElementById('productNameToDelete');
        const submitBtn = form.querySelector('button[type="submit"]');

        form.action = isBulk ? '/admin/product-management/products/bulk-destroy' : `/admin/product-management/products/${productIds}`;
        form.dataset.isBulk = isBulk;
        form.dataset.productIds = JSON.stringify(Array.isArray(productIds) ? productIds.map(String) : [String(productIds)]);

        nameElement.textContent = productNameOrCount;
        submitBtn.textContent = isBulk ? 'Xác nhận Xóa mềm' : 'Xóa Sản phẩm';

        clearValidationErrors(form);
        bulkDeleteProductsModal.show();
    };

    /**
     * Hiển thị modal xác nhận xóa vĩnh viễn hoặc hàng loạt xóa vĩnh viễn.
     * @param {Array<string>|string} productIds - ID sản phẩm hoặc mảng các ID.
     * @param {string} productNameOrCount - Tên sản phẩm hoặc số lượng sản phẩm.
     * @param {boolean} isBulk - True nếu là xóa hàng loạt.
     */
    const handleShowForceDeleteModal = (productIds, productNameOrCount, isBulk = false) => {
        const form = document.getElementById('bulkForceDeleteProductsForm');
        const nameElement = document.getElementById('productNameToForceDelete');
        const submitBtn = form.querySelector('button[type="submit"]');

        form.action = isBulk ? '/admin/product-management/products/bulk-force-delete' : `/admin/product-management/products/${productIds}/force-delete`;
        form.dataset.isBulk = isBulk;
        form.dataset.productIds = JSON.stringify(Array.isArray(productIds) ? productIds.map(String) : [String(productIds)]);

        nameElement.textContent = productNameOrCount;
        submitBtn.textContent = isBulk ? 'Xóa Vĩnh viễn' : 'Xóa Vĩnh viễn';

        clearValidationErrors(form);
        bulkForceDeleteProductsModal.show();
    };

    /**
     * Hiển thị modal xác nhận khôi phục hoặc hàng loạt khôi phục.
     * @param {Array<string>|string} productIds - ID sản phẩm hoặc mảng các ID.
     * @param {string} productNameOrCount - Tên sản phẩm hoặc số lượng sản phẩm.
     * @param {boolean} isBulk - True nếu là khôi phục hàng loạt.
     */
    const handleShowRestoreModal = (productIds, productNameOrCount, isBulk = false) => {
        const form = document.getElementById('bulkRestoreProductsForm');
        const nameElement = document.getElementById('productNameToRestore');
        const submitBtn = form.querySelector('button[type="submit"]');

        form.action = isBulk ? '/admin/product-management/products/bulk-restore' : `/admin/product-management/products/${productIds}/restore`;
        form.dataset.isBulk = isBulk;
        form.dataset.productIds = JSON.stringify(Array.isArray(productIds) ? productIds.map(String) : [String(productIds)]);

        nameElement.textContent = productNameOrCount;
        submitBtn.textContent = isBulk ? 'Xác nhận Khôi phục' : 'Xác nhận Khôi phục';

        clearValidationErrors(form);
        bulkRestoreProductsModal.show();
    };

    /**
     * Hiển thị modal bật/tắt trạng thái hàng loạt.
     */
    const handleShowBulkToggleStatusModal = () => {
        selectedProductsCountToggleModalSpan.textContent = selectedProductIds.size;
        clearValidationErrors(document.getElementById('bulkToggleStatusProductsForm'));
        bulkToggleStatusProductsModal.show();
    };

    // --- CÁC HÀM XỬ LÝ HÀNH ĐỘNG (AJAX) ---

    /**
     * Xử lý bật/tắt trạng thái sản phẩm
     * @param {HTMLElement} button - Nút toggle status
     */
    async function handleToggleStatus(button) {
        showAppLoader();
        try {
            const url = button.dataset.statusUrl;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Lỗi không xác định");

            showToast(result.message, 'success');
            performSearch(getCurrentPage());
        } catch (error) {
            console.error('Lỗi khi bật/tắt trạng thái:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    }

    // --- GẮN KẾT SỰ KIỆN & THIẾT LẬP FORM ---

    /**
     * Lấy số trang hiện tại từ phân trang.
     */
    function getCurrentPage() {
        if (paginationLinksContainer) {
            const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
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
    function updateTableContent(tableRowsHtml, paginationLinksHtml) {
        productTableBody.innerHTML = tableRowsHtml || `
            <tr id="no-products-row">
                <td colspan="10" class="text-center">
                    <div class="alert alert-info mb-0">Không tìm thấy kết quả phù hợp.</div>
                </td>
            </tr>`;

        const cardBody = document.querySelector('#adminProductsPage .card-body');
        if (!cardBody) {
            console.error("Card body not found. Cannot update pagination.");
            // Không return ở đây vì productTableBody đã được cập nhật.
        } else {
            // Xóa phần tử phân trang hiện có nếu nó tồn tại
            let existingPaginationDiv = cardBody.querySelector('#pagination-links');
            if (existingPaginationDiv) {
                existingPaginationDiv.remove();
            }

            // Nếu có HTML phân trang mới, tạo và thêm phần tử mới
            if (paginationLinksHtml && paginationLinksHtml.trim() !== '') {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = paginationLinksHtml; // HTML này chứa div#pagination-links
                const newPaginationDiv = tempDiv.querySelector('#pagination-links'); // Tìm div thực tế bên trong container tạm

                if (newPaginationDiv) {
                    cardBody.appendChild(newPaginationDiv);
                } else {
                    console.warn("Pagination HTML returned by server did not contain #pagination-links div.");
                }
            }
        }

        // Cập nhật biến toàn cục để trỏ đến phần tử mới (hoặc null nếu không có)
        paginationLinksContainer = document.getElementById('pagination-links');

        updateCheckboxStates();
        updateBulkActionButtons();
        attachPaginationListeners();
    }

    /**
     * Thực hiện tìm kiếm, lọc và sắp xếp bằng AJAX và cập nhật bảng.
     * @param {number} page - Số trang muốn fetch.
     */
    async function performSearch(page = 1) {
        showAppLoader();
        try {
            const currentSearchQuery = productSearchInput.value;
            const currentFilter = productFilterSelect.value;
            const currentSort = productSortSelect.value;

            const urlParams = new URLSearchParams();
            urlParams.append('page', page);
            if (currentSearchQuery) urlParams.append('search', currentSearchQuery);
            if (currentFilter && currentFilter !== 'all') urlParams.append('status', currentFilter);
            if (currentSort && currentSort !== 'latest') urlParams.append('sort_by', currentSort);

            const url = `/admin/product-management/products?${urlParams.toString()}`;

            const response = await fetch(url, {
                method: 'GET', // Đã thay đổi từ 'POST' sang 'GET'
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
            }
            const data = await response.json();
            updateTableContent(data.table_rows, data.pagination_links);
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu sản phẩm:', error);
            showToast('Không thể tải dữ liệu sản phẩm. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Gắn lại event listeners cho các liên kết phân trang sau khi DOM được cập nhật.
     */
    function attachPaginationListeners() {
        // Sử dụng biến toàn cục `paginationLinksContainer`
        if (paginationLinksContainer) {
            // Xóa listener hiện có để tránh trùng lặp
            paginationLinksContainer.removeEventListener('click', handlePaginationClick);
            // Thêm listener mới
            paginationLinksContainer.addEventListener('click', handlePaginationClick);
        }
        // Không còn console.warn ở đây vì việc không tìm thấy container là trường hợp dự kiến.
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
                performSearch(page);
            }
        }
    }

    /**
     * Xử lý thành công khi tạo mới, cập nhật hoặc toggle trạng thái đơn lẻ.
     * @param {object} product - Đối tượng sản phẩm trả về từ server.
     */
    function handleUpdateOrToggleSuccess(product) {
        performSearch(getCurrentPage());
    }

    /**
     * Xử lý thành công khi xóa sản phẩm (mềm, vĩnh viễn, khôi phục)
     * @param {Array<number>} affectedIds - Mảng ID của các sản phẩm bị ảnh hưởng.
     * @param {string} actionType - Loại hành động đã thực hiện ('delete', 'forceDelete', 'restore').
     */
    function handleActionSuccess(affectedIds, actionType) {
        if (!Array.isArray(affectedIds)) {
            console.error("Affected IDs must be an array. Received:", affectedIds);
            return;
        }

        affectedIds.forEach(id => {
            selectedProductIds.delete(String(id));
        });

        // Nếu hành động là khôi phục, chúng ta muốn chuyển về bộ lọc 'all' hoặc 'active_only'
        if (actionType === 'restore') {
            productFilterSelect.value = 'all'; // Đặt bộ lọc về 'Tất cả trạng thái'
            performSearch(1); // Tải lại trang 1 với bộ lọc mới để hiển thị sản phẩm đã khôi phục
        } else {
            const currentRowsInDom = productTableBody.querySelectorAll('tr:not(#no-products-row)').length;
            const totalRowsAfterRemoval = currentRowsInDom - affectedIds.length;

            if (totalRowsAfterRemoval <= 0 && getCurrentPage() > 1) {
                performSearch(getCurrentPage() - 1);
            } else {
                performSearch(getCurrentPage());
            }
        }
        clearSelectedProducts();
    }


    /**
     * Thiết lập các event listener
     */
    const setupEventListeners = () => {
        productTableBody.addEventListener('click', async function (event) {
            const button = event.target.closest('.btn-action');
            if (!button) return;

            const id = button.dataset.id;
            const name = button.dataset.name;

            // Data attributes from _product_table_rows.blade.php
            const deleteUrl = button.dataset.deleteUrl;
            const forceDeleteUrl = button.dataset.forceDeleteUrl;
            const restoreUrl = button.dataset.restoreUrl;
            const statusUrl = button.dataset.statusUrl;


            if (button.classList.contains('btn-view')) await handleShowViewModal(id);
            else if (button.classList.contains('btn-edit')) await handleShowUpdateModal(id);
            else if (button.classList.contains('btn-delete-product')) handleShowDeleteModal(id, name, false);
            else if (button.classList.contains('toggle-status-product-btn')) await handleToggleStatus(button);
            else if (button.classList.contains('btn-restore-product')) handleShowRestoreModal(id, name, false);
            else if (button.classList.contains('btn-force-delete-product')) handleShowForceDeleteModal(id, name, false);
        });

        document.body.addEventListener('click', function (event) {
            if (event.target.classList.contains('img-preview-remove')) {
                event.preventDefault();
                event.target.closest('.img-preview-wrapper').remove();
            }
        });

        document.getElementById('editProductFromViewBtn').addEventListener('click', function (event) {
            const productId = event.currentTarget.dataset.productId;
            if (productId) {
                viewProductModal.hide();
                setTimeout(() => handleShowUpdateModal(productId), 200);
            }
        });

        $(createProductModalEl).on('shown.bs.modal', () => {
            $('#createProductForm .selectpicker').selectpicker('refresh');
        });

        $(updateProductModalEl).on('shown.bs.modal', () => { // Corrected element reference
            const categoryId = updateProductModalEl.dataset.categoryId;
            const brandId = updateProductModalEl.dataset.brandId;
            const vehicleModelIds = JSON.parse(updateProductModalEl.dataset.vehicleModelIds || '[]');
            $('#productCategoryUpdate').selectpicker('val', categoryId);
            $('#productBrandUpdate').selectpicker('val', brandId);
            $('#productVehicleModelsUpdate').selectpicker('val', vehicleModelIds);
            $('#updateProductForm .selectpicker').selectpicker('refresh');
        });

        // Search, Filter, Sort Event Listeners
        productSearchBtn.addEventListener('click', () => performSearch(1));
        productSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(1);
            }
        });
        productFilterSelect.addEventListener('change', () => performSearch(1));
        productSortSelect.addEventListener('change', () => performSearch(1));

        // Bulk Action Button Listeners
        bulkToggleStatusBtn.addEventListener('click', handleShowBulkToggleStatusModal);
        bulkRestoreBtn.addEventListener('click', () => {
            handleShowRestoreModal(Array.from(selectedProductIds), `${selectedProductIds.size} sản phẩm đã chọn`, true);
        });
        bulkDeleteBtn.addEventListener('click', () => {
            handleShowDeleteModal(Array.from(selectedProductIds), `${selectedProductIds.size} sản phẩm đã chọn`, true);
        });
        bulkForceDeleteBtn.addEventListener('click', () => {
            handleShowForceDeleteModal(Array.from(selectedProductIds), `${selectedProductIds.size} sản phẩm đã chọn`, true);
        });
    };

    /**
     * Thiết lập xử lý AJAX cho một form cụ thể.
     * @param {string} formId - ID của form.
     * @param {Object} modalInstance - Instance của Bootstrap Modal chứa form.
     * @param {function} successCallback - Hàm callback khi form gửi thành công.
     */
    function setupAjaxForm(formId, modalInstance, successCallback) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Không thể thiết lập AJAX form: Form ID "${formId}" không tồn tại.`);
            return;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();
            clearValidationErrors(form);

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                if (formId.includes('ProductsForm')) {
                     submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...`;
                } else {
                    submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${submitButton.textContent}...`;
                }
            }

            let requestBody = new FormData(this); // LUÔN LUÔN SỬ DỤNG FORM DATA cho tất cả các form
            let requestHeaders = {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            };

            // Handle _method spoofing for PUT/DELETE
            if (formId === 'updateProductForm') {
                requestBody.append('_method', 'PUT');
            } else if (form.dataset.isBulk === 'false' && (formId === 'bulkDeleteProductsForm' || formId === 'bulkForceDeleteProductsForm')) {
                requestBody.append('_method', 'DELETE');
            }

            // Special handling for price field: parse from VNĐ format to raw number
            const priceInput = form.querySelector('[name="price"]');
            if (priceInput && requestBody.has('price')) {
                requestBody.set('price', parseFormattedCurrency(requestBody.get('price')));
            }

            // Gắn các ID đã chọn cho hành động hàng loạt
            const isBulkActionForm = formId === 'bulkDeleteProductsForm' || formId === 'bulkForceDeleteProductsForm' || formId === 'bulkRestoreProductsForm' || formId === 'bulkToggleStatusProductsForm';
            if (isBulkActionForm) {
                // Thêm từng ID dưới dạng 'ids[]' để Laravel nhận diện là một mảng
                Array.from(selectedProductIds).forEach(id => {
                    requestBody.append('ids[]', id);
                });
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST', // Fetch API vẫn dùng POST, Laravel xử lý _method
                    headers: requestHeaders,
                    body: requestBody // FormData tự động đặt Content-Type là multipart/form-data
                });

                const result = await response.json();

                if (response.ok) { // Status code 2xx
                    showToast(result.message, 'success');
                    modalInstance.hide();

                    let affectedIdsToPass = [];
                    let actionType = '';

                    if (formId === 'bulkDeleteProductsForm') {
                        affectedIdsToPass = result.deleted_ids || [];
                        actionType = 'delete';
                    } else if (formId === 'bulkForceDeleteProductsForm') {
                        affectedIdsToPass = result.force_deleted_ids || [];
                        actionType = 'forceDelete';
                    } else if (formId === 'bulkRestoreProductsForm') {
                        affectedIdsToPass = result.restored_ids || [];
                        actionType = 'restore';
                    } else if (formId === 'bulkToggleStatusProductsForm') {
                        // For bulk toggle status, we just need to re-perform search
                        // and clear selected products, no specific affectedIds handling needed here.
                        performSearch(getCurrentPage());
                        clearSelectedProducts();
                        hideAppLoader(); // Hide loader here as no further action is needed
                        return; // Exit function early
                    }

                    // For single delete/force-delete/restore, get ID from dataset
                    if (form.dataset.isBulk === 'false' && affectedIdsToPass.length === 0 && form.dataset.productIds) {
                         try {
                            affectedIdsToPass = JSON.parse(form.dataset.productIds);
                        } catch (e) {
                            console.error("Error parsing single product ID from dataset:", e);
                            affectedIdsToPass = [];
                        }
                    }

                    handleActionSuccess(affectedIdsToPass, actionType);

                } else if (response.status === 422) { // Validation errors
                    displayValidationErrors(form, result.errors);
                    showToast(result.message || 'Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else { // Other errors
                    showToast(result.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.', 'error');
                    console.error('AJAX Error:', result);
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
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
    }

    // --- CHECKBOX & BULK ACTIONS ---
    /**
     * Cập nhật trạng thái các nút hành động hàng loạt (disabled/enabled) và số lượng đã chọn.
     */
    function updateBulkActionButtons() {
        const count = selectedProductIds.size;
        bulkActionsDropdownBtn.disabled = count === 0;
        selectedCountBulkSpan.textContent = count;
        selectedProductsCountToggleModalSpan.textContent = count;
    }

    /**
     * Cập nhật trạng thái của từng checkbox (checked/unchecked) dựa trên `selectedProductIds`.
     */
    function updateCheckboxStates() {
        const currentCheckboxes = document.querySelectorAll('.product-checkbox');
        if (currentCheckboxes.length === 0) {
            if (selectAllProductsCheckbox) selectAllProductsCheckbox.checked = false;
            return;
        }
        let allVisibleChecked = true;
        currentCheckboxes.forEach(checkbox => {
            checkbox.checked = selectedProductIds.has(checkbox.value);
            if (!checkbox.checked) allVisibleChecked = false;
        });
        if (selectAllProductsCheckbox) selectAllProductsCheckbox.checked = allVisibleChecked;
        updateBulkActionButtons();
    }

    /**
     * Xóa tất cả các lựa chọn checkbox và reset trạng thái.
     */
    function clearSelectedProducts() {
        selectedProductIds.clear();
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        if (selectAllProductsCheckbox) selectAllProductsCheckbox.checked = false;
        updateBulkActionButtons();
    }

    // Lắng nghe sự kiện cho checkbox "Chọn tất cả"
    selectAllProductsCheckbox.addEventListener('change', function () {
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedProductIds.add(checkbox.value);
            } else {
                selectedProductIds.delete(checkbox.value);
            }
        });
        updateBulkActionButtons();
    });

    // Lắng nghe sự kiện cho từng checkbox sản phẩm (sử dụng delegation trên tableBody)
    productTableBody.addEventListener('change', function (event) {
        const checkbox = event.target.closest('.product-checkbox');
        if (checkbox) {
            if (checkbox.checked) {
                selectedProductIds.add(checkbox.value);
            } else {
                selectedProductIds.delete(checkbox.value);
            }
            updateBulkActionButtons();
            const allIndividualCheckboxes = document.querySelectorAll('.product-checkbox');
            const checkedIndividualCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            if (selectAllProductsCheckbox) {
                selectAllProductsCheckbox.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
            }
        }
    });

    // --- CHẠY CÁC HÀM KHỞI TẠO ---
    initializeSelectPickers();
    setupModalResets();
    setupEventListeners();

    // Thiết lập AJAX cho các form
    setupAjaxForm('createProductForm', createProductModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('updateProductForm', updateProductModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('bulkDeleteProductsForm', bulkDeleteProductsModal, handleActionSuccess);
    setupAjaxForm('bulkForceDeleteProductsForm', bulkForceDeleteProductsModal, handleActionSuccess);
    setupAjaxForm('bulkRestoreProductsForm', bulkRestoreProductsModal, handleActionSuccess);
    setupAjaxForm('bulkToggleStatusProductsForm', bulkToggleStatusProductsModal, handleActionSuccess);


    // Áp dụng định dạng tiền tệ cho input price
    const productPriceCreateInput = document.getElementById('productPriceCreate');
    const productPriceUpdateInput = document.getElementById('productPriceUpdate');

    if (productPriceCreateInput) {
        productPriceCreateInput.setAttribute('data-currency-input', 'true');
        formatCurrencyInput(productPriceCreateInput);
    }
    if (productPriceUpdateInput) {
        productPriceUpdateInput.setAttribute('data-currency-input', 'true');
        formatCurrencyInput(productPriceUpdateInput);
    }

    // Perform initial search to load data
    performSearch(1);


    console.log("JS cho trang Sản phẩm đã được khởi tạo thành công với đầy đủ tính năng.");
}
