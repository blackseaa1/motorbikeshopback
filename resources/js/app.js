// File: resources/js/app.js

import './bootstrap'; // File bootstrap.js mặc định của Laravel

// === CÁC THƯ VIỆN CHÍNH ===
import Chart from 'chart.js/auto'; //
import * as bootstrap from 'bootstrap'; // Import tất cả mọi thứ từ bootstrap

// === GÁN CÁC THƯ VIỆN VÀO `window` ĐỂ CÁC SCRIPT BÊN NGOÀI CÓ THỂ TRUY CẬP ===
window.Chart = Chart; //
window.bootstrap = bootstrap; // Gán toàn bộ module bootstrap đã import vào window

console.log("Vite (app.js): Các thư viện chính (Chart.js, Bootstrap bundle) đã được nạp và gán vào window.");