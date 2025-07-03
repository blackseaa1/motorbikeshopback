// public/assets_admin/js/reports/monthly_revenue_chart.js

let monthlyRevenueChartInstance = null; // To store the Chart.js instance

/**
 * Fetches monthly revenue data from the API and updates the chart and table.
 * @param {number} year - The year to fetch data for.
 */
function fetchMonthlyRevenueData(year) {
    const monthlyRevenueTableBody = document.getElementById('monthlyRevenueTableBody');
    monthlyRevenueTableBody.innerHTML = '<tr><td colspan="2" class="text-center">Đang tải dữ liệu...</td></tr>';

    fetch(`/admin/reports/api/monthly-revenue?year=${year}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const labels = data.map(item => item.month);
            const revenues = data.map(item => item.total_revenue);

            // Destroy previous chart instance if it exists
            if (monthlyRevenueChartInstance) {
                monthlyRevenueChartInstance.destroy();
            }

            const ctx = document.getElementById('monthlyRevenueChart').getContext('2d');
            monthlyRevenueChartInstance = new Chart(ctx, {
                type: 'line', // Line chart for monthly data
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh Thu (VNĐ)',
                        data: revenues,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                        fill: false
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
                                callback: function(value, index, values) {
                                    return value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tháng'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                                }
                            }
                        }
                    }
                }
            });

            // Update table
            monthlyRevenueTableBody.innerHTML = '';
            if (data.length > 0) {
                data.forEach(item => {
                    // Thêm data-month và data-year vào hàng
                    const row = `
                        <tr data-month="${item.month.replace('Tháng ', '')}" data-year="${year}">
                            <td>${item.month}</td>
                            <td class="revenue-cell" data-month="${item.month.replace('Tháng ', '')}" data-year="${year}">${item.formatted_revenue}</td> 
                        </tr>
                    `;
                    monthlyRevenueTableBody.insertAdjacentHTML('beforeend', row);
                });

                // Attach hover listeners to revenue cells
                monthlyRevenueTableBody.querySelectorAll('.revenue-cell').forEach(cell => {
                    const month = cell.dataset.month;
                    const year = cell.dataset.year;
                    if (month && year) {
                        cell.addEventListener('mouseover', (event) => showTopProductForRevenueTooltip('monthly', { month: month, year: year }, event));
                        cell.addEventListener('mouseout', hideProductDetailTooltip);
                    }
                });

            } else {
                monthlyRevenueTableBody.innerHTML = '<tr><td colspan="2" class="text-center">Không có dữ liệu doanh thu cho năm này.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching monthly revenue data:', error);
            monthlyRevenueTableBody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</td></tr>';
        });
}

/**
 * Initializes the monthly revenue chart and sets up event listeners.
 */
function initializeMonthlyRevenueChart() {
    const yearSelect = document.getElementById('monthlyRevenueYearSelect');

    if (!yearSelect) {
        console.warn('Monthly revenue chart elements not found.');
        return;
    }

    // Initial load
    fetchMonthlyRevenueData(yearSelect.value);

    // Event listener for changes
    yearSelect.addEventListener('change', () => fetchMonthlyRevenueData(yearSelect.value));
}