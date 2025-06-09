// resources/js/app.js

import './bootstrap';

// 1. Import các thư viện cần thiết
import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';
import 'bootstrap-select'; // Đảm bảo import bootstrap-select JS

// 2. Gán tất cả các thư viện cần dùng chung vào `window`
window.$ = window.jQuery = $;
window.bootstrap = bootstrap;
window.Chart = Chart;

// 3. ================= THÊM DÒNG NÀY VÀO =================
// Cấu hình thủ công phiên bản Bootstrap cho thư viện bootstrap-select
$.fn.selectpicker.Constructor.BootstrapVersion = '5';
// =======================================================

console.log("Vite (app.js): jQuery, Bootstrap, và Chart.js đã được public. Bootstrap-select được cấu hình cho BS5.");