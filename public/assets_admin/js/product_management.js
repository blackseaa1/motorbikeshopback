/**
 * ===================================================================
 * product_management.js
 * Xử lý JavaScript cho trang quản lý Sản phẩm (Thêm, Sửa, Xóa, Xem).
 * Phiên bản: Hoàn chỉnh, đã sửa lỗi route 405 và đảm bảo phương thức POST cho update.
 * Đã tích hợp thông báo Toast và định dạng tiền tệ (tương tự promotion_manager.js).
 * ===================================================================
 */

function initializeProductsPage() {
    console.log("Khởi tạo JS cho trang Sản phẩm...");

    // Lấy các element modal chính
    const createProductModalEl = document.getElementById('createProductModal');
    const updateProductModalEl = document.getElementById('updateProductModal');
    const viewProductModalEl = document.getElementById('viewProductModal');
    const deleteModalEl = document.getElementById('confirmDeleteModal');
    const forceDeleteModalEl = document.getElementById('confirmForceDeleteModal');
    const restoreModalEl = document.getElementById('confirmRestoreModal');
    const productTableBody = document.getElementById('product-table-body');

    // Kiểm tra sự tồn tại của các element
    if (!createProductModalEl || !updateProductModalEl || !viewProductModalEl || !deleteModalEl || !forceDeleteModalEl || !restoreModalEl || !productTableBody) {
        console.warn('Một hoặc nhiều element quan trọng không tồn tại. Script sẽ không chạy.');
        return;
    }

    // Lấy CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    // Lấy các hàm helper toàn cục từ admin_layout.js (nếu có, giả định tồn tại)
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
                    } else if (fieldName === 'vehicle_models') {
                        inputField = formElement.querySelector('#productVehicleModelsCreate') || formElement.querySelector('#productVehicleModelsUpdate');
                    }
                    // Thêm các trường khác nếu cần
                }


                if (inputField) {
                    inputField.classList.add('is-invalid');
                    // Tìm phần tử invalid-feedback phù hợp. Có thể cần điều chỉnh selector nếu cấu trúc HTML phức tạp hơn.
                    let errorDiv = inputField.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        // Nếu không tìm thấy ngay sau đó, thử tìm trong parent div (ví dụ: cho selectpicker)
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
    };

    // --- CÁC HÀM HIỂN THỊ MODAL ---

    /**
     * Hiển thị modal xem chi tiết sản phẩm
     * @param {number} productId - ID sản phẩm
     */
    const handleShowViewModal = async (productId) => {
        showAppLoader();
        try {
            const response = await fetch(`/admin/product-management/products/${productId}`);
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

            const modal = new bootstrap.Modal(viewProductModalEl);
            modal.show();
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
            const response = await fetch(`/admin/product-management/products/${productId}`);
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const product = await response.json();

            const form = document.getElementById('updateProductForm');
            form.action = `/admin/product-management/products/${productId}`;
            form.setAttribute('method', 'POST'); // Đảm bảo phương thức là POST cho form submit AJAX với _method PUT

            form.querySelector('#productNameUpdate').value = product.name || '';
            form.querySelector('#productDescriptionUpdate').value = product.description || '';
            // Gán giá trị price gốc cho input, format sẽ được áp dụng bởi formatCurrencyInput
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

            // Gọi lại formatCurrencyInput sau khi gán giá trị
            const priceUpdateInput = form.querySelector('#productPriceUpdate');
            if (priceUpdateInput) {
                // Kích hoạt lại sự kiện input để giá trị được định dạng
                priceUpdateInput.value = new Intl.NumberFormat('vi-VN').format(parseFloat(priceUpdateInput.value));
            }


            const modal = new bootstrap.Modal(updateProductModalEl);
            modal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            showAppInfoModal(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error', 'Lỗi Hệ thống');
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
        const modal = bootstrap.Modal.getInstance(deleteModalEl) || new bootstrap.Modal(deleteModalEl);
        modal.show();
    };

    /**
     * Hiển thị modal xác nhận xóa vĩnh viễn
     * @param {string} deleteUrl - URL để xóa vĩnh viễn
     * @param {string} productName - Tên sản phẩm
     */
    const handleShowForceDeleteModal = (deleteUrl, productName) => {
        const form = document.getElementById('forceDeleteProductForm');
        form.action = deleteUrl;
        form.setAttribute('method', 'POST'); // Đảm bảo phương thức là POST
        const nameElement = document.getElementById('productNameToForceDelete');
        if (nameElement) {
            nameElement.textContent = productName || 'Sản phẩm này';
        }
        const modal = bootstrap.Modal.getInstance(forceDeleteModalEl) || new bootstrap.Modal(forceDeleteModalEl);
        modal.show();
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
        const modal = bootstrap.Modal.getInstance(restoreModalEl) || new bootstrap.Modal(restoreModalEl);
        modal.show();
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
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Lỗi không xác định");

            showToast(result.message, 'success');
            const product = result.product;
            const row = document.getElementById(`product-row-${product.id}`);

            if (row) {
                row.classList.toggle('row-inactive', product.status !== 'active');
                row.querySelector('.status-cell').innerHTML = `<span class="badge ${product.status_badge_class}">${product.status_text}</span>`;

                const isActive = product.status === 'active';
                button.classList.toggle('btn-secondary', isActive);
                button.classList.toggle('btn-success', !isActive);
                button.title = isActive ? 'Dừng bán' : 'Mở bán';
                button.querySelector('i').className = `bi ${isActive ? 'bi-pause-circle-fill' : 'bi-play-circle-fill'}`;
            } else {
                // Fallback nếu không tìm thấy row (ví dụ: reload trang nếu không thể cập nhật DOM)
                setTimeout(() => window.location.reload(), 1000);
            }

        } catch (error) {
            console.error('Lỗi khi bật/tắt trạng thái:', error);
            showToast(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    }

    // --- GẮN KẾT SỰ KIỆN & THIẾT LẬP FORM ---

    /**
     * Thiết lập các event listener
     */
    const setupEventListeners = () => {
        productTableBody.addEventListener('click', async function (event) {
            const button = event.target.closest('.btn-action');
            if (!button) return;

            const id = button.dataset.id;
            const name = button.dataset.name;
            const url = button.dataset.url; // Giữ lại nếu có
            const deleteUrl = button.dataset.deleteUrl;

            if (button.classList.contains('btn-view')) await handleShowViewModal(id);
            else if (button.classList.contains('btn-edit')) await handleShowUpdateModal(id);
            else if (button.classList.contains('btn-delete')) handleShowDeleteModal(id, name);
            else if (button.classList.contains('toggle-status-product-btn')) await handleToggleStatus(button);
            else if (button.classList.contains('btn-restore-product')) handleShowRestoreModal(id, name);
            else if (button.classList.contains('btn-force-delete-product')) handleShowForceDeleteModal(deleteUrl, name);
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
                const viewModal = bootstrap.Modal.getInstance(viewProductModalEl);
                if (viewModal) viewModal.hide();
                setTimeout(() => handleShowUpdateModal(productId), 200); // Đợi modal cũ đóng hẳn
            }
        });

        $(createProductModalEl).on('shown.bs.modal', () => {
            $('#createProductForm .selectpicker').selectpicker('refresh');
        });

        $(updateProductModalEl).on('shown.bs.modal', () => {
            const categoryId = updateProductModalEl.dataset.categoryId;
            const brandId = updateProductModalEl.dataset.brandId;
            const vehicleModelIds = JSON.parse(updateProductModalEl.dataset.vehicleModelIds || '[]');
            $('#productCategoryUpdate').selectpicker('val', categoryId);
            $('#productBrandUpdate').selectpicker('val', brandId);
            $('#productVehicleModelsUpdate').selectpicker('val', vehicleModelIds);
            $('#updateProductForm .selectpicker').selectpicker('refresh');
        });
    };

    /**
     * Thiết lập xử lý AJAX cho một form cụ thể.
     * @param {string} formId - ID của form.
     * @param {string} modalId - ID của modal chứa form.
     * @param {function} successCallback - Hàm callback khi form gửi thành công.
     * @param {string} method - Phương thức HTTP ('POST', 'PUT', 'DELETE').
     */
    function setupAjaxForm(formId, modalId, successCallback, method = 'POST') {
        const form = document.getElementById(formId);
        const modalEl = document.getElementById(modalId);
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

        if (!form || !modalEl) {
            console.error(`Không thể thiết lập AJAX form: Form ID "${formId}" hoặc Modal ID "${modalId}" không tồn tại.`);
            return;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();
            clearValidationErrors(form);

            const formData = new FormData(form);

            // Thêm _method cho PUT/DELETE (Laravel sẽ xử lý)
            if (method === 'PUT' || method === 'DELETE') {
                formData.append('_method', method);
            }

            // Xử lý đặc biệt cho trường price: parse từ định dạng VNĐ về số gốc
            const priceInput = form.querySelector('[name="price"]');
            if (priceInput && formData.has('price')) {
                formData.set('price', parseFormattedCurrency(priceInput.value));
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST', // Luôn là POST với FormData, Laravel sẽ đọc _method
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) { // Status code 2xx
                    showToast(result.message, 'success');
                    modalInstance.hide();
                    successCallback(result.product);
                } else if (response.status === 422) { // Validation errors
                    displayValidationErrors(form, result.errors);
                    showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
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

    // --- CHẠY CÁC HÀM KHỞI TẠO ---
    initializeSelectPickers();
    setupModalResets();
    setupEventListeners();

    // Hàm callback khi tạo/cập nhật thành công
    const reloadPageAfterSuccess = () => setTimeout(() => {
        if (typeof Turbo !== 'undefined') {
            Turbo.visit(window.location.href, { action: 'replace' });
        } else {
            window.location.reload();
        }
    }, 1200);

    // Thiết lập AJAX cho các form
    setupAjaxForm('createProductForm', 'createProductModal', reloadPageAfterSuccess, 'POST');
    setupAjaxForm('updateProductForm', 'updateProductModal', reloadPageAfterSuccess, 'POST'); // Sử dụng PUT cho update
    setupAjaxForm('deleteProductForm', 'confirmDeleteModal', reloadPageAfterSuccess, 'DELETE');
    setupAjaxForm('forceDeleteProductForm', 'confirmForceDeleteModal', reloadPageAfterSuccess, 'DELETE'); // Dùng DELETE
    setupAjaxForm('restoreProductForm', 'confirmRestoreModal', reloadPageAfterSuccess, 'POST');

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


    console.log("JS cho trang Sản phẩm đã được khởi tạo thành công với đầy đủ tính năng.");
}

// Hàm initializeProductsPage() sẽ được gọi bởi admin_layout.js,
// không cần gọi lại ở đây để tránh lặp.