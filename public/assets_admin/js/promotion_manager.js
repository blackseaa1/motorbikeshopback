/**
 * ===================================================================
 * promotion_manager.js (Phiên bản đầy đủ, đã tái cấu trúc và cải tiến AJAX/UX)
 *
 * Xử lý toàn bộ logic JavaScript cho trang Quản lý Mã Khuyến Mãi,
 * bao gồm xem, tạo, sửa, xóa và bật/tắt trạng thái bằng AJAX.
 * ===================================================================
 */

// Bọc toàn bộ code trong sự kiện 'DOMContentLoaded' để đảm bảo DOM đã sẵn sàng.
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // -----------------------------------------------------------------------------
    // SECTION 1: KHAI BÁO BIẾN & LẤY ELEMENTS
    // -----------------------------------------------------------------------------

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Lỗi nghiêm trọng: Không tìm thấy CSRF Token!');
        // Dừng thực thi nếu không có CSRF token để tránh lỗi không cần thiết
        return;
    }

    // Lấy các hàm helper toàn cục từ admin_layout.js (nếu có, giả định tồn tại)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showAppInfoModal = typeof window.showAppInfoModal === 'function' ? window.showAppInfoModal : (msg, type) => alert(`${type}: ${msg}`);
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Fallback đơn giản nếu showToast không được định nghĩa toàn cục
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`); // Fallback sang alert nếu không có container
            return;
        }

        const toastEl = document.createElement('div');
        // Sử dụng các lớp Bootstrap Toast
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
        toastContainer.appendChild(toastEl); // Thêm vào container thay vì body

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };


    // Lấy các element chính của trang
    const tableBody = document.getElementById('promotions-table-body');
    const createModalEl = document.getElementById('createPromotionModal');
    const updateModalEl = document.getElementById('updatePromotionModal');
    const deleteModalEl = document.getElementById('deletePromotionModal');
    const viewModalEl = document.getElementById('viewPromotionModal');

    // Kiểm tra các element quan trọng trước khi chạy
    if (!tableBody || !createModalEl || !updateModalEl || !deleteModalEl || !viewModalEl) {
        console.warn('Cảnh báo: Một hoặc nhiều element modal/table quan trọng không tồn tại. Script có thể không hoạt động đầy đủ.');
        return;
    }

    // Khởi tạo các đối tượng Modal của Bootstrap một lần duy nhất
    const createModal = new bootstrap.Modal(createModalEl);
    const updateModal = new bootstrap.Modal(updateModalEl);
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    const viewModal = new bootstrap.Modal(viewModalEl);

    // Constants cho loại giảm giá
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED = 'fixed';

    // -----------------------------------------------------------------------------
    // SECTION 2: HÀM TIỆN ÍCH (HELPER FUNCTIONS)
    // -----------------------------------------------------------------------------

    /**
     * Định dạng ngày giờ từ chuỗi ISO sang định dạng dd/mm/yyyy, hh:mm:ss.
     * @param {string} dateString - Chuỗi ngày giờ.
     * @returns {string} - Chuỗi đã định dạng hoặc chuỗi rỗng nếu đầu vào không hợp lệ.
     */
    function formatLocaleDateTime(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toLocaleString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Ho_Chi_Minh' // Explicitly set to UTC+7 (Vietnam timezone)
            });
        } catch (e) {
            console.error("Lỗi định dạng ngày:", e);
            return dateString;
        }
    }

    /**
     * Định dạng ngày giờ sang chuẩn cho input `datetime-local`.
     * @param {string} dateString - Chuỗi ngày giờ.
     * @returns {string} - Chuỗi định dạng YYYY-MM-ddTHH:mm.
     */
    function formatForInput(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            // Use Intl.DateTimeFormat to format the date in Asia/Ho_Chi_Minh timezone
            const formatter = new Intl.DateTimeFormat('sv-SE', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Ho_Chi_Minh'
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
        clearValidationErrors(formElement); // Xóa lỗi cũ trước
        let firstErrorField = null;

        for (const fieldName in errors) {
            if (errors.hasOwnProperty(fieldName)) {
                // Tìm trường input hoặc select tương ứng
                // Có thể cần điều chỉnh cách tìm kiếm nếu id không khớp hoàn toàn với name
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);

                // Xử lý đặc biệt cho các trường có tên khác với ID (ví dụ, lỗi chung cho discount_percentage nếu ID là promoDiscountCreate)
                // Cần đảm bảo rằng mỗi trường input có một div .invalid-feedback ngay sau nó.
                if (!inputField) {
                    // Thử tìm theo ID nếu tên trường có chứa từ khóa của ID (ví dụ: discount_percentage -> promoDiscountCreate)
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
                    const errorDiv = inputField.nextElementSibling; // Lấy div invalid-feedback ngay sau input

                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0]; // Chỉ hiển thị lỗi đầu tiên
                    } else {
                        // Fallback nếu không tìm thấy invalid-feedback đúng cách
                        console.warn(`Không tìm thấy div .invalid-feedback cho trường: ${fieldName}`);
                        // Có thể tạo một div và chèn vào nếu cần
                    }

                    if (!firstErrorField) {
                        firstErrorField = inputField;
                    }
                } else {
                    console.warn(`Không tìm thấy trường input cho lỗi: ${fieldName}`);
                }
            }
        }
        // Di chuyển focus đến trường đầu tiên có lỗi
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }

    /**
     * Ẩn/hiện các trường input dựa trên loại giảm giá được chọn.
     * @param {string} type - Loại giảm giá ('percentage' hoặc 'fixed').
     * @param {string} modalPrefix - Tiền tố ID của modal ('Create' hoặc 'Update').
     */
    function toggleDiscountInputs(type, modalPrefix) {
        const percentageGroup = document.getElementById(`promoDiscountPercentageGroup${modalPrefix}`);
        const fixedGroup = document.getElementById(`promoFixedDiscountAmountGroup${modalPrefix}`);
        const maxDiscountGroup = document.getElementById(`promoMaxDiscountAmountGroup${modalPrefix}`);

        // Lấy input elements trong các group
        const percentageInput = percentageGroup?.querySelector('input');
        const fixedInput = fixedGroup?.querySelector('input');
        const maxDiscountInput = maxDiscountGroup?.querySelector('input');

        if (type === DISCOUNT_TYPE_PERCENTAGE) {
            if (percentageGroup) percentageGroup.style.display = 'block';
            if (fixedGroup) fixedGroup.style.display = 'none';
            if (maxDiscountGroup) maxDiscountGroup.style.display = 'block';

            if (percentageInput) percentageInput.setAttribute('required', 'true');
            if (fixedInput) fixedInput.removeAttribute('required');

            // Xóa giá trị của trường không hiển thị để tránh gửi dữ liệu không mong muốn
            if (fixedInput) fixedInput.value = '';
        } else if (type === DISCOUNT_TYPE_FIXED) {
            if (percentageGroup) percentageGroup.style.display = 'none';
            if (fixedGroup) fixedGroup.style.display = 'block';
            if (maxDiscountGroup) maxDiscountGroup.style.display = 'none';

            if (fixedInput) fixedInput.setAttribute('required', 'true');
            if (percentageInput) percentageInput.removeAttribute('required');

            // Xóa giá trị của trường không hiển thị
            if (percentageInput) percentageInput.value = '';
            if (maxDiscountInput) maxDiscountInput.value = '';
        }
    }

    /**
  * Định dạng số tiền theo chuẩn VNĐ khi người dùng nhập.
  * Hỗ trợ phần thập phân bằng dấu phẩy, tự động thêm dấu phân cách hàng nghìn.
  *
  * @param {HTMLInputElement} inputElement - Trường input cần định dạng.
  */
    function formatCurrencyInput(inputElement) {
        inputElement.addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^0-9,]/g, ''); // Chỉ cho phép số và dấu phẩy

            let parts = value.split(',');
            let integerPart = parts[0].replace(/\./g, ''); // Xóa dấu chấm cũ nếu có
            let decimalPart = parts.length > 1 ? ',' + parts[1] : '';

            // Thêm dấu chấm phân cách hàng nghìn cho phần nguyên
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            e.target.value = integerPart + decimalPart;

            // Giữ con trỏ ở cuối cùng (hoặc có thể tối ưu nếu cần đặt ở vị trí cũ)
            e.target.setSelectionRange(e.target.value.length, e.target.value.length);
        });

        inputElement.addEventListener('focus', function (e) {
            e.target.select();
        });
    }

    /**
     * Chuyển chuỗi tiền tệ "1.250.000,75" => "1250000.75"
     *
     * @param {string} formattedValue - Chuỗi tiền tệ VNĐ
     * @returns {string} - Chuỗi số thập phân sạch gửi về server
     */
    function parseFormattedCurrency(formattedValue) {
        if (typeof formattedValue !== 'string') return formattedValue;
        return formattedValue.replace(/\./g, '').replace(',', '.');
    }


    // -----------------------------------------------------------------------------
    // SECTION 3: CÁC HÀM XỬ LÝ MODAL (HIỂN THỊ DỮ LIỆU)
    // -----------------------------------------------------------------------------

    async function handleShowViewModal(button) {
        showAppLoader();
        try {
            const response = await fetch(button.dataset.url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();

            // Điền dữ liệu vào modal xem chi tiết
            viewModalEl.querySelector('#viewModalPromoCodeStrong').textContent = data.code;
            viewModalEl.querySelector('#viewDetailPromoCode').textContent = data.code;
            viewModalEl.querySelector('#viewDetailPromoDescription').textContent = data.description || '(Không có mô tả)';

            // Hiển thị loại giảm giá
            viewModalEl.querySelector('#viewDetailPromoDiscountType').textContent =
                data.discount_type === DISCOUNT_TYPE_PERCENTAGE ? 'Phần trăm (%)' : 'Số tiền cố định (VNĐ)';

            // Hiển thị giá trị giảm giá (phần trăm hoặc số tiền cố định)
            viewModalEl.querySelector('#viewDetailPromoDiscount').textContent = data.formatted_discount;

            // Hiển thị số tiền giảm tối đa
            viewModalEl.querySelector('#viewDetailPromoMaxDiscountAmount').textContent =
                data.max_discount_amount !== null ? `${new Intl.NumberFormat('vi-VN').format(data.max_discount_amount)}đ` : 'Không giới hạn';

            viewModalEl.querySelector('#viewDetailPromoStartDate').textContent = formatLocaleDateTime(data.start_date);
            viewModalEl.querySelector('#viewDetailPromoEndDate').textContent = formatLocaleDateTime(data.end_date);
            viewModalEl.querySelector('#viewDetailPromoMaxUses').textContent = data.max_uses || 'Không giới hạn';
            viewModalEl.querySelector('#viewDetailPromoUsesCount').textContent = data.uses_count;

            // Hiển thị giá trị đơn hàng tối thiểu
            viewModalEl.querySelector('#viewDetailPromoMinOrderAmount').textContent =
                data.min_order_amount !== null ? `${new Intl.NumberFormat('vi-VN').format(data.min_order_amount)}đ` : 'Không yêu cầu';

            viewModalEl.querySelector('#viewDetailPromoStatusConfigText').innerHTML = `<span class="badge ${data.manual_status_badge_class}">${data.manual_status_text}</span>`;
            viewModalEl.querySelector('#viewDetailPromoStatusDisplayBadge').innerHTML = `<span class="badge ${data.effective_status_badge_class}">${data.effective_status_text}</span>`;

            // Gán data cho nút "Chỉnh sửa" bên trong modal xem
            const editBtn = viewModalEl.querySelector('#editFromViewBtn');
            editBtn.dataset.url = button.dataset.url;
            editBtn.dataset.updateUrl = button.closest('tr').querySelector('.edit-promotion-btn')?.dataset.updateUrl;

            viewModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu xem chi tiết:', error);
            showAppInfoModal('Không thể lấy dữ liệu chi tiết. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    async function handleShowUpdateModal(button) {
        showAppLoader();
        try {
            const response = await fetch(button.dataset.url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const data = await response.json();

            const form = updateModalEl.querySelector('form');
            form.action = button.dataset.updateUrl; // Lấy URL update từ nút sửa

            // Điền dữ liệu vào form cập nhật
            form.querySelector('#promoCodeUpdate').value = data.code;
            form.querySelector('#promoDescriptionUpdate').value = data.description || '';
            form.querySelector('#promoDiscountTypeUpdate').value = data.discount_type; // Điền loại giảm giá

            form.querySelector('#promoDiscountUpdate').value = data.discount_percentage || '';
            form.querySelector('#promoFixedDiscountAmountUpdate').value = data.fixed_discount_amount || '';
            form.querySelector('#promoMaxDiscountAmountUpdate').value = data.max_discount_amount || '';

            form.querySelector('#promoStartDateUpdate').value = formatForInput(data.start_date);
            form.querySelector('#promoEndDateUpdate').value = formatForInput(data.end_date);
            form.querySelector('#promoMaxUsesUpdate').value = data.max_uses || '';
            form.querySelector('#promoMinOrderAmountUpdate').value = data.min_order_amount || ''; // Điền giá trị đơn hàng tối thiểu
            form.querySelector('#promoStatusUpdate').value = data.status;

            // Gọi hàm để ẩn/hiện các trường input dựa trên loại giảm giá đã chọn
            toggleDiscountInputs(data.discount_type, 'Update');

            updateModal.show();
        } catch (error) {
            console.error('Lỗi khi lấy dữ liệu cập nhật:', error);
            showAppInfoModal('Không thể lấy dữ liệu để sửa. Vui lòng thử lại.', 'error');
        } finally {
            hideAppLoader();
        }
    }

    function handleShowDeleteModal(button) {
        const form = deleteModalEl.querySelector('form');
        form.action = button.dataset.deleteUrl;
        deleteModalEl.querySelector('#deletePromotionCode').textContent = button.dataset.code;
        deleteModal.show();
    }

    // -----------------------------------------------------------------------------
    // SECTION 4: GẮN KẾT SỰ KIỆN (EVENT LISTENERS)
    // -----------------------------------------------------------------------------

    // Sử dụng Event Delegation trên body để xử lý tất cả các click
    document.body.addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;

        // Xử lý nút mở Modal Xem Chi Tiết
        if (button.classList.contains('view-promotion-btn')) {
            event.preventDefault();
            await handleShowViewModal(button);
        }

        // Xử lý nút mở Modal Chỉnh Sửa
        if (button.classList.contains('edit-promotion-btn')) {
            event.preventDefault();
            await handleShowUpdateModal(button);
        }

        // Xử lý nút mở Modal Xóa
        if (button.classList.contains('delete-promotion-btn')) {
            event.preventDefault();
            handleShowDeleteModal(button);
        }

        // Xử lý nút "Chỉnh sửa" từ bên trong Modal Xem
        if (button.id === 'editFromViewBtn') {
            viewModal.hide();
            // Đợi một chút để modal cũ đóng hẳn rồi mới mở modal mới
            setTimeout(() => handleShowUpdateModal(button), 200);
        }

        // Xử lý nút Bật/Tắt trạng thái
        if (button.classList.contains('toggle-status-btn')) {
            event.preventDefault();
            showAppLoader();
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || "Lỗi không xác định");

                showToast(result.message, 'success'); // Sử dụng showToast
                // Tải lại trang để cập nhật trạng thái một cách đồng bộ nhất
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                console.error('Lỗi khi bật/tắt trạng thái:', error);
                showToast(error.message, 'error'); // Sử dụng showToast
            } finally {
                hideAppLoader();
            }
        }
    });

    // Xử lý sự kiện thay đổi loại giảm giá trong modal tạo
    const createDiscountTypeSelect = createModalEl.querySelector('#promoDiscountTypeCreate');
    if (createDiscountTypeSelect) {
        createDiscountTypeSelect.addEventListener('change', function () {
            toggleDiscountInputs(this.value, 'Create');
        });
    }

    // Xử lý sự kiện thay đổi loại giảm giá trong modal cập nhật
    const updateDiscountTypeSelect = updateModalEl.querySelector('#promoDiscountTypeUpdate');
    if (updateDiscountTypeSelect) {
        updateDiscountTypeSelect.addEventListener('change', function () {
            toggleDiscountInputs(this.value, 'Update');
        });
    }

    // Reset form khi modal được đóng để tránh giữ lại dữ liệu cũ
    createModalEl.addEventListener('hidden.bs.modal', () => {
        const form = createModalEl.querySelector('form');
        form.reset();
        clearValidationErrors(form);
        // Đảm bảo trạng thái ban đầu của các input khi đóng modal
        toggleDiscountInputs(createDiscountTypeSelect.value, 'Create');
    });
    updateModalEl.addEventListener('hidden.bs.modal', () => {
        const form = updateModalEl.querySelector('form');
        form.reset();
        clearValidationErrors(form);
        // Đảm bảo trạng thái ban đầu của các input khi đóng modal
        // Lấy giá trị mặc định hoặc giá trị hiện tại của select khi đóng modal
        const currentType = updateModalEl.querySelector('#promoDiscountTypeUpdate').value;
        toggleDiscountInputs(currentType, 'Update');
    });

    // -----------------------------------------------------------------------------
    // SECTION 5: THIẾT LẬP FORM AJAX CHUNG
    // -----------------------------------------------------------------------------

    /**
     * Thiết lập xử lý AJAX cho một form cụ thể.
     * @param {string} formId - ID của form (ví dụ: 'createPromotionForm').
     * @param {string} modalId - ID của modal chứa form (ví dụ: 'createPromotionModal').
     * @param {function} successCallback - Hàm callback khi form gửi thành công.
     * @param {string} method - Phương thức HTTP ('POST', 'PUT', 'DELETE').
     */
    function setupAjaxForm(formId, modalId, successCallback, method = 'POST') {
        const form = document.getElementById(formId);
        const modalEl = document.getElementById(modalId);
        const modalInstance = bootstrap.Modal.getInstance(modalEl); // Lấy instance của modal

        if (!form || !modalEl || !modalInstance) {
            console.error(`Không thể thiết lập AJAX form: Form ID "${formId}" hoặc Modal ID "${modalId}" không tồn tại.`);
            return;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault(); // Ngăn chặn hành vi submit mặc định
            showAppLoader(); // Hiển thị loader

            clearValidationErrors(form); // Xóa lỗi cũ trước mỗi lần submit

            const formData = new FormData(form);

            // Xử lý đặc biệt cho các trường số tiền: parse từ định dạng VNĐ về số gốc
            form.querySelectorAll('[data-currency-input="true"]').forEach(input => {
                if (formData.has(input.name)) {
                    formData.set(input.name, parseFormattedCurrency(input.value));
                }
            });


            // Thêm _method cho PUT/DELETE nếu cần (được xử lý bởi Laravel)
            if (method === 'PUT' || method === 'DELETE') {
                formData.append('_method', method);
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST', // Luôn là POST với FormData, Laravel sẽ đọc _method
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json' // Báo server trả về JSON
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) { // Status code 2xx
                    showToast(result.message, 'success');
                    modalInstance.hide(); // Đóng modal sau khi thành công
                    successCallback(result.promotion); // Gọi callback thành công, truyền dữ liệu promotion mới/cập nhật
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
                hideAppLoader(); // Ẩn loader
            }
        });
    }

    // -----------------------------------------------------------------------------
    // SECTION 6: KHỞI TẠO VÀ ÁP DỤNG
    // -----------------------------------------------------------------------------

    // Hàm callback khi tạo/cập nhật thành công
    const reloadPageAfterSuccess = () => setTimeout(() => window.location.reload(), 1200);

    // Thiết lập AJAX cho form tạo mới
    setupAjaxForm('createPromotionForm', 'createPromotionModal', reloadPageAfterSuccess, 'POST');

    // Thiết lập AJAX cho form cập nhật
    setupAjaxForm('updatePromotionForm', 'updatePromotionModal', reloadPageAfterSuccess, 'POST');

    // Thiết lập AJAX cho form xóa
    // Lưu ý: Đối với form xóa, cần thêm input hidden cho mật khẩu xác nhận nếu config('admin.deletion_password') là true
    // và xử lý validation đó trong Laravel. Callback có thể khác (ví dụ, remove row từ DOM).
    // Hiện tại, giữ nguyên reloadPageAfterSuccess cho đơn giản.
    setupAjaxForm('deletePromotionForm', 'deletePromotionModal', reloadPageAfterSuccess, 'DELETE');


    // Áp dụng định dạng tiền tệ cho các input
    const currencyInputs = [
        createModalEl.querySelector('#promoMinOrderAmountCreate'),
        createModalEl.querySelector('#promoFixedDiscountAmountCreate'),
        createModalEl.querySelector('#promoMaxDiscountAmountCreate'),
        updateModalEl.querySelector('#promoMinOrderAmountUpdate'),
        updateModalEl.querySelector('#promoFixedDiscountAmountUpdate'),
        updateModalEl.querySelector('#promoMaxDiscountAmountUpdate'),
    ].filter(Boolean); // Lọc bỏ các element null nếu không tồn tại

    currencyInputs.forEach(input => {
        if (input) {
            input.setAttribute('data-currency-input', 'true'); // Đánh dấu để dễ tìm và xử lý
            formatCurrencyInput(input);
        }
    });


    // Khởi tạo trạng thái ban đầu cho các modal khi trang được tải
    if (createDiscountTypeSelect) {
        toggleDiscountInputs(createDiscountTypeSelect.value, 'Create');
    }
    // Đối với update modal, trạng thái ban đầu sẽ được set khi mở modal qua handleShowUpdateModal

    console.log("Module Quản lý Mã Khuyến Mãi đã được khởi tạo thành công.");
});