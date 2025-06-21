/**
 * ===================================================================
 * checkout.js - PHIÊN BẢN SỬ DỤNG BOOTSTRAP SELECT
 * ===================================================================
 */
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let isUpdatingSummary = false;

    /**
     * HÀM 1: Cập nhật giao diện của khu vực tóm tắt đơn hàng.
     */
    function renderCheckoutSummary(data) {
        document.getElementById('summary-subtotal').textContent = `${Number(data.subtotal || 0).toLocaleString('vi-VN')} ₫`;
        document.getElementById('summary-shipping-fee').textContent = `${Number(data.shipping_fee || 0).toLocaleString('vi-VN')} ₫`;
        document.getElementById('summary-discount').textContent = `-${Number(data.discount_amount || 0).toLocaleString('vi-VN')} ₫`;
        document.getElementById('summary-grand-total').textContent = `${Number(data.grand_total || 0).toLocaleString('vi-VN')} ₫`;

        document.getElementById('summary-discount-row').classList.toggle('d-none', (data.discount_amount || 0) <= 0);

        const promoFeedback = document.getElementById('promo-feedback');
        if (promoFeedback) {
            if (data.promotion_info && data.promotion_info.code) {
                promoFeedback.innerHTML = `<div class='text-success small mt-1'>Đã áp dụng mã: <strong>${data.promotion_info.code}</strong></div>`;
            } else {
                promoFeedback.innerHTML = '';
            }
        }
    }

    /**
     * HÀM 2: Gửi yêu cầu cập nhật tóm tắt đơn hàng lên server.
     */
    async function updateCheckoutSummary() {
        if (isUpdatingSummary) return;
        isUpdatingSummary = true;
        window.showAppLoader();

        // Lấy giá trị từ bootstrap-select bằng jQuery
        const deliveryServiceId = $('#delivery_service_id').val();
        const shippingAddressId = document.getElementById('shipping_address_id')?.value;
        const promotionCode = document.getElementById('promotion_code')?.value;

        const payload = {
            delivery_service_id: deliveryServiceId,
            shipping_address_id: shippingAddressId,
            promotion_code: promotionCode,
        };

        try {
            const response = await fetch('/api/cart/update-summary', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok) {
                let errorMessage = data.message || 'Có lỗi xảy ra.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                throw new Error(errorMessage);
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
     * HÀM 3: Xử lý các dropdown địa chỉ phụ thuộc cho khách vãng lai.
     */
    function setupGuestAddressDropdowns() {
        const provinceSelect = document.getElementById('guest_province_id');
        const districtSelect = document.getElementById('guest_district_id');
        const wardSelect = document.getElementById('guest_ward_id');

        // Chỉ chạy nếu các element này tồn tại (tức là đang ở chế độ guest)
        if (!provinceSelect || !districtSelect || !wardSelect) return;

        // Hàm helper để điền lựa chọn vào dropdown
        const populateDropdown = (selectEl, items, placeholder) => {
            selectEl.innerHTML = `<option value=''>${placeholder}</option>`;
            items.forEach(item => {
                selectEl.add(new Option(item.name, item.id));
            });
            selectEl.disabled = false;
        };

        // Khi thay đổi Tỉnh/Thành
        provinceSelect.addEventListener('change', async function () {
            const provinceId = this.value;
            districtSelect.disabled = true;
            wardSelect.disabled = true;
            districtSelect.innerHTML = '<option value="">Đang tải...</option>';
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';

            if (!provinceId) return;

            try {
                const response = await fetch(`/api/provinces/${provinceId}/districts`);
                const districts = await response.json();
                populateDropdown(districtSelect, districts, 'Chọn Quận/Huyện');
            } catch (error) {
                console.error('Lỗi khi tải danh sách quận/huyện:', error);
            }
        });

        // Khi thay đổi Quận/Huyện
        districtSelect.addEventListener('change', async function () {
            const districtId = this.value;
            wardSelect.disabled = true;
            wardSelect.innerHTML = '<option value="">Đang tải...</option>';

            if (!districtId) return;

            try {
                const response = await fetch(`/api/districts/${districtId}/wards`);
                const wards = await response.json();
                populateDropdown(wardSelect, wards, 'Chọn Phường/Xã');
            } catch (error) {
                console.error('Lỗi khi tải danh sách phường/xã:', error);
            }
        });
    }

    /**
     * HÀM 4: Xử lý xóa mã giảm giá
     */
    function removePromotionCode() {
        // Clear the promotion code input field
        const promoInput = document.getElementById('promotion_code');
        if (promoInput) {
            promoInput.value = '';
        }

        // Trigger update of checkout summary with no promotion code
        updateCheckoutSummary();
    }

    /**
     * HÀM 5: KHỞI TẠO
     */
    window.initializeCheckoutPage = function () {
        if (!document.getElementById('checkout-form')) return;

        // CHẠY HÀM MỚI ĐỂ XỬ LÝ DROPDOWN CHO KHÁCH
        setupGuestAddressDropdowns();

        try {
            $('.selectpicker').selectpicker();
        } catch (e) {
            console.error("Lỗi Bootstrap-select", e);
        }

        $('#shipping_address_id, #delivery_service_id').on('changed.bs.select', updateCheckoutSummary);
        $('#apply-promo-btn').on('click', updateCheckoutSummary);
        $('#remove-promo-btn').on('click', removePromotionCode);
    };
})();