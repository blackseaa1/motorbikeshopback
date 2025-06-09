/**
 * ===================================================================
 * product_management.js
 * Xử lý JavaScript cho trang quản lý Sản phẩm (Thêm, Sửa, Xóa).
 * Bao gồm khởi tạo selectpicker, xử lý preview ảnh, và các sự kiện modal.
 * ===================================================================
 */

// Hàm chính sẽ được chạy khi toàn bộ cây DOM đã được tải.
document.addEventListener('DOMContentLoaded', initializeProductsPage);

/**
 * Hàm khởi tạo chính cho toàn bộ trang quản lý sản phẩm.
 */
function initializeProductsPage() {
    console.log("Khởi tạo JS cho trang Sản phẩm...");

    // --- 1. Lấy các element DOM quan trọng ---
    const createProductModalEl = document.getElementById('createProductModal');
    const updateProductModalEl = document.getElementById('updateProductModal');
    const deleteProductModalEl = document.getElementById('deleteProductModal');
    const productTableBody = document.getElementById('product-table-body');

    // Nếu không tìm thấy các modal cần thiết, dừng thực thi để tránh lỗi.
    if (!createProductModalEl || !updateProductModalEl || !deleteProductModalEl || !productTableBody) {
        console.warn('Một hoặc nhiều element quan trọng (modal, table body) không tồn tại. Script sẽ không chạy.');
        return;
    }

    // --- 2. Khởi tạo các thư viện và chức năng phụ ---

    /**
     * Khởi tạo thư viện bootstrap-select cho các dropdown.
     * Thư viện này giúp tạo các dropdown có chức năng tìm kiếm.
     */
    const initializeSelectPickers = () => {
        try {
            $('.selectpicker').selectpicker({
                liveSearch: true,
                width: '100%',
                noneSelectedText: 'Chưa chọn mục nào',
                actionsBox: true,
                selectAllText: 'Chọn tất cả',
                deselectAllText: 'Bỏ chọn tất cả'
            });
        } catch (error) {
            console.error('Lỗi nghiêm trọng khi khởi tạo selectpicker:', error);
            // Giả sử bạn có một hàm hiển thị thông báo lỗi chung
            if (typeof window.showAppInfoModal === 'function') {
                window.showAppInfoModal('Không thể khởi tạo dropdown. Vui lòng kiểm tra thư viện bootstrap-select.', 'error');
            }
        }
    };


    /**
     * Xử lý hiển thị ảnh preview khi người dùng chọn file.
     * @param {HTMLInputElement} inputEl - Element input type="file".
     * @param {HTMLElement} previewContainerEl - Element div để chứa ảnh preview.
     */
    const setupImagePreviews = (inputEl, previewContainerEl) => {
        inputEl.addEventListener('change', function(event) {
            // Xóa các ảnh preview mới (được tạo từ lần chọn trước)
            previewContainerEl.querySelectorAll('.new-preview').forEach(el => el.remove());

            const files = event.target.files;
            if (!files) return;

            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewWrapper = document.createElement('div');
                    // 'new-preview' dùng để phân biệt với ảnh đã có sẵn từ server
                    previewWrapper.className = 'img-preview-wrapper new-preview';
                    previewWrapper.innerHTML = `
                        <img src="${e.target.result}" class="img-preview" alt="${file.name}">
                        <button type="button" class="img-preview-remove" title="Xóa ảnh này">×</button>
                    `;
                    previewContainerEl.appendChild(previewWrapper);
                };
                reader.readAsDataURL(file);
            }
        });
    };

    // --- 3. Logic xử lý cho từng Modal ---

    // ** CREATE MODAL **
    const setupCreateModal = () => {
        // Gắn sự kiện để reset form khi modal được đóng
        createProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('createProductForm');
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.getElementById('productImagesPreviewCreate').innerHTML = '';
            $('#productCategoryCreate, #productBrandCreate, #productVehicleModelsCreate').selectpicker('val', '');
        });

        // Kích hoạt preview ảnh cho form create
        const createImagesInput = document.getElementById('productImagesCreate');
        const createImagesPreview = document.getElementById('productImagesPreviewCreate');
        if (createImagesInput && createImagesPreview) {
            setupImagePreviews(createImagesInput, createImagesPreview);
        }
    };

    // ** UPDATE MODAL **
    const setupUpdateModal = () => {
        // Gắn sự kiện để reset form khi modal được đóng
        updateProductModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('updateProductForm');
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.getElementById('productImagesPreviewUpdate').innerHTML = '';
            document.getElementById('productImagesUpdate').value = ''; // Reset file input
            $('#productCategoryUpdate, #productBrandUpdate, #productVehicleModelsUpdate').selectpicker('val', '');
        });

        // Kích hoạt preview cho các ảnh mới tải lên ở form update
        const updateImagesInput = document.getElementById('productImagesUpdate');
        const updateImagesPreview = document.getElementById('productImagesPreviewUpdate');
        if (updateImagesInput && updateImagesPreview) {
            setupImagePreviews(updateImagesInput, updateImagesPreview);
        }
    };

    // --- 4. Gắn các Event Listener chính ---

    const setupEventListeners = () => {
        // Sử dụng event delegation cho toàn bộ table body
        productTableBody.addEventListener('click', async function(event) {
            const editButton = event.target.closest('.btn-edit');
            const deleteButton = event.target.closest('.btn-delete');

            if (editButton) {
                await handleShowUpdateModal(editButton.dataset.id);
            }

            if (deleteButton) {
                handleShowDeleteModal(deleteButton.dataset.id, deleteButton.dataset.name);
            }
        });

        // Sự kiện xóa ảnh preview (cho cả ảnh cũ và ảnh mới)
        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('img-preview-remove')) {
                event.preventDefault();
                event.target.closest('.img-preview-wrapper').remove();
            }
        });

         // Sự kiện làm mới selectpicker khi modal được hiển thị
         $(createProductModalEl).on('shown.bs.modal', () => $('.selectpicker').selectpicker('refresh'));
         $(updateProductModalEl).on('shown.bs.modal', () => $('.selectpicker').selectpicker('refresh'));
    };


    /**
     * Lấy dữ liệu sản phẩm và hiển thị modal Cập nhật.
     * @param {string} productId ID của sản phẩm.
     */
    const handleShowUpdateModal = async (productId) => {
        if (typeof window.showAppLoader === 'function') window.showAppLoader();

        try {
            const response = await fetch(`/admin/productManagement/products/${productId}`);
            if (!response.ok) {
                throw new Error(`Lỗi mạng: ${response.statusText}`);
            }
            const product = await response.json();

            const form = document.getElementById('updateProductForm');
            form.action = `/admin/productManagement/products/${productId}`;

            // Điền dữ liệu vào form
            form.querySelector('#productNameUpdate').value = product.name || '';
            form.querySelector('#productDescriptionUpdate').value = product.description || '';
            form.querySelector('#productPriceUpdate').value = parseFloat(product.price) || 0;
            form.querySelector('#productStockUpdate').value = product.stock_quantity || 0;
            form.querySelector('#productMaterialUpdate').value = product.material || '';
            form.querySelector('#productColorUpdate').value = product.color || '';
            form.querySelector('#productSpecificationsUpdate').value = product.specifications || '';
            form.querySelector('#productIsActiveUpdate').checked = product.status === 'active';

            // Cập nhật selectpicker
            $('#productCategoryUpdate').selectpicker('val', product.category_id);
            $('#productBrandUpdate').selectpicker('val', product.brand_id);
            const vehicleModelIds = product.vehicle_models ? product.vehicle_models.map(model => model.id) : [];
            $('#productVehicleModelsUpdate').selectpicker('val', vehicleModelIds);

            // Hiển thị ảnh hiện có
            const previewContainer = document.getElementById('productImagesPreviewUpdate');
            previewContainer.innerHTML = ''; // Xóa preview cũ
            if (product.images && product.images.length > 0) {
                product.images.forEach(image => {
                    const previewWrapper = document.createElement('div');
                    previewWrapper.className = 'img-preview-wrapper existing-preview';
                    previewWrapper.innerHTML = `
                        <input type="hidden" name="existing_images[]" value="${image.id}">
                        <img src="${image.image_full_url}" class="img-preview" alt="${image.alt_text || ''}">
                        <button type="button" class="img-preview-remove" title="Xóa ảnh này">×</button>
                    `;
                    previewContainer.appendChild(previewWrapper);
                });
            }

            const modal = new bootstrap.Modal(updateProductModalEl);
            modal.show();

        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu để cập nhật:', error);
            if (typeof window.showAppInfoModal === 'function') {
                window.showAppInfoModal(error.message || 'Lỗi không xác định khi lấy dữ liệu.', 'error');
            }
        } finally {
            if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
        }
    };


    /**
     * Hiển thị modal xác nhận Xóa.
     * @param {string} productId ID sản phẩm.
     * @param {string} productName Tên sản phẩm.
     */
    const handleShowDeleteModal = (productId, productName) => {
        const form = document.getElementById('deleteProductForm');
        form.action = `/admin/productManagement/products/${productId}`;
        document.getElementById('productNameToDelete').textContent = productName || 'Sản phẩm không xác định';

        const modal = new bootstrap.Modal(deleteProductModalEl);
        modal.show();
    };

    // --- 5. Khởi tạo các form AJAX ---

    const setupAjaxForms = () => {
        // Giả sử bạn có một hàm toàn cục `setupAjaxForm` để xử lý việc gửi form qua AJAX
        if (typeof window.setupAjaxForm !== 'function') {
            console.warn('Hàm global `setupAjaxForm` không được định nghĩa.');
            return;
        }

        const reloadPage = () => setTimeout(() => window.location.reload(), 1200);
        const showError = (error) => window.showAppInfoModal(error.message || 'Có lỗi xảy ra', 'error');
        const showSuccess = (result) => window.showAppInfoModal(result.message, 'success');

        window.setupAjaxForm('createProductForm', 'createProductModal', (result) => {
            showSuccess(result);
            reloadPage();
        }, showError);

        window.setupAjaxForm('updateProductForm', 'updateProductModal', (result) => {
            showSuccess(result);
            reloadPage();
        }, showError);

        window.setupAjaxForm('deleteProductForm', 'deleteProductModal', (result) => {
            showSuccess(result);
            reloadPage();
        }, showError);
    };


    // --- 6. Chạy các hàm khởi tạo ---
    initializeSelectPickers();
    setupCreateModal();
    setupUpdateModal();
    setupEventListeners();
    setupAjaxForms();

    console.log("JS cho trang Sản phẩm đã được khởi tạo thành công.");
}