/**
 * ===================================================================
 * admin_layout.js (Phiên bản không sử dụng Turbo)
 *
 * File lõi, chứa các mã JavaScript chung cho toàn bộ layout admin.
 * - Khởi tạo Sidebar, Image Preview và các thành phần chung.
 * - Điều phối các script của từng trang để chạy khi trang được tải.
 *
 * Cập nhật: 28/05/2025
 * ===================================================================
 */

// --- HÀM TIỆN ÍCH CHUNG ---
/**
 * Gỡ bỏ và gắn lại một event listener cho một element.
 * Hữu ích để tránh việc gắn nhiều listener vào cùng một element sau các thao tác DOM.
 * @param {Element} element - Element cần gắn sự kiện.
 * @param {string} eventType - Loại sự kiện (ví dụ: 'click').
 * @param {Function} handler - Hàm xử lý sự kiện.
 * @returns {Element} - Element mới đã được gắn sự kiện.
 */
function rebindEventListener(element, eventType, handler) {
    if (!element) return;
    // Thay thế element cũ bằng một bản sao để gỡ bỏ mọi listener cũ
    const newElement = element.cloneNode(true);
    element.parentNode.replaceChild(newElement, element);
    newElement.addEventListener(eventType, handler);
    return newElement;
}


// --- CÁC MODULE CHỨC NĂNG CHUNG ---

// 1. Chức năng Sidebar
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        // Sử dụng rebind để đảm bảo listener luôn mới
        rebindEventListener(sidebarToggle, 'click', () => sidebar.classList.toggle('active'));
    }

    const collapsibleLinks = sidebar.querySelectorAll('a.nav-link[data-bs-toggle="collapse"]');
    collapsibleLinks.forEach(link => {
        rebindEventListener(link, 'click', function () {
            const currentTargetId = this.getAttribute('href');
            // Đóng các submenu khác khi mở một submenu mới
            collapsibleLinks.forEach(otherLink => {
                const otherTargetId = otherLink.getAttribute('href');
                if (otherTargetId !== currentTargetId) {
                    const otherSubmenu = document.querySelector(otherTargetId);
                    if (otherSubmenu && otherSubmenu.classList.contains('show')) {
                        const bsCollapse = window.Collapse.getInstance(otherSubmenu) || new window.Collapse(otherSubmenu, { toggle: false });
                        bsCollapse.hide();
                    }
                }
            });
        });
    });
    console.log("Layout JS: Sidebar đã khởi tạo.");
}

// 2. Lấy số thông báo chưa đọc
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
        console.error('Lỗi API thông báo:', error);
    }
}

function initializeNotifications() {
    fetchNotificationCount();
    // Xóa interval cũ trước khi tạo cái mới để tránh rò rỉ bộ nhớ
    if (notificationInterval) clearInterval(notificationInterval);
    notificationInterval = setInterval(fetchNotificationCount, 30000); // Lấy lại sau mỗi 30s
    console.log("Layout JS: Thông báo đã khởi tạo.");
}

// 3. Khởi tạo chức năng Xem trước ảnh (Image Preview)
function initializeImagePreviews() {
    console.log("Layout JS: Thiết lập các trình xem trước ảnh...");
    // Vì bạn đang làm đồ án về xe đồ chơi, bạn có thể thêm các ID cho sản phẩm, phụ kiện...
    const imagePreviewPairs = [
        // Brand
        ['brandLogoCreate', 'brandLogoPreviewCreate'],
        ['brandLogoUpdate', 'brandLogoPreviewUpdate'],
        // Category
        ['categoryLogoCreate', 'categoryLogoPreviewCreate'],
        ['categoryLogoUpdate', 'categoryLogoPreviewUpdate'],
        // Product
        ['productImageCreate', 'productImagePreviewCreate'],
        ['productImageUpdate', 'productImagePreviewUpdate'],
        // Thêm các cặp [inputId, previewId] khác của bạn ở đây
    ];

    function setupPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (input && preview) {
            // Không cần `rebindEventListener` ở đây vì `runPageSpecificInitializers` chỉ chạy một lần
            input.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    preview.src = URL.createObjectURL(file);
                }
            });
        }
    }
    imagePreviewPairs.forEach(pair => setupPreview(pair[0], pair[1]));
}


// --- HÀM KHỞI TẠO TỔNG HỢP ---
function initializeCommonComponents() {
    initializeSidebar();
    initializeNotifications();
}

// --- ORCHESTRATOR (Hàm điều phối) ---
// Tìm và chạy các hàm khởi tạo dành riêng cho từng trang
function runPageSpecificInitializers() {
    console.log("Layout JS: Tìm và chạy các hàm khởi tạo của trang...");

    // Luôn chạy hàm khởi tạo image preview vì nó có thể xuất hiện ở nhiều trang
    initializeImagePreviews();

    // Gọi các hàm của từng trang nếu chúng tồn tại
    // Lưu ý: Các hàm này được định nghĩa trong các file JS riêng (ví dụ: brand_manager.js)
    if (typeof initializeDashboardChart === 'function') initializeDashboardChart();
    if (typeof initializeBrandsPage === 'function') initializeBrandsPage();
    if (typeof initializeCategoriesPage === 'function') initializeCategoriesPage();
    if (typeof initializeGeographyPage === 'function') initializeGeographyPage();
    // if (typeof initializeProductsPage === 'function') initializeProductsPage(); // Bạn có thể thêm file cho trang sản phẩm
}


// --- XỬ LÝ SỰ KIỆN TẢI TRANG ---
const showLoading = () => document.getElementById('loading-overlay')?.classList.add('active');
const hideLoading = () => document.getElementById('loading-overlay')?.classList.remove('active');

// Chỉ chạy tất cả các mã JavaScript sau khi toàn bộ cây DOM đã được tải
document.addEventListener('DOMContentLoaded', () => {
    console.log("Layout JS (DOMContentLoaded): Khởi tạo trang.");
    showLoading();

    // Khởi tạo các thành phần chung của layout (sidebar, notifications)
    initializeCommonComponents();

    // Khởi tạo các thành phần riêng của từng trang (biểu đồ, modal, ...)
    runPageSpecificInitializers();

    hideLoading();
    console.log("Layout JS: Khởi tạo hoàn tất.");
});