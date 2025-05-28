// resources/js/app.js

import './bootstrap'; // File bootstrap.js mặc định của Laravel

// === CÁC THƯ VIỆN CHÍNH ===
import '@hotwired/turbo'; // Kích hoạt Turbo Drive
import Chart from 'chart.js/auto';
import { Collapse, Modal, Tab, Dropdown, Tooltip, Popover } from 'bootstrap';

// === GÁN CÁC THƯ VIỆN VÀO `window` ĐỂ CÁC SCRIPT BÊN NGOÀI CÓ THỂ TRUY CẬP ===
// Điều này rất quan trọng vì các file trong `public` không được Vite bundle
// và không thể `import` trực tiếp.
window.Chart = Chart;
window.Collapse = Collapse;
window.Modal = Modal;
window.Tab = Tab;
window.Dropdown = Dropdown;
window.Tooltip = Tooltip;
window.Popover = Popover;

// File này giờ chỉ làm nhiệm vụ nạp thư viện.
// Toàn bộ logic ứng dụng đã được chuyển sang các file trong `public/assets_admin/js`.
console.log("Vite (app.js): Các thư viện chính (Turbo, Chart.js, Bootstrap JS) đã được nạp.");