/**
 * ===================================================================
 * address_manager.js
 *
 * Xử lý logic cho các form địa chỉ của khách hàng.
 * - Dropdown Tỉnh -> Huyện -> Xã phụ thuộc.
 * - Tải lại danh sách khi chỉnh sửa.
 * ===================================================================
 */
(function () {
    'use strict';

    // Hàm helper để điền lựa chọn vào một dropdown
    function populateDropdown(selectElement, items, placeholder, selectedValue = null) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => {
            const option = new Option(item.name, item.id);
            if (selectedValue && item.id == selectedValue) {
                option.selected = true;
            }
            selectElement.add(option);
        });
        selectElement.disabled = false;
    }

    // Hàm để lấy danh sách Quận/Huyện từ API
    async function fetchDistricts(provinceId, districtSelect, wardSelect, selectedDistrictId = null) {
        if (!provinceId) {
            districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            districtSelect.disabled = true;
            wardSelect.disabled = true;
            return;
        }
        try {
            const response = await fetch(`/api/provinces/${provinceId}/districts`);
            if (!response.ok) throw new Error('Failed to fetch districts');
            const districts = await response.json();
            populateDropdown(districtSelect, districts, 'Chọn Quận/Huyện', selectedDistrictId);
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            wardSelect.disabled = true;

            // Nếu có selectedDistrictId (trang edit), tự động trigger để load xã
            if (selectedDistrictId) {
                districtSelect.dispatchEvent(new Event('change'));
            }
        } catch (error) {
            console.error(error);
        }
    }

    // Hàm để lấy danh sách Phường/Xã từ API
    async function fetchWards(districtId, wardSelect, selectedWardId = null) {
        if (!districtId) {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            wardSelect.disabled = true;
            return;
        }
        try {
            const response = await fetch(`/api/districts/${districtId}/wards`);
            if (!response.ok) throw new Error('Failed to fetch wards');
            const wards = await response.json();
            populateDropdown(wardSelect, wards, 'Chọn Phường/Xã', selectedWardId);
        } catch (error) {
            console.error(error);
        }
    }

    /**
     * Hàm khởi tạo chính, được gọi bởi customer_layout.js
     */
    window.initializeAddressForms = function() {
        const provinceSelect = document.getElementById('province_id');
        const districtSelect = document.getElementById('district_id');
        const wardSelect = document.getElementById('ward_id');

        // Chỉ chạy khi các element này tồn tại (tức là đang ở trang thêm/sửa địa chỉ)
        if (!provinceSelect || !districtSelect || !wardSelect) return;

        // Xử lý khi trang là trang Sửa (có sẵn giá trị cũ)
        const initialProvinceId = provinceSelect.value;
        const initialDistrictId = window.oldDistrictId || null;
        const initialWardId = window.oldWardId || null;

        if (initialProvinceId) {
            fetchDistricts(initialProvinceId, districtSelect, wardSelect, initialDistrictId).then(() => {
                if(initialDistrictId) {
                    fetchWards(initialDistrictId, wardSelect, initialWardId);
                }
            });
        }

        // Lắng nghe sự kiện khi người dùng thay đổi Tỉnh/Thành
        provinceSelect.addEventListener('change', function() {
            fetchDistricts(this.value, districtSelect, wardSelect);
        });

        // Lắng nghe sự kiện khi người dùng thay đổi Quận/Huyện
        districtSelect.addEventListener('change', function() {
            fetchWards(this.value, wardSelect);
        });
    }

})();