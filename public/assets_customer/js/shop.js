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
            // Trigger product fetch when vehicle brand changes
            triggerProductFetch();
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

        // Helper function to get form data and trigger product fetch
        function triggerProductFetch() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            const newUrl = `${window.productsPageUrl}?${params}`;
            window.history.pushState({ path: newUrl }, '', newUrl);

            const apiUrl = `${window.productsApiUrl}?${params}`;
            fetchProducts(apiUrl);
        }

        // Add event listeners for selectpicker changes
        // Use 'changed.bs.select' event for bootstrap-select
        $('select[name="categories[]"]').on('changed.bs.select', triggerProductFetch);
        $('select[name="brands[]"]').on('changed.bs.select', triggerProductFetch);
        $('#vehicle-model-select').on('changed.bs.select', triggerProductFetch);


        // Sự kiện submit form (keep this, it will also call triggerProductFetch)
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerProductFetch();
        });

        // Sự kiện click phân trang (sử dụng event delegation)
        document.body.addEventListener('click', function (e) {
            const targetLink = e.target.closest('#pagination-links a.page-link');
            if (!targetLink || !targetLink.href) return;

            e.preventDefault();

            // Lấy URL đầy đủ từ liên kết phân trang
            const fullUrl = new URL(targetLink.href);

            // Cập nhật URL trên thanh địa chỉ của trình duyệt
            const newBrowserUrl = `${window.productsPageUrl}${fullUrl.search}`;
            window.history.pushState({ path: newBrowserUrl }, '', newBrowserUrl);

            // Lấy chỉ các tham số truy vấn từ URL phân trang
            const params = fullUrl.search;

            // Xây dựng URL API với các tham số phân trang
            const apiUrl = `${window.productsApiUrl}${params}`; // ĐÃ SỬA: Đảm bảo gọi API endpoint

            fetchProducts(apiUrl); // Gọi hàm fetchProducts với URL API
        });

        // *** THAY ĐỔI MỚI: Kích hoạt tìm kiếm sản phẩm ban đầu khi trang tải nếu có tham số trong URL ***
        // Kiểm tra xem có bất kỳ tham số truy vấn nào trong URL hiện tại không
        if (window.location.search) {
            triggerProductFetch();
        }
    };
})();