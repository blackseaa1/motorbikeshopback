// public/assets_admin/js/reports_main.js

// Ensure the DOM is fully loaded before running scripts
document.addEventListener('DOMContentLoaded', function() {
    console.log('Reports Main Script Loaded');

    // Dynamically load individual report scripts
    // This approach helps keep concerns separated and improves maintainability

    const loadScript = (src) => {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    };

    // Load all report scripts
    Promise.all([
        loadScript('/assets_admin/js/reports/daily_revenue_chart.js'),
        loadScript('/assets_admin/js/reports/monthly_revenue_chart.js'),
        loadScript('/assets_admin/js/reports/low_stock_products.js'),
        loadScript('/assets_admin/js/reports/best_selling_products.js'),
        loadScript('/assets_admin/js/reports/product_detail_hover.js'), // For product hover
        loadScript('/assets_admin/js/reports/order_detail_view.js') // NEW: For order details
    ])
    .then(() => {
        console.log('All report scripts loaded successfully. Initializing reports...');
        // Initialize all reports once their scripts are loaded
        // These functions will be defined in their respective files
        if (typeof initializeDailyRevenueChart === 'function') {
            initializeDailyRevenueChart();
        }
        if (typeof initializeMonthlyRevenueChart === 'function') {
            initializeMonthlyRevenueChart();
        }
        if (typeof initializeLowStockProducts === 'function') {
            initializeLowStockProducts();
        }
        if (typeof initializeBestSellingProducts === 'function') {
            initializeBestSellingProducts();
        }
        if (typeof initializeOrderDetailsListeners === 'function') { // NEW: Initialize order detail listeners
            initializeOrderDetailsListeners();
        }
    })
    .catch(error => {
        console.error('Error loading one or more report scripts:', error);
    });
});