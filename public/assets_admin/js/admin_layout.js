/**
 * ===================================================================
 * admin_layout.js
 *
 * File lõi, chứa các mã JavaScript chung cho toàn bộ layout admin.
 * - Khởi tạo Sidebar, Image Preview và các thành phần chung.
 * - Điều phối các script của từng trang để chạy khi trang được tải.
 *
 * Cập nhật: 05/06/2025 (Làm rõ logic sidebar, giữ nguyên các chức năng khác)
 * ===================================================================
 */

// --- CÁC MODULE CHỨC NĂNG CHUNG ---

/**
 * 1. Khởi tạo và quản lý chức năng của Sidebar.
 * Bao gồm việc mở/đóng sidebar chính và xử lý hành vi của các submenu (accordion).
 */
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) {
        console.warn("Layout JS: Element #sidebar không tìm thấy. Chức năng sidebar sẽ không hoạt động.");
        return;
    }

    const sidebarToggle = document.getElementById('sidebarToggle'); // Nút hamburger ở topnav
    const sidebarCloseButton = document.getElementById('sidebarCloseButton'); // Nút X trong sidebar (cho mobile)
    const mainContent = document.querySelector('.main-content'); // Phần content chính

    function openSidebar() {
        sidebar.classList.add('active');
        if (mainContent) mainContent.classList.add('sidebar-active'); // Thêm class để content có thể điều chỉnh (ví dụ: overlay)
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        if (mainContent) mainContent.classList.remove('sidebar-active');
    }

    // Xử lý click nút hamburger để mở sidebar (thường trên desktop khi sidebar thu gọn, hoặc mobile)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Ngăn sự kiện click lan ra document
            // Nếu sidebar đang không active, hoặc đang active nhưng không phải do toggle này (ví dụ: trên mobile), thì mở nó
            if (!sidebar.classList.contains('active')) {
                openSidebar();
            } else {
                // Nếu muốn nút này cũng có thể đóng sidebar khi nó đang mở, thêm logic vào đây.
                // Hiện tại, chỉ có nút X và click ngoài (trên mobile) đóng sidebar.
            }
        });
    }

    // Xử lý click nút X để đóng sidebar (thường chỉ hiển thị trên mobile)
    if (sidebarCloseButton) {
        sidebarCloseButton.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    // Xử lý click ra ngoài sidebar để đóng nó (chỉ trên màn hình nhỏ/mobile)
    document.addEventListener('click', function (event) {
        // Chỉ hoạt động khi sidebar đang mở và kích thước màn hình nhỏ (theo breakpoint của Bootstrap là < 992px)
        if (sidebar.classList.contains('active') && window.innerWidth < 992) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggler = sidebarToggle ? sidebarToggle.contains(event.target) : false;

            if (!isClickInsideSidebar && !isClickOnToggler) {
                closeSidebar();
            }
        }
    });

    // Xử lý hành vi accordion cho các submenu (ul.collapse)
    // Mục tiêu: Khi một submenu được mở, các submenu khác đang mở sẽ tự động đóng lại.
    const allSubmenus = sidebar.querySelectorAll('ul.collapse');

    if (allSubmenus.length > 0) {
        allSubmenus.forEach(submenu => {
            // Sự kiện 'show.bs.collapse' của Bootstrap được kích hoạt ngay khi phương thức 'show' (mở submenu) được gọi.
            submenu.addEventListener('show.bs.collapse', function () {
                // 'this' tham chiếu đến 'submenu' hiện tại đang trong quá trình được mở.
                const currentOpeningSubmenu = this;

                allSubmenus.forEach(otherSubmenu => {
                    // Chỉ xử lý các submenu khác với submenu đang được mở
                    if (otherSubmenu !== currentOpeningSubmenu) {
                        // Lấy instance của Bootstrap Collapse cho submenu khác này.
                        // Điều này cần thiết để có thể gọi phương thức .hide() của Bootstrap.
                        // Nếu `getInstance` trả về null, có thể submenu đó chưa được Bootstrap khởi tạo đúng cách.
                        const bsCollapseInstance = bootstrap.Collapse.getInstance(otherSubmenu);

                        // Kiểm tra xem có instance không và submenu đó có đang thực sự hiển thị (có class 'show') không.
                        if (bsCollapseInstance && otherSubmenu.classList.contains('show')) {
                            // Nếu thỏa mãn, gọi phương thức hide() để đóng submenu này lại.
                            bsCollapseInstance.hide();
                        }
                    }
                });
            });
        });
    } else {
        // console.log("Layout JS: Không tìm thấy submenu (ul.collapse) nào trong sidebar.");
    }
    // console.log("Layout JS: Sidebar đã khởi tạo.");
}

/**
 * 2. Lấy và hiển thị số lượng thông báo chưa đọc.
 */
let notificationInterval; // Biến để lưu interval, cho phép clear nếu cần
async function fetchNotificationCount() {
    try {
        // Đảm bảo route API '/api/notifications/unread-count' tồn tại và trả về JSON dạng { count: number }
        const response = await fetch('/api/notifications/unread-count');
        if (!response.ok) {
            // console.error('Lỗi khi lấy số thông báo:', response.status, response.statusText);
            return;
        }
        const data = await response.json();
        const badge = document.getElementById('notification-badge-count'); // Element hiển thị số thông báo
        if (badge) {
            badge.textContent = data.count;
            badge.classList.toggle('d-none', data.count <= 0); // Ẩn badge nếu không có thông báo
        }
    } catch (error) {
        // console.error('Lỗi API thông báo:', error);
    }
}

function initializeNotifications() {
    fetchNotificationCount(); // Lấy lần đầu khi tải trang
    if (notificationInterval) clearInterval(notificationInterval); // Xóa interval cũ nếu có
    notificationInterval = setInterval(fetchNotificationCount, 30000); // Cập nhật mỗi 30 giây
    // console.log("Layout JS: Thông báo đã khởi tạo.");
}

/**
 * 3. Khởi tạo chức năng Xem trước ảnh (Image Preview) cho các input type="file".
 */
function initializeImagePreviews() {
    // Danh sách các cặp [ID của input file, ID của thẻ img để preview]
    const imagePreviewPairs = [
        ['vbLogoCreate', 'vbLogoPreviewCreate'], // Vehicle Brand Modals
        ['vbLogoUpdate', 'vbLogoPreviewUpdate'], // Vehicle Brand Modals
        ['categoryLogoCreate', 'categoryLogoPreviewCreate'], // Category Modals (ví dụ)
        ['categoryLogoUpdate', 'categoryLogoPreviewUpdate'], // Category Modals (ví dụ)
        ['productImageCreate', 'productImagePreviewCreate'], // Product form (ví dụ)
        ['adminAvatarInput', 'adminAvatarPreview'],         // Profile page
        // Thêm các cặp ID khác nếu cần
    ];

    function setupPreview(inputId, previewId) {
        const inputElement = document.getElementById(inputId);
        const previewElement = document.getElementById(previewId);

        if (inputElement && previewElement) {
            const defaultSrc = previewElement.src; // Lưu lại src mặc định của ảnh preview

            inputElement.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) { // Chỉ xử lý nếu là file ảnh
                    previewElement.src = URL.createObjectURL(file);
                    // Giải phóng bộ nhớ khi ảnh đã được tải
                    previewElement.onload = () => URL.revokeObjectURL(previewElement.src);
                } else {
                    // Nếu người dùng hủy chọn file hoặc chọn file không phải ảnh
                    if (event.target.value === '') { // Input file đã bị xóa (clear)
                        previewElement.src = defaultSrc; // Reset về ảnh mặc định
                    }
                    // Có thể thêm thông báo lỗi nếu file không hợp lệ
                }
            });
        }
    }
    imagePreviewPairs.forEach(pair => setupPreview(pair[0], pair[1]));
    // console.log("Layout JS: Image previews đã được cấu hình.");
}


// --- HÀM KHỞI TẠO TỔNG HỢP CHO CÁC THÀNH PHẦN CHUNG ---
function initializeCommonComponents() {
    initializeSidebar();
    initializeNotifications();
    // initializeImagePreviews() sẽ được gọi trong runPageSpecificInitializers
    // để đảm bảo các element của trang cụ thể đã có trong DOM khi nó chạy.
}

// --- ORCHESTRATOR (Hàm điều phối gọi các script của từng trang cụ thể) ---
// Hàm này sẽ gọi các hàm khởi tạo JS cho từng trang riêng biệt.
// Điều này giúp module hóa code và chỉ chạy JS cần thiết cho trang hiện tại.
function runPageSpecificInitializers() {
    // Gọi initializeImagePreviews ở đây để nó có thể tìm thấy các element
    // preview ảnh trên bất kỳ trang nào có sử dụng.
    initializeImagePreviews();

    // Kiểm tra sự tồn tại của hàm và element đặc trưng của trang trước khi gọi để tránh lỗi.
    if (typeof initializeDashboardChart === 'function' && document.querySelector('.dashboard-page-identifier')) { // Giả sử .dashboard-page-identifier là class/id đặc trưng của trang dashboard
        initializeDashboardChart();
    }
    if (typeof initializeProfilePage === 'function' && document.getElementById('adminProfilePage')) {
        initializeProfilePage();
    }
    if (typeof initializeCategoriesPage === 'function' && document.getElementById('adminCategoriesPage')) {
        initializeCategoriesPage();
    }

    // Gọi hàm khởi tạo cho trang quản lý xe hợp nhất (Hãng xe & Dòng xe)
    // Hàm initializeVehicleManagementPage() được định nghĩa trong file vehicle_manager_combined.js
    if (document.getElementById('adminVehicleManagementPage') && typeof initializeVehicleManagementPage === 'function') {
        initializeVehicleManagementPage();
    }

    if (typeof initializeGeographyPage === 'function' && document.getElementById('adminGeographyPage')) { // Giả sử ID trang địa lý
        initializeGeographyPage();
    }

    // Thêm các hàm khởi tạo cho các trang khác ở đây
    // Ví dụ:
    // if (typeof initializeProductsPage === 'function' && document.getElementById('adminProductsPage')) {
    //     initializeProductsPage();
    // }
    // console.log("Layout JS: Page specific initializers đã chạy.");
}


// --- XỬ LÝ SỰ KIỆN TẢI TRANG VÀ LOADING OVERLAY TOÀN CỤC ---

/**
 * Hiển thị loading overlay toàn cục.
 * Được gọi trước các tác vụ AJAX hoặc khi tải trang.
 */
window.showAppLoader = () => {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.classList.add('active');
    } else {
        // console.warn('Layout JS: Element #loading-overlay không tìm thấy để hiển thị.');
    }
};

/**
 * Ẩn loading overlay toàn cục.
 * Được gọi sau khi tác vụ AJAX hoàn thành hoặc trang đã tải xong.
 */
window.hideAppLoader = () => {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    } else {
        // console.warn('Layout JS: Element #loading-overlay không tìm thấy để ẩn.');
    }
};

// Sự kiện DOMContentLoaded đảm bảo rằng toàn bộ HTML đã được tải và phân tích cú pháp.
document.addEventListener('DOMContentLoaded', () => {
    // Hiển thị loader ngay khi DOM sẵn sàng, trước khi các script nặng chạy.
    if (typeof window.showAppLoader === 'function') {
        window.showAppLoader();
    }

    initializeCommonComponents(); // Khởi tạo các thành phần chung như sidebar, notifications.
    runPageSpecificInitializers(); // Chạy các hàm JS dành riêng cho trang hiện tại.

    // Ẩn loader sau khi mọi thứ đã khởi tạo xong.
    // Thêm một chút trễ nhỏ (50ms) để trình duyệt có thời gian render các thay đổi cuối cùng
    // và tránh việc loader biến mất quá nhanh gây cảm giác giật cục.
    setTimeout(() => {
        if (typeof window.hideAppLoader === 'function') {
            window.hideAppLoader();
        }
    }, 50);

    // console.log("Layout JS: DOMContentLoaded, Khởi tạo hoàn tất.");
});

/**
 * ===================================================================
 * Chức năng hiển thị Modal Thông Báo Chung (App Info Modal)
 * Dùng để hiển thị các thông báo thành công, lỗi, cảnh báo từ ứng dụng.
 * type: 'success', 'error', 'warning', 'info' (default)
 * ===================================================================
 */
window.showAppInfoModal = function (message, type = 'info', title = 'Thông báo') {
    const modalElement = document.getElementById('appInfoModal');
    if (!modalElement) {
        console.error('Modal element #appInfoModal không tìm thấy!');
        // Fallback nếu modal HTML không tồn tại trong DOM
        const messageText = (typeof message === 'object' && message.html) ?
            `HTML Content (see console for details): ${title}` :
            (title !== 'Thông báo' ? `${title}: ${message}` : message);
        alert(messageText);
        if (typeof message === 'object' && message.html) console.error("HTML content for missing modal:", message.html);
        return;
    }

    const modalTitleElement = modalElement.querySelector('#appInfoModalLabel');
    const modalBodyElement = modalElement.querySelector('#appInfoModalBody');
    const modalHeaderElement = modalElement.querySelector('.modal-header');

    if (modalTitleElement) {
        modalTitleElement.textContent = title;
    }

    if (modalBodyElement) {
        // Cho phép truyền HTML vào body của modal nếu 'message' là một object có key 'html'.
        // Cẩn thận với XSS nếu nội dung HTML không được kiểm soát.
        if (typeof message === 'object' && message.html) {
            modalBodyElement.innerHTML = message.html;
        } else {
            modalBodyElement.textContent = String(message); // Đảm bảo message luôn là string
        }
    }

    if (modalHeaderElement) {
        // Reset các class màu nền và màu chữ cũ của header
        const bgClasses = ['bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary'];
        const textClasses = ['text-white', 'text-dark']; // Có thể có nhiều class text hơn
        modalHeaderElement.classList.remove(...bgClasses, ...textClasses);

        let headerBgClass = 'bg-primary'; // Màu nền mặc định
        let headerTextClass = 'text-white'; // Màu chữ mặc định

        switch (type) {
            case 'success':
                headerBgClass = 'bg-success';
                break;
            case 'error':
            case 'validation_error': // Dùng chung kiểu 'error' cho lỗi validation từ AJAX
                headerBgClass = 'bg-danger';
                break;
            case 'warning':
                headerBgClass = 'bg-warning';
                headerTextClass = 'text-dark'; // Màu chữ tối cho nền vàng để dễ đọc hơn
                break;
            case 'info': // Có thể dùng màu 'bg-info' hoặc giữ 'bg-primary'
            default:
                headerBgClass = 'bg-info'; // Hoặc 'bg-primary'
                // headerTextClass giữ nguyên 'text-white' hoặc điều chỉnh nếu cần
                break;
        }
        modalHeaderElement.classList.add(headerBgClass, headerTextClass);
    }

    try {
        // Lấy instance đã có của modal hoặc tạo mới nếu chưa có.
        const appModalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        appModalInstance.show();
    } catch (e) {
        console.error("Lỗi khi hiển thị Bootstrap modal (#appInfoModal):", e);
        // Fallback alert nếu có lỗi với việc khởi tạo/hiển thị Bootstrap Modal
        const messageText = (typeof message === 'object' && message.html) ?
            `HTML Content (see console for details): ${title}` :
            (title !== 'Thông báo' ? `${title}: ${message}` : message);
        alert(messageText);
        if (typeof message === 'object' && message.html) console.error("HTML content for modal error:", message.html);
    }
};