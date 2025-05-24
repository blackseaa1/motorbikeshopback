// JavaScript to toggle sidebar on smaller screens
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('active');
    });
}

// Chart.js Revenue Chart Example
const ctxRevenue = document.getElementById('revenueChart');
if (ctxRevenue) {
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
            datasets: [{
                label: 'Doanh Thu (Triệu VNĐ)',
                data: [120, 190, 150, 220, 180, 250], // Example data
                borderColor: 'rgba(24, 144, 255, 1)', // var(--sidebar-active-bg)
                backgroundColor: 'rgba(24, 144, 255, 0.1)',
                tension: 0.3, // Smoother lines
                fill: true, // Fill area under line
                pointBackgroundColor: 'rgba(24, 144, 255, 1)',
                pointBorderColor: '#fff',
                pointHoverRadius: 7,
                pointHoverBackgroundColor: 'rgba(24, 144, 255, 1)',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value + ' Tr';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + ' Triệu VNĐ';
                            }
                            return label;
                        }
                    }
                }
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
        }
    });
}
