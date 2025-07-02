// File: public/assets_admin/js/product_management.js (UPDATED)

/**
 * ===================================================================
 * product_management.js
 * Xử lý JavaScript cho trang quản lý Sản phẩm (Thêm, Sửa, Xóa, Xem).
 * Phiên bản: Hoàn chỉnh, đã sửa lỗi route 405 và đảm bảo phương thức POST cho update.
 * Đã tích hợp thông báo Toast và định dạng tiền tệ (tương tự promotion_manager.js).
 * Đã thêm các tính năng quản lý hàng loạt và bộ lọc/sắp xếp/tìm kiếm nâng cao.
 * ===================================================================
 */

function initializeProductsPage() {
    console.log("Khởi tạo JS cho trang Sản phẩm...");

    // ===================================================================
    // KHAI BÁO CÁC PHẦN TỬ HTML CẦN THIẾT (đảm bảo ID khớp với Blade)
    // ===================================================================

    // Main Modal Elements
    const createProductModalEl = document.getElementById('createProductModal');
    const updateProductModalEl = document.getElementById('updateProductModal');
    const viewProductModalEl = document.getElementById('viewProductModal');
    const deleteModalEl = document.getElementById('confirmDeleteModal'); // Single soft delete confirmation
    const forceDeleteModalEl = document.getElementById('confirmForceDeleteModal'); // Single force delete confirmation
    const restoreModalEl = document.getElementById('confirmRestoreModal'); // Single restore confirmation

    // Bulk Action Modal Elements
    const bulkDeleteProductModalEl = document.getElementById('bulkDeleteProductModal'); // Bulk soft delete
    const bulkForceDeleteProductModalEl = document.getElementById('bulkForceDeleteProductModal'); // Bulk force delete
    const bulkRestoreProductModalEl = document.getElementById('bulkRestoreProductModal'); // Bulk restore
    const bulkToggleStatusProductModalEl = document.getElementById('bulkToggleStatusProductModal'); // Bulk toggle status

    // Table and Control Elements
    const productTableBody = document.getElementById('product-table-body');
    const productSearchInput = document.getElementById('productSearchInput');
    const productSearchBtn = document.getElementById('productSearchBtn');
    const productFilterSelect = document.getElementById('productFilterSelect');
    const productSortSelect = document.getElementById('productSortSelect');
    const paginationLinksContainer = document.getElementById('pagination-links');

    // Bulk Action Buttons and Counters
    const selectAllProductsCheckbox = document.getElementById('selectAllProducts');
    const bulkSoftDeleteBtn = document.getElementById('bulkSoftDeleteBtn');
    const bulkToggleStatusBtn = document.getElementById('bulkToggleStatusBtn');
    const bulkRestoreBtn = document.getElementById('bulkRestoreBtn');
    const bulkForceDeleteBtn = document.getElementById('bulkForceDeleteBtn');

    const selectedCountSoftDeleteSpan = document.getElementById('selectedCountSoftDelete');
    const selectedCountToggleSpan = document.getElementById('selectedCountToggle');
    const selectedCountRestoreSpan = document.getElementById('selectedCountRestore');
    const selectedCountForceDeleteSpan = document.getElementById('selectedCountForceDelete');

    // Bulk Action Visibility Toggles (Tabs)
    const bulkActionsNormalDiv = document.getElementById('bulkActionsNormal');
    const bulkActionsTrashedDiv = document.getElementById('bulkActionsTrashed');
    const allProductsTab = document.getElementById('allProductsTab');
    const trashedProductsTab = document.getElementById('trashedProductsTab');


    // ===================================================================
    // KIỂM TRA SỰ TỒN TẠI CỦA CÁC PHẦN TỬ QUAN TRỌNG
    // ===================================================================
    const requiredElements = {
        createProductModalEl, updateProductModalEl, viewProductModalEl, deleteModalEl, forceDeleteModalEl, restoreModalEl,
        bulkDeleteProductModalEl, bulkForceDeleteProductModalEl, bulkRestoreProductModalEl, bulkToggleStatusProductModalEl,
        productTableBody, productSearchInput, productSearchBtn, productFilterSelect, productSortSelect, paginationLinksContainer,
        selectAllProductsCheckbox, bulkSoftDeleteBtn, bulkToggleStatusBtn, bulkRestoreBtn, bulkForceDeleteBtn,
        selectedCountSoftDeleteSpan, selectedCountToggleSpan, selectedCountRestoreSpan, selectedCountForceDeleteSpan,
        bulkActionsNormalDiv, bulkActionsTrashedDiv, allProductsTab, trashedProductsTab
    };

    const missingElements = Object.entries(requiredElements).filter(([key, value]) => !value);

    if (missingElements.length > 0) {
        console.warn('Cảnh báo: Một hoặc nhiều element modal/table/filter/sort/bulk quan trọng không tồn tại. Script có thể không hoạt động đầy đủ. Các phần tử thiếu:', missingElements.map(([key]) => key).join(', '));
        return; // Dừng khởi tạo nếu thiếu các phần tử cốt lõi
    }

    // Lấy CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // Lấy các hàm helper toàn cục từ admin_layout.js (giả định tồn tại)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showAppInfoModal = typeof window.showAppInfoModal === 'function' ? window.showAppInfoModal : (msg, type) => alert(`${type}: ${msg}`);
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`); // Fallback to alert if toast container is missing
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

    // Bootstrap Modal Instances
    const createProductModal = new bootstrap.Modal(createProductModalEl);
    const updateProductModal = new bootstrap.Modal(updateProductModalEl);
    const viewProductModal = new bootstrap.Modal(viewProductModalEl);
    const deleteProductModal = new bootstrap.Modal(deleteModalEl); // Single soft delete
    const forceDeleteProductModal = new bootstrap.Modal(forceDeleteModalEl); // Single force delete
    const restoreProductModal = new bootstrap.Modal(restoreModalEl); // Single restore
    const bulkDeleteProductModal = new bootstrap.Modal(bulkDeleteProductModalEl); // Bulk soft delete
    const bulkForceDeleteProductModal = new bootstrap.Modal(bulkForceDeleteProductModalEl); // Bulk force delete
    const bulkRestoreProductModal = new bootstrap.Modal(bulkRestoreProductModalEl); // Bulk restore
    const bulkToggleStatusProductModal = new bootstrap.Modal(bulkToggleStatusProductModalEl); // Bulk toggle status


    let selectedProductIds = new Set(); // Stores IDs of selected products


    // --- CÁC HÀM KHỞI TẠO & HỖ TRỢ ---

    /**
     * Khởi tạo SelectPicker cho các select element
     * This function should be called initially on page load.
     * When a modal is shown, only `refresh` is typically needed if options are not dynamic.
     * If options ARE dynamic (e.g., loaded by AJAX), then `destroy` and re-create is valid.
     */
    const initializeSelectPickers = () => {
        try {
            const $pickers = $('.selectpicker');
            if ($pickers.length === 0) return;
            // Destroy existing selectpicker instances to prevent duplicates
            if ($pickers.data('selectpicker')) { // Check if selectpicker is already initialized
                $pickers.selectpicker('destroy');
            }
            $pickers.selectpicker({
                liveSearch: true,
                width: '100%',
                noneSelectedText: 'Chưa chọn mục nào',
                actionsBox: true,
                selectAllText: 'Chọn tất cả',
                deselectAllText: 'Bỏ chọn tất cả'
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
            // Remove only newly added previews, keep existing ones for update modal
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

                // Xử lý các trường hợp đặc biệt nếu name không khớp trực tiếp với ID
                if (!inputField) {
                    if (fieldName === 'category_id') {
                        inputField = formElement.querySelector('#productCategoryCreate') || formElement.querySelector('#productCategoryUpdate');
                    } else if (fieldName === 'brand_id') {
                        inputField = formElement.querySelector('#productBrandCreate') || formElement.querySelector('#productBrandUpdate');
                    } else if (fieldName === 'vehicle_model_ids') {
                        inputField = formElement.querySelector('#productVehicleModelsCreate') || formElement.querySelector('#productVehicleModelsUpdate');
                    } else if (fieldName === 'admin_password_confirm_delete') {
                        inputField = formElement.querySelector('#admin_password_confirm_delete');
                    } else if (fieldName === 'admin_password_bulk_force_delete') {
                        inputField = formElement.querySelector('#admin_password_bulk_force_delete');
                    }
                    // Thêm các trường khác nếu cần
                }


                if (inputField) {
                    inputField.classList.add('is-invalid');
                    // Find the appropriate invalid-feedback element
                    let errorDiv = inputField.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        // For selectpickers or other complex structures, find within parent group
                        const formGroup = inputField.closest('.form-group') || inputField.closest('.mb-3');
                        if (formGroup) {
                            errorDiv = formGroup.querySelector('.invalid-feedback');
                        }
                    }

                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0];
                    } else {
                        console.warn(`Không tìm thấy div .invalid-feedback cho trường: ${fieldName}`);
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
        const formatValue = (value) => {
            if (value === null || value === undefined) return '';
            let raw = String(value);

            // Remove all non-numeric and non-comma characters
            raw = raw.replace(/[^0-9,]/g, '');

            // Split into integer and decimal parts
            let parts = raw.split(',');
            let integerPart = parts[0];
            let decimalPart = parts[1] ? ',' + parts[1] : '';

            // Format integer part with dot as thousands separator
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            return integerPart + decimalPart;
        };

        // Set initial formatted value if not empty
        if (inputElement.value) {
            inputElement.value = formatValue(inputElement.value);
        }

        inputElement.addEventListener('input', function (e) {
            const originalValue = e.target.value;
            const originalLength = originalValue.length;
            const caretPosition = e.target.selectionStart;

            // Get current raw numbers for caret position calculation
            const rawNumbersBeforeCaret = originalValue.substring(0, caretPosition).replace(/[^0-9]/g, '');

            const formattedValue = formatValue(originalValue);
            e.target.value = formattedValue;

            // Calculate new caret position
            let newCaretPosition = 0;
            let currentRawIndex = 0;
            for (let i = 0; i < formattedValue.length; i++) {
                if (newCaretPosition >= caretPosition + (formattedValue.length - originalLength)) {
                    break;
                }
                if (/\d/.test(formattedValue[i])) { // If character is a digit
                    if (currentRawIndex < rawNumbersBeforeCaret.length && formattedValue[i] === rawNumbersBeforeCaret[currentRawIndex]) {
                        currentRawIndex++;
                    }
                }
                newCaretPosition++;
            }
            e.target.setSelectionRange(newCaretPosition, newCaretPosition);
        });

        // Optional: select all text on focus
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
            if (form) {
                form.reset();
                clearValidationErrors(form);
                document.getElementById('productImagesPreviewCreate').innerHTML = '';
                // Refresh selectpickers after reset
                $('#createProductForm .selectpicker').selectpicker('val', '');
                $('#createProductForm .selectpicker').selectpicker('refresh');
                // Reset currency inputs if they have a data-currency-input attribute
                form.querySelectorAll('[data-currency-input="true"]').forEach(input => {
                    input.value = ''; // Clear actual value
                });
            }
        });
        const createImagesInput = document.getElementById('productImagesCreate');
        const createImagesPreview = document.getElementById('productImagesPreviewCreate');
        if (createImagesInput && createImagesPreview) setupImagePreviews(createImagesInput, createImagesPreview);

        // Reset modal cập nhật
        updateProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('updateProductForm');
            if (form) {
                form.reset();
                clearValidationErrors(form);
                document.getElementById('productImagesPreviewUpdate').innerHTML = '';
                // The file input's value cannot be programmatically set for security reasons.
                // Resetting it by form.reset() should be enough or re-create it if issues persist.
                document.getElementById('productImagesUpdate').value = '';
                // Ensure selectpickers are reset when modal closes
                $('#updateProductForm .selectpicker').selectpicker('val', '');
                $('#updateProductForm .selectpicker').selectpicker('refresh');
                // Reset currency inputs if they have a data-currency-input attribute
                form.querySelectorAll('[data-currency-input="true"]').forEach(input => {
                    input.value = ''; // Clear actual value
                });
            }
        });
        const updateImagesInput = document.getElementById('productImagesUpdate');
        const updateImagesPreview = document.getElementById('productImagesPreviewUpdate');
        if (updateImagesInput && updateImagesPreview) setupImagePreviews(updateImagesInput, updateImagesPreview);

        // Clear validation on bulk action modals when hidden
        bulkDeleteProductModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('bulkDeleteProductForm'));
        });
        bulkForceDeleteProductModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('bulkForceDeleteProductForm'));
            document.getElementById('bulkForceDeleteProductForm').querySelector('#admin_password_bulk_force_delete').value = '';
        });
        bulkRestoreProductModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('bulkRestoreProductForm'));
        });
        bulkToggleStatusProductModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('bulkToggleStatusProductForm'));
        });
        deleteModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('deleteProductForm'));
        });
        forceDeleteModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('forceDeleteProductForm'));
            document.getElementById('forceDeleteProductForm').querySelector('#admin_password_confirm_delete').value = '';
        });
        restoreModalEl.addEventListener('hidden.bs.modal', () => {
            clearValidationErrors(document.getElementById('restoreProductForm'));
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
            const response = await fetch(`/admin/product-management/products/${productId}/details`); // Using new API route
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
            showToast(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error');
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

            // Apply currency formatting for price input
            const priceUpdateInput = form.querySelector('#productPriceUpdate');
            if (priceUpdateInput) {
                priceUpdateInput.value = product.price; // Set raw value
                formatCurrencyInput(priceUpdateInput); // Format it
            }

            form.querySelector('#productStockUpdate').value = product.stock_quantity || 0;
            form.querySelector('#productMaterialUpdate').value = product.material || '';
            form.querySelector('#productColorUpdate').value = product.color || '';
            form.querySelector('#productSpecificationsUpdate').value = product.specifications || '';
            form.querySelector('#productIsActiveUpdate').checked = product.status === 'active';

            const vehicleModelIds = product.vehicle_models ? product.vehicle_models.map(model => String(model.id)) : [];

            // IMPORTANT FIX for Selectpicker not populating:
            // Ensure selectpicker is initialized (or re-initialized) BEFORE setting values.
            // Then set values, and then refresh.
            // Calling initializeSelectPickers() here will destroy and recreate,
            // ensuring a clean state before setting values.
            initializeSelectPickers();

            // Set values for selectpickers
            $('#productCategoryUpdate').selectpicker('val', product.category_id);
            $('#productBrandUpdate').selectpicker('val', product.brand_id);
            $('#productVehicleModelsUpdate').selectpicker('val', vehicleModelIds);

            // Refresh individual selectpickers after setting their values
            $('#productCategoryUpdate').selectpicker('refresh');
            $('#productBrandUpdate').selectpicker('refresh');
            $('#productVehicleModelsUpdate').selectpicker('refresh');


            const previewContainer = document.getElementById('productImagesPreviewUpdate');
            previewContainer.innerHTML = '';
            if (product.images && product.images.length > 0) {
                product.images.forEach(image => {
                    // Include hidden input for existing image ID
                    previewContainer.innerHTML += `<div class="img-preview-wrapper existing-preview"><input type="hidden" name="existing_images[]" value="${image.id}"><img src="${image.image_full_url}" class="img-preview" alt=""><button type="button" class="img-preview-remove" title="Xóa ảnh này">×</button></div>`;
                });
            }

            updateProductModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            showToast(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error');
        } finally {
            hideAppLoader();
        }
    };

    /**
     * Hiển thị modal xác nhận xóa mềm
     * @param {number} productId - ID sản phẩm
     * @param {string} productName - Tên sản phẩm
     */
    const handleShowDeleteModal = (productId, productName) => {
        const form = document.getElementById('deleteProductForm');
        form.action = `/admin/product-management/products/${productId}`;
        document.getElementById('productNameToDelete').textContent = productName || 'Sản phẩm này';
        deleteProductModal.show();
    };

    /**
     * Hiển thị modal xác nhận xóa vĩnh viễn
     * @param {string} deleteUrl - URL để xóa vĩnh viễn
     * @param {string} productName - Tên sản phẩm
     */
    const handleShowForceDeleteModal = (deleteUrl, productName) => {
        const form = document.getElementById('forceDeleteProductForm');
        form.action = deleteUrl;
        const nameElement = document.getElementById('productNameToForceDelete');
        if (nameElement) {
            nameElement.textContent = productName || 'Sản phẩm này';
        }
        forceDeleteProductModal.show();
    };

    /**
     * Hiển thị modal xác nhận khôi phục
     * @param {number} productId - ID sản phẩm
     * @param {string} productName - Tên sản phẩm
     */
    const handleShowRestoreModal = (productId, productName) => {
        const form = document.getElementById('restoreProductForm');
        form.action = `/admin/product-management/products/${productId}/restore`;
        document.getElementById('productNameToRestore').textContent = productName || 'Sản phẩm này';
        restoreProductModal.show();
    };

    // --- CÁC HÀM XỬ LÝ HÀNH ĐỘNG (AJAX) ---

    /**
     * Xử lý bật/tắt trạng thái sản phẩm
     * @param {HTMLElement} button - Nút toggle status
     */
    async function handleToggleStatus(button) {
        showAppLoader();
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Lỗi không xác định");

            showToast(result.message, 'success');
            // Refresh the table to reflect the change and maintain filters/sorts
            handleUpdateOrToggleSuccess();

        } catch (error) {
            console.error('Lỗi khi bật/tắt trạng thái:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    }


    /**
     * Update table content and re-attach event listeners for pagination.
     * @param {string} tableRowsHtml - HTML for table rows.
     * @param {string} paginationLinksHtml - HTML for pagination links.
     */
    function updateTableContent(tableRowsHtml, paginationLinksHtml) {
        productTableBody.innerHTML = tableRowsHtml || `
            <tr id="no-products-row"><td colspan="10" class="text-center">
                <div class="alert alert-info mb-0" role="alert"><i class="bi bi-info-circle me-2"></i>Không tìm thấy kết quả phù hợp.</div>
            </td></tr>`;

        if (paginationLinksContainer) {
            paginationLinksContainer.innerHTML = paginationLinksHtml || '';
        }
        updateCheckboxStates();
        updateBulkActionButtons();
        attachPaginationListeners(); // Re-attach listeners to new pagination links
        checkAndToggleBulkActionVisibility(); // Check and toggle bulk action buttons based on current tab
    }

    /**
     * Perform AJAX search, filter, and sort for products.
     * @param {number} page - Current page number.
     * @param {string} currentSearchQuery - Current search input value.
     */
    async function performSearch(page = 1) {
        showAppLoader();
        try {
            const currentSearchQuery = productSearchInput.value;
            const currentFilter = productFilterSelect.value;
            const currentSort = productSortSelect.value;

            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', page);
            urlParams.set('filter', currentFilter); // Always set filter, even if 'all'
            urlParams.set('sort_by', currentSort); // Always set sort_by

            // Remove search if empty, otherwise add it
            if (currentSearchQuery) {
                urlParams.set('search', currentSearchQuery);
            } else {
                urlParams.delete('search');
            }

            // Always update the 'status' URL parameter based on the current active tab
            const currentStatusTab = productFilterSelect.value === 'trashed' ? 'trashed' : null;
            if (currentStatusTab) {
                urlParams.set('status', currentStatusTab);
            } else {
                urlParams.delete('status');
            }


            const url = `/admin/product-management/products?${urlParams.toString()}`;

            const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();
            updateTableContent(data.table_rows, data.pagination_links);
            clearSelectedProducts(); // Clear selections on new search/filter/sort
        } catch (error) {
            console.error('Lỗi khi tìm kiếm, lọc hoặc sắp xếp:', error);
            showToast('Không thể tải dữ liệu. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    // Attach listeners for pagination links dynamically
    function attachPaginationListeners() {
        if (paginationLinksContainer) {
            // Remove existing listeners to prevent duplicates
            paginationLinksContainer.removeEventListener('click', handlePaginationClick);
            // Add new listener
            paginationLinksContainer.addEventListener('click', handlePaginationClick);
        }
    }

    // Handle pagination link clicks
    function handlePaginationClick(event) {
        const link = event.target.closest('.pagination a');
        if (link) {
            event.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            if (page) {
                performSearch(parseInt(page, 10));
            }
        }
    }

    /**
     * Callback function for successful create/update/single toggle status.
     * Re-fetches current page to ensure fresh data and correct ordering/filtering.
     */
    function handleUpdateOrToggleSuccess() {
        // Attempt to get the current page from the pagination links
        let currentPage = 1;
        if (paginationLinksContainer) {
            const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
            if (activePageLink) {
                currentPage = parseInt(activePageLink.textContent, 10);
            }
        }
        performSearch(currentPage);
    }

    /**
     * Callback for single delete/restore/force delete success.
     * @param {Array<number>} affectedIds - Array of IDs that were affected.
     */
    function handleDeleteRestoreOrForceDeleteSuccess(affectedIds) {
        if (!Array.isArray(affectedIds)) return;

        affectedIds.forEach(id => {
            const row = document.getElementById(`product-row-${id}`);
            if (row) {
                row.remove();
            }
            selectedProductIds.delete(String(id));
        });

        // If all items on the current page are removed, go to the previous page
        const currentRows = productTableBody.querySelectorAll('tr:not(#no-products-row)');
        if (currentRows.length === 0) {
            let currentPage = 1;
            if (paginationLinksContainer) {
                const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
                if (activePageLink) {
                    currentPage = parseInt(activePageLink.textContent, 10);
                }
            }
            const targetPage = currentPage > 1 ? currentPage - 1 : 1;
            performSearch(targetPage);
        } else {
            // Re-index STT if rows are removed from the current page
            const currentPageNum = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            const itemsPerPage = 10; // Assuming 10 items per page based on controller pagination
            const startIndex = (currentPageNum - 1) * itemsPerPage; // 0-indexed start
            Array.from(productTableBody.children).forEach((row, index) => {
                const sTTCell = row.querySelector('th[scope="row"]');
                if (sTTCell) sTTCell.textContent = startIndex + index + 1;
            });
            updateBulkActionButtons();
            updateCheckboxStates(); // Ensure selectAllProducts is updated
        }
        clearSelectedProducts(); // Clear all selections after action
    }

    // --- GẮN KẾT SỰ KIỆN & THIẾT LẬP FORM ---

    /**
     * Thiết lập các event listener cho các nút hành động đơn lẻ và xử lý ảnh.
     */
    const setupEventListeners = () => {
        productTableBody.addEventListener('click', async function (event) {
            const button = event.target.closest('.btn-action');
            if (!button) return;

            const id = button.dataset.id;
            const name = button.dataset.name;
            const deleteUrl = button.dataset.deleteUrl;

            if (button.classList.contains('btn-view')) {
                await handleShowViewModal(id);
            } else if (button.classList.contains('btn-edit')) {
                await handleShowUpdateModal(id);
            } else if (button.classList.contains('btn-delete')) {
                handleShowDeleteModal(id, name);
            } else if (button.classList.contains('toggle-status-product-btn')) {
                await handleToggleStatus(button);
            } else if (button.classList.contains('btn-restore-product')) {
                handleShowRestoreModal(id, name);
            } else if (button.classList.contains('btn-force-delete-product')) {
                handleShowForceDeleteModal(deleteUrl, name);
            }
        });

        // Event listener for removing image previews (both new and existing)
        document.body.addEventListener('click', function (event) {
            if (event.target.classList.contains('img-preview-remove')) {
                event.preventDefault();
                const wrapper = event.target.closest('.img-preview-wrapper');
                if (wrapper && wrapper.classList.contains('existing-preview')) {
                    // For existing images, mark the hidden input for deletion
                    wrapper.querySelector('input[type="hidden"]').name = 'deleted_existing_images[]';
                    wrapper.style.display = 'none'; // Hide instead of remove
                } else if (wrapper) {
                    wrapper.remove(); // For new images, just remove
                }
            }
        });

        // "Edit" button inside "View" modal
        document.getElementById('editProductFromViewBtn').addEventListener('click', function (event) {
            const productId = event.currentTarget.dataset.productId;
            if (productId) {
                viewProductModal.hide();
                setTimeout(() => handleShowUpdateModal(productId), 200); // Wait for view modal to close
            }
        });

        // Initialize selectpickers on modal shown (for dynamic content)
        // This is primarily for `createProductModal` because `updateProductModal`
        // calls `initializeSelectPickers()` directly within `handleShowUpdateModal`.
        $(createProductModalEl).on('shown.bs.modal', () => {
            $('#createProductForm .selectpicker').selectpicker('refresh');
        });

        // Search, Filter, Sort event listeners
        productSearchBtn.addEventListener('click', () => performSearch(1));
        productSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(1);
            }
        });
        productFilterSelect.addEventListener('change', () => performSearch(1));
        productSortSelect.addEventListener('change', () => performSearch(1));

        // Event listeners for tab changes (All Products, Trashed)
        allProductsTab.addEventListener('click', function (e) {
            e.preventDefault();
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('status'); // Remove status param for 'all'
            window.history.pushState({}, '', currentUrl.toString()); // Update URL without reloading
            productFilterSelect.value = 'all'; // Set filter to 'all'
            performSearch(1);
            checkAndToggleBulkActionVisibility();
        });

        trashedProductsTab.addEventListener('click', function (e) {
            e.preventDefault();
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('status', 'trashed'); // Add status=trashed param
            window.history.pushState({}, '', currentUrl.toString()); // Update URL without reloading
            productFilterSelect.value = 'trashed'; // Set filter to 'trashed'
            performSearch(1);
            checkAndToggleBulkActionVisibility();
        });
    };

    /**
     * Thiết lập xử lý AJAX cho một form cụ thể.
     * @param {string} formId - ID của form.
     * @param {object} modalInstance - Bootstrap Modal instance.
     * @param {function} successCallback - Hàm callback khi form gửi thành công.
     * @param {string} method - Phương thức HTTP ('POST', 'PUT', 'DELETE').
     */
    function setupAjaxForm(formId, modalInstance, successCallback, method = 'POST') {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();
            clearValidationErrors(form);

            const formData = new FormData(form);

            // Add _method for DELETE requests only, as per provided web.php routes
            if (method === 'DELETE') {
                formData.append('_method', 'DELETE');
            }
            // For 'update' which is now POST /{product}, no _method is needed.
            // For 'toggleStatus' and 'restore', which are POST /{product}/action, no _method is needed.

            // Special handling for price field: parse from VNĐ format to raw number
            const priceInput = form.querySelector('[name="price"]');
            if (priceInput && formData.has('price')) {
                formData.set('price', parseFormattedCurrency(priceInput.value));
            }

            // Handle bulk action IDs
            const isBulkDeleteForm = formId === 'bulkDeleteProductForm';
            const isBulkForceDeleteForm = formId === 'bulkForceDeleteProductForm';
            const isBulkRestoreForm = formId === 'bulkRestoreProductForm';
            const isBulkToggleStatusForm = formId === 'bulkToggleStatusProductForm';

            if (isBulkDeleteForm || isBulkForceDeleteForm || isBulkRestoreForm || isBulkToggleStatusForm) {
                formData.append('ids', JSON.stringify(Array.from(selectedProductIds)));
            }

            try {
                // All form submissions will be sent as POST requests, Laravel's routing will handle it.
                // For DELETE routes, Laravel checks for the _method field.
                const response = await fetch(form.action, {
                    method: 'POST', // Always POST for form submissions with FormData
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // Important for Laravel to detect AJAX
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) { // Status code 2xx
                    showToast(result.message, 'success');
                    modalInstance.hide();
                    if (result.deleted_ids || result.restored_ids) { // For delete/restore (single or bulk)
                        successCallback(result.deleted_ids || result.restored_ids);
                    } else if (result.products || result.product) { // For update/create/bulk toggle status
                        // If it's a single product update/create, result.product will be present. Wrap it in an array.
                        // If it's bulk toggle status, result.products will be present.
                        successCallback(result.products || [result.product]);
                    } else {
                        successCallback(); // Generic refresh for other cases
                    }
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
            }
        });
    }

    // --- CHECKBOX & BULK ACTIONS ---

    function updateBulkActionButtons() {
        const count = selectedProductIds.size;
        // Update counts
        if (selectedCountSoftDeleteSpan) selectedCountSoftDeleteSpan.textContent = count;
        if (selectedCountToggleSpan) selectedCountToggleSpan.textContent = count;
        if (selectedCountRestoreSpan) selectedCountRestoreSpan.textContent = count;
        if (selectedCountForceDeleteSpan) selectedCountForceDeleteSpan.textContent = count;

        // Enable/disable buttons based on count
        const disableButtons = count === 0;

        if (bulkSoftDeleteBtn) bulkSoftDeleteBtn.disabled = disableButtons;
        if (bulkToggleStatusBtn) bulkToggleStatusBtn.disabled = disableButtons;
        if (bulkRestoreBtn) bulkRestoreBtn.disabled = disableButtons;
        if (bulkForceDeleteBtn) bulkForceDeleteBtn.disabled = disableButtons;
    }

    function updateCheckboxStates() {
        const currentCheckboxes = document.querySelectorAll('.product-checkbox');
        let allChecked = true;
        if (currentCheckboxes.length === 0) {
            allChecked = false;
        } else {
            currentCheckboxes.forEach(checkbox => {
                if (selectedProductIds.has(checkbox.value)) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                    allChecked = false;
                }
            });
        }
        if (selectAllProductsCheckbox) {
            selectAllProductsCheckbox.checked = allChecked;
        }
        updateBulkActionButtons();
    }

    function clearSelectedProducts() {
        selectedProductIds.clear();
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        if (selectAllProductsCheckbox) selectAllProductsCheckbox.checked = false;
        updateBulkActionButtons();
    }

    // Event listener for "select all" checkbox
    if (selectAllProductsCheckbox) {
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
    }

    // Event listener for individual product checkboxes (using delegation)
    productTableBody.addEventListener('change', function (event) {
        const checkbox = event.target.closest('.product-checkbox');
        if (checkbox) {
            if (checkbox.checked) {
                selectedProductIds.add(checkbox.value);
            } else {
                selectedProductIds.delete(checkbox.value);
            }
            updateBulkActionButtons();
            // Update "select all" checkbox state
            const allIndividualCheckboxes = document.querySelectorAll('.product-checkbox');
            const checkedIndividualCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            if (selectAllProductsCheckbox) {
                selectAllProductsCheckbox.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
            }
        }
    });

    // Bulk Delete (soft)
    if (bulkSoftDeleteBtn) {
        bulkSoftDeleteBtn.addEventListener('click', function () {
            const count = selectedProductIds.size;
            document.getElementById('bulkDeleteCount').textContent = count;
            bulkDeleteProductModal.show();
        });
    }

    // Bulk Toggle Status
    if (bulkToggleStatusBtn) {
        bulkToggleStatusBtn.addEventListener('click', function () {
            const count = selectedProductIds.size;
            document.getElementById('bulkToggleStatusCount').textContent = count;
            bulkToggleStatusProductModal.show();
        });
    }

    // Bulk Restore
    if (bulkRestoreBtn) {
        bulkRestoreBtn.addEventListener('click', function () {
            const count = selectedProductIds.size;
            document.getElementById('bulkRestoreCount').textContent = count;
            // Form action is not hardcoded in blade for bulkRestore, set it here explicitly
            document.getElementById('bulkRestoreProductForm').action = '/admin/product-management/products/bulk-restore';
            bulkRestoreProductModal.show();
        });
    }

    // Bulk Force Delete
    if (bulkForceDeleteBtn) {
        bulkForceDeleteBtn.addEventListener('click', function () {
            const count = selectedProductIds.size;
            document.getElementById('bulkForceDeleteCount').textContent = count;
            bulkForceDeleteProductModal.show();
        });
    }

    function checkAndToggleBulkActionVisibility() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'trashed') {
            if (bulkActionsNormalDiv) bulkActionsNormalDiv.classList.add('d-none');
            if (bulkActionsTrashedDiv) bulkActionsTrashedDiv.classList.remove('d-none');
            if (trashedProductsTab) {
                trashedProductsTab.classList.add('btn-dark');
                trashedProductsTab.classList.remove('btn-outline-dark');
            }
            if (allProductsTab) {
                allProductsTab.classList.remove('btn-dark');
                allProductsTab.classList.add('btn-outline-dark');
            }
        } else {
            if (bulkActionsNormalDiv) bulkActionsNormalDiv.classList.remove('d-none');
            if (bulkActionsTrashedDiv) bulkActionsTrashedDiv.classList.add('d-none');
            if (trashedProductsTab) {
                trashedProductsTab.classList.remove('btn-dark');
                trashedProductsTab.classList.add('btn-outline-dark');
            }
            if (allProductsTab) {
                allProductsTab.classList.add('btn-dark');
                allProductsTab.classList.remove('btn-outline-dark');
            }
        }
        clearSelectedProducts(); // Clear selections when switching tabs
    }


    // --- CHẠY CÁC HÀM KHỞI TẠO ---
    initializeSelectPickers(); // Initialize on page load for all selectpickers
    setupModalResets();
    setupEventListeners();

    // Setup AJAX for all forms
    // IMPORTANT: 'method' parameter in setupAjaxForm indicates the Laravel route's HTTP method.
    // Your web.php currently shows `Route::post('/{product}', 'update')`.
    // For DELETE routes, Laravel checks for the _method field.

    setupAjaxForm('createProductForm', createProductModal, handleUpdateOrToggleSuccess, 'POST'); //
    setupAjaxForm('updateProductForm', updateProductModal, handleUpdateOrToggleSuccess, 'POST'); // Changed to POST to match web.php route
    setupAjaxForm('deleteProductForm', deleteProductModal, handleDeleteRestoreOrForceDeleteSuccess, 'DELETE'); //
    setupAjaxForm('forceDeleteProductForm', forceDeleteProductModal, handleDeleteRestoreOrForceDeleteSuccess, 'DELETE'); //
    setupAjaxForm('restoreProductForm', restoreProductModal, handleDeleteRestoreOrForceDeleteSuccess, 'POST'); //
    setupAjaxForm('bulkDeleteProductForm', bulkDeleteProductModal, handleDeleteRestoreOrForceDeleteSuccess, 'POST'); //
    setupAjaxForm('bulkToggleStatusProductForm', bulkToggleStatusProductModal, handleUpdateOrToggleSuccess, 'POST'); //
    setupAjaxForm('bulkRestoreProductForm', bulkRestoreProductModal, handleDeleteRestoreOrForceDeleteSuccess, 'POST'); //
    setupAjaxForm('bulkForceDeleteProductForm', bulkForceDeleteProductModal, handleDeleteRestoreOrForceDeleteSuccess, 'POST'); //


    // Apply currency formatting to price inputs
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

    // Initial load/search with current filter/sort values (if page reloads with them)
    // and set up bulk action button visibility based on URL status param
    const currentStatusInUrl = new URLSearchParams(window.location.search).get('status');
    if (productFilterSelect) { // Ensure productFilterSelect exists before accessing its value
        if (currentStatusInUrl === 'trashed') {
            productFilterSelect.value = 'trashed';
        } else {
            productFilterSelect.value = 'all'; // Default
        }
    }
    checkAndToggleBulkActionVisibility();
    performSearch(); // Initial table load

    console.log("JS cho trang Sản phẩm đã được khởi tạo thành công với đầy đủ tính năng.");
}

// Call the initialization function when the DOM is ready.
// This ensures that all HTML elements are available before the script tries to access them.
// Ensure this is called only once, perhaps within a larger app initialization script.
// For demonstration, directly call it if not part of a larger framework:
// document.addEventListener('DOMContentLoaded', initializeProductsPage); // If not already called by admin_layout.js
