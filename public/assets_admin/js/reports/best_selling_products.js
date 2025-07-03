// public/assets_admin/js/reports/best_selling_products.js

/**
 * Fetches best-selling products data from the API and updates the table.
 * @param {string} startDate - Start date in YYYY-MM-DD format.
 * @param {string} endDate - End date in YYYY-MM-DD format.
 * @param {number} limit - Number of top products to fetch.
 */
function fetchBestSellingProducts(startDate, endDate, limit) {
    const bestSellingProductsTableBody = document.getElementById('bestSellingProductsTableBody');
    const bestSellingNoData = document.getElementById('bestSellingNoData');

    bestSellingProductsTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>';
    bestSellingNoData.classList.add('d-none'); // Hide no data message initially

    fetch(`/admin/reports/api/best-selling-products?start_date=${startDate}&end_date=${endDate}&limit=${limit}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            bestSellingProductsTableBody.innerHTML = ''; // Clear loading message

            if (data.length > 0) {
                data.forEach(product => {
                    // Thêm data-product-id vào hàng để JavaScript có thể lấy ID sản phẩm
                    const row = `
                        <tr data-product-id="${product.id}">
                            <td>${product.id}</td>
                            <td><img src="${product.thumbnail_url}" alt="${product.name}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='https://placehold.co/50x50/grey/white?text=No+Img'"></td>
                            <td class="product-name-hover">${product.name}</td>
                            <td><span class="badge bg-primary">${product.total_quantity_sold}</span></td>
                            <td>${product.total_revenue_generated}</td>
                        </tr>
                    `;
                    bestSellingProductsTableBody.insertAdjacentHTML('beforeend', row);
                });

                // Gắn các trình xử lý sự kiện hover sau khi các hàng được thêm vào DOM
                bestSellingProductsTableBody.querySelectorAll('tr[data-product-id]').forEach(row => {
                    const productId = row.dataset.productId;
                    if (productId) {
                        row.addEventListener('mouseover', (event) => showProductDetailTooltip(productId, event));
                        row.addEventListener('mouseout', hideProductDetailTooltip);
                    }
                });

            } else {
                bestSellingProductsTableBody.innerHTML = ''; // Đảm bảo thân bảng trống
                bestSellingNoData.classList.remove('d-none'); // Hiển thị thông báo không có dữ liệu
            }
        })
        .catch(error => {
            console.error('Error fetching best-selling products:', error);
            bestSellingProductsTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</td></tr>';
            bestSellingNoData.classList.add('d-none'); // Ẩn thông báo không có dữ liệu khi có lỗi
        });
}

/**
 * Initializes the best-selling products report and sets up event listeners.
 */
function initializeBestSellingProducts() {
    const startDateInput = document.getElementById('bestSellingStartDate');
    const endDateInput = document.getElementById('bestSellingEndDate');
    const limitInput = document.getElementById('bestSellingLimit');
    const applyButton = document.getElementById('applyBestSellingFilter');

    if (!startDateInput || !endDateInput || !limitInput || !applyButton) {
        console.warn('Best-selling products elements not found.');
        return;
    }

    // Đặt ngày mặc định nếu chúng trống (cho lần tải ban đầu)
    if (!startDateInput.value) {
        startDateInput.valueAsDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
    }
    if (!endDateInput.value) {
        endDateInput.valueAsDate = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0);
    }

    // Tải ban đầu
    fetchBestSellingProducts(startDateInput.value, endDateInput.value, limitInput.value);

    // Trình xử lý sự kiện cho nút áp dụng
    applyButton.addEventListener('click', () => {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const limit = limitInput.value;

        if (startDate && endDate && limit && !isNaN(limit) && parseInt(limit) > 0) {
            fetchBestSellingProducts(startDate, endDate, parseInt(limit));
        } else {
            alert('Vui lòng nhập ngày bắt đầu, ngày kết thúc và số lượng hợp lệ.');
        }
    });
}