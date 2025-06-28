(function () {
    'use strict';

    window.initializeShopPage = function () {
        console.log('Khởi tạo chức năng cho trang sản phẩm...');
        const filterForm = document.getElementById('filter-form');
        if (!filterForm) return;

        // Elements
        const productListWrapper = document.getElementById('product-list-wrapper');
        const paginationLinks = document.getElementById('pagination-links');
        const productCount = document.getElementById('product-count');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Khởi tạo bootstrap-select
        $('.selectpicker').selectpicker();

        // Xử lý dropdown phụ thuộc
        const vehicleData = window.vehicleDataForFilter || {};
        const modelSelect = $('#vehicle-model-select');
        function populateVehicleModels(brandId) {
            const currentModelVal = modelSelect.val();
            modelSelect.empty();
            if (brandId && vehicleData[brandId] && vehicleData[brandId].vehicle_models.length > 0) {
                vehicleData[brandId].vehicle_models.forEach(model => {
                    modelSelect.append($('<option>', { value: model.id, text: model.name }));
                });
                modelSelect.prop('disabled', false);
            } else {
                modelSelect.prop('disabled', true);
            }
            modelSelect.selectpicker('refresh');
            modelSelect.selectpicker('val', currentModelVal);
        }

        const initialBrandId = window.selectedFilters.brandId;
        if (initialBrandId) {
            populateVehicleModels(initialBrandId);
            if (window.selectedFilters.modelId) {
                modelSelect.selectpicker('val', window.selectedFilters.modelId);
            }
        }

        $('#vehicle-brand-select').on('changed.bs.select', function () {
            populateVehicleModels($(this).val());
        });

        // Hàm gọi API và cập nhật giao diện
        async function fetchProducts(url) {
            if (loadingOverlay) loadingOverlay.style.display = 'flex';
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                productListWrapper.innerHTML = data.products_html;
                paginationLinks.innerHTML = data.pagination_html;
                productCount.textContent = `Tìm thấy ${data.total} sản phẩm`;

            } catch (error) {
                console.error('Fetch error:', error);
            } finally {
                if (loadingOverlay) loadingOverlay.style.display = 'none';
            }
        }

        // Sự kiện submit form
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();

            const newUrl = `${window.productsPageUrl}?${params}`;
            window.history.pushState({ path: newUrl }, '', newUrl);

            const apiUrl = `${window.productsApiUrl}?${params}`;
            fetchProducts(apiUrl);
        });

        // Sự kiện click phân trang (sử dụng event delegation)
        document.body.addEventListener('click', function (e) {
            const targetLink = e.target.closest('#pagination-links a.page-link');
            if (!targetLink || !targetLink.href) return;

            e.preventDefault();
            const url = targetLink.href;
            const newUrl = `${window.productsPageUrl}${new URL(url).search}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
            fetchProducts(url);
        });
    };
})();
