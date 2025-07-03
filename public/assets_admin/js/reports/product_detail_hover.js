// public/assets_admin/js/reports/product_detail_hover.js

let productDetailTooltipTimeout;
const productDetailTooltip = document.getElementById('productDetailTooltip');
const tooltipProductThumbnail = document.getElementById('tooltipProductThumbnail');
const tooltipProductName = document.getElementById('tooltipProductName');
const tooltipProductPrice = document.getElementById('tooltipProductPrice');
const tooltipProductStock = document.getElementById('tooltipProductStock');
const tooltipProductStatus = document.getElementById('tooltipProductStatus');
const tooltipProductCategory = document.getElementById('tooltipProductCategory');
const tooltipProductBrand = document.getElementById('tooltipProductBrand');
const tooltipProductMaterial = document.getElementById('tooltipProductMaterial');
const tooltipProductColor = document.getElementById('tooltipProductColor');
const tooltipProductSpecs = document.getElementById('tooltipProductSpecs');
const tooltipProductVehicleModels = document.getElementById('tooltipProductVehicleModels');
const tooltipProductRating = document.getElementById('tooltipProductRating');
const tooltipProductReviewsCount = document.getElementById('tooltipProductReviewsCount');

/**
 * Shows the product detail tooltip with data from the API.
 * @param {number} productId - The ID of the product to show details for.
 * @param {Event} event - The mouseover event to get positioning.
 */
function showProductDetailTooltip(productId, event) {
    // Clear any existing timeout to prevent multiple tooltips or rapid API calls
    clearTimeout(productDetailTooltipTimeout);

    // Set a timeout to fetch and display the tooltip after a short delay (e.g., 300ms)
    // This debounces the hover effect, so it only shows if the user hovers for a moment.
    productDetailTooltipTimeout = setTimeout(() => {
        fetch(`/admin/reports/api/product-details/${productId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(product => {
                // Populate tooltip with product data
                tooltipProductThumbnail.src = product.thumbnail_url;
                tooltipProductName.textContent = product.name;
                tooltipProductPrice.textContent = product.price;
                tooltipProductStock.textContent = product.stock_quantity;
                tooltipProductStatus.textContent = product.status_text;
                tooltipProductCategory.textContent = product.category;
                tooltipProductBrand.textContent = product.brand;
                tooltipProductMaterial.textContent = product.material || 'N/A';
                tooltipProductColor.textContent = product.color || 'N/A';
                tooltipProductSpecs.textContent = product.specifications || 'N/A';
                tooltipProductVehicleModels.textContent = product.vehicle_models && product.vehicle_models.length > 0 ? product.vehicle_models.join(', ') : 'N/A';
                tooltipProductRating.textContent = product.average_rating ? `${product.average_rating}/5 sao` : 'Chưa có đánh giá';
                tooltipProductReviewsCount.textContent = product.reviews_count !== undefined ? product.reviews_count : 0;


                // Position and show the tooltip
                productDetailTooltip.style.left = `${event.pageX + 15}px`; // 15px offset from mouse
                productDetailTooltip.style.top = `${event.pageY + 15}px`;
                productDetailTooltip.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching product details:', error);
                // Optionally show a basic error message in the tooltip or hide it
                productDetailTooltip.style.display = 'none';
            });
    }, 300); // 300ms debounce
}

/**
 * Shows a tooltip with the top selling product for a given revenue period.
 * Reuses the existing product detail tooltip structure.
 * @param {string} type - 'daily' or 'monthly'.
 * @param {object} params - Contains date (for daily) or month/year (for monthly).
 * @param {Event} event - The mouseover event to get positioning.
 */
function showTopProductForRevenueTooltip(type, params, event) {
    clearTimeout(productDetailTooltipTimeout);

    productDetailTooltipTimeout = setTimeout(() => {
        let apiUrl = '/admin/reports/api/top-selling-product-for-period?';
        if (type === 'daily') {
            apiUrl += `date=${params.date}`;
        } else if (type === 'monthly') {
            apiUrl += `month=${params.month}&year=${params.year}`;
        } else {
            console.error('Invalid type for top selling product tooltip.');
            return;
        }

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    // If no product found (404), just hide the tooltip without error
                    if (response.status === 404) {
                        return Promise.reject('No product found for this period.');
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(product => {
                // Populate tooltip with product data (similar to showProductDetailTooltip)
                tooltipProductThumbnail.src = product.thumbnail_url;
                tooltipProductName.textContent = `SP bán chạy: ${product.name}`; // Indicate it's a top product
                tooltipProductPrice.textContent = product.price;
                tooltipProductStock.textContent = product.stock_quantity;
                tooltipProductStatus.textContent = product.status_text;
                tooltipProductCategory.textContent = product.category;
                tooltipProductBrand.textContent = product.brand;
                // For top product, only show relevant fields
                tooltipProductMaterial.textContent = `Đã bán: ${product.total_quantity_sold}`; // Re-purpose for quantity sold
                tooltipProductColor.textContent = `Doanh thu: ${product.total_revenue_generated}`; // Re-purpose for revenue generated
                tooltipProductSpecs.textContent = ''; // Clear if not relevant
                tooltipProductVehicleModels.textContent = ''; // Clear if not relevant
                tooltipProductRating.textContent = ''; // Clear if not relevant
                tooltipProductReviewsCount.textContent = ''; // Clear if not relevant


                // Position and show the tooltip
                productDetailTooltip.style.left = `${event.pageX + 15}px`;
                productDetailTooltip.style.top = `${event.pageY + 15}px`;
                productDetailTooltip.style.display = 'block';
            })
            .catch(error => {
                console.warn('Could not fetch top product for revenue period:', error);
                productDetailTooltip.style.display = 'none'; // Hide tooltip if no product or error
            });
    }, 300);
}

/**
 * Hides the product detail tooltip.
 */
function hideProductDetailTooltip() {
    clearTimeout(productDetailTooltipTimeout);
    productDetailTooltip.style.display = 'none';
}

// Ensure the main reports_main.js loads this script
// and calls functions to attach event listeners.
// These listeners will be added in low_stock_products.js and best_selling_products.js
// and now in daily_revenue_chart.js and monthly_revenue_chart.js