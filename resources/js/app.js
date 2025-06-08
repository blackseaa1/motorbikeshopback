// File: resources/js/app.js

import './bootstrap'; // File bootstrap.js mặc định của Laravel
import $ from 'jquery'; // Nếu jQuery chưa được nạp trong bootstrap.js
import Chart from 'chart.js/auto';
import * as bootstrap from 'bootstrap';
import 'bootstrap-select/dist/js/bootstrap-select.min.js';

// Gán jQuery vào window (nếu cần)
window.jQuery = window.$ = $;

// Gán các thư viện vào window (chỉ nếu cần cho script bên ngoài)
window.Chart = Chart;
window.bootstrap = bootstrap;

// Khởi tạo Bootstrap-select
$(document).ready(function () {
    $('.selectpicker').selectpicker();
});

// Console log để debug (xóa khi triển khai)
console.log("Vite (app.js): Các thư viện chính (Chart.js, Bootstrap, Bootstrap-select) đã được nạp.");