import './bootstrap'; // File bootstrap.js mặc định của Laravel

// === CÁC THƯ VIỆN CHÍNH ===
import '@hotwired/turbo';
import Chart from 'chart.js/auto';
import { Collapse, Modal, Tab, Dropdown, Tooltip, Popover } from 'bootstrap'; // Import Collapse và các module khác nếu cần

// Gán Chart vào window nếu cần (ít dùng khi đã có module)
window.Chart = Chart;

/**
 * ===================================================================
 * HÀM KHỞI TẠO CÁC THÀNH PHẦN CỤ THỂ CỦA TRANG
 * Được gọi mỗi khi Turbo tải một trang mới và cả lần đầu trang nạp.
 * ===================================================================
 */
function initializePageSpecificComponents() {
    console.log("DEBUG: initializePageSpecificComponents() được gọi.");

    // --- 1. Chức năng bật/tắt Sidebar VÀ TỰ ĐỘNG ĐÓNG CÁC SUBMENU KHÁC ---
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const sidebarToggle = document.getElementById('sidebarToggle'); // Giả sử ID của nút toggle là 'sidebarToggle'
        if (sidebarToggle) {
            // Gỡ listener cũ (nếu có) trước khi gắn mới để tránh lỗi lặp lại
            const newSidebarToggle = sidebarToggle.cloneNode(true);
            sidebarToggle.parentNode.replaceChild(newSidebarToggle, sidebarToggle);
            newSidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        const collapsibleLinks = sidebar.querySelectorAll('a.nav-link[data-bs-toggle="collapse"]');
        collapsibleLinks.forEach(link => {
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);

            newLink.addEventListener('click', function (event) {
                const currentTargetId = this.getAttribute('href');
                collapsibleLinks.forEach(otherLink => {
                    const otherTargetId = otherLink.getAttribute('href');
                    if (otherTargetId !== currentTargetId) {
                        const otherSubmenu = document.querySelector(otherTargetId);
                        if (otherSubmenu && otherSubmenu.classList.contains('show')) {
                            const bsCollapseInstance = Collapse.getInstance(otherSubmenu) || new Collapse(otherSubmenu, { toggle: false });
                            bsCollapseInstance.hide();
                        }
                    }
                });
            });
        });
    }

    // --- 2. Khởi tạo biểu đồ doanh thu (Chart.js) ---
    const ctxRevenue = document.getElementById('revenueChart');
    if (ctxRevenue) {
        if (window.myRevenueChart instanceof Chart) {
            window.myRevenueChart.destroy(); // Hủy biểu đồ cũ trước khi vẽ mới
        }
        window.myRevenueChart = new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'Doanh Thu (Triệu VNĐ)',
                    data: [120, 190, 150, 220, 180, 250], // Dữ liệu ví dụ
                    borderColor: 'rgba(24, 144, 255, 1)',
                    backgroundColor: 'rgba(24, 144, 255, 0.1)',
                    tension: 0.3, fill: true,
                    pointBackgroundColor: 'rgba(24, 144, 255, 1)', pointBorderColor: '#fff',
                    pointHoverRadius: 7, pointHoverBackgroundColor: 'rgba(24, 144, 255, 1)', pointRadius: 5,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: value => value + ' Tr' } } },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: context => {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) label += context.parsed.y + ' Triệu VNĐ';
                                return label;
                            }
                        }
                    }
                },
                interaction: { mode: 'index', intersect: false },
            }
        });
    }

    // --- 3. Chức năng xem trước ảnh (Preview Image) ---
    function setupPreviewImage(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (input && preview) {
            const new_input = input.cloneNode(true);
            input.parentNode.replaceChild(new_input, input);
            new_input.addEventListener('change', function (event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const currentPreview = document.getElementById(previewId);
                        if (currentPreview) currentPreview.src = e.target.result;
                    }
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        }
    }
    const imagePreviews = [
        ['adminAvatarCreate', 'adminAvatarPreviewCreate'], ['adminAvatarUpdate', 'adminAvatarPreviewUpdate'],
        ['customerAvatarCreate', 'customerAvatarPreviewCreate'], ['customerAvatarUpdate', 'customerAvatarPreviewUpdate'],
        ['categoryLogoCreate', 'categoryLogoPreviewCreate'], ['categoryLogoUpdate', 'categoryLogoPreviewUpdate'],
    ];
    imagePreviews.forEach(pair => setupPreviewImage(pair[0], pair[1]));

    // --- 4. Lấy số thông báo chưa đọc ---
    async function fetchNotificationCount() {
        try {
            const response = await fetch('/api/notifications/unread-count'); // Đảm bảo route này tồn tại
            if (!response.ok) {
                console.warn('Lỗi khi lấy thông báo. Trạng thái:', response.status);
                return;
            }
            const data = await response.json();
            const count = data.count;
            const badge = document.getElementById('notification-badge-count'); // Đảm bảo ID này đúng
            if (badge) {
                badge.textContent = count;
                badge.classList.toggle('d-none', count <= 0);
            }
        } catch (error) {
            console.error('Lỗi không xác định khi gọi API thông báo:', error);
        }
    }
    fetchNotificationCount();
    if (window.notificationInterval) clearInterval(window.notificationInterval);
    window.notificationInterval = setInterval(fetchNotificationCount, 30000);

    // --- 5. LOGIC RIÊNG CHO TRANG GEOGRAPHY ---
    const geographyTabsContent = document.getElementById('geographyTabsContent');
    if (geographyTabsContent) {
        console.log("DEBUG: Đang ở trang Geography, khởi tạo các listener cho modal và dropdown.");

        const allLaravelErrors = window.laravelErrors || {};
        const errorUpdateProvinceId = window.errorUpdateProvinceId || null;
        const errorUpdateDistrictId = window.errorUpdateDistrictId || null;
        const errorUpdateWardId = window.errorUpdateWardId || null;

        const fetchDistrictsForModal = async (provinceId, districtSelectElement, selectedDistrictId = null, placeholder = '-- Chọn Quận/Huyện --') => {
            if (!provinceId) {
                districtSelectElement.innerHTML = `<option value="">-- Chọn Tỉnh/Thành trước --</option>`;
                districtSelectElement.disabled = true; return;
            }
            districtSelectElement.disabled = true; districtSelectElement.innerHTML = `<option value="">Đang tải...</option>`;
            try {
                const apiUrl = `/api/provinces/${provinceId}/districts`;
                const response = await fetch(apiUrl);
                if (!response.ok) throw new Error('Lỗi mạng khi fetch districts');
                const data = await response.json();
                districtSelectElement.innerHTML = `<option value="">${placeholder}</option>`;
                if (data && data.length > 0) {
                    data.forEach(district => {
                        const option = new Option(district.name, district.id);
                        if (selectedDistrictId && district.id == selectedDistrictId) option.selected = true;
                        districtSelectElement.add(option);
                    });
                } else { districtSelectElement.innerHTML = `<option value="">Không có quận/huyện</option>`; }
            } catch (error) {
                console.error('Lỗi fetchDistrictsForModal:', error);
                districtSelectElement.innerHTML = `<option value="">Lỗi tải Quận/Huyện</option>`;
            } finally { districtSelectElement.disabled = false; }
        };

        const setupModalForErrors = (options) => {
            const { modalId, errorBagPrefix, errorIdValue, baseUrl } = options;
            const modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            const errorBag = allLaravelErrors[errorBagPrefix];
            if (errorBag && Object.keys(errorBag).length > 0) {
                const isUpdateModal = modalId.startsWith('update');
                if (!isUpdateModal || (isUpdateModal && errorIdValue)) {
                    let modalInstance = Modal.getInstance(modalEl);
                    if (!modalInstance) {
                        modalInstance = new Modal(modalEl);
                    }
                    const form = modalEl.querySelector('form');
                    if (form && isUpdateModal && errorIdValue && baseUrl) form.action = `${baseUrl}/${errorIdValue}`;

                    for (const field in errorBag) {
                        if (form) {
                            const inputField = form.querySelector(`[name="${field}"]`);
                            if (inputField) {
                                inputField.classList.add('is-invalid');
                                let errorElement = inputField.nextElementSibling;
                                if (errorElement && errorElement.classList.contains('invalid-feedback')) errorElement.textContent = errorBag[field][0];
                            }
                        }
                    }
                    if (modalId.endsWith('WardModal') && form) {
                        const oldProvinceId = form.querySelector('[name="province_id_for_ward"]').value;
                        const oldDistrictId = form.querySelector('[name="district_id"]').value;
                        if (oldProvinceId) fetchDistrictsForModal(oldProvinceId, form.querySelector('[name="district_id"]'), oldDistrictId);
                    }
                    modalInstance.show();
                }
            }
        };

        // Sử dụng URL tuyệt đối hoặc route helper của Laravel nếu có thể truyền vào đây
        // Vì đây là file JS, không thể dùng {{ route(...) }} trực tiếp
        // Bạn cần đảm bảo các baseUrl này là chính xác
        const provinceBaseUrl = "/admin/system/geography/provinces";
        const districtBaseUrl = "/admin/system/geography/districts";
        const wardBaseUrl = "/admin/system/geography/wards";

        setupModalForErrors({ modalId: 'createProvinceModal', errorBagPrefix: 'storeProvince' });
        setupModalForErrors({ modalId: 'updateProvinceModal', errorBagPrefix: 'updateProvince', errorIdValue: errorUpdateProvinceId, baseUrl: provinceBaseUrl });
        setupModalForErrors({ modalId: 'createDistrictModal', errorBagPrefix: 'storeDistrict' });
        setupModalForErrors({ modalId: 'updateDistrictModal', errorBagPrefix: 'updateDistrict', errorIdValue: errorUpdateDistrictId, baseUrl: districtBaseUrl });
        setupModalForErrors({ modalId: 'createWardModal', errorBagPrefix: 'storeWard' });
        setupModalForErrors({ modalId: 'updateWardModal', errorBagPrefix: 'updateWard', errorIdValue: errorUpdateWardId, baseUrl: wardBaseUrl });

        ['provinceForWardCreate', 'provinceForWardUpdate'].forEach(selectId => {
            const provinceSelect = document.getElementById(selectId);
            if (provinceSelect) {
                const districtSelectId = (selectId === 'provinceForWardCreate') ? 'wardDistrictIdCreate' : 'wardDistrictIdUpdate';
                const districtSelect = document.getElementById(districtSelectId);
                if (districtSelect) {
                    const newProvinceSelect = provinceSelect.cloneNode(true);
                    provinceSelect.parentNode.replaceChild(newProvinceSelect, provinceSelect);
                    newProvinceSelect.addEventListener('change', (event) => fetchDistrictsForModal(event.target.value, districtSelect));
                    if (newProvinceSelect.value) {
                        const selectedDistrictId = districtSelect.value;
                        fetchDistrictsForModal(newProvinceSelect.value, districtSelect, selectedDistrictId);
                    }
                }
            }
        });

        // Logic điền data vào modal khi mở (chỉ nên gắn listener một lần)
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            const modalId = button.dataset.bsTarget;
            if (!modalId) return;
            const modalEl = document.getElementById(modalId.substring(1));
            if (modalEl && !modalEl.hasAttribute('data-modal-listener-setup')) {
                modalEl.addEventListener('show.bs.modal', function (event) {
                    const triggerButton = event.relatedTarget;
                    if (!triggerButton) return;
                    const form = modalEl.querySelector('form');
                    if (!form) return;
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                    let currentBaseUrl = '';
                    if (modalId.includes('Province')) currentBaseUrl = provinceBaseUrl;
                    else if (modalId.includes('District')) currentBaseUrl = districtBaseUrl;
                    else if (modalId.includes('Ward')) currentBaseUrl = wardBaseUrl;

                    if (modalId.includes('update')) {
                        modalEl.querySelector('.modal-title').textContent = `Cập nhật: ${triggerButton.dataset.name || ''}`;
                        if (currentBaseUrl && triggerButton.dataset.id) form.action = `${currentBaseUrl}/${triggerButton.dataset.id}`;

                        Object.keys(triggerButton.dataset).forEach(key => {
                            const formKey = key.replace(/([A-Z])/g, '_$1').toLowerCase();
                            const input = form.querySelector(`[name="${formKey}"]`);
                            if (input) input.value = triggerButton.dataset[key];
                        });

                        if (modalId === '#updateWardModal') {
                            const provinceIdForWard = triggerButton.dataset.province_id;
                            const districtIdForWard = triggerButton.dataset.district_id;
                            const provinceSelect = form.querySelector('[name="province_id_for_ward"]');
                            const districtSelect = form.querySelector('[name="district_id"]');
                            if (provinceSelect && districtSelect && provinceIdForWard) {
                                provinceSelect.value = provinceIdForWard;
                                fetchDistrictsForModal(provinceIdForWard, districtSelect, districtIdForWard);
                            }
                        }
                    } else if (modalId.includes('delete')) {
                        if (currentBaseUrl && triggerButton.dataset.id) form.action = `${currentBaseUrl}/${triggerButton.dataset.id}`;
                        const strongEl = modalEl.querySelector('.modal-body strong');
                        if (strongEl) strongEl.textContent = triggerButton.dataset.name;
                    }
                });
                modalEl.setAttribute('data-modal-listener-setup', 'true');
            }
        });
    }
    console.log("DEBUG: Khởi tạo các thành phần trang HOÀN TẤT.");
}

/**
 * ===================================================================
 * XỬ LÝ LOADING OVERLAY VÀ CÁC SỰ KIỆN CỦA TURBO
 * ===================================================================
 */
window.showLoadingOverlay = () => {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.add('active');
};
window.hideLoadingOverlay = () => {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.remove('active');
};

if (!window.turboEventsAttached) {
    console.log("DEBUG: Gắn các sự kiện Turbo lần đầu tiên.");
    document.addEventListener("turbo:visit", window.showLoadingOverlay);
    document.addEventListener("turbo:load", () => {
        console.log("DEBUG: Sự kiện turbo:load - Gọi hideLoadingOverlay và initializePageSpecificComponents.");
        window.hideLoadingOverlay();
        initializePageSpecificComponents();
    });
    document.addEventListener("turbo:fetch-request-error", window.hideLoadingOverlay);
    document.addEventListener("turbo:visit-aborted", window.hideLoadingOverlay);
    document.addEventListener("turbo:render", () => {
        console.log("DEBUG: Sự kiện turbo:render - Gọi initializePageSpecificComponents.");
        initializePageSpecificComponents();
    });
    window.turboEventsAttached = true;
}

document.addEventListener('DOMContentLoaded', () => {
    console.log("DEBUG: DOMContentLoaded - Gọi initializePageSpecificComponents cho lần tải đầu.");
    initializePageSpecificComponents();
});