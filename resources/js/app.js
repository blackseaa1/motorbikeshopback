import './bootstrap';

// SỬA LỖI: Thay đổi cách import Turbo
import '@hotwired/turbo';

// Phần NProgress để hiển thị thanh loading (đã đúng)
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// Các thư viện khác của bạn (đã đúng)
import Chart from 'chart.js/auto';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
window.Chart = Chart;


// Các sự kiện của Turbo để điều khiển NProgress (đã đúng)
document.addEventListener("turbo:visit", () => {
    NProgress.start();
});

document.addEventListener("turbo:load", () => {
    NProgress.done();
});