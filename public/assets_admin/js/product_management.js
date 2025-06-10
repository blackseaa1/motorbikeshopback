/**
 * ===================================================================
 * product_management.js
 * Xử lý JavaScript cho trang quản lý Sản phẩm (Thêm, Sửa, Xóa, Xem).
 * Phiên bản: Hoàn chỉnh, đã sửa lỗi route 405 và đảm bảo phương thức POST cho update.
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

    // --- CÁC HÀM KHỞI TẠO & HỖ TRỢ ---

    /**
     * Khởi tạo SelectPicker cho các select element
     */
    const initializeSelectPickers = () => {
        try {
            const $pickers = $('.selectpicker');
            if ($pickers.length === 0) return;
            // Hủy các instance cũ trước khi khởi tạo lại để tránh lỗi
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
            // Xóa các ảnh preview mới được thêm vào trước đó
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
     * Reset form và các trạng thái khi modal đóng
     */
    const setupModalResets = () => {
        // Reset modal tạo mới
        createProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('createProductForm');
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.getElementById('productImagesPreviewCreate').innerHTML = '';
            $('#createProductForm .selectpicker').selectpicker('val', '');
        });
        const createImagesInput = document.getElementById('productImagesCreate');
        const createImagesPreview = document.getElementById('productImagesPreviewCreate');
        if (createImagesInput && createImagesPreview) setupImagePreviews(createImagesInput, createImagesPreview);

        // Reset modal cập nhật
        updateProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('updateProductForm');
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.getElementById('productImagesPreviewUpdate').innerHTML = '';
            document.getElementById('productImagesUpdate').value = '';
            $('#updateProductForm .selectpicker').selectpicker('val', '');
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
        window.showAppLoader();
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
            window.showAppInfoModal(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error', 'Lỗi Hệ thống');
        } finally {
            window.hideAppLoader();
        }
    };

    /**
     * Hiển thị modal cập nhật sản phẩm
     * @param {number} productId - ID sản phẩm
     */
    const handleShowUpdateModal = async (productId) => {
        window.showAppLoader();
        try {
            const response = await fetch(`/admin/product-management/products/${productId}`);
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const product = await response.json();

            const form = document.getElementById('updateProductForm');
            form.action = `/admin/product-management/products/${productId}`;
            // Explicitly set form method to POST to match route
            form.setAttribute('method', 'POST');

            form.querySelector('#productNameUpdate').value = product.name || '';
            form.querySelector('#productDescriptionUpdate').value = product.description || '';
            form.querySelector('#productPriceUpdate').value = parseFloat(product.price) || 0;
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

            const modal = new bootstrap.Modal(updateProductModalEl);
            modal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            window.showAppInfoModal(error.message || 'Không thể lấy dữ liệu sản phẩm.', 'error', 'Lỗi Hệ thống');
        } finally {
            window.hideAppLoader();
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
        document.getElementById('customerNameToForceDelete').textContent = productName || 'Sản phẩm này';
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
        window.showAppLoader();
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            window.showAppInfoModal(result.message, 'success');
            const product = result.product;
            const row = document.getElementById(`product-row-${product.id}`);

            row.classList.toggle('row-inactive', product.status !== 'active');
            row.querySelector('.status-cell').innerHTML = `<span class="badge ${product.status_badge_class}">${product.status_text}</span>`;

            const isActive = product.status === 'active';
            button.classList.toggle('btn-secondary', isActive);
            button.classList.toggle('btn-success', !isActive);
            button.title = isActive ? 'Dừng bán' : 'Mở bán';
            button.querySelector('i').className = `bi ${isActive ? 'bi-pause-circle-fill' : 'bi-play-circle-fill'}`;
        } catch (error) {
            window.showAppInfoModal(error.message, 'error', 'Lỗi Hệ thống');
        } finally {
            window.hideAppLoader();
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
            const url = button.dataset.url;
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
                handleShowUpdateModal(productId);
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
     * Thiết lập AJAX cho các form
     */
    const setupAjaxForms = () => {
        if (typeof window.setupAjaxForm !== 'function') {
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

        window.setupAjaxForm('createProductForm', 'createProductModal', reloadPage);
        window.setupAjaxForm('updateProductForm', 'updateProductModal', reloadPage);
        window.setupAjaxForm('deleteProductForm', 'confirmDeleteModal', reloadPage);
        window.setupAjaxForm('forceDeleteProductForm', 'confirmForceDeleteModal', reloadPage);
        window.setupAjaxForm('restoreProductForm', 'confirmRestoreModal', reloadPage);
    };

    // --- CHẠY CÁC HÀM KHỞI TẠO ---
    initializeSelectPickers();
    setupModalResets();
    setupEventListeners();
    setupAjaxForms();

    console.log("JS cho trang Sản phẩm đã được khởi tạo thành công với đầy đủ tính năng.");
}

// Hàm initializeProductsPage() sẽ được gọi bởi admin_layout.js,
// không cần gọi lại ở đây để tránh lặp.