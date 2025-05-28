/**
 * ===================================================================
 * admin_layout.js
 * Chứa các mã JavaScript chung cho toàn bộ layout admin.
 * Bao gồm: Sidebar, Thông báo, Loading Overlay, và xử lý Turbo.
 * ===================================================================
 */

// Hàm tiện ích: Gỡ bỏ và gắn lại listener để tránh lặp lại do Turbo cache
function rebindEventListener(element, eventType, handler) {
    if (!element) return;
    const newElement = element.cloneNode(true);
    element.parentNode.replaceChild(newElement, element);
    newElement.addEventListener(eventType, handler);
    return newElement;
}

// --- 1. Chức năng bật/tắt Sidebar & Tự động đóng Submenu ---
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    const sidebarToggle = document.getElementById('sidebarToggle');
    rebindEventListener(sidebarToggle, 'click', () => sidebar.classList.toggle('active'));

    const collapsibleLinks = sidebar.querySelectorAll('a.nav-link[data-bs-toggle="collapse"]');
    collapsibleLinks.forEach(link => {
        rebindEventListener(link, 'click', function () {
            const currentTargetId = this.getAttribute('href');
            collapsibleLinks.forEach(otherLink => {
                const otherTargetId = otherLink.getAttribute('href');
                if (otherTargetId !== currentTargetId) {
                    const otherSubmenu = document.querySelector(otherTargetId);
                    if (otherSubmenu && otherSubmenu.classList.contains('show')) {
                        const bsCollapse = window.Collapse.getInstance(otherSubmenu) || new window.Collapse(otherSubmenu);
                        bsCollapse.hide();
                    }
                }
            });
        });
    });
    console.log("Layout JS: Sidebar đã khởi tạo.");
}

// --- 2. Lấy số thông báo chưa đọc ---
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
    if (notificationInterval) clearInterval(notificationInterval);
    notificationInterval = setInterval(fetchNotificationCount, 30000);
    console.log("Layout JS: Thông báo đã khởi tạo.");
}

// --- 3. Xử lý Loading Overlay ---
window.showLoadingOverlay = () => document.getElementById('loading-overlay')?.classList.add('active');
window.hideLoadingOverlay = () => document.getElementById('loading-overlay')?.classList.remove('active');

// --- HÀM KHỞI TẠO TỔNG ---
function initializeCommonComponents() {
    initializeSidebar();
    initializeNotifications();
}

// --- ORCHESTRATOR: ĐIỀU PHỐI KHỞI TẠO SCRIPT ---
function runPageSpecificInitializers() {
    console.log("Layout JS: Tìm và chạy các hàm khởi tạo của trang...");
    if (typeof initializeDashboardChart === 'function') initializeDashboardChart();
    if (typeof initializeImagePreviews === 'function') initializeImagePreviews();
    if (typeof initializeGeographyPage === 'function') initializeGeographyPage();
}

// --- XỬ LÝ SỰ KIỆN TURBO ---
if (!window.turboEventsAttached) {
    console.log("Layout JS: Gắn các sự kiện Turbo lần đầu.");
    document.addEventListener("turbo:visit", window.showLoadingOverlay);
    document.addEventListener("turbo:render", () => {
        console.log("Layout JS (turbo:render): Chạy lại các hàm khởi tạo.");
        initializeCommonComponents();
        runPageSpecificInitializers();
    });
    document.addEventListener("turbo:load", window.hideLoadingOverlay);
    window.turboEventsAttached = true;
}

// --- KHỞI TẠO LẦN ĐẦU KHI TẢI TRANG (KHÔNG QUA TURBO) ---
document.addEventListener('DOMContentLoaded', () => {
    console.log("Layout JS (DOMContentLoaded): Khởi tạo lần đầu.");
    initializeCommonComponents();
    runPageSpecificInitializers();
});