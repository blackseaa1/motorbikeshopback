/**
 * ===================================================================
 * product_manager.js
 * Xử lý JavaScript cho trang quản lý Sản phẩm.
 * ===================================================================
 */
function initializeProductsPage() {
    console.log("Khởi tạo JS cho trang Sản phẩm...");

    const createProductModalEl = document.getElementById('createProductModal');
    const updateProductModalEl = document.getElementById('updateProductModal');
    const deleteProductModalEl = document.getElementById('deleteProductModal');

    if (!createProductModalEl || !updateProductModalEl || !deleteProductModalEl) {
        console.warn('Một hoặc nhiều modal sản phẩm không tồn tại.');
        return;
    }

    // --- KHỞI TẠO BOOTSTRAP-SELECT CHO CÁC MODAL ---
    $('#productCategoryCreate').selectpicker({
        liveSearch: true,
        width: '100%',
        title: 'Chọn danh mục...'
    });
    $('#productBrandCreate').selectpicker({
        liveSearch: true,
        width: '100%',
        title: 'Chọn thương hiệu...'
    });
    $('#productVehicleModelsCreate').selectpicker({
        liveSearch: true,
        width: '100%',
        title: 'Chọn các dòng xe tương thích',
        actionsBox: true
    });

    $('#productCategoryUpdate').selectpicker({
        liveSearch: true,
        width: '100%',
        title: 'Chọn danh mục...'
    });
    $('#productBrandUpdate').selectpicker({
        liveSearch: true,
        width: '100%',
        title: 'Chọn thương hiệu...'
    });
    $('#productVehicleModelsUpdate').selectpicker({
        liveSearch: true,
        width: '100%',
        title: 'Chọn các dòng xe tương thích',
        actionsBox: true
    });

    /**
     * Hàm xem trước nhiều ảnh cho một input[type=file]
     * @param {string} inputId - ID của input file
     * @param {string} previewContainerId - ID của div chứa ảnh xem trước
     */
    function setupMultipleImagePreview(inputId, previewContainerId) {
        const input = document.getElementById(inputId);
        const previewContainer = document.getElementById(previewContainerId);
        if (!input || !previewContainer) return;

        input.addEventListener('change', function (event) {
            const newPreviews = previewContainer.querySelectorAll('.new-preview');
            newPreviews.forEach(p => p.remove());

            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewWrapper = document.createElement('div');
                        previewWrapper.className = 'img-preview-container new-preview';
                        previewWrapper.innerHTML = `
                            <img src="${e.target.result}" class="img-preview" alt="Preview">
                            <button type="button" class="img-preview-remove" title="Bỏ chọn">&times;</button>
                        `;
                        previewContainer.appendChild(previewWrapper);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }

    setupMultipleImagePreview('productImagesCreate', 'productImagesPreviewCreate');
    setupMultipleImagePreview('productImagesUpdate', 'productImagesPreviewUpdate');

    // --- XỬ LÝ SỰ KIỆN XÓA ẢNH ---
    document.body.addEventListener('click', function (event) {
        if (event.target.classList.contains('img-preview-remove')) {
            event.preventDefault(); // Sửa lỗi typo từ preventPrevent thành preventDefault
            const previewContainer = event.target.closest('.img-preview-container');
            if (previewContainer) {
                previewContainer.remove();
            }
        }
    });

    // --- LOGIC CHO CREATE MODAL ---
    if (typeof window.setupAjaxForm === 'function') {
        window.setupAjaxForm('createProductForm', 'createProductModal', (result) => {
            window.showAppInfoModal(result.message, 'success', 'Thành công');
            setTimeout(() => window.location.reload(), 1200);
        });
    } else {
        console.warn('Hàm window.setupAjaxForm không khả dụng.');
    }

    // Reset form và selectpicker khi modal được đóng
    createProductModalEl.addEventListener('hidden.bs.modal', function () {
        const createForm = document.getElementById('createProductForm');
        if (createForm) {
            createForm.reset();
            createForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.getElementById('productImagesPreviewCreate').innerHTML = '';
        }
        $('#productCategoryCreate').selectpicker('val', '');
        $('#productBrandCreate').selectpicker('val', '');
        $('#productVehicleModelsCreate').selectpicker('val', []);
    });

    // --- LOGIC CHO UPDATE MODAL ---
    document.getElementById('product-table-body').addEventListener('click', async function (event) {
        const editButton = event.target.closest('.btn-edit');
        if (!editButton) return;

        const productId = editButton.dataset.id;
        if (typeof window.showAppLoader === 'function') window.showAppLoader();

        try {
            const response = await fetch(`/admin/productManagement/products/${productId}`);
            if (!response.ok) throw new Error('Không thể lấy dữ liệu sản phẩm.');
            const product = await response.json();

            // Populate form
            const updateForm = document.getElementById('updateProductForm');
            if (updateForm) {
                updateForm.action = `/admin/productManagement/products/${productId}`;
                updateForm.querySelector('#productNameUpdate').value = product.name;
                updateForm.querySelector('#productDescriptionUpdate').value = product.description || '';
                updateForm.querySelector('#productCategoryUpdate').value = product.category_id;
                updateForm.querySelector('#productBrandUpdate').value = product.brand_id;
                updateForm.querySelector('#productPriceUpdate').value = parseFloat(product.price);
                updateForm.querySelector('#productStockUpdate').value = product.stock_quantity;
                updateForm.querySelector('#productMaterialUpdate').value = product.material || '';
                updateForm.querySelector('#productColorUpdate').value = product.color || '';
                updateForm.querySelector('#productSpecificationsUpdate').value = product.specifications || '';
                updateForm.querySelector('#productIsActiveUpdate').checked = product.status === 'active';

                // Populate Bootstrap-select
                $('#productCategoryUpdate').selectpicker('val', product.category_id);
                $('#productBrandUpdate').selectpicker('val', product.brand_id);
                const vehicleModelIds = product.vehicle_models.map(model => model.id);
                $('#productVehicleModelsUpdate').selectpicker('val', vehicleModelIds);

                // Populate existing images
                const previewContainer = document.getElementById('productImagesPreviewUpdate');
                previewContainer.innerHTML = '';
                const storageUrlPrefix = '/storage/';
                product.images.forEach(image => {
                    const previewWrapper = document.createElement('div');
                    previewWrapper.className = 'img-preview-container existing-preview';
                    previewWrapper.innerHTML = `
                        <input type="hidden" name="existing_images[]" value="${image.id}">
                        <img src="${storageUrlPrefix}${image.image_path}" class="img-preview" alt="${image.alt_text || ''}">
                        <button type="button" class="img-preview-remove" title="Xóa ảnh này">&times;</button>
                    `;
                    previewContainer.appendChild(previewWrapper);
                });
            }

            const modal = new bootstrap.Modal(updateProductModalEl);
            modal.show();

        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            if (typeof window.showAppInfoModal === 'function') {
                window.showAppInfoModal(error.message, 'error', 'Lỗi');
            }
        } finally {
            if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
        }
    });

    // Setup form submit cho Update
    if (typeof window.setupAjaxForm === 'function') {
        window.setupAjaxForm('updateProductForm', 'updateProductModal', (result) => {
            window.showAppInfoModal(result.message, 'success', 'Thành công');
            setTimeout(() => window.location.reload(), 1200);
        });
    }

    // Reset form và selectpicker khi modal được đóng
    updateProductModalEl.addEventListener('hidden.bs.modal', function () {
        const updateForm = document.getElementById('updateProductForm');
        if (updateForm) {
            updateForm.reset();
            updateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.getElementById('productImagesPreviewUpdate').innerHTML = '';
        }
        $('#productCategoryUpdate').selectpicker('val', '');
        $('#productBrandUpdate').selectpicker('val', '');
        $('#productVehicleModelsUpdate').selectpicker('val', []);
    });

    // --- LOGIC CHO DELETE MODAL ---
    document.getElementById('product-table-body').addEventListener('click', function (event) {
        const deleteButton = event.target.closest('.btn-delete');
        if (!deleteButton) return;

        const productId = deleteButton.dataset.id;
        const productName = deleteButton.dataset.name;

        const deleteForm = document.getElementById('deleteProductForm');
        if (deleteForm) {
            deleteForm.action = `/admin/productManagement/products/${productId}`;
        }
        document.getElementById('productNameToDelete').textContent = productName;

        const modal = new bootstrap.Modal(deleteProductModalEl);
        modal.show();
    });

    // Setup form submit cho Delete
    if (typeof window.setupAjaxForm === 'function') {
        window.setupAjaxForm('deleteProductForm', 'deleteProductModal', (result) => {
            window.showAppInfoModal(result.message, 'success', 'Thành công');
            setTimeout(() => window.location.reload(), 1200);
        });
    }
}