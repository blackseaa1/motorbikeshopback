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

            // Thêm một option mặc định "Tất cả dòng xe"
            modelSelect.append($('<option>', { value: '', text: 'Tất cả dòng xe' }));

            if (brandId && vehicleData[brandId] && vehicleData[brandId].vehicle_models.length > 0) {
                vehicleData[brandId].vehicle_models.forEach(model => {
                    modelSelect.append($('<option>', { value: model.id, text: model.name }));
                });
                modelSelect.prop('disabled', false);
            } else {
                modelSelect.prop('disabled', true);
            }
            modelSelect.selectpicker('refresh');
            // Cố gắng đặt lại giá trị cũ nếu có
            if (currentModelVal) {
                modelSelect.selectpicker('val', currentModelVal);
            }
        }

        // Khi trang tải, kiểm tra xem có hãng xe nào được chọn sẵn không và tải các dòng xe tương ứng
        const initialBrandId = $('#vehicle-brand-select').val();
        if (initialBrandId) {
            populateVehicleModels(initialBrandId);
        }

        $('#vehicle-brand-select').on('changed.bs.select', function () {
            populateVehicleModels($(this).val());
            triggerProductFetch(); // Kích hoạt tìm kiếm khi thay đổi hãng xe
        });

        // Hàm gọi API và cập nhật giao diện
        async function fetchProducts(url) {
            if (loadingOverlay) loadingOverlay.style.display = 'flex';
            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();
                productListWrapper.innerHTML = data.products_html;
                if (paginationLinks) paginationLinks.innerHTML = data.pagination_html;
                if (productCount) productCount.textContent = `Tìm thấy ${data.total} sản phẩm`;

            } catch (error) {
                console.error('Fetch error:', error);
                if (productCount) productCount.textContent = 'Có lỗi xảy ra khi tải sản phẩm.';
            } finally {
                if (loadingOverlay) loadingOverlay.style.display = 'none';
            }
        }

        // Hàm lấy dữ liệu form và kích hoạt tìm kiếm
        function triggerProductFetch() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            const newUrl = `${window.productsPageUrl}?${params}`;
            window.history.pushState({ path: newUrl }, '', newUrl);

            const apiUrl = `${window.productsApiUrl}?${params}`;
            fetchProducts(apiUrl);
        }

        // Gắn sự kiện cho các bộ lọc
        $('select[name="categories[]"]').on('changed.bs.select', triggerProductFetch);
        $('select[name="brands[]"]').on('changed.bs.select', triggerProductFetch);
        $('#vehicle-model-select').on('changed.bs.select', triggerProductFetch);
        // Có thể thêm các input khác như price-range ở đây

        // Sự kiện submit form
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerProductFetch();
        });

        // Sự kiện click phân trang (sử dụng event delegation)
        document.body.addEventListener('click', function (e) {
            const targetLink = e.target.closest('#pagination-links a.page-link');
            if (!targetLink || !targetLink.href) return;

            e.preventDefault();
            const fullUrl = new URL(targetLink.href);
            const newBrowserUrl = `${window.productsPageUrl}${fullUrl.search}`;
            window.history.pushState({ path: newBrowserUrl }, '', newBrowserUrl);
            const apiUrl = `${window.productsApiUrl}${fullUrl.search}`;
            fetchProducts(apiUrl);
        });

        // *** ĐÃ XÓA ĐOẠN CODE GÂY LỖI Ở ĐÂY ***
        // Không cần gọi triggerProductFetch() khi trang tải,
        // vì server đã xử lý việc hiển thị sản phẩm ban đầu rồi.
    };
})();
