// public/assets_admin/js/dashboard_chart.js

// Biến toàn cục để lưu trữ instance của biểu đồ
let myRevenueChart = null;

async function initializeDashboardChart() {
    const ctxRevenue = document.getElementById('revenueChart');
    if (!ctxRevenue) {
        console.log("Chart JS: Không tìm thấy canvas 'revenueChart'.");
        return;
    }

    // Hủy bỏ biểu đồ cũ nếu nó đã tồn tại
    if (myRevenueChart) {
        myRevenueChart.destroy();
        console.log("Chart JS: Đã hủy biểu đồ doanh thu cũ.");
    }

    console.log("Chart JS: Khởi tạo biểu đồ doanh thu.");

    try {
        // Lấy năm hiện tại để gửi lên API
        const currentYear = new Date().getFullYear();
        // Gọi API để lấy dữ liệu doanh thu
        const response = await fetch(`/admin/dashboard/revenue-data?year=${currentYear}`);

        if (!response.ok) {
            // Nếu phản hồi không thành công (ví dụ: 404, 500), throw error
            const errorText = await response.text(); // Đọc phản hồi dưới dạng văn bản
            console.error('Phản hồi lỗi từ server:', errorText);
            throw new Error(`Không thể tải dữ liệu doanh thu. Mã lỗi: ${response.status}. Chi tiết: ${errorText.substring(0, 100)}...`);
        }

        const chartData = await response.json(); // Phân tích JSON

        myRevenueChart = new window.Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: chartData.labels, // Dữ liệu labels từ API
                datasets: [{
                    label: 'Doanh Thu (Triệu VNĐ)',
                    data: chartData.data, // Dữ liệu doanh thu từ API
                    borderColor: 'rgba(24, 144, 255, 1)',
                    backgroundColor: 'rgba(24, 144, 255, 0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value + ' Tr' // Định dạng trục Y
                        }
                    },
                    x: {
                        grid: {
                            display: false // Ẩn lưới trục X
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
                                return context.dataset.label + ': ' + formatCurrency(context.raw * 1000000); // Hiển thị đầy đủ số tiền
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error fetching revenue data:', error);
        // Hiển thị thông báo lỗi thân thiện hơn cho người dùng
        showAppInfoModal('Không thể tải dữ liệu biểu đồ doanh thu. Vui lòng thử lại sau.', 'Lỗi Biểu Đồ', 'error');
    }
}
