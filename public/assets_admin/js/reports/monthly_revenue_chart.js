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
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                                    }
                                    return label;
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
                    const row = `
                        <tr>
                            <td>${item.month}</td>
                            <td>${item.formatted_revenue}</td>
                        </tr>
                    `;
                    monthlyRevenueTableBody.insertAdjacentHTML('beforeend', row);
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
