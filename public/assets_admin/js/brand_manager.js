/**
 * ===================================================================
 * brand_manager.js (Phiên bản Đã Sửa Lỗi và Tối Ưu Mới Nhất)
 *
 * Xử lý JavaScript đầy đủ cho trang quản lý Thương hiệu,
 * bao gồm xem, tạo, sửa, xóa, bật/tắt trạng thái, tìm kiếm, lọc, sắp xếp
 * và hành động hàng loạt bằng AJAX.
 * ===================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Đảm bảo script chỉ chạy một lần
    if (document.body.dataset.brandManagerInitialized) {
        console.log("Brand Manager đã được khởi tạo trước đó. Bỏ qua khởi tạo lại.");
        return;
    }
    document.body.dataset.brandManagerInitialized = 'true';
    console.log("Khởi tạo JS cho trang Quản lý Thương hiệu...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token! Script sẽ dừng.');
        return;
    }

    // Lấy các hàm helper toàn cục (showAppLoader, hideAppLoader, showToast)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('DEBUG: Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('DEBUG: Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Lỗi: Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
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

    // --- Khai báo và lấy các phần tử DOM chính, sử dụng optional chaining để an toàn ---
    const tableBody = document.getElementById('brands-table-body');
    const createBrandModalElement = document.getElementById('createBrandModal');
    const updateBrandModalElement = document.getElementById('updateBrandModal');
    const viewBrandModalElement = document.getElementById('viewBrandModal');
    const deleteBrandModalElement = document.getElementById('deleteBrandModal');
    const bulkToggleStatusModalElement = document.getElementById('bulkToggleStatusModal'); // NEW

    const brandSearchInput = document.getElementById('brandSearchInput'); // NEW
    const brandSearchBtn = document.getElementById('brandSearchBtn');     // NEW
    const brandFilterSelect = document.getElementById('brandFilterSelect'); // NEW
    const brandSortSelect = document.getElementById('brandSortSelect');     // NEW
    const paginationLinksContainer = document.getElementById('pagination-links'); // Có thể là null

    // Bulk action elements
    const selectAllCheckboxes = document.getElementById('selectAllBrands'); // NEW ID
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkToggleStatusBtn = document.getElementById('bulkToggleStatusBtn');
    const selectedCountDeleteSpan = document.getElementById('selectedCountDelete');
    const selectedCountToggleSpan = document.getElementById('selectedCountToggle');
    const selectedCountToggleModalSpan = document.getElementById('selectedCountToggleModal'); // Span inside bulk toggle modal

    // Kiểm tra các element quan trọng nhất, nếu thiếu thì không chạy script
    // Bỏ `paginationLinksContainer` ra khỏi điều kiện cứng vì nó có thể không tồn tại
    if (!tableBody || !createBrandModalElement || !updateBrandModalElement || !viewBrandModalElement || !deleteBrandModalElement || !bulkToggleStatusModalElement ||
        !brandSearchInput || !brandSearchBtn || !brandFilterSelect || !brandSortSelect ||
        !selectAllCheckboxes || !bulkDeleteBtn || !bulkToggleStatusBtn ||
        !selectedCountDeleteSpan || !selectedCountToggleSpan || !selectedCountToggleModalSpan
    ) {
        console.error('Lỗi: Một hoặc nhiều element DOM quan trọng không được tìm thấy. Vui lòng kiểm tra lại ID trong Blade và HTML.');
        return;
    }

    // Khởi tạo các đối tượng Modal của Bootstrap
    const createBrandModal = new bootstrap.Modal(createBrandModalElement);
    const updateBrandModal = new bootstrap.Modal(updateBrandModalElement);
    const viewBrandModal = new bootstrap.Modal(viewBrandModalElement);
    const deleteBrandModal = new bootstrap.Modal(deleteBrandModalElement);
    const bulkToggleStatusModal = new bootstrap.Modal(bulkToggleStatusModalElement);

    let selectedBrandIds = new Set(); // Để lưu trữ các ID thương hiệu đã chọn

    // -----------------------------------------------------------------------------
    // SECTION 2: HÀM TIỆN ÍCH (HELPER FUNCTIONS)
    // -----------------------------------------------------------------------------

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
        clearValidationErrors(formElement); // Xóa lỗi cũ trước
        let firstErrorField = null;

        for (const fieldName in errors) {
            if (Object.hasOwnProperty.call(errors, fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);
                let errorDiv = null;

                // Xử lý đặc biệt cho lỗi mật khẩu xóa/bulk action
                if (fieldName === 'deletion_password') {
                    inputField = formElement.querySelector('#brandDeletionPassword') || formElement.querySelector('#bulkToggleStatusPassword');
                    errorDiv = formElement.querySelector('#brandDeletionPasswordError') || formElement.querySelector('#bulkToggleStatusPasswordError');
                }
                // Xử lý riêng cho logo_url nếu nó không khớp với name trực tiếp trên input file
                else if (fieldName === 'logo_url') {
                     inputField = formElement.querySelector('#brandLogoCreate') || formElement.querySelector('#brandLogoUpdate');
                     // Find the corresponding invalid-feedback for logo_url
                     if (inputField) {
                         errorDiv = inputField.closest('.mb-3')?.querySelector('.invalid-feedback') || inputField.nextElementSibling;
                     }
                }


                if (inputField) {
                    inputField.classList.add('is-invalid');
                    // Lấy div lỗi tiếp theo nếu chưa tìm thấy, hoặc nếu đã tìm thấy qua ID riêng
                    if (!errorDiv) { // Ensure errorDiv is not already set by specific handling
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
                    showToast(`Lỗi dữ liệu: ${fieldName} - ${errors[fieldName][0]}`, 'error');
                }
            }
        }
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }

    /**
     * Hàm thiết lập preview logo cho input file.
     * @param {string} inputId - ID của input file (e.g., 'brandLogoCreate').
     * @param {string} previewId - ID của thẻ img để hiển thị preview (e.g., 'brandLogoPreviewCreate').
     */
    function setupLogoPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (input && preview) {
            // Lưu defaultSrc ban đầu nếu có, để reset khi không chọn file
            if (!preview.dataset.defaultSrc) {
                preview.dataset.defaultSrc = preview.src || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview';
            }

            input.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    preview.src = URL.createObjectURL(file);
                    preview.onload = () => URL.revokeObjectURL(preview.src); // Giải phóng bộ nhớ sau khi tải xong
                } else {
                    // Reset về ảnh mặc định khi không chọn file
                    preview.src = preview.dataset.defaultSrc;
                }
            });
        }
    }

    // -----------------------------------------------------------------------------
    // SECTION 3: CÁC HÀM XỬ LÝ MODAL (HIỂN THỊ DỮ LIỆU)
    // -----------------------------------------------------------------------------

    /**
     * Điền dữ liệu vào modal xem chi tiết hoặc cập nhật thương hiệu bằng AJAX.
     * @param {HTMLElement} triggerButton - Nút đã kích hoạt modal (có data-url).
     * @param {Object} modalInstance - Instance của Bootstrap Modal.
     * @param {string} type - Loại modal ('view' hoặc 'update').
     */
    async function handleShowModal(triggerButton, modalInstance, type) {
        showAppLoader();
        try {
            const url = triggerButton.dataset.url; // URL để fetch dữ liệu chi tiết
            if (!url) throw new Error('Không tìm thấy data-url trên nút kích hoạt modal.');

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
            }
            const data = await response.json(); // Dữ liệu thương hiệu

            if (type === 'view') {
                viewBrandModalElement.querySelector('#brandIdView').textContent = data.id || '-';
                viewBrandModalElement.querySelector('#brandNameView').textContent = data.name || '-';
                viewBrandModalElement.querySelector('#brandDescriptionView').textContent = data.description || 'Không có mô tả';
                viewBrandModalElement.querySelector('#brandLogoView').src = data.logo_full_url || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                viewBrandModalElement.querySelector('#brandCreatedAtView').textContent = new Date(data.created_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';
                viewBrandModalElement.querySelector('#brandUpdatedAtView').textContent = new Date(data.updated_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';

                const statusViewEl = viewBrandModalElement.querySelector('#brandStatusView');
                if (statusViewEl) {
                    statusViewEl.innerHTML = `<span class="badge ${data.status_badge_class}">${data.status_text}</span>`;
                }

                const editButtonFromView = viewBrandModalElement.querySelector('#editBrandFromViewButton');
                if (editButtonFromView) {
                    editButtonFromView.dataset.id = data.id;
                    editButtonFromView.dataset.url = triggerButton.dataset.url; // Giữ lại URL show để fetch lại nếu cần
                    editButtonFromView.dataset.updateUrl = triggerButton.dataset.updateUrl || `/admin/product-management/brands/${data.id}`; // URL update cho form
                }
            } else if (type === 'update') {
                const form = updateBrandModalElement.querySelector('#updateBrandForm');
                if (!form) throw new Error('Không tìm thấy form cập nhật trong modal.');

                form.action = triggerButton.dataset.updateUrl || `/admin/product-management/brands/${data.id}`;
                form.querySelector('#brandNameUpdate').value = data.name;
                form.querySelector('#brandDescriptionUpdate').value = data.description || '';
                form.querySelector('#brandStatusUpdate').value = data.status;
                const logoPreviewUpdate = form.querySelector('#brandLogoPreviewUpdate');
                if (logoPreviewUpdate) {
                    logoPreviewUpdate.src = data.logo_full_url || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=LOGO';
                    logoPreviewUpdate.dataset.defaultSrc = logoPreviewUpdate.src; // Cập nhật defaultSrc cho logo hiện tại
                }
                form.querySelector('#brandLogoUpdate').value = ''; // Clear input file

                clearValidationErrors(form); // Xóa lỗi cũ trước khi điền dữ liệu mới
            }

            modalInstance.show();
        } catch (error) {
            console.error(`Lỗi khi lấy dữ liệu cho modal ${type}:`, error);
            showToast(`Không thể lấy dữ liệu ${type}. Vui lòng thử lại.`, 'error');
        } finally {
            hideAppLoader();
        }
    }

    /**
     * Hiển thị modal xác nhận xóa đơn lẻ.
     * @param {HTMLElement} triggerButton - Nút xóa đã kích hoạt.
     */
    function handleShowDeleteModal(triggerButton) {
        const form = deleteBrandModalElement.querySelector('#deleteBrandForm');
        if (!form) return;

        form.action = triggerButton.dataset.deleteUrl;
        deleteBrandModalElement.querySelector('#brandNameToDelete').textContent = triggerButton.dataset.name;
        form.dataset.id = triggerButton.dataset.id; // Lưu ID cho single delete
        deleteBrandModal.show();
    }

    // -----------------------------------------------------------------------------
    // SECTION 4: GẮN KẾT SỰ KIỆN (EVENT LISTENERS)
    // -----------------------------------------------------------------------------

    // Sử dụng Event Delegation trên body để xử lý tất cả các click liên quan đến nút hành động
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;

        if (button.classList.contains('btn-view-brand')) {
            event.preventDefault();
            await handleShowModal(button, viewBrandModal, 'view');
        } else if (button.classList.contains('btn-edit-brand')) {
            event.preventDefault();
            await handleShowModal(button, updateBrandModal, 'update');
        } else if (button.classList.contains('btn-delete-brand') && !button.closest('#bulkDeleteBtn')) {
            event.preventDefault();
            handleShowDeleteModal(button);
        } else if (button.classList.contains('toggle-status-btn')) {
            event.preventDefault();
            showAppLoader();
            try {
                const url = button.dataset.url;
                const response = await fetch(url, {
                    method: 'POST', // Laravel uses POST with _method spoofing
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || `Lỗi HTTP: ${response.status}`);
                }
                showToast(result.message, 'success');
                handleUpdateOrToggleSuccess(result.brand); // Pass the updated brand
            } catch (error) {
                console.error('Lỗi khi bật/tắt trạng thái:', error);
                showToast(error.message, 'error');
            } finally {
                hideAppLoader();
            }
        } else if (button.id === 'editBrandFromViewButton') {
            viewBrandModal.hide();
            setTimeout(() => handleShowModal(button, updateBrandModal, 'update'), 200);
        }
    });

    // Reset form và xóa lỗi khi các modal tạo/cập nhật đóng
    [createBrandModalElement, updateBrandModalElement].forEach(modalEl => {
        modalEl.addEventListener('hidden.bs.modal', () => {
            const form = modalEl.querySelector('form');
            if (form) {
                form.reset();
                clearValidationErrors(form);
                // Reset logo preview to default
                const logoPreview = modalEl.id === 'createBrandModal' ? document.getElementById('brandLogoPreviewCreate') : document.getElementById('brandLogoPreviewUpdate');
                if (logoPreview) {
                    logoPreview.src = logoPreview.dataset.defaultSrc || 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=Preview';
                }
            }
        });
    });

    // Reset mật khẩu và lỗi khi modal xóa/bulk toggle mở
    [deleteBrandModalElement, bulkToggleStatusModalElement].forEach(modalEl => {
        modalEl.addEventListener('show.bs.modal', () => {
            const passwordInput = modalEl.querySelector('#brandDeletionPassword') || modalEl.querySelector('#bulkToggleStatusPassword');
            const passwordErrorDiv = modalEl.querySelector('#brandDeletionPasswordError') || modalEl.querySelector('#bulkToggleStatusPasswordError');
            if (passwordInput) {
                passwordInput.value = '';
                passwordInput.classList.remove('is-invalid');
            }
            if (passwordErrorDiv) {
                passwordErrorDiv.textContent = '';
                passwordErrorDiv.style.display = 'none';
            }
        });
    });

    // -----------------------------------------------------------------------------
    // SECTION 5: AJAX, DOM UPDATE, SEARCH, FILTER & PAGINATION
    // -----------------------------------------------------------------------------

    /**
     * Cập nhật nội dung bảng và liên kết phân trang.
     * @param {string} tableRowsHtml - HTML cho các hàng của bảng.
     * @param {string} paginationLinksHtml - HTML cho các liên kết phân trang.
     */
    function updateTableContent(tableRowsHtml, paginationLinksHtml) {
        tableBody.innerHTML = tableRowsHtml || `
            <tr id="no-brands-row"><td colspan="7" class="text-center">
                <div class="alert alert-info mb-0">Không tìm thấy kết quả phù hợp.</div>
            </td></tr>`; // Updated colspan to 7
        if (paginationLinksContainer) {
            paginationLinksContainer.innerHTML = paginationLinksHtml || '';
        }
        updateCheckboxStates(); // Cập nhật trạng thái checkbox sau khi DOM thay đổi
        updateBulkActionButtons(); // Cập nhật trạng thái nút hành động hàng loạt
        attachPaginationListeners(); // Gắn lại listeners cho các liên kết phân trang mới
    }

    /**
     * Thực hiện tìm kiếm, lọc và sắp xếp bằng AJAX và cập nhật bảng.
     * @param {number} page - Số trang muốn fetch.
     */
    async function performSearch(page = 1) {
        showAppLoader();
        try {
            const currentSearchQuery = brandSearchInput.value;
            const currentFilter = brandFilterSelect.value;
            const currentSort = brandSortSelect.value;

            const urlParams = new URLSearchParams();
            urlParams.append('page', page);
            if (currentSearchQuery) urlParams.append('search', currentSearchQuery);
            if (currentFilter && currentFilter !== 'all') urlParams.append('filter', currentFilter);
            if (currentSort && currentSort !== 'latest') urlParams.append('sort_by', currentSort);

            const url = `/admin/product-management/brands?${urlParams.toString()}`;

            const response = await fetch(url, {
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
            console.error('Lỗi khi tải dữ liệu thương hiệu:', error);
            showToast('Không thể tải dữ liệu thương hiệu. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    // --- Lắng nghe sự kiện tìm kiếm, lọc và sắp xếp ---
    brandSearchBtn.addEventListener('click', () => performSearch(1));
    brandSearchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(1);
        }
    });

    brandFilterSelect.addEventListener('change', () => performSearch(1));
    brandSortSelect.addEventListener('change', () => performSearch(1));

    /**
     * Gắn lại event listeners cho các liên kết phân trang sau khi DOM được cập nhật.
     */
    function attachPaginationListeners() {
        if (paginationLinksContainer) {
            paginationLinksContainer.removeEventListener('click', handlePaginationClick);
            paginationLinksContainer.addEventListener('click', handlePaginationClick);
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
                performSearch(page);
            }
        }
    }

    /**
     * Xử lý thành công khi tạo mới, cập nhật hoặc toggle trạng thái đơn lẻ.
     * @param {object} brand - Đối tượng thương hiệu trả về từ server.
     */
    function handleUpdateOrToggleSuccess(brand) {
        let currentPage = '1';
        if (paginationLinksContainer) {
            const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
            if (activePageLink) {
                currentPage = activePageLink.textContent;
            }
        }
        performSearch(parseInt(currentPage, 10));
    }

    /**
     * Xử lý thành công khi xóa thương hiệu (đơn lẻ hoặc hàng loạt).
     * @param {Array<number>} deletedIds - Mảng ID của các thương hiệu đã xóa.
     */
    function handleDeleteSuccess(deletedIds) {
        if (!Array.isArray(deletedIds)) {
            console.error("Deleted IDs must be an array.");
            return;
        }

        deletedIds.forEach(id => {
            document.getElementById(`brand-row-${id}`)?.remove();
            selectedBrandIds.delete(String(id));
        });

        const currentRows = tableBody.querySelectorAll('tr:not(#no-brands-row)');
        if (currentRows.length === 0) {
            const currentPage = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            const targetPage = currentPage > 1 ? currentPage - 1 : 1;
            performSearch(targetPage);
        } else {
            const currentPage = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            performSearch(currentPage);
        }

        clearSelectedBrands();
    }

    /**
     * Xử lý thành công khi thay đổi trạng thái hàng loạt.
     * @param {Array<object>} updatedBrands - Mảng các brand đã được cập nhật từ server.
     */
    function handleBulkToggleStatusSuccess(updatedBrands) {
        const currentPage = paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1';
        performSearch(parseInt(currentPage, 10));
        clearSelectedBrands();
    }


    // -----------------------------------------------------------------------------
    // SECTION 6: THIẾT LẬP FORM AJAX CHUNG
    // -----------------------------------------------------------------------------

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
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
            }

            const formData = new FormData(this);

            const isDeleteForm = formId === 'deleteBrandForm';
            const isUpdateForm = formId === 'updateBrandForm';
            const isBulkDeleteForm = isDeleteForm && this.action.includes('bulk-destroy');
            const isBulkToggleStatusForm = formId === 'bulkToggleStatusForm';

            // Xử lý _method spoofing cho các phương thức PUT/DELETE
            if (isUpdateForm) {
                formData.append('_method', 'PUT');
            }
            // QUAN TRỌNG: Loại bỏ _method: 'DELETE' cho bulkDeleteForm
            // Vì route bulk-destroy đã là POST, không cần spoofing DELETE
            else if (isDeleteForm && !isBulkDeleteForm) { // Chỉ cho xóa đơn lẻ
                formData.append('_method', 'DELETE');
            }


            // Gắn các ID đã chọn cho hành động hàng loạt
            if (isBulkDeleteForm || isBulkToggleStatusForm) {
                formData.append('ids', JSON.stringify(Array.from(selectedBrandIds)));
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST', // Laravel luôn nhận POST khi dùng _method spoofing hoặc là route POST
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const result = await response.json();

                if (response.ok) {
                    modalInstance.hide();
                    showToast(result.message, 'success');

                    if (isDeleteForm) {
                        const deletedIds = result.deleted_ids || (isBulkDeleteForm ? Array.from(selectedBrandIds) : [parseInt(form.dataset.id, 10)]);
                        successCallback(deletedIds);
                    } else if (isBulkToggleStatusForm) {
                        successCallback(result.brands);
                    } else { // create & update
                        successCallback(result.brand);
                    }
                } else if (response.status === 422 && result.errors) {
                    displayValidationErrors(form, result.errors);
                    showToast(result.message || 'Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else {
                    showToast(result.message || 'Đã xảy ra lỗi không xác định.', 'error');
                }
            } catch (error) {
                console.error('Lỗi Fetch:', error);
                showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader();
                if (submitButton) {
                    submitButton.disabled = false;
                    if (formId === 'createBrandForm') submitButton.innerHTML = 'Tạo Thương hiệu';
                    else if (formId === 'updateBrandForm') submitButton.innerHTML = 'Lưu thay đổi';
                    else if (formId === 'deleteBrandForm') submitButton.innerHTML = 'Xóa Vĩnh Viễn';
                    else if (formId === 'bulkToggleStatusForm') submitButton.innerHTML = 'Xác nhận';
                }
            }
        });
    }

    // -----------------------------------------------------------------------------
    // SECTION 7: CHECKBOX & BULK ACTIONS
    // -----------------------------------------------------------------------------

    /**
     * Cập nhật trạng thái các nút hành động hàng loạt (disabled/enabled) và số lượng đã chọn.
     */
    function updateBulkActionButtons() {
        const count = selectedBrandIds.size;
        bulkDeleteBtn.disabled = count === 0;
        bulkToggleStatusBtn.disabled = count === 0;
        selectedCountDeleteSpan.textContent = count;
        selectedCountToggleSpan.textContent = count;
        selectedCountToggleModalSpan.textContent = count;
    }

    /**
     * Cập nhật trạng thái của từng checkbox (checked/unchecked) dựa trên `selectedBrandIds`.
     */
    function updateCheckboxStates() {
        const currentCheckboxes = document.querySelectorAll('.brand-checkbox');
        if (currentCheckboxes.length === 0) {
            if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
            return;
        }
        let allVisibleChecked = true;
        currentCheckboxes.forEach(checkbox => {
            checkbox.checked = selectedBrandIds.has(checkbox.value);
            if (!checkbox.checked) allVisibleChecked = false;
        });
        if (selectAllCheckboxes) selectAllCheckboxes.checked = allVisibleChecked;
        updateBulkActionButtons();
    }

    /**
     * Xóa tất cả các lựa chọn checkbox và reset trạng thái.
     */
    function clearSelectedBrands() {
        selectedBrandIds.clear();
        document.querySelectorAll('.brand-checkbox').forEach(cb => cb.checked = false);
        if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
        updateBulkActionButtons();
    }

    // Lắng nghe sự kiện cho checkbox "Chọn tất cả"
    selectAllCheckboxes.addEventListener('change', function () {
        document.querySelectorAll('.brand-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedBrandIds.add(checkbox.value);
            } else {
                selectedBrandIds.delete(checkbox.value);
            }
        });
        updateBulkActionButtons();
    });

    // Lắng nghe sự kiện cho từng checkbox thương hiệu (sử dụng delegation trên tableBody)
    tableBody.addEventListener('change', function (event) {
        const checkbox = event.target.closest('.brand-checkbox');
        if (checkbox) {
            if (checkbox.checked) {
                selectedBrandIds.add(checkbox.value);
            } else {
                selectedBrandIds.delete(checkbox.value);
            }
            updateBulkActionButtons();
            const allIndividualCheckboxes = document.querySelectorAll('.brand-checkbox');
            const checkedIndividualCheckboxes = document.querySelectorAll('.brand-checkbox:checked');
            if (selectAllCheckboxes) {
                selectAllCheckboxes.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
            }
        }
    });

    // Lắng nghe sự kiện click cho nút "Xóa đã chọn" (Bulk Delete)
    bulkDeleteBtn.addEventListener('click', function () {
        deleteBrandModalElement.querySelector('#brandNameToDelete').textContent = `${selectedBrandIds.size} thương hiệu đã chọn`;
        const form = deleteBrandModalElement.querySelector('#deleteBrandForm');
        form.action = '/admin/product-management/brands/bulk-destroy'; // Đặt action cho bulk delete
        form.removeAttribute('data-id');
        deleteBrandModal.show();
    });

    // Lắng nghe sự kiện click cho nút "Chuyển trạng thái đã chọn" (Bulk Toggle Status)
    bulkToggleStatusBtn.addEventListener('click', function () {
        selectedCountToggleModalSpan.textContent = selectedBrandIds.size;
        bulkToggleStatusModal.show();
    });

    // Handle show event for delete modal to correctly set single/bulk context
    deleteBrandModalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = deleteBrandModalElement.querySelector('#deleteBrandForm');
        form.removeAttribute('data-id'); // Clear any previous single delete ID

        if (button && button.classList.contains('btn-delete-brand') && !button.closest('#bulkDeleteBtn')) {
            const brandId = button.dataset.id;
            form.action = button.dataset.deleteUrl;
            form.dataset.id = brandId;
            deleteBrandModalElement.querySelector('#brandNameToDelete').textContent = button.dataset.name;
        } else if (button && button.id === 'bulkDeleteBtn') {
            // Bulk delete is already handled by bulkDeleteBtn click listener setting the action and text.
        }

        // Reset password input and error on modal show
        const passwordInput = deleteBrandModalElement.querySelector('#brandDeletionPassword');
        const passwordErrorDiv = deleteBrandModalElement.querySelector('#brandDeletionPasswordError');
        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.classList.remove('is-invalid');
        }
        if (passwordErrorDiv) {
            passwordErrorDiv.textContent = '';
            passwordErrorDiv.style.display = 'none';
        }
    });

    // Reset password input and error on bulk toggle status modal show
    bulkToggleStatusModalElement.addEventListener('show.bs.modal', function () {
        const passwordInput = bulkToggleStatusModalElement.querySelector('#bulkToggleStatusPassword');
        const passwordErrorDiv = bulkToggleStatusModalElement.querySelector('#bulkToggleStatusPasswordError');
        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.classList.remove('is-invalid');
        }
        if (passwordErrorDiv) {
            passwordErrorDiv.textContent = '';
            passwordErrorDiv.style.display = 'none';
        }
    });


    // -----------------------------------------------------------------------------
    // SECTION 8: KHỞI TẠO VÀ ÁP DỤNG
    // -----------------------------------------------------------------------------

    // Thiết lập preview logo cho cả tạo mới và cập nhật
    setupLogoPreview('brandLogoCreate', 'brandLogoPreviewCreate');
    setupLogoPreview('brandLogoUpdate', 'brandLogoPreviewUpdate');

    // Thiết lập các form AJAX
    setupAjaxForm('createBrandForm', createBrandModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('updateBrandForm', updateBrandModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('deleteBrandForm', deleteBrandModal, handleDeleteSuccess);
    setupAjaxForm('bulkToggleStatusForm', bulkToggleStatusModal, handleBulkToggleStatusSuccess);

    // Khởi tạo trạng thái ban đầu của các checkbox và nút
    updateCheckboxStates();
    attachPaginationListeners(); // Gắn listeners cho phân trang ngay khi tải trang

    // Thực hiện tìm kiếm/lọc/sắp xếp ban đầu khi trang được tải
    // Điều này sẽ tải dữ liệu ban đầu vào bảng, áp dụng bất kỳ tham số URL nào
    performSearch(1);

    console.log("Module Quản lý Thương hiệu đã được khởi tạo thành công.");
});