// public/assets_admin/js/reports/low_stock_products.js

/**
 * Fetches low stock products data from the API and updates the table.
 * @param {number} threshold - The stock quantity threshold.
 */
function fetchLowStockProducts(threshold) {
    const lowStockProductsTableBody = document.getElementById('lowStockProductsTableBody');
    const lowStockNoData = document.getElementById('lowStockNoData');

    lowStockProductsTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Đang tải dữ liệu...</td></tr>';
    lowStockNoData.classList.add('d-none'); // Hide no data message initially

    fetch(`/admin/reports/api/low-stock-products?threshold=${threshold}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            lowStockProductsTableBody.innerHTML = ''; // Clear loading message

            if (data.length > 0) {
                data.forEach(product => {
                    const row = `
                        <tr>
                            <td>${product.id}</td>
                            <td><img src="${product.thumbnail_url}" alt="${product.name}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='https://placehold.co/50x50/grey/white?text=No+Img'"></td>
                            <td>${product.name}</td>
                            <td>${product.category}</td>
                            <td>${product.brand}</td>
                            <td><span class="badge bg-warning">${product.stock_quantity}</span></td>
                            <td>${product.price}</td>
                        </tr>
                    `;
                    lowStockProductsTableBody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                lowStockProductsTableBody.innerHTML = ''; // Ensure table body is empty
                lowStockNoData.classList.remove('d-none'); // Show no data message
            }
        })
        .catch(error => {
            console.error('Error fetching low stock products:', error);
            lowStockProductsTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi khi tải dữ liệu. Vui lòng thử lại.</td></tr>';
            lowStockNoData.classList.add('d-none'); // Hide no data message on error
        });
}

/**
 * Initializes the low stock products report and sets up event listeners.
 */
function initializeLowStockProducts() {
    const thresholdInput = document.getElementById('lowStockThreshold');
    const applyButton = document.getElementById('applyLowStockThreshold');

    if (!thresholdInput || !applyButton) {
        console.warn('Low stock products elements not found.');
        return;
    }

    // Initial load
    fetchLowStockProducts(thresholdInput.value);

    // Event listener for apply button
    applyButton.addEventListener('click', () => {
        const threshold = thresholdInput.value;
        if (threshold && !isNaN(threshold) && parseInt(threshold) > 0) {
            fetchLowStockProducts(parseInt(threshold));
        } else {
            alert('Vui lòng nhập một số hợp lệ cho ngưỡng tồn kho.');
        }
    });
}
