(function () {
    'use strict';

    /**
     * ===============================================================
     * A. CÁC HÀM TOÀN CỤC (GLOBAL HELPERS)
     * Các hàm này được định nghĩa ở phạm vi toàn cục (window)
     * để có thể gọi từ mọi nơi, bao gồm cả trang login.
     * ===============================================================
     */

    /**
     * A.1. Hiển thị Lớp phủ Tải (Loading Overlay)
     */
    window.showAppLoader = () => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('active');
        } else {
            console.warn('Không tìm thấy loading-overlay.');
        }
    };

    /**
     * A.2. Ẩn Lớp phủ Tải (Loading Overlay)
     */
    window.hideAppLoader = () => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        } else {
            console.warn('Không tìm thấy loading-overlay.');
        }
    };

    /**
     * A.3. Hiển thị Modal Thông Báo Chung
     */
    window.showAppInfoModal = function (message, type = 'info', title = 'Thông báo') {
        const modalElement = document.getElementById('appInfoModal');
        if (!modalElement) {
            console.error('Modal element #appInfoModal không tìm thấy!');
            const messageText = (typeof message === 'object' && message.html)
                ? `HTML Content (see console for details): ${title}`
                : (title !== 'Thông báo' ? `${title}: ${message}` : message);
            // Fallback for when modal element is not found. Avoid alert() in production.
            alert(messageText);
            if (typeof message === 'object' && message.html) {
                console.error("HTML content for missing modal:", message.html);
            }
            return;
        }

        const modalTitleElement = modalElement.querySelector('#appInfoModalLabel');
        const modalBodyElement = modalElement.querySelector('#appInfoModalBody');
        const modalHeaderElement = modalElement.querySelector('.modal-header');

        if (modalTitleElement) {
            modalTitleElement.textContent = title;
        }
        if (modalBodyElement) {
            if (typeof message === 'object' && message.html) {
                modalBodyElement.innerHTML = message.html;
            } else {
                modalBodyElement.textContent = String(message);
            }
        }

        if (modalHeaderElement) {
            const bgClasses = ['bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary'];
            const textClasses = ['text-white', 'text-dark'];
            modalHeaderElement.classList.remove(...bgClasses, ...textClasses);
            let headerBgClass = 'bg-primary';
            let headerTextClass = 'text-white';
            switch (type) {
                case 'success':
                    headerBgClass = 'bg-success';
                    break;
                case 'error':
                case 'validation_error':
                    headerBgClass = 'bg-danger';
                    break;
                case 'warning':
                    headerBgClass = 'bg-warning';
                    headerTextClass = 'text-dark';
                    break;
                case 'info':
                default:
                    headerBgClass = 'bg-info';
                    break;
            }
            modalHeaderElement.classList.add(headerBgClass, headerTextClass);
        }

        try {
            const appModalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            // Hide other open modals to avoid overlapping backdrops
            document.querySelectorAll('.modal').forEach(modalEl => {
                if (modalEl !== modalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }
            });
            appModalInstance.show();
        } catch (e) {
            console.error("Lỗi khi hiển thị Bootstrap modal (#appInfoModal):", e);
            const messageText = (typeof message === 'object' && message.html)
                ? `HTML Content (see console for details): ${title}`
                : (title !== 'Thông báo' ? `${title}: ${message}` : message);
            alert(messageText); // Fallback to alert if modal fails
            if (typeof message === 'object' && message.html) {
                console.error("HTML content for modal error:", message.html);
            }
        }
    };

    /**
     * A.4. Hiển thị lỗi validation trên một form bất kỳ.
     * Cập nhật để tìm thẻ `div` có `data-field` thay vì `.invalid-feedback`
     */
    window.displayValidationErrors = function (form, errors) {
        if (!form || !errors) {
            console.warn('Form hoặc errors không hợp lệ:', { form, errors });
            return;
        }
        // Clear existing errors first
        window.clearValidationErrors(form); // Use the global clearValidationErrors

        for (const field in errors) {
            // Tìm input field
            const inputField = form.querySelector(`[name="${field}"]`);
            if (inputField) {
                inputField.classList.add('is-invalid');
                let errorDiv = inputField.parentElement.querySelector(`[data-field="${field}"]`);
                if (!errorDiv) { // Nếu không có data-field, tạo invalid-feedback như fallback
                    errorDiv = document.createElement('div');
                    errorDiv.classList.add('invalid-feedback');
                    inputField.parentNode.insertBefore(errorDiv, inputField.nextSibling);
                }
                errorDiv.textContent = errors[field][0];
            } else {
                // Xử lý lỗi cho các trường không có input trực tiếp hoặc là mảng (ví dụ: items.*.product_id)
                // Tìm div với data-field tương ứng
                let errorContainer = form.querySelector(`[data-field="${field}"]`);
                if (errorContainer) {
                    errorContainer.textContent = errors[field][0];
                } else {
                    // Nếu không tìm thấy data-field, kiểm tra các trường con trong mảng 'items'
                    // Đây là logic cụ thể cho items.*
                    if (field.startsWith('items.') && (field.endsWith('.product_id') || field.endsWith('.quantity') || field.endsWith('.price'))) {
                        const parts = field.split('.'); // items.0.product_id
                        if (parts.length === 3) {
                            const itemIndex = parts[1];
                            const itemField = parts[2];
                            // Tìm hàng sản phẩm tương ứng và div lỗi cụ thể
                            const itemRow = form.querySelector(`.product-item-row[data-item-id="${itemIndex}"]`) ||
                                form.querySelectorAll('.product-item-row')[itemIndex]; // Fallback for dynamically added rows without specific data-item-id for old inputs
                            if (itemRow) {
                                let specificErrorDiv;
                                if (itemField === 'product_id') {
                                    specificErrorDiv = itemRow.querySelector('.product-id-error');
                                } else if (itemField === 'quantity') {
                                    specificErrorDiv = itemRow.querySelector('.quantity-error');
                                }
                                if (specificErrorDiv) {
                                    specificErrorDiv.textContent = errors[field][0];
                                }
                            }
                        }
                    } else {
                        // Fallback chung nếu không tìm thấy input hoặc data-field cụ thể
                        console.warn(`Không tìm thấy input hoặc container lỗi cho trường: ${field}. Lỗi: ${errors[field][0]}`);
                        const generalErrorContainer = form.querySelector('#product_stock_error_create'); // Example for create order items error
                        if (generalErrorContainer) {
                            generalErrorContainer.textContent = errors[field][0];
                        }
                    }
                }
            }
        }
    };

    /**
     * A.5. Xóa tất cả các lỗi validation hiển thị trên một form.
     * Cập nhật để xóa lỗi từ `data-field` và `.invalid-feedback`
     */
    window.clearValidationErrors = function (formElement) {
        const errorMessages = formElement.querySelectorAll('[data-field], .invalid-feedback, .product-id-error, .quantity-error');
        errorMessages.forEach(el => el.textContent = '');

        const invalidInputs = formElement.querySelectorAll('.is-invalid');
        invalidInputs.forEach(el => el.classList.remove('is-invalid'));

        // Clear general error containers if they exist (e.g., for product stock)
        const generalErrorContainer = formElement.querySelector('#product_stock_error_create');
        if (generalErrorContainer) {
            generalErrorContainer.textContent = '';
        }
    };


    /**
      * A.6. Gắn sự kiện submit AJAX cho một form.
      * === PHIÊN BẢN SỬA LỖI HOÀN CHỈNH ===
      * Logic này giờ sử dụng phương thức POST cho tất cả các request,
      * và dựa vào trường ẩn `_method` (ví dụ: <input name="_method" value="DELETE">)
      * để server Laravel có thể định tuyến chính xác.
      * Điều này đảm bảo FormData được gửi đi một cách nhất quán và đáng tin cậy.
      */
    window.setupAjaxForm = function (formId, modalId = null, successCallback = null, errorCallback = null) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Không tìm thấy form với ID: ${formId}`);
            return;
        }

        // Loại bỏ listener cũ để tránh trùng lặp nếu setupAjaxForm được gọi nhiều lần trên cùng một form
        const oldListener = form.dataset.formSubmitListener;
        if (oldListener) {
            form.removeEventListener('submit', eval(oldListener)); // Cẩn thận với eval, nhưng trong ngữ cảnh này là ok
            delete form.dataset.formSubmitListener;
        }

        const newListener = async function (e) {
            e.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            const spinner = submitButton ? submitButton.querySelector('.spinner-border') : null;

            if (submitButton) {
                // Thêm spinner nếu chưa có
                if (!spinner) {
                    const newSpinner = document.createElement('span');
                    newSpinner.classList.add('spinner-border', 'spinner-border-sm', 'ms-2', 'd-none');
                    newSpinner.setAttribute('role', 'status');
                    newSpinner.setAttribute('aria-hidden', 'true');
                    submitButton.appendChild(newSpinner);
                    spinner = newSpinner;
                }
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
            }
            window.showAppLoader(); // Use global helper

            try {
                const formData = new FormData(form);

                // **THAY ĐỔI CỐT LÕI**: Luôn gửi bằng POST. Laravel sẽ tự xử lý
                // các phương thức PUT/PATCH/DELETE thông qua trường `_method` có trong formData.
                const response = await fetch(form.action, {
                    method: 'POST', // Luôn sử dụng POST
                    body: formData,   // FormData sẽ chứa _method nếu có
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        window.displayValidationErrors(form, result.errors); // Use global helper
                        window.showAppInfoModal(result.message || 'Vui lòng kiểm tra lại dữ liệu.', 'validation_error', 'Lỗi Nhập liệu'); // Use global helper
                    } else {
                        throw new Error(result.message || 'Có lỗi không xác định.');
                    }
                    if (errorCallback) errorCallback(result);
                    return;
                }

                if (modalId) {
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    }
                }

                // Chỉ reset form nếu không phải là form tìm kiếm hoặc filter
                if (!form.classList.contains('form-search') && !form.classList.contains('form-filter')) {
                    form.reset();
                    // Clear validation errors after successful reset
                    window.clearValidationErrors(form);
                }

                window.showAppInfoModal(result.message, 'success', 'Thành công'); // Use global helper

                if (successCallback) {
                    successCallback(result);
                } else {
                    // Nếu không có callback thành công cụ thể, thực hiện tải lại trang
                    // để đảm bảo dữ liệu luôn được cập nhật.
                    setTimeout(() => {
                        if (typeof Turbo !== 'undefined') {
                            Turbo.visit(window.location.href, { action: 'replace' });
                        } else {
                            window.location.reload();
                        }
                    }, 1200);
                }

            } catch (error) {
                console.error(`Lỗi khi submit form ${formId}:`, error);
                window.showAppInfoModal(error.message, 'error', 'Lỗi Hệ thống'); // Use global helper
                if (errorCallback) errorCallback(error);
            } finally {
                if (submitButton) submitButton.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                window.hideAppLoader(); // Use global helper
            }
        };

        form.addEventListener('submit', newListener);
        form.dataset.formSubmitListener = `(${newListener.toString()})`; // Lưu trữ reference
    };

    /**
     * A.7. Thiết lập xem trước ảnh (Image Preview) cho input file
     */
    window.setupImagePreviews = (inputEl, previewContainerEl) => {
        if (!inputEl || !previewContainerEl) {
            console.warn('Input hoặc container xem trước ảnh không hợp lệ.');
            return;
        }
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

    /**
     * ===============================================================
     * B. CÁC MODULE CHỨC NĂNG CỤ THỂ
     * ===============================================================
     */

    /**
     * B.1. Khởi tạo và quản lý chức năng của Sidebar.
     */
    function initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCloseButton = document.getElementById('sidebarCloseButton');
        const mainContent = document.querySelector('.main-content');

        function openSidebar() {
            sidebar.classList.add('active');
            if (mainContent) mainContent.classList.add('sidebar-active');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            if (mainContent) mainContent.classList.remove('sidebar-active');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                if (!sidebar.classList.contains('active')) {
                    openSidebar();
                }
            });
        }

        if (sidebarCloseButton) {
            sidebarCloseButton.addEventListener('click', (e) => {
                e.stopPropagation();
                closeSidebar();
            });
        }

        document.addEventListener('click', function (event) {
            if (sidebar.classList.contains('active') && window.innerWidth < 992) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggler = sidebarToggle ? sidebarToggle.contains(event.target) : false;
                if (!isClickInsideSidebar && !isClickOnToggler) {
                    closeSidebar();
                }
            }
        });

        // Quản lý submenu: Đóng submenu khác khi mở một submenu
        const allSubmenus = sidebar.querySelectorAll('ul.collapse');
        if (allSubmenus.length > 0) {
            allSubmenus.forEach(submenu => {
                const parentLink = submenu.closest('.nav-item').querySelector('.nav-link[data-bs-toggle="collapse"]');
                if (parentLink) {
                    parentLink.addEventListener('click', function (e) {
                        const isExpanded = parentLink.getAttribute('aria-expanded') === 'true';
                        if (!isExpanded) {
                            // Đóng tất cả các submenu khác trước khi mở submenu này
                            allSubmenus.forEach(otherSubmenu => {
                                if (otherSubmenu !== submenu) {
                                    const bsCollapseInstance = bootstrap.Collapse.getInstance(otherSubmenu) || new bootstrap.Collapse(otherSubmenu, { toggle: false });
                                    if (otherSubmenu.classList.contains('show')) {
                                        bsCollapseInstance.hide();
                                    }
                                }
                            });
                        }
                    });
                }

                // Ngăn sự kiện show.bs.collapse lặp lại không cần thiết
                submenu.addEventListener('show.bs.collapse', function (e) {
                    const currentOpeningSubmenu = this;
                    allSubmenus.forEach(otherSubmenu => {
                        if (otherSubmenu !== currentOpeningSubmenu) {
                            const bsCollapseInstance = bootstrap.Collapse.getInstance(otherSubmenu) || new bootstrap.Collapse(otherSubmenu, { toggle: false });
                            if (otherSubmenu.classList.contains('show')) {
                                bsCollapseInstance.hide();
                            }
                        }
                    });
                });
            });
        }
    }

    /**
     * B.2. Lấy và hiển thị số lượng thông báo chưa đọc.
     */


    /**
     * B.3. Khởi tạo chức năng Xem trước ảnh (Image Preview).
     * Hàm này được gọi bởi runPageSpecificInitializers
     * Nó cấu hình các input file để hiển thị ảnh preview.
     */
    function initializeImagePreviews() {
        const imagePreviewPairs = [
            // Các cặp input và preview ID (ví dụ: inputId: 'myInput', previewId: 'myPreviewImg')
            { inputId: 'vbLogoCreate', previewId: 'vbLogoPreviewCreate' },
            { inputId: 'vbLogoUpdate', previewId: 'vbLogoPreviewUpdate' },
            { inputId: 'categoryLogoCreate', previewId: 'categoryLogoPreviewCreate' },
            { inputId: 'categoryLogoUpdate', previewId: 'categoryLogoPreviewUpdate' },
            { inputId: 'productImagesCreate', previewId: 'productImagesPreviewCreate' },
            { inputId: 'productImagesUpdate', previewId: 'productImagesPreviewUpdate' },
            { inputId: 'adminAvatarInput', previewId: 'adminAvatarPreview' },
            { inputId: 'dsLogoCreate', previewId: 'dsLogoPreviewCreate' },
            { inputId: 'dsLogoUpdate', previewId: 'dsLogoPreviewUpdate' },
            { inputId: 'staffAvatarCreate', previewId: 'staffAvatarPreviewCreate' },
            { inputId: 'staffAvatarUpdate', previewId: 'staffAvatarPreviewUpdate' },
            { inputId: 'customerAvatarCreate', previewId: 'customerAvatarPreviewCreate' },
            { inputId: 'customerAvatarUpdate', previewId: 'customerAvatarPreviewUpdate' },
        ];

        function setupPreview(inputId, previewId) {
            const inputElement = document.getElementById(inputId);
            const previewElement = document.getElementById(previewId);

            if (inputElement && previewElement) {
                const defaultSrc = previewElement.dataset.defaultSrc || previewElement.src;
                if (!previewElement.dataset.defaultSrc && defaultSrc) {
                    previewElement.dataset.defaultSrc = defaultSrc;
                }

                inputElement.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const objectURL = URL.createObjectURL(file);
                        previewElement.src = objectURL;
                        previewElement.onload = () => URL.revokeObjectURL(objectURL);
                        previewElement.onerror = () => {
                            console.error(`[ImagePreview] Lỗi tải ảnh cho ${previewId}`);
                            previewElement.src = defaultSrc; // Quay lại ảnh mặc định
                        };
                    } else {
                        previewElement.src = defaultSrc; // Quay lại ảnh mặc định
                    }
                });
            }
        }
        imagePreviewPairs.forEach(pair => setupPreview(pair.inputId, pair.previewId));
    }


    /**
     * ===============================================================
     * C. HÀM KHỞI TẠO VÀ ĐIỀU PHỐI CHUNG
     * ===============================================================
     */

    /**
     * Hàm này khởi tạo các thành phần CHỈ CÓ TRÊN LAYOUT ADMIN.
     */
    function initializeAdminLayoutComponents() {
        initializeSidebar();
    }

    /**
     * Hàm này điều phối việc gọi các script của TỪNG TRANG CỤ THỂ.
     * Các hàm khởi tạo trang cụ thể (ví dụ: initializeCustomersPage)
     * phải được định nghĩa toàn cục hoặc trong phạm vi mà hàm này có thể truy cập.
     */
    function runPageSpecificInitializers() {
        initializeImagePreviews(); // Cấu hình preview ảnh cho tất cả các trang

        // Các hàm khởi tạo cho từng trang cụ thể
        if (typeof initializeDashboardChart === 'function' && document.querySelector('.dashboard-page-identifier')) {
            initializeDashboardChart();
        }
        if (typeof initializeBlogsPage === 'function' && document.getElementById('adminBlogsPage')) {
            initializeBlogsPage();
        }
        // MODIFIED: Pass global utility functions to initializeOrderManager
        if (typeof initializeOrderManager === 'function' && document.getElementById('adminOrdersPage')) {
            initializeOrderManager(
                window.showAppLoader,
                window.hideAppLoader,
                window.showAppInfoModal,
                window.setupAjaxForm,
                window.displayValidationErrors,
                window.clearValidationErrors
            );
        }

        if (typeof initializePromotionsPage === 'function' && document.getElementById('adminPromotionsPage')) {
            initializePromotionsPage();
        }
        if (typeof initializeProductsPage === 'function' && document.getElementById('adminProductsPage')) {
            initializeProductsPage();
        }
        if (typeof initializeProfilePage === 'function' && document.getElementById('adminProfilePage')) {
            initializeProfilePage();
        }
        if (typeof initializeCategoriesPage === 'function' && document.getElementById('adminCategoriesPage')) {
            initializeCategoriesPage();
        }
        if (typeof initializeBrandsPage === 'function' && document.getElementById('adminBrandsPage')) {
            // Có thể truyền tham số nếu cần thiết
            const hasValidationErrors = window.brandValidationErrors || false;
            const formMarker = window.brandFormMarker || null;
            initializeBrandsPage(hasValidationErrors, formMarker);
        }
        if (document.getElementById('adminVehicleManagementPage') && typeof initializeVehicleManagementPage === 'function') {
            initializeVehicleManagementPage();
        }
        if (document.getElementById('adminDeliveryServicesPage') && typeof initializeDeliveryServicesPage === 'function') {
            initializeDeliveryServicesPage();
        }
        if (typeof initializeStaffsPage === 'function' && document.getElementById('adminStaffsPage')) {
            initializeStaffsPage();
        }
        if (typeof initializeCustomersPage === 'function' && document.getElementById('adminCustomersPage')) {
            initializeCustomersPage();
        }
        if (typeof initializeGeographyPage === 'function' && document.getElementById('adminGeographyPage')) {
            initializeGeographyPage();
        }
    }

    /**
     * ===============================================================
     * D. ĐIỂM BẮT ĐẦU THỰC THI (ĐÃ SỬA LỖI)
     * ===============================================================
     */

    // Hàm này chứa tất cả logic khởi tạo chính của ứng dụng
    function initializeApp() {
        console.log("App initializing...");
        window.showAppLoader(); // Hiển thị loader ngay khi bắt đầu

        // Khởi tạo các thành phần layout admin (sidebar, notifications)
        if (document.getElementById('sidebar')) {
            initializeAdminLayoutComponents();
        }

        // Chạy các script cụ thể cho từng trang
        runPageSpecificInitializers();

        // Xử lý logic đặc biệt cho trang login nếu có
        if (typeof window.initializeLoginPage === 'function' && document.getElementById('loginForm')) {
            window.initializeLoginPage();
        }

        window.hideAppLoader(); // Ẩn loader khi tất cả đã tải xong
        console.log("App initialization complete.");
    }

    // Lắng nghe cả hai sự kiện để đảm bảo khởi tạo đúng lúc:
    // 1. 'turbo:load': Được kích hoạt bởi Turbo (nếu bạn đang sử dụng Hotwired Laravel Turbo Laravel)
    //    cho các lần điều hướng trang mềm mại (SPA-like).
    // 2. 'DOMContentLoaded': Được kích hoạt khi DOM đã tải đầy đủ cho lần tải trang đầu tiên
    //    hoặc khi không sử dụng Turbo.
    document.addEventListener('turbo:load', initializeApp);
    document.addEventListener('DOMContentLoaded', initializeApp);

})();