/**
 * ===================================================================
 * admin_layout.js
 *
 * File lõi, chứa các mã JavaScript chung cho toàn bộ layout admin.
 * - Khởi tạo Sidebar, Image Preview và các thành phần chung.
 * - Điều phối các script của từng trang để chạy khi trang được tải.
 *
 * Cập nhật: 04/06/2025 (Hoàn thiện nút X trong sidebar và đóng khi click ngoài)
 * ===================================================================
 */

// --- CÁC MODULE CHỨC NĂNG CHUNG ---

// 1. Chức năng Sidebar
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) {
        // console.log("Layout JS: Element #sidebar không tìm thấy.");
        return;
    }

    const sidebarToggle = document.getElementById('sidebarToggle'); // Nút hamburger ở topnav
    const sidebarCloseButton = document.getElementById('sidebarCloseButton'); // Nút X mới trong sidebar
    const mainContent = document.querySelector('.main-content'); // Phần content chính

    // Hàm để mở sidebar
    function openSidebar() {
        sidebar.classList.add('active');
        if (mainContent) mainContent.classList.add('sidebar-active'); // Để làm mờ content nếu cần
    }

    // Hàm để đóng sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        if (mainContent) mainContent.classList.remove('sidebar-active');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Ngăn sự kiện click lan ra document
            // Nút hamburger ở topnav chỉ có nhiệm vụ MỞ sidebar trên mobile
            if (!sidebar.classList.contains('active')) {
                openSidebar();
            }
            // Nếu muốn nút này cũng có thể đóng sidebar (khi sidebar đang mở), thêm:
            // else { closeSidebar(); }
        });
    }

    if (sidebarCloseButton) {
        sidebarCloseButton.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    // Đóng sidebar khi click ra ngoài (chỉ trên mobile)
    document.addEventListener('click', function (event) {
        // Kiểm tra xem sidebar có active và màn hình có đủ nhỏ không (ví dụ: < 992px)
        if (sidebar.classList.contains('active') && window.innerWidth < 992) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            // Kiểm tra xem click có phải vào nút toggler ở topnav không
            const isClickOnToggler = sidebarToggle ? sidebarToggle.contains(event.target) : false;

            if (!isClickInsideSidebar && !isClickOnToggler) {
                closeSidebar();
            }
        }
    });


    // Xử lý tự động đóng các submenu khác khi một submenu được mở
    const allSubmenus = sidebar.querySelectorAll('ul.collapse');
    if (allSubmenus.length > 0) {
        allSubmenus.forEach(submenu => {
            submenu.addEventListener('show.bs.collapse', function () {
                allSubmenus.forEach(otherSubmenu => {
                    if (otherSubmenu !== this) {
                        const bsCollapseInstance = bootstrap.Collapse.getInstance(otherSubmenu);
                        if (bsCollapseInstance && otherSubmenu.classList.contains('show')) {
                            bsCollapseInstance.hide();
                        }
                    }
                });
            });
        });
    }
    // console.log("Layout JS: Sidebar đã khởi tạo.");
}

// 2. Lấy số thông báo chưa đọc
let notificationInterval;
async function fetchNotificationCount() {
    try {
        const response = await fetch('/api/notifications/unread-count');
        if (!response.ok) {
            // console.error('Lỗi khi lấy số thông báo:', response.status, response.statusText);
            return;
        }
        const data = await response.json();
        const badge = document.getElementById('notification-badge-count');
        if (badge) {
            badge.textContent = data.count;
            badge.classList.toggle('d-none', data.count <= 0);
        }
    } catch (error) {
        // console.error('Lỗi API thông báo:', error);
    }
}

function initializeNotifications() {
    fetchNotificationCount();
    if (notificationInterval) clearInterval(notificationInterval);
    notificationInterval = setInterval(fetchNotificationCount, 30000);
    // console.log("Layout JS: Thông báo đã khởi tạo.");
}

// 3. Khởi tạo chức năng Xem trước ảnh (Image Preview)
function initializeImagePreviews() {
    const imagePreviewPairs = [
        ['brandLogoCreate', 'brandLogoPreviewCreate'],
        ['brandLogoUpdate', 'brandLogoPreviewUpdate'],
        ['categoryLogoCreate', 'categoryLogoPreviewCreate'],
        ['categoryLogoUpdate', 'categoryLogoPreviewUpdate'],
        ['productImageCreate', 'productImagePreviewCreate'],
        ['productImageUpdate', 'productImagePreviewUpdate'],
        ['adminAvatarInput', 'adminAvatarPreview'],
    ];

    function setupPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (input && preview) {
            input.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    preview.src = URL.createObjectURL(file);
                    preview.onload = () => URL.revokeObjectURL(preview.src);
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
function runPageSpecificInitializers() {
    initializeImagePreviews();

    if (typeof initializeDashboardChart === 'function') initializeDashboardChart();
    if (typeof initializeBrandsPage === 'function') initializeBrandsPage();
    if (typeof initializeCategoriesPage === 'function') initializeCategoriesPage();
    if (typeof initializeGeographyPage === 'function') initializeGeographyPage();
}


// --- XỬ LÝ SỰ KIỆN TẢI TRANG ---
const showLoading = () => {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.add('active');
}
const hideLoading = () => {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.remove('active');
}

document.addEventListener('DOMContentLoaded', () => {
    showLoading();
    initializeCommonComponents();
    runPageSpecificInitializers();
    hideLoading();
    // console.log("Layout JS: Khởi tạo hoàn tất.");
});
