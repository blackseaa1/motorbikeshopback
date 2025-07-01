/**
 * ===================================================================
 * category_manager.js (Phiên bản Đã Sửa Lỗi và Tối Ưu Mới Nhất)
 *
 * Xử lý JavaScript đầy đủ cho trang quản lý Danh mục sản phẩm,
 * bao gồm xem, tạo, sửa, xóa, bật/tắt trạng thái, tìm kiếm, lọc, sắp xếp
 * và hành động hàng loạt bằng AJAX.
 * ===================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    console.log("Khởi tạo JS cho trang Quản lý Danh mục...");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token! Script sẽ dừng.');
        return; // Dừng thực thi nếu không có CSRF token
    }

    // Lấy các hàm helper toàn cục (giả định từ admin_layout.js) hoặc fallback an toàn
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('DEBUG: Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('DEBUG: Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Lỗi: Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`); // Fallback sang alert nếu không có container
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
    const tableBody = document.getElementById('categories-table-body');
    const createCategoryModalElement = document.getElementById('createCategoryModal');
    const updateCategoryModalElement = document.getElementById('updateCategoryModal');
    const viewCategoryModalElement = document.getElementById('viewCategoryModal');
    const deleteCategoryModalElement = document.getElementById('deleteCategoryModal');
    const bulkToggleStatusModalElement = document.getElementById('bulkToggleStatusModal');

    const categorySearchInput = document.getElementById('categorySearchInput');
    const categorySearchBtn = document.getElementById('categorySearchBtn');
    const categoryFilterSelect = document.getElementById('categoryFilterSelect');
    const categorySortSelect = document.getElementById('categorySortSelect');
    const paginationLinksContainer = document.getElementById('pagination-links'); // Có thể là null

    const selectAllCheckboxes = document.getElementById('selectAllCategories');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkToggleStatusBtn = document.getElementById('bulkToggleStatusBtn');
    const selectedCountDeleteSpan = document.getElementById('selectedCountDelete');
    const selectedCountToggleSpan = document.getElementById('selectedCountToggle');
    const selectedCountToggleModalSpan = document.getElementById('selectedCountToggleModal');

    // Kiểm tra các element quan trọng nhất, nếu thiếu thì không chạy script
    if (!tableBody || !createCategoryModalElement || !updateCategoryModalElement || !viewCategoryModalElement || !deleteCategoryModalElement || !bulkToggleStatusModalElement ||
        !categorySearchInput || !categorySearchBtn || !categoryFilterSelect || !categorySortSelect ||
        !selectAllCheckboxes || !bulkDeleteBtn || !bulkToggleStatusBtn ||
        !selectedCountDeleteSpan || !selectedCountToggleSpan || !selectedCountToggleModalSpan // Đảm bảo tất cả các span cũng tồn tại
    ) {
        console.error('Lỗi: Một hoặc nhiều element DOM quan trọng không được tìm thấy. Vui lòng kiểm tra lại ID trong Blade và HTML.');
        // Hiển thị thông báo rõ ràng cho người dùng (hoặc admin) nếu môi trường cho phép
        // showToast('Hệ thống bị lỗi khởi tạo. Vui lòng liên hệ quản trị viên.', 'error');
        return;
    }

    // Khởi tạo các đối tượng Modal của Bootstrap một lần duy nhất
    const createCategoryModal = new bootstrap.Modal(createCategoryModalElement);
    const updateCategoryModal = new bootstrap.Modal(updateCategoryModalElement);
    const viewCategoryModal = new bootstrap.Modal(viewCategoryModalElement);
    const deleteCategoryModal = new bootstrap.Modal(deleteCategoryModalElement);
    const bulkToggleStatusModal = new bootstrap.Modal(bulkToggleStatusModalElement);

    let selectedCategoryIds = new Set(); // Để lưu trữ các ID danh mục đã chọn

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
        // Clear specific error divs if they exist (e.g., for password)
        formElement.querySelectorAll('[id$="Error"]').forEach(el => {
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
                    inputField = formElement.querySelector('#categoryDeletionPassword') || formElement.querySelector('#bulkToggleStatusPassword');
                    errorDiv = formElement.querySelector('#categoryDeletionPasswordError') || formElement.querySelector('#bulkToggleStatusPasswordError');
                }

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    // Lấy div lỗi tiếp theo nếu chưa tìm thấy
                    if (!errorDiv) {
                        errorDiv = inputField.nextElementSibling;
                    }

                    if (errorDiv && errorDiv.classList.contains('invalid-feedback') || (errorDiv && errorDiv.id && errorDiv.id.endsWith('Error'))) {
                        errorDiv.textContent = errors[fieldName][0]; // Chỉ hiển thị lỗi đầu tiên
                        errorDiv.style.display = 'block'; // Đảm bảo div lỗi hiển thị
                    } else {
                        console.warn(`Không tìm thấy div .invalid-feedback hoặc error cụ thể cho trường: ${fieldName}`);
                    }

                    if (!firstErrorField) {
                        firstErrorField = inputField;
                    }
                } else {
                    console.warn(`Không tìm thấy trường input cho lỗi: ${fieldName}`);
                    // Fallback: nếu không tìm thấy input, hiển thị toast cho lỗi này
                    showToast(`Lỗi dữ liệu: ${fieldName} - ${errors[fieldName][0]}`, 'error');
                }
            }
        }
        // Di chuyển focus đến trường đầu tiên có lỗi
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }

    // -----------------------------------------------------------------------------
    // SECTION 3: CÁC HÀM XỬ LÝ MODAL (HIỂN THỊ DỮ LIỆU)
    // -----------------------------------------------------------------------------

    /**
     * Điền dữ liệu vào modal xem chi tiết hoặc cập nhật danh mục bằng AJAX.
     * @param {HTMLElement} triggerButton - Nút đã kích hoạt modal (có data-url).
     * @param {Object} modalInstance - Instance của Bootstrap Modal (ví dụ: `viewCategoryModal` hoặc `updateCategoryModal`).
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
                    'X-Requested-With': 'XMLHttpRequest' // Quan trọng cho Laravel expectsJson()
                }
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định từ máy chủ.' }));
                throw new Error(errorData.message || `Lỗi mạng: ${response.statusText}`);
            }
            const data = await response.json(); // Dữ liệu danh mục

            if (type === 'view') {
                viewCategoryModalElement.querySelector('#categoryIdView').textContent = data.id || '-';
                viewCategoryModalElement.querySelector('#categoryNameView').textContent = data.name || '-';
                viewCategoryModalElement.querySelector('#categoryDescriptionView').textContent = data.description || 'Không có mô tả';
                viewCategoryModalElement.querySelector('#categoryCreatedAtView').textContent = new Date(data.created_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';
                viewCategoryModalElement.querySelector('#categoryUpdatedAtView').textContent = new Date(data.updated_at).toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }) || '-';

                const statusViewEl = viewCategoryModalElement.querySelector('#categoryStatusView');
                if (statusViewEl) {
                    statusViewEl.innerHTML = `<span class="badge ${data.status_badge_class}">${data.status_text}</span>`;
                }

                const editButtonFromView = viewCategoryModalElement.querySelector('#editCategoryFromViewButton');
                if (editButtonFromView) {
                    editButtonFromView.dataset.id = data.id;
                    editButtonFromView.dataset.url = triggerButton.dataset.url; // Giữ lại URL show để fetch lại nếu cần
                    editButtonFromView.dataset.updateUrl = triggerButton.dataset.updateUrl || `/admin/product-management/categories/${data.id}`; // URL update cho form
                }
            } else if (type === 'update') {
                const form = updateCategoryModalElement.querySelector('#updateCategoryForm');
                if (!form) throw new Error('Không tìm thấy form cập nhật trong modal.');

                form.action = triggerButton.dataset.updateUrl || `/admin/product-management/categories/${data.id}`;
                form.querySelector('#categoryNameUpdate').value = data.name;
                form.querySelector('#categoryDescriptionUpdate').value = data.description || '';
                form.querySelector('#categoryStatusUpdate').value = data.status;

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
        const form = deleteCategoryModalElement.querySelector('#deleteCategoryForm');
        if (!form) return;

        form.action = triggerButton.dataset.deleteUrl;
        deleteCategoryModalElement.querySelector('#categoryNameToDelete').textContent = triggerButton.dataset.name;
        form.dataset.id = triggerButton.dataset.id; // Lưu ID cho single delete
        deleteCategoryModal.show();
    }


    // -----------------------------------------------------------------------------
    // SECTION 4: GẮN KẾT SỰ KIỆN (EVENT LISTENERS)
    // -----------------------------------------------------------------------------

    // Sử dụng Event Delegation trên body để xử lý tất cả các click liên quan đến nút hành động
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;

        if (button.classList.contains('btn-view-category')) {
            event.preventDefault();
            await handleShowModal(button, viewCategoryModal, 'view');
        } else if (button.classList.contains('btn-edit-category')) {
            event.preventDefault();
            await handleShowModal(button, updateCategoryModal, 'update');
        } else if (button.classList.contains('btn-delete-category') && !button.closest('#bulkDeleteBtn')) {
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
                // Gọi callback sau khi AJAX thành công, truyền dữ liệu category
                handleUpdateOrToggleSuccess(result.category);
            } catch (error) {
                console.error('Lỗi khi bật/tắt trạng thái:', error);
                showToast(error.message, 'error');
            } finally {
                hideAppLoader();
            }
        } else if (button.id === 'editCategoryFromViewButton') {
            viewCategoryModal.hide();
            // Đợi một chút để modal xem đóng hẳn rồi mới mở modal sửa
            setTimeout(() => handleShowModal(button, updateCategoryModal, 'update'), 200);
        }
    });

    // Reset form và xóa lỗi khi các modal tạo/cập nhật đóng
    [createCategoryModalElement, updateCategoryModalElement].forEach(modalEl => {
        modalEl.addEventListener('hidden.bs.modal', () => {
            const form = modalEl.querySelector('form');
            if (form) {
                form.reset();
                clearValidationErrors(form);
            }
        });
    });

    // Reset mật khẩu và lỗi khi modal xóa/bulk toggle mở
    [deleteCategoryModalElement, bulkToggleStatusModalElement].forEach(modalEl => {
        modalEl.addEventListener('show.bs.modal', () => {
            const passwordInput = modalEl.querySelector('#categoryDeletionPassword') || modalEl.querySelector('#bulkToggleStatusPassword');
            const passwordErrorDiv = modalEl.querySelector('#categoryDeletionPasswordError') || modalEl.querySelector('#bulkToggleStatusPasswordError');
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
            <tr id="no-categories-row"><td colspan="6" class="text-center">
                <div class="alert alert-info mb-0">Không tìm thấy kết quả phù hợp.</div>
            </td></tr>`;
        if (paginationLinksContainer) { // Chỉ cập nhật nếu element này tồn tại
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
            const currentSearchQuery = categorySearchInput.value;
            const currentFilter = categoryFilterSelect.value;
            const currentSort = categorySortSelect.value;

            const urlParams = new URLSearchParams();
            urlParams.append('page', page);
            if (currentSearchQuery) urlParams.append('search', currentSearchQuery);
            if (currentFilter && currentFilter !== 'all') urlParams.append('filter', currentFilter);
            if (currentSort && currentSort !== 'latest') urlParams.append('sort_by', currentSort);

            const url = `/admin/product-management/categories?${urlParams.toString()}`;

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
            console.error('Lỗi khi tải dữ liệu danh mục:', error);
            showToast('Không thể tải dữ liệu danh mục. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    // --- Lắng nghe sự kiện tìm kiếm, lọc và sắp xếp ---
    categorySearchBtn.addEventListener('click', () => performSearch(1));
    categorySearchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(1);
        }
    });

    categoryFilterSelect.addEventListener('change', () => performSearch(1));
    categorySortSelect.addEventListener('change', () => performSearch(1));

    /**
     * Gắn lại event listeners cho các liên kết phân trang sau khi DOM được cập nhật.
     */
    function attachPaginationListeners() {
        if (paginationLinksContainer) {
            // Loại bỏ các listeners cũ để tránh trùng lặp
            paginationLinksContainer.removeEventListener('click', handlePaginationClick);
            // Gắn listener mới
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
            event.preventDefault(); // Ngăn chặn hành vi mặc định của thẻ <a>
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            if (page) {
                performSearch(page);
            }
        }
    }

    /**
     * Xử lý thành công khi tạo mới, cập nhật hoặc toggle trạng thái đơn lẻ.
     * @param {object} category - Đối tượng danh mục trả về từ server.
     */
    function handleUpdateOrToggleSuccess(category) {
        // Xác định trang hiện tại để fetch lại.
        // Cần an toàn nếu paginationLinksContainer không tồn tại (ví dụ: chỉ có 1 trang).
        let currentPage = '1';
        if (paginationLinksContainer) {
            const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
            if (activePageLink) {
                currentPage = activePageLink.textContent;
            }
        }
        performSearch(parseInt(currentPage, 10)); // Gọi performSearch với trang hiện tại
    }

    /**
     * Xử lý thành công khi xóa danh mục (đơn lẻ hoặc hàng loạt).
     * @param {Array<number>} deletedIds - Mảng ID của các danh mục đã xóa.
     */
    function handleDeleteSuccess(deletedIds) {
        if (!Array.isArray(deletedIds)) {
            console.error("Deleted IDs must be an array.");
            return;
        }

        // Xóa các hàng khỏi DOM cục bộ mà không cần tải lại toàn bộ trang
        deletedIds.forEach(id => {
            document.getElementById(`category-row-${id}`)?.remove();
            selectedCategoryIds.delete(String(id)); // Loại bỏ khỏi tập hợp đã chọn
        });

        // Kiểm tra xem bảng có còn hàng nào không
        const currentRows = tableBody.querySelectorAll('tr:not(#no-categories-row)');
        if (currentRows.length === 0) {
            // Nếu không còn hàng nào, cố gắng tải lại trang trước đó hoặc trang 1
            const currentPage = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            const targetPage = currentPage > 1 ? currentPage - 1 : 1;
            performSearch(targetPage);
        } else {
            // Nếu còn hàng, cần cập nhật lại STT cục bộ và trạng thái các nút
            // Hoặc đơn giản hơn và an toàn hơn là gọi performSearch để server tự re-index
            const currentPage = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            performSearch(currentPage); // An toàn nhất là để server re-render
        }

        clearSelectedCategories(); // Xóa các lựa chọn sau khi xóa thành công
    }

    /**
     * Xử lý thành công khi thay đổi trạng thái hàng loạt.
     * @param {Array<object>} updatedCategories - Mảng các category đã được cập nhật từ server.
     */
    function handleBulkToggleStatusSuccess(updatedCategories) {
        // Fetch lại trang hiện tại để đảm bảo trạng thái hiển thị đúng
        const currentPage = paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1';
        performSearch(parseInt(currentPage, 10));
        clearSelectedCategories(); // Xóa các lựa chọn sau khi hành động hàng loạt thành công
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
            clearValidationErrors(form); // Xóa lỗi cũ

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
            }

            const formData = new FormData(this);

            const isDeleteForm = formId === 'deleteCategoryForm';
            const isUpdateForm = formId === 'updateCategoryForm';
            const isBulkDeleteForm = isDeleteForm && this.action.includes('bulk-destroy');
            const isBulkToggleStatusForm = formId === 'bulkToggleStatusForm';

            // Xử lý _method spoofing cho các phương thức PUT/DELETE
            if (isUpdateForm) {
                formData.append('_method', 'PUT');
            } else if (isDeleteForm && !isBulkDeleteForm) {
                formData.append('_method', 'DELETE');
            }

            // Gắn các ID đã chọn cho hành động hàng loạt
            if (isBulkDeleteForm || isBulkToggleStatusForm) {
                formData.append('ids', JSON.stringify(Array.from(selectedCategoryIds)));
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST', // Laravel luôn nhận POST khi dùng _method spoofing
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
                        // Đối với xóa, truyền mảng các ID đã xóa (từ server hoặc lấy từ selectedCategoryIds)
                        const deletedIds = result.deleted_ids || (isBulkDeleteForm ? Array.from(selectedCategoryIds) : [parseInt(form.dataset.id, 10)]);
                        successCallback(deletedIds);
                    } else if (isBulkToggleStatusForm) {
                        // Đối với bulk toggle status, truyền mảng các category đã cập nhật
                        successCallback(result.categories);
                    } else { // create & update
                        // Đối với tạo/cập nhật, truyền đối tượng category đã được xử lý
                        successCallback(result.category);
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
                    // Đặt lại text của nút submit
                    if (formId === 'createCategoryForm') submitButton.innerHTML = 'Tạo Danh mục';
                    else if (formId === 'updateCategoryForm') submitButton.innerHTML = 'Lưu thay đổi';
                    else if (formId === 'deleteCategoryForm') submitButton.innerHTML = 'Xóa Vĩnh Viễn';
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
        const count = selectedCategoryIds.size;
        bulkDeleteBtn.disabled = count === 0;
        bulkToggleStatusBtn.disabled = count === 0;
        selectedCountDeleteSpan.textContent = count;
        selectedCountToggleSpan.textContent = count;
        selectedCountToggleModalSpan.textContent = count; // Cập nhật cả trong modal
    }

    /**
     * Cập nhật trạng thái của từng checkbox (checked/unchecked) dựa trên `selectedCategoryIds`.
     */
    function updateCheckboxStates() {
        const currentCheckboxes = document.querySelectorAll('.category-checkbox');
        if (currentCheckboxes.length === 0) {
            if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
            return;
        }
        let allVisibleChecked = true;
        currentCheckboxes.forEach(checkbox => {
            checkbox.checked = selectedCategoryIds.has(checkbox.value);
            if (!checkbox.checked) allVisibleChecked = false;
        });
        if (selectAllCheckboxes) selectAllCheckboxes.checked = allVisibleChecked;
        updateBulkActionButtons(); // Luôn cập nhật trạng thái nút sau khi cập nhật checkbox
    }

    /**
     * Xóa tất cả các lựa chọn checkbox và reset trạng thái.
     */
    function clearSelectedCategories() {
        selectedCategoryIds.clear();
        document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
        if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
        updateBulkActionButtons();
    }

    // Lắng nghe sự kiện cho checkbox "Chọn tất cả"
    selectAllCheckboxes.addEventListener('change', function () {
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedCategoryIds.add(checkbox.value);
            } else {
                selectedCategoryIds.delete(checkbox.value);
            }
        });
        updateBulkActionButtons();
    });

    // Lắng nghe sự kiện cho từng checkbox danh mục (sử dụng delegation trên tableBody)
    tableBody.addEventListener('change', function (event) {
        const checkbox = event.target.closest('.category-checkbox');
        if (checkbox) {
            if (checkbox.checked) {
                selectedCategoryIds.add(checkbox.value);
            } else {
                selectedCategoryIds.delete(checkbox.value);
            }
            updateBulkActionButtons();
            // Cập nhật trạng thái của checkbox "Chọn tất cả"
            const allIndividualCheckboxes = document.querySelectorAll('.category-checkbox');
            const checkedIndividualCheckboxes = document.querySelectorAll('.category-checkbox:checked');
            if (selectAllCheckboxes) {
                selectAllCheckboxes.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
            }
        }
    });

    // Lắng nghe sự kiện click cho nút "Xóa đã chọn" (Bulk Delete)
    bulkDeleteBtn.addEventListener('click', function () {
        // Thiết lập context cho modal xóa để nó hiểu đây là xóa hàng loạt
        deleteCategoryModalElement.querySelector('#categoryNameToDelete').textContent = `${selectedCategoryIds.size} danh mục đã chọn`;
        const form = deleteCategoryModalElement.querySelector('#deleteCategoryForm');
        form.action = '/admin/product-management/categories/bulk-destroy'; // Đặt action cho bulk delete
        form.removeAttribute('data-id'); // Xóa data-id nếu có từ xóa đơn lẻ
        deleteCategoryModal.show();
    });

    // Lắng nghe sự kiện click cho nút "Chuyển trạng thái đã chọn" (Bulk Toggle Status)
    bulkToggleStatusBtn.addEventListener('click', function () {
        // Thiết lập context cho modal bulk toggle status
        selectedCountToggleModalSpan.textContent = selectedCategoryIds.size;
        bulkToggleStatusModal.show();
    });

    // -----------------------------------------------------------------------------
    // SECTION 8: KHỞI TẠO VÀ ÁP DỤNG
    // -----------------------------------------------------------------------------

    // Thiết lập các form AJAX
    setupAjaxForm('createCategoryForm', createCategoryModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('updateCategoryForm', updateCategoryModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('deleteCategoryForm', deleteCategoryModal, handleDeleteSuccess);
    setupAjaxForm('bulkToggleStatusForm', bulkToggleStatusModal, handleBulkToggleStatusSuccess);

    // Khởi tạo trạng thái ban đầu của các checkbox và nút
    updateCheckboxStates();
    attachPaginationListeners(); // Gắn listeners cho phân trang ngay khi tải trang

    // Thực hiện tìm kiếm/lọc/sắp xếp ban đầu khi trang được tải
    // Điều này sẽ tải dữ liệu ban đầu vào bảng, áp dụng bất kỳ tham số URL nào
    performSearch(1);

    console.log("Module Quản lý Danh mục đã được khởi tạo thành công.");
});