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
            // Đóng tất cả modal khác trước khi mở modal mới
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
            alert(messageText);
            if (typeof message === 'object' && message.html) {
                console.error("HTML content for modal error:", message.html);
            }
        }
    };

    /**
     * A.4. Hiển thị lỗi validation trên một form bất kỳ.
     */
    window.displayValidationErrors = function (form, errors) {
        if (!form || !errors) {
            console.warn('Form hoặc errors không hợp lệ:', { form, errors });
            return;
        }
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        for (const field in errors) {
            const inputField = form.querySelector(`[name="${field}"]`);
            if (inputField) {
                inputField.classList.add('is-invalid');
                const errorDiv = inputField.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.textContent = errors[field][0];
                }
            }
        }

        // Làm mới selectpicker sau khi hiển thị lỗi
        try {
            $(form).find('.selectpicker').selectpicker('refresh');
        } catch (error) {
            console.error('Lỗi khi làm mới selectpicker sau validation:', error);
        }
    };

    /**
     * A.5. Gắn sự kiện submit AJAX cho một form.
     */
    window.setupAjaxForm = function (formId, modalId = null, successCallback = null, errorCallback = null) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Không tìm thấy form với ID: ${formId}`);
            return;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            window.showAppLoader();
            try {
                const method = form.querySelector('input[name="_method"]')?.value || form.method;
                const response = await fetch(form.action, {
                    method: method.toUpperCase() === 'POST' ? 'POST' : method.toUpperCase(),
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        window.displayValidationErrors(form, result.errors);
                        window.showAppInfoModal('Vui lòng kiểm tra lại dữ liệu.', 'validation_error', 'Lỗi Nhập liệu');
                    } else {
                        throw new Error(result.message || 'Có lỗi không xác định.');
                    }
                    if (errorCallback) errorCallback(result);
                    return;
                }

                if (modalId) {
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                form.reset();
                // Làm mới selectpicker sau khi reset form
                try {
                    $(form).find('.selectpicker').selectpicker('val', '').selectpicker('refresh');
                } catch (error) {
                    console.error('Lỗi khi làm mới selectpicker sau reset form:', error);
                }

                window.showAppInfoModal(result.message, 'success', 'Thành công');

                if (successCallback) {
                    successCallback(result);
                } else {
                    setTimeout(() => window.location.reload(), 1200);
                }
            } catch (error) {
                console.error(`Lỗi khi submit form ${formId}:`, error);
                window.showAppInfoModal(error.message, 'error', 'Lỗi Hệ thống');
                if (errorCallback) errorCallback(error);
            } finally {
                window.hideAppLoader();
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

        const allSubmenus = sidebar.querySelectorAll('ul.collapse');
        if (allSubmenus.length > 0) {
            allSubmenus.forEach(submenu => {
                submenu.addEventListener('show.bs.collapse', function () {
                    const currentOpeningSubmenu = this;
                    allSubmenus.forEach(otherSubmenu => {
                        if (otherSubmenu !== currentOpeningSubmenu) {
                            const bsCollapseInstance = bootstrap.Collapse.getInstance(otherSubmenu);
                            if (bsCollapseInstance && otherSubmenu.classList.contains('show')) {
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
    let notificationInterval;
    async function fetchNotificationCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            if (!response.ok) return;
            const data = await response.json();
            const badge = document.getElementById('notification-badge-count');
            if (badge) {
                badge.textContent = data.count;
                badge.classList.toggle('d-none', data.count <= 0);
            }
        } catch (error) {
            console.error('Lỗi khi lấy số lượng thông báo:', error);
        }
    }
    function initializeNotifications() {
        if (!document.getElementById('notification-badge-count')) return;
        fetchNotificationCount();
        if (notificationInterval) clearInterval(notificationInterval);
        notificationInterval = setInterval(fetchNotificationCount, 30000);
    }

    /**
     * B.3. Khởi tạo chức năng Xem trước ảnh (Image Preview).
     */
    function initializeImagePreviews() {
        const imagePreviewPairs = [
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
                            previewElement.src = defaultSrc;
                        };
                    } else {
                        previewElement.src = defaultSrc;
                    }
                });
            }
        }
        imagePreviewPairs.forEach(pair => setupPreview(pair.inputId, pair.previewId));
    }

    /**
     * ===============================================================
     * C. HÀM KHỞI TẠO VÀ ĐIỀU PHỐI
     * ===============================================================
     */

    /**
     * Hàm này khởi tạo các thành phần CHỈ CÓ TRÊN LAYOUT ADMIN.
     */
    function initializeAdminLayoutComponents() {
        initializeSidebar();
        initializeNotifications();
    }

    /**
     * Hàm này điều phối việc gọi các script của TỪNG TRANG CỤ THỂ.
     */
    function runPageSpecificInitializers() {
        initializeImagePreviews(); // An toàn để chạy trên mọi trang

        if (typeof initializeDashboardChart === 'function' && document.querySelector('.dashboard-page-identifier')) {
            initializeDashboardChart();
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
     * D. ĐIỂM BẮT ĐẦU THỰC THI
     * ===============================================================
     */
    document.addEventListener('DOMContentLoaded', () => {
        window.showAppLoader();

        // Kiểm tra sự tồn tại của #sidebar để quyết định có phải là trang admin không
        if (document.getElementById('sidebar')) {
            initializeAdminLayoutComponents();
        }

        // Luôn chạy các hàm khởi tạo cho từng trang cụ thể
        runPageSpecificInitializers();

        // Khởi tạo cho trang login nếu tìm thấy login form
        if (typeof window.initializeLoginPage === 'function' && document.getElementById('loginForm')) {
            window.initializeLoginPage();
        }

        // Làm mới tất cả selectpicker sau khi DOM sẵn sàng
        setTimeout(() => {
            try {
                $('.selectpicker').selectpicker('refresh');
            } catch (error) {
                console.error('Lỗi khi làm mới selectpicker:', error);
            }
            window.hideAppLoader();
        }, 100);
    });

})();