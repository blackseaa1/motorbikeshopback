/**
 * ===================================================================
 * promotion_manager.js (Phiên bản đã hợp nhất, sửa lỗi và tối ưu hóa)
 *
 * Xử lý toàn bộ logic JavaScript cho trang Quản lý Mã Khuyến Mãi,
 * bao gồm xem, tạo, sửa, xóa và bật/tắt trạng thái bằng AJAX.
 * ===================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // -----------------------------------------------------------------------------
    // SECTION 1: KHAI BÁO BIẾN & LẤY ELEMENTS
    // -----------------------------------------------------------------------------

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        return;
    }

    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
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

    const tableBody = document.getElementById('promotions-table-body');
    const createModalEl = document.getElementById('createPromotionModal');
    const updateModalEl = document.getElementById('updatePromotionModal');
    const deleteModalEl = document.getElementById('deletePromotionModal');
    const viewModalEl = document.getElementById('viewPromotionModal');
    const bulkToggleStatusModalEl = document.getElementById('bulkToggleStatusModal');

    const selectAllCheckboxes = document.getElementById('selectAllPromotions');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkToggleStatusBtn = document.getElementById('bulkToggleStatusBtn');
    const selectedCountDeleteSpan = document.getElementById('selectedCountDelete');
    const selectedCountToggleSpan = document.getElementById('selectedCountToggle');

    const promotionSearchInput = document.getElementById('promotionSearchInput');
    const promotionSearchBtn = document.getElementById('promotionSearchBtn');
    const promotionFilterSelect = document.getElementById('promotionFilterSelect');
    const promotionSortSelect = document.getElementById('promotionSortSelect');
    const paginationLinksContainer = document.getElementById('pagination-links');

    if (!tableBody || !createModalEl || !updateModalEl || !deleteModalEl || !viewModalEl || !bulkToggleStatusModalEl || !promotionFilterSelect || !promotionSortSelect || !selectAllCheckboxes || !bulkDeleteBtn || !bulkToggleStatusBtn || !selectedCountDeleteSpan || !selectedCountToggleSpan
    ) {
        console.warn('Cảnh báo: Một hoặc nhiều element modal/table/filter/sort/bulk quan trọng không tồn tại. Script có thể không hoạt động đầy đủ.');
        return;
    }

    const createModal = new bootstrap.Modal(createModalEl);
    const updateModal = new bootstrap.Modal(updateModalEl);
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    const viewModal = new bootstrap.Modal(viewModalEl);
    const bulkToggleStatusModal = new bootstrap.Modal(bulkToggleStatusModalEl);

    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED = 'fixed';

    let selectedPromotionIds = new Set();

    // -----------------------------------------------------------------------------
    // SECTION 2: HÀM TIỆN ÍCH (HELPER FUNCTIONS)
    // -----------------------------------------------------------------------------

    function formatLocaleDateTime(dateString) {
        if (!dateString) return '';
        try {
            return new Date(dateString).toLocaleString('vi-VN', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', second: '2-digit',
                hour12: false, timeZone: 'Asia/Ho_Chi_Minh'
            });
        } catch (e) {
            console.error("Lỗi định dạng ngày:", e);
            return dateString;
        }
    }

    function formatForInput(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            const formatter = new Intl.DateTimeFormat('sv-SE', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Ho_Chi_Minh'
            });
            const parts = formatter.formatToParts(date);
            const year = parts.find(p => p.type === 'year').value;
            const month = parts.find(p => p.type === 'month').value;
            const day = parts.find(p => p.type === 'day').value;
            const hour = parts.find(p => p.type === 'hour').value;
            const minute = parts.find(p => p.type === 'minute').value;
            return `${year}-${month}-${day}T${hour}:${minute}`;
        } catch (e) {
            console.error("Lỗi định dạng ngày cho input:", e);
            return '';
        }
    }

    function clearValidationErrors(formElement) {
        if (!formElement) return;
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    function displayValidationErrors(formElement, errors) {
        clearValidationErrors(formElement);
        let firstErrorField = null;
        for (const fieldName in errors) {
            if (Object.hasOwnProperty.call(errors, fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);

                if (!inputField) {
                    if (fieldName === 'discount_percentage') {
                        inputField = formElement.querySelector('[id^="promoDiscount"]');
                    } else if (fieldName === 'fixed_discount_amount') {
                        inputField = formElement.querySelector('[id^="promoFixedDiscountAmount"]');
                    } else if (fieldName === 'max_discount_amount') {
                        inputField = formElement.querySelector('[id^="promoMaxDiscountAmount"]');
                    } else if (fieldName === 'min_order_amount') {
                        inputField = formElement.querySelector('[id^="promoMinOrderAmount"]');
                    } else if (fieldName === 'start_date') {
                        inputField = formElement.querySelector('[id^="promoStartDate"]');
                    } else if (fieldName === 'end_date') {
                        inputField = formElement.querySelector('[id^="promoEndDate"]');
                    } else if (fieldName === 'status') {
                        inputField = formElement.querySelector('[id^="promoStatus"]');
                    }
                }

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    const errorDiv = inputField.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0];
                    }
                    if (!firstErrorField) firstErrorField = inputField;
                }
            }
        }
        if (firstErrorField) firstErrorField.focus();
    }

    function toggleDiscountInputs(type, modalPrefix) {
        const percentageGroup = document.getElementById(`promoDiscountPercentageGroup${modalPrefix}`);
        const fixedGroup = document.getElementById(`promoFixedDiscountAmountGroup${modalPrefix}`);
        const maxDiscountGroup = document.getElementById(`promoMaxDiscountAmountGroup${modalPrefix}`);
        const percentageInput = percentageGroup?.querySelector('input');
        const fixedInput = fixedGroup?.querySelector('input');
        const maxDiscountInput = maxDiscountGroup?.querySelector('input');

        if (type === DISCOUNT_TYPE_PERCENTAGE) {
            if (percentageGroup) percentageGroup.style.display = 'block';
            if (fixedGroup) fixedGroup.style.display = 'none';
            if (maxDiscountGroup) maxDiscountGroup.style.display = 'block';
            if (percentageInput) percentageInput.required = true;
            if (fixedInput) { fixedInput.required = false; fixedInput.value = ''; }
        } else if (type === DISCOUNT_TYPE_FIXED) {
            if (percentageGroup) percentageGroup.style.display = 'none';
            if (fixedGroup) fixedGroup.style.display = 'block';
            if (maxDiscountGroup) maxDiscountGroup.style.display = 'none';
            if (fixedInput) fixedInput.required = true;
            if (percentageInput) { percentageInput.required = false; percentageInput.value = ''; }
            if (maxDiscountInput) maxDiscountInput.value = '';
        }
    }

    function formatCurrencyInput(inputElement) {
        const formatValue = (value) => {
            if (!value) return '';
            const numberString = String(value).replace(/[^0-9]/g, '');
            if (numberString === '') return '';
            const number = parseInt(numberString, 10);
            return isNaN(number) ? '' : new Intl.NumberFormat('vi-VN').format(number);
        };
        inputElement.value = formatValue(inputElement.value);
        inputElement.addEventListener('input', (e) => {
            const originalValue = e.target.value;
            const caretPosition = e.target.selectionStart;
            const originalLength = originalValue.length;
            const formattedValue = formatValue(originalValue);
            e.target.value = formattedValue;
            const newLength = formattedValue.length;
            e.target.setSelectionRange(caretPosition + (newLength - originalLength), caretPosition + (newLength - originalLength));
        });
        inputElement.addEventListener('focus', (e) => e.target.select());
    }

    function parseFormattedCurrency(formattedValue) {
        return typeof formattedValue === 'string' ? formattedValue.replace(/\./g, '') : formattedValue;
    }

    // -----------------------------------------------------------------------------
    // SECTION 3: CÁC HÀM XỬ LÝ MODAL (HIỂN THỊ DỮ LIỆU)
    // -----------------------------------------------------------------------------

    async function handleShowModal(button, modal, modalInstance, formId) {
        showAppLoader();
        try {
            const response = await fetch(button.dataset.url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();

            if (formId === 'view') {
                viewModalEl.querySelector('#viewModalPromoCodeStrong').textContent = data.code;
                viewModalEl.querySelector('#viewDetailPromoCode').textContent = data.code;
                viewModalEl.querySelector('#viewDetailPromoDescription').textContent = data.description || '(Không có mô tả)';
                viewModalEl.querySelector('#viewDetailPromoDiscountType').textContent = data.discount_type === DISCOUNT_TYPE_PERCENTAGE ? 'Phần trăm (%)' : 'Số tiền cố định (VNĐ)';
                viewModalEl.querySelector('#viewDetailPromoDiscount').textContent = data.formatted_discount;
                viewModalEl.querySelector('#viewDetailPromoMaxDiscountAmount').textContent = data.max_discount_amount !== null ? `${new Intl.NumberFormat('vi-VN').format(data.max_discount_amount)}đ` : 'Không giới hạn';
                viewModalEl.querySelector('#viewDetailPromoStartDate').textContent = formatLocaleDateTime(data.start_date);
                viewModalEl.querySelector('#viewDetailPromoEndDate').textContent = formatLocaleDateTime(data.end_date);
                viewModalEl.querySelector('#viewDetailPromoMaxUses').textContent = data.max_uses || 'Không giới hạn';
                viewModalEl.querySelector('#viewDetailPromoUsesCount').textContent = data.uses_count;
                viewModalEl.querySelector('#viewDetailPromoMinOrderAmount').textContent = data.min_order_amount !== null ? `${new Intl.NumberFormat('vi-VN').format(data.min_order_amount)}đ` : 'Không yêu cầu';
                viewModalEl.querySelector('#viewDetailPromoStatusConfigText').innerHTML = `<span class="badge ${data.manual_status_badge_class}">${data.manual_status_text}</span>`;
                viewModalEl.querySelector('#viewDetailPromoStatusDisplayBadge').innerHTML = `<span class="badge ${data.effective_status_badge_class}">${data.effective_status_text}</span>`;
                const editBtn = viewModalEl.querySelector('#editFromViewBtn');
                editBtn.dataset.url = button.dataset.url;
                editBtn.dataset.updateUrl = button.closest('tr').querySelector('.edit-promotion-btn')?.dataset.updateUrl;
            } else if (formId === 'update') {
                const form = updateModalEl.querySelector('form');
                form.action = button.dataset.updateUrl;
                form.querySelector('#promoCodeUpdate').value = data.code;
                form.querySelector('#promoDescriptionUpdate').value = data.description || '';
                form.querySelector('#promoDiscountTypeUpdate').value = data.discount_type;
                form.querySelector('#promoDiscountUpdate').value = data.discount_percentage || '';
                const formatInitialAmount = (amount) => {
                    if (amount === null || amount === undefined) return '';
                    const number = Math.round(parseFloat(amount));
                    return number > 0 ? new Intl.NumberFormat('vi-VN').format(number) : '';
                };
                form.querySelector('#promoFixedDiscountAmountUpdate').value = formatInitialAmount(data.fixed_discount_amount);
                form.querySelector('#promoMaxDiscountAmountUpdate').value = formatInitialAmount(data.max_discount_amount);
                form.querySelector('#promoMinOrderAmountUpdate').value = formatInitialAmount(data.min_order_amount);
                form.querySelector('#promoStartDateUpdate').value = formatForInput(data.start_date);
                form.querySelector('#promoEndDateUpdate').value = formatForInput(data.end_date);
                form.querySelector('#promoMaxUsesUpdate').value = data.max_uses || '';
                form.querySelector('#promoStatusUpdate').value = data.status;
                toggleDiscountInputs(data.discount_type, 'Update');
            }

            modalInstance.show();
        } catch (error) {
            console.error(`Lỗi khi lấy dữ liệu cho modal ${formId}:`, error);
            showToast('Không thể lấy dữ liệu. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    function handleShowDeleteModal(button) {
        const form = deleteModalEl.querySelector('form');
        form.action = button.dataset.deleteUrl;
        deleteModalEl.querySelector('#deletePromotionCode').textContent = button.dataset.code;
        form.dataset.id = button.dataset.id; // Store id for single delete
        deleteModal.show();
    }

    // -----------------------------------------------------------------------------
    // SECTION 4: GẮN KẾT SỰ KIỆN (EVENT LISTENERS)
    // -----------------------------------------------------------------------------

    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;

        if (button.classList.contains('view-promotion-btn')) {
            event.preventDefault();
            await handleShowModal(button, viewModalEl, viewModal, 'view');
        } else if (button.classList.contains('edit-promotion-btn')) {
            event.preventDefault();
            await handleShowModal(button, updateModalEl, updateModal, 'update');
        } else if (button.classList.contains('delete-promotion-btn') && !button.closest('#bulkDeleteBtn')) {
            event.preventDefault();
            handleShowDeleteModal(button);
        } else if (button.id === 'editFromViewBtn') {
            viewModal.hide();
            setTimeout(() => handleShowModal(button, updateModalEl, updateModal, 'update'), 200);
        } else if (button.classList.contains('toggle-status-btn')) {
            event.preventDefault();
            showAppLoader();
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || "Lỗi không xác định");
                showToast(result.message, 'success');
                handleUpdateOrToggleSuccess(result.promotion);
            } catch (error) {
                console.error('Lỗi khi bật/tắt trạng thái:', error);
                showToast(error.message, 'error');
            } finally {
                hideAppLoader();
            }
        }
    });

    [createModalEl, updateModalEl].forEach(modalEl => {
        const select = modalEl.querySelector('select[name="discount_type"]');
        const prefix = modalEl.id.includes('create') ? 'Create' : 'Update';
        if (select) {
            select.addEventListener('change', () => toggleDiscountInputs(select.value, prefix));
        }
        modalEl.addEventListener('hidden.bs.modal', () => {
            const form = modalEl.querySelector('form');
            if (form) {
                form.reset();
                clearValidationErrors(form);
                // Reset to default percentage display or current selected value
                toggleDiscountInputs(select?.value || DISCOUNT_TYPE_PERCENTAGE, prefix);
            }
        });
    });

    // -----------------------------------------------------------------------------
    // SECTION 5: AJAX, DOM UPDATE, SEARCH, FILTER & PAGINATION
    // -----------------------------------------------------------------------------

    function updateTableContent(tableRowsHtml, paginationLinksHtml) {
        tableBody.innerHTML = tableRowsHtml || `
            <tr id="no-promotions-row"><td colspan="10" class="text-center">
                <div class="alert alert-info mb-0">Không tìm thấy kết quả phù hợp.</div>
            </td></tr>`;
        if (paginationLinksContainer) { // [FIX] Đảm bảo kiểm tra paginationLinksContainer
            paginationLinksContainer.innerHTML = paginationLinksHtml || '';
        }
        updateCheckboxStates();
        updateBulkActionButtons();
        attachPaginationListeners(); // Re-attach listeners to new pagination links
    }

    async function performSearch(page = 1, query = '') {
        showAppLoader();
        try {
            const currentSearchQuery = promotionSearchInput.value;
            const currentFilter = promotionFilterSelect.value;
            const currentSort = promotionSortSelect.value;

            const urlParams = new URLSearchParams();
            urlParams.append('page', page);
            if (currentSearchQuery) urlParams.append('search', currentSearchQuery);
            if (currentFilter && currentFilter !== 'all') urlParams.append('filter', currentFilter);
            if (currentSort && currentSort !== 'latest') urlParams.append('sort_by', currentSort);

            const url = `/admin/sales/promotions?${urlParams.toString()}`;

            const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();
            updateTableContent(data.table_rows, data.pagination_links);
        } catch (error) {
            console.error('Lỗi khi tìm kiếm, lọc hoặc sắp xếp:', error);
            showToast('Không thể tải dữ liệu. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    // --- Lắng nghe sự kiện tìm kiếm, lọc và phân trang ---
    promotionSearchBtn?.addEventListener('click', () => performSearch(1, promotionSearchInput.value)); // [FIX] Truyền query
    promotionSearchInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(1, promotionSearchInput.value); // [FIX] Truyền query
        }
    });

    promotionFilterSelect?.addEventListener('change', () => performSearch(1, promotionSearchInput.value)); // [FIX] Truyền query
    promotionSortSelect?.addEventListener('change', () => performSearch(1, promotionSearchInput.value));   // [FIX] Truyền query


    function attachPaginationListeners() {
        if (paginationLinksContainer) {
            paginationLinksContainer.removeEventListener('click', handlePaginationClick);
            paginationLinksContainer.addEventListener('click', handlePaginationClick);
        }
    }

    function handlePaginationClick(event) {
        const link = event.target.closest('.pagination a');
        if (link) {
            event.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page');
            const currentSearchQuery = promotionSearchInput.value; // [FIX] Lấy query
            if (page) {
                performSearch(page, currentSearchQuery); // [FIX] Truyền query
            }
        }
    }

    /**
     * Xử lý thành công khi tạo mới, cập nhật hoặc toggle trạng thái đơn lẻ.
     * @param {object} promotion - Đối tượng khuyến mãi trả về từ server.
     */
    function handleUpdateOrToggleSuccess(promotion) {
        // [FIX] Kiểm tra an toàn `paginationLinksContainer` trước khi sử dụng `querySelector`
        let currentPage = '1';
        if (paginationLinksContainer) {
            const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
            if (activePageLink) {
                currentPage = activePageLink.textContent;
            }
        }
        performSearch(parseInt(currentPage, 10), promotionSearchInput.value); // [FIX] Truyền search query
    }

    /**
     * Xử lý thành công khi xóa khuyến mãi (đơn lẻ hoặc hàng loạt).
     * @param {Array<number>} deletedIds - Mảng ID của các khuyến mãi đã xóa.
     */
    function handleDeleteSuccess(deletedIds) {
        if (!Array.isArray(deletedIds)) return;

        deletedIds.forEach(id => {
            document.getElementById(`promotion-row-${id}`)?.remove();
            selectedPromotionIds.delete(String(id));
        });

        const currentRows = tableBody.querySelectorAll('tr:not(#no-promotions-row)');
        if (currentRows.length === 0) {
            // [FIX] Kiểm tra an toàn `paginationLinksContainer`
            const currentPage = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            const targetPage = currentPage > 1 ? currentPage - 1 : 1;
            performSearch(targetPage, promotionSearchInput.value); // [FIX] Truyền search query
        } else {
            // [FIX] Kiểm tra an toàn `paginationLinksContainer`
            const currentPageNum = parseInt(paginationLinksContainer?.querySelector('.page-item.active .page-link')?.textContent || '1', 10);
            const itemsPerPage = 10;
            const startIndex = (currentPageNum - 1) * itemsPerPage + 1;
            Array.from(tableBody.children).forEach((row, index) => {
                const sTTCell = row.querySelector('th[scope="row"]');
                if (sTTCell) sTTCell.textContent = startIndex + index;
            });
            updateBulkActionButtons();
            updateCheckboxStates();
        }
        if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
        clearSelectedPromotions();
    }

    /**
     * Xử lý thành công khi thay đổi trạng thái hàng loạt.
     * @param {Array<object>} updatedPromotions - Mảng các promotion đã được cập nhật.
     */
    function handleBulkToggleStatusSuccess(updatedPromotions) {
        // [FIX] Kiểm tra an toàn `paginationLinksContainer` trước khi sử dụng `querySelector`
        let currentPage = '1';
        if (paginationLinksContainer) {
            const activePageLink = paginationLinksContainer.querySelector('.page-item.active .page-link');
            if (activePageLink) {
                currentPage = activePageLink.textContent;
            }
        }
        performSearch(parseInt(currentPage, 10), promotionSearchInput.value); // [FIX] Truyền search query
        clearSelectedPromotions();
    }

    // -----------------------------------------------------------------------------
    // SECTION 6: THIẾT LẬP FORM AJAX CHUNG
    // -----------------------------------------------------------------------------

    function setupAjaxForm(formId, modalInstance, successCallback) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();
            clearValidationErrors(form);

            const formData = new FormData(this);
            form.querySelectorAll('[data-currency-input="true"]').forEach(input => {
                // [FIX] Đảm bảo parseFormattedCurrency được gọi cho các input tiền tệ
                if (formData.has(input.name)) {
                    formData.set(input.name, parseFormattedCurrency(input.value));
                }
            });

            const isDeleteForm = formId === 'deletePromotionForm';
            const isUpdateForm = formId === 'updatePromotionForm';
            const isBulkDeleteForm = isDeleteForm && form.action.includes('bulk-destroy');
            const isBulkToggleStatusForm = formId === 'bulkToggleStatusForm';


            if (isUpdateForm) {
                formData.append('_method', 'PUT');
            } else if (isDeleteForm && !isBulkDeleteForm) { // Chỉ cho single delete
                formData.append('_method', 'DELETE');
            }

            if (isBulkDeleteForm) { // Bulk delete
                formData.append('ids', JSON.stringify(Array.from(selectedPromotionIds)));
            } else if (isBulkToggleStatusForm) { // Bulk toggle status
                formData.append('ids', JSON.stringify(Array.from(selectedPromotionIds)));
                // Status value is already part of form data via select element in modal
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const result = await response.json();

                if (response.ok) {
                    showToast(result.message, 'success');
                    modalInstance.hide();

                    if (isDeleteForm) {
                        const deletedIds = result.deleted_ids || (isBulkDeleteForm ? Array.from(selectedPromotionIds) : [parseInt(form.dataset.id, 10)]);
                        successCallback(deletedIds);
                    } else if (isBulkToggleStatusForm) {
                        successCallback(result.promotions);
                    } else { // create & update
                        successCallback(result.promotion);
                    }
                } else if (response.status === 422) {
                    displayValidationErrors(form, result.errors);
                    showToast(result.message || 'Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else {
                    showToast(result.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.', 'error');
                }
            } catch (error) {
                console.error('Lỗi Fetch:', error);
                showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader();
            }
        });
    }

    // -----------------------------------------------------------------------------
    // SECTION 7: CHECKBOX & BULK ACTIONS
    // -----------------------------------------------------------------------------

    function updateBulkActionButtons() {
        const count = selectedPromotionIds.size;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
        if (bulkToggleStatusBtn) bulkToggleStatusBtn.disabled = count === 0;
        if (selectedCountDeleteSpan) selectedCountDeleteSpan.textContent = count;
        if (selectedCountToggleSpan) selectedCountToggleSpan.textContent = count;
    }

    function updateCheckboxStates() {
        const currentCheckboxes = document.querySelectorAll('.promotion-checkbox');
        let allChecked = true;
        if (currentCheckboxes.length === 0) {
            allChecked = false;
        } else {
            currentCheckboxes.forEach(checkbox => {
                if (selectedPromotionIds.has(checkbox.value)) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                    allChecked = false;
                }
            });
        }
        if (selectAllCheckboxes) {
            selectAllCheckboxes.checked = allChecked;
        }
        updateBulkActionButtons();
    }

    function clearSelectedPromotions() {
        selectedPromotionIds.clear();
        document.querySelectorAll('.promotion-checkbox').forEach(cb => cb.checked = false);
        if (selectAllCheckboxes) selectAllCheckboxes.checked = false;
        updateBulkActionButtons();
    }

    // Event listener for "select all" checkbox
    if (selectAllCheckboxes) {
        selectAllCheckboxes.addEventListener('change', function () {
            document.querySelectorAll('.promotion-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedPromotionIds.add(checkbox.value);
                } else {
                    selectedPromotionIds.delete(checkbox.value);
                }
            });
            updateBulkActionButtons();
        });
    }

    // Event listener for individual promotion checkboxes (using delegation)
    tableBody.addEventListener('change', function (event) {
        const checkbox = event.target.closest('.promotion-checkbox');
        if (checkbox) {
            if (checkbox.checked) {
                selectedPromotionIds.add(checkbox.value);
            } else {
                selectedPromotionIds.delete(checkbox.value);
            }
            updateBulkActionButtons();
            // Update "select all" checkbox state
            const allIndividualCheckboxes = document.querySelectorAll('.promotion-checkbox');
            const checkedIndividualCheckboxes = document.querySelectorAll('.promotion-checkbox:checked');
            if (selectAllCheckboxes) {
                selectAllCheckboxes.checked = allIndividualCheckboxes.length > 0 && allIndividualCheckboxes.length === checkedIndividualCheckboxes.length;
            }
        }
    });

    // Event listener for bulk delete button to set up the modal
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const count = selectedPromotionIds.size;
            deleteModalEl.querySelector('#deletePromotionCode').textContent = `${count} mã đã chọn`; // Update modal text
            const form = deleteModalEl.querySelector('form');
            form.action = '/admin/sales/promotions/bulk-destroy'; // Set action for bulk delete
            deleteModal.show();
        });
    }

    // Event listener for bulk toggle status button to set up the modal
    if (bulkToggleStatusBtn) {
        bulkToggleStatusBtn.addEventListener('click', function () {
            const count = selectedPromotionIds.size;
            bulkToggleStatusModalEl.querySelector('#bulkToggleStatusCount').textContent = count;
            bulkToggleStatusModal.show();
        });
    }

    // Handle form submission for bulk status toggle
    const bulkToggleStatusForm = bulkToggleStatusModalEl.querySelector('#bulkToggleStatusForm');
    if (bulkToggleStatusForm) {
        bulkToggleStatusForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();
            clearValidationErrors(bulkToggleStatusForm);

            const formData = new FormData(this);
            formData.append('ids', JSON.stringify(Array.from(selectedPromotionIds)));
            // Status value is already part of form data via select element in modal

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    bulkToggleStatusModal.hide();
                    handleBulkToggleStatusSuccess(result.promotions); // Pass updated promotions
                    clearSelectedPromotions();
                } else if (response.status === 422) {
                    displayValidationErrors(bulkToggleStatusForm, result.errors);
                    showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else {
                    showToast(result.message || 'Đã xảy ra lỗi khi cập nhật trạng thái hàng loạt.', 'error');
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader();
            }
        });
    }


    // -----------------------------------------------------------------------------
    // SECTION 8: SEARCH FUNCTIONALITY
    // -----------------------------------------------------------------------------

    if (promotionSearchInput && promotionSearchBtn) {
        promotionSearchBtn.addEventListener('click', function () {
            performSearch(1, promotionSearchInput.value);
        });

        promotionSearchInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                performSearch(1, promotionSearchInput.value);
            }
        });
    }

    // -----------------------------------------------------------------------------
    // SECTION 9: KHỞI TẠO VÀ ÁP DỤNG
    // -----------------------------------------------------------------------------

    setupAjaxForm('createPromotionForm', createModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('updatePromotionForm', updateModal, handleUpdateOrToggleSuccess);
    setupAjaxForm('deletePromotionForm', deleteModal, handleDeleteSuccess);
    setupAjaxForm('bulkToggleStatusForm', bulkToggleStatusModal, handleBulkToggleStatusSuccess);


    const currencyInputs = [
        createModalEl.querySelector('#promoMinOrderAmountCreate'),
        createModalEl.querySelector('#promoFixedDiscountAmountCreate'),
        createModalEl.querySelector('#promoMaxDiscountAmountCreate'),
        updateModalEl.querySelector('#promoMinOrderAmountUpdate'),
        updateModalEl.querySelector('#promoFixedDiscountAmountUpdate'),
        updateModalEl.querySelector('#promoMaxDiscountAmountUpdate'),
    ].filter(Boolean);

    currencyInputs.forEach(input => {
        if (input) {
            input.setAttribute('data-currency-input', 'true');
            formatCurrencyInput(input);
        }
    });

    updateBulkActionButtons();
    if (createModalEl.querySelector('#promoDiscountTypeCreate')) {
        toggleDiscountInputs(createModalEl.querySelector('#promoDiscountTypeCreate').value, 'Create');
    }

    attachPaginationListeners();

    // Initial load/search with current filter/sort values (if page reloads with them)
    performSearch(1, promotionSearchInput.value);

    console.log("Module Quản lý Mã Khuyến Mãi đã được khởi tạo thành công.");
});