/**
 * checkout.js - Handles checkout page functionality with Bootstrap Select
 */
(function () {
    'use strict';

    // Global variables
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let isUpdatingSummary = false;

    /**
     * Renders the checkout summary with provided data.
     * @param {Object} data - Summary data from server.
     */
    function renderCheckoutSummary(data) {
        const elements = {
            subtotal: document.getElementById('summary-subtotal'),
            shippingFee: document.getElementById('summary-shipping-fee'),
            discount: document.getElementById('summary-discount'),
            grandTotal: document.getElementById('summary-grand-total'),
            discountRow: document.getElementById('summary-discount-row'),
            promoFeedback: document.getElementById('promo-feedback')
        };

        // Format numbers to Vietnamese locale
        elements.subtotal.textContent = `${Number(data.subtotal || 0).toLocaleString('vi-VN')} ₫`;
        elements.shippingFee.textContent = `${Number(data.shipping_fee || 0).toLocaleString('vi-VN')} ₫`;
        elements.discount.textContent = `-${Number(data.discount_amount || 0).toLocaleString('vi-VN')} ₫`;
        elements.grandTotal.textContent = `${Number(data.grand_total || 0).toLocaleString('vi-VN')} ₫`;

        // Toggle discount row visibility
        elements.discountRow.classList.toggle('d-none', (data.discount_amount || 0) <= 0);

        // Update promotion feedback
        if (elements.promoFeedback) {
            elements.promoFeedback.innerHTML = data.promotion_info?.code
                ? `<div class="text-success small mt-1">Đã áp dụng mã: <strong>${data.promotion_info.code}</strong></div>`
                : '';
        }
    }

    /**
     * Sends request to update checkout summary.
     */
    async function updateCheckoutSummary() {
        if (isUpdatingSummary) return;
        isUpdatingSummary = true;
        window.showAppLoader();

        try {
            const payload = {
                delivery_service_id: $('#delivery_service_id').val(),
                shipping_address_id: document.getElementById('shipping_address_id')?.value,
                promotion_code: document.getElementById('promotion_code')?.value
            };

            const response = await fetch('/api/cart/update-summary', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || Object.values(data.errors || {}).flat().join('\n') || 'Có lỗi xảy ra.');
            }

            renderCheckoutSummary(data);
        } catch (error) {
            window.showAppInfoModal(error.message, 'error');
        } finally {
            window.hideAppLoader();
            isUpdatingSummary = false;
        }
    }

    /**
     * Sets up dependent address dropdowns for guest users.
     */
    function setupGuestAddressDropdowns() {
        const selectors = {
            province: document.getElementById('guest_province_id'),
            district: document.getElementById('guest_district_id'),
            ward: document.getElementById('guest_ward_id')
        };

        if (!selectors.province || !selectors.district || !selectors.ward) return;

        /**
         * Populates a dropdown with items.
         * @param {HTMLSelectElement} select - The select element.
         * @param {Array} items - Array of {id, name} objects.
         * @param {string} placeholder - Placeholder text.
         */
        const populateDropdown = (select, items, placeholder) => {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            items.forEach(item => select.add(new Option(item.name, item.id)));
            select.disabled = false;
        };

        // Province change handler
        selectors.province.addEventListener('change', async () => {
            const provinceId = selectors.province.value;
            selectors.district.disabled = true;
            selectors.ward.disabled = true;
            selectors.district.innerHTML = '<option value="">Đang tải...</option>';
            selectors.ward.innerHTML = '<option value="">Chọn Phường/Xã</option>';

            if (!provinceId) return;

            try {
                const response = await fetch(`/api/provinces/${provinceId}/districts`);
                const districts = await response.json();
                populateDropdown(selectors.district, districts, 'Chọn Quận/Huyện');
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        });

        // District change handler
        selectors.district.addEventListener('change', async () => {
            const districtId = selectors.district.value;
            selectors.ward.disabled = true;
            selectors.ward.innerHTML = '<option value="">Đang tải...</option>';

            if (!districtId) return;

            try {
                const response = await fetch(`/api/districts/${districtId}/wards`);
                const wards = await response.json();
                populateDropdown(selectors.ward, wards, 'Chọn Phường/Xã');
            } catch (error) {
                console.error('Error loading wards:', error);
            }
        });
    }

    /**
     * Clears the promotion code and updates summary.
     */
    function removePromotionCode() {
        const promoInput = document.getElementById('promotion_code');
        const clearButton = document.getElementById('clear-promo-btn');

        if (promoInput) {
            promoInput.value = '';
        }
        if (clearButton) {
            clearButton.classList.add('d-none');
        }

        updateCheckoutSummary();
    }

    /**
     * Initializes the checkout page.
     */
    window.initializeCheckoutPage = () => {
        if (!document.getElementById('checkout-form')) return;

        // Initialize Bootstrap Select
        try {
            $('.selectpicker').selectpicker();
        } catch (error) {
            console.error('Bootstrap Select initialization failed:', error);
        }

        // Setup guest address dropdowns
        setupGuestAddressDropdowns();

        // Toggle clear button based on promotion code input
        const promoInput = document.getElementById('promotion_code');
        const clearButton = document.getElementById('clear-promo-btn');
        if (promoInput && clearButton) {
            clearButton.classList.toggle('d-none', !promoInput.value);
            promoInput.addEventListener('input', () => {
                clearButton.classList.toggle('d-none', !promoInput.value);
            });
        }

        // Event listeners
        $('#shipping_address_id, #delivery_service_id').on('changed.bs.select', updateCheckoutSummary);
        $('#apply-promo-btn').on('click', updateCheckoutSummary);
        $('#clear-promo-btn').on('click', removePromotionCode);
    };
})();