// public/assets_admin/js/reports/daily_revenue_chart.js

let dailyRevenueChartInstance = null; // To store the Chart.js instance

/**
 * Fetches daily revenue data from the API and updates the chart and table.
 * @param {number} month - The month to fetch data for (1-12).
 * @param {number} year - The year to fetch data for.
 */
function fetchDailyRevenueData(month, year) {
    const dailyRevenueTableBody = document.getElementById('dailyRevenueTableBody');
    dailyRevenueTableBody.innerHTML = '<tr><td colspan="2" class="text-center">Đang tải dữ liệu...</td></tr>';

    fetch(`/admin/reports/api/daily-revenue?month=${month}&year=${year}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const labels = data.map(item => item.date);
            const revenues = data.map(item => item.total_revenue);

            // Destroy previous chart instance if it exists
            if (dailyRevenueChartInstance) {
                dailyRevenueChartInstance.destroy();
            }

            const ctx = document.getElementById('dailyRevenueChart').getContext('2d');
            dailyRevenueChartInstance = new Chart(ctx, {
                type: 'bar', // Bar chart for daily data
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh Thu (VNĐ)',
                        data: revenues,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Doanh Thu'
                            },
                            ticks: {
                                callback: function (value, index, values) {
                                    return value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Ngày'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                                }
                            }
                        }
                    }
                }
            });

            // Update table
            dailyRevenueTableBody.innerHTML = '';
            if (data.length > 0) {
                data.forEach(item => {
                    // Thêm data-date vào hàng để truyền ngày tháng chính xác
                    const row = `
                        <tr data-date="${item.date.split('/').reverse().join('-')}">
                            <td>${item.date}</td>
                            <td class="revenue-cell" data-date="${item.date.split('/').reverse().join('-')}">${item.formatted_revenue}</td> 
                        </tr>
                    `;
                    dailyRevenueTableBody.insertAdjacentHTML('beforeend', row);
                });

                // Attach hover listeners to revenue cells
                dailyRevenueTableBody.querySelectorAll('.revenue-cell').forEach(cell => {
                    const date = cell.dataset.date;
                    if (date) {
                        cell.addEventListener('mouseover', (event) => showTopProductForRevenueTooltip('daily', { date: date }, event));
                        cell.addEventListener('mouseout', hideProductDetailTooltip);
                    }
                });

            } else {
                dailyRevenueTableBody.innerHTML = '<tr><td colspan="2" class="text-center">Không có dữ liệu doanh thu cho tháng này.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching daily revenue data:', error);
            dailyRevenueTableBody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</td></tr>';
        });
}

/**
 * Initializes the daily revenue chart and sets up event listeners.
 */
function initializeDailyRevenueChart() {
    const monthSelect = document.getElementById('dailyRevenueMonthSelect');
    const yearSelect = document.getElementById('dailyRevenueYearSelect');

    if (!monthSelect || !yearSelect) {
        console.warn('Daily revenue chart elements not found.');
        return;
    }

    // Initial load
    fetchDailyRevenueData(monthSelect.value, yearSelect.value);

    // Event listeners for changes
    monthSelect.addEventListener('change', () => fetchDailyRevenueData(monthSelect.value, yearSelect.value));
    yearSelect.addEventListener('change', () => fetchDailyRevenueData(monthSelect.value, yearSelect.value));
}