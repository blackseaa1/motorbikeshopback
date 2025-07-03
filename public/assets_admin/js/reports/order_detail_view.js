// public/assets_admin/js/reports/order_detail_view.js

const orderDetailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
const orderItemDetailModal = new bootstrap.Modal(document.getElementById('orderItemDetailModal'));

const modalDateOrMonthSpan = document.getElementById('modalDateOrMonth');
const orderModalLoading = document.getElementById('orderModalLoading');
const orderModalNoData = document.getElementById('orderModalNoData');
const orderDetailTableBody = document.getElementById('orderDetailTableBody');

const modalOrderIdSpan = document.getElementById('modalOrderId');
const orderItemDetailTableBody = document.getElementById('orderItemDetailTableBody');

/**
 * Fetches order details for a given date or month/year and displays them in a modal.
 * @param {string} type - 'daily' or 'monthly'.
 * @param {object} params - Contains date (for daily) or month/year (for monthly).
 */
async function fetchAndShowOrderDetails(type, params) {
    orderDetailTableBody.innerHTML = ''; // Clear previous data
    orderModalLoading.classList.remove('d-none'); // Show loading message
    orderModalNoData.classList.add('d-none'); // Hide no data message

    let apiUrl = '';
    let displayString = '';

    if (type === 'daily') {
        apiUrl = `/admin/reports/api/orders-by-date?date=${params.date}`;
        displayString = `(Ngày ${params.date.split('-').reverse().join('/')})`;
    } else if (type === 'monthly') {
        apiUrl = `/admin/reports/api/orders-by-month?month=${params.month}&year=${params.year}`;
        displayString = `(Tháng ${params.month}/${params.year})`;
    }

    modalDateOrMonthSpan.textContent = displayString;
    orderDetailModal.show(); // Show the main order detail modal

    try {
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const orders = await response.json();

        orderModalLoading.classList.add('d-none'); // Hide loading message

        if (orders.length > 0) {
            orders.forEach(order => {
                const row = `
                    <tr>
                        <td>${order.id}</td>
                        <td>${order.customer_name}</td>
                        <td>${order.total_price}</td>
                        <td><span class="badge ${order.status_badge_class}">${order.status_text}</span></td>
                        <td>${order.created_at}</td>
                        <td>${order.item_count}</td>
                        <td><button class="btn btn-sm btn-info view-order-items" data-bs-toggle="modal" data-bs-target="#orderItemDetailModal" data-order-id="${order.id}">Chi tiết</button></td>
                    </tr>
                `;
                orderDetailTableBody.insertAdjacentHTML('beforeend', row);
            });

            // Attach event listeners to "Chi tiết" buttons
            orderDetailTableBody.querySelectorAll('.view-order-items').forEach(button => {
                button.addEventListener('click', (event) => {
                    const orderId = event.target.dataset.orderId;
                    const order = orders.find(o => o.id == orderId);
                    if (order) {
                        showOrderItemDetails(order);
                    }
                });
            });

        } else {
            orderModalNoData.classList.remove('d-none'); // Show no data message
        }

    } catch (error) {
        console.error('Error fetching order details:', error);
        orderModalLoading.classList.add('d-none');
        orderDetailTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi khi tải dữ liệu đơn hàng.</td></tr>';
    }
}

/**
 * Populates and shows the order item details modal.
 * @param {object} order - The order object containing items.
 */
function showOrderItemDetails(order) {
    modalOrderIdSpan.textContent = `#${order.id}`;
    orderItemDetailTableBody.innerHTML = ''; // Clear previous items

    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            const row = `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.price}</td>
                    <td>${item.total}</td>
                </tr>
            `;
            orderItemDetailTableBody.insertAdjacentHTML('beforeend', row);
        });
    } else {
        orderItemDetailTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Không có sản phẩm trong đơn hàng này.</td></tr>';
    }
    // The modal is already triggered by data-bs-target, so no need to call show() here
}

/**
 * Initializes listeners for daily and monthly revenue tables.
 * Call this function from reports_main.js after relevant scripts are loaded.
 */
function initializeOrderDetailsListeners() {
    const dailyRevenueTableBody = document.getElementById('dailyRevenueTableBody');
    const monthlyRevenueTableBody = document.getElementById('monthlyRevenueTableBody');

    // Add click listener to daily revenue table body (event delegation)
    if (dailyRevenueTableBody) {
        dailyRevenueTableBody.addEventListener('click', (event) => {
            // Find the closest row that has data-date
            const row = event.target.closest('tr[data-date]');
            if (row) {
                const date = row.dataset.date; // Date is already in YYYY-MM-DD from daily_revenue_chart.js
                fetchAndShowOrderDetails('daily', { date: date });
            }
        });
    }

    // Add click listener to monthly revenue table body (event delegation)
    if (monthlyRevenueTableBody) {
        monthlyRevenueTableBody.addEventListener('click', (event) => {
            // Find the closest row that has data-month and data-year
            const row = event.target.closest('tr[data-month][data-year]');
            if (row) {
                const month = row.dataset.month;
                const year = row.dataset.year;
                fetchAndShowOrderDetails('monthly', { month: month, year: year });
            }
        });
    }
}