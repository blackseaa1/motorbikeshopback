// public/assets_admin/js/dashboard_chart.js

function initializeDashboardChart() {
    const ctxRevenue = document.getElementById('revenueChart');
    if (!ctxRevenue) return;

    if (window.myRevenueChart) window.myRevenueChart.destroy();
    console.log("Chart JS: Khởi tạo biểu đồ doanh thu.");

    window.myRevenueChart = new window.Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
            datasets: [{
                label: 'Doanh Thu (Triệu VNĐ)',
                data: [120, 190, 150, 220, 180, 250],
                borderColor: 'rgba(24, 144, 255, 1)',
                backgroundColor: 'rgba(24, 144, 255, 0.1)',
                tension: 0.3, fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { callback: value => value + ' Tr' } } },
        }
    });
}