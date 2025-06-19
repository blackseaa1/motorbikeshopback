<<<<<<< HEAD
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
=======
/**
 * ===================================================================
 * shop.js
 *
 * Xử lý logic hoàn chỉnh cho trang cửa hàng / lọc sản phẩm.
 * - Khởi tạo bộ lọc bootstrap-select.
 * - Xử lý dropdown phụ thuộc.
 * - Gọi API để lấy và render sản phẩm/phân trang một cách động.
 * ===================================================================
 */
(function () {
    'use strict';

    /**
     * Khởi tạo các thành phần trên trang cửa hàng.
     */
    window.initializeShopPage = function () {
        const filterForm = document.getElementById('filter-form');
        if (!filterForm) return; // Chỉ chạy script nếu có form lọc

        //======================================================================
        // 1. KHỞI TẠO BỘ LỌC & DROPDOWN PHỤ THUỘC
        //======================================================================
>>>>>>> c0d0073cd07a3039456b2079010e2e03cbac3c12

        // Khởi tạo bootstrap-select
        $('.selectpicker').selectpicker();

<<<<<<< HEAD
        // Xử lý dropdown phụ thuộc
        const vehicleData = window.vehicleDataForFilter || {};
        const modelSelect = $('#vehicle-model-select');
        function populateVehicleModels(brandId) {
            const currentModelVal = modelSelect.val();
            modelSelect.empty();
            if (brandId && vehicleData[brandId] && vehicleData[brandId].vehicle_models.length > 0) {
                vehicleData[brandId].vehicle_models.forEach(model => {
=======
        // Lấy dữ liệu cho dropdown phụ thuộc từ view
        const vehicleData = window.vehicleDataForFilter || {};
        const selectedVehicleBrandId = window.selectedFilters.brandId || null;
        const selectedVehicleModelId = window.selectedFilters.modelId || null;
        const modelSelect = $('#vehicle-model-select');

        function populateVehicleModels(brandId) {
            modelSelect.empty();
            if (brandId && vehicleData[brandId] && vehicleData[brandId].vehicle_models.length > 0) {
                const models = vehicleData[brandId].vehicle_models;
                models.forEach(function (model) {
>>>>>>> c0d0073cd07a3039456b2079010e2e03cbac3c12
                    modelSelect.append($('<option>', { value: model.id, text: model.name }));
                });
                modelSelect.prop('disabled', false);
            } else {
                modelSelect.prop('disabled', true);
            }
            modelSelect.selectpicker('refresh');
<<<<<<< HEAD
            modelSelect.selectpicker('val', currentModelVal);
        }

        const initialBrandId = window.selectedFilters.brandId;
        if (initialBrandId) {
            populateVehicleModels(initialBrandId);
            if (window.selectedFilters.modelId) {
                modelSelect.selectpicker('val', window.selectedFilters.modelId);
            }
        }

=======
        }

        // Xử lý khi trang tải xong: nếu có loại xe được chọn sẵn, hiển thị các mẫu xe tương ứng
        if (selectedVehicleBrandId) {
            populateVehicleModels(selectedVehicleBrandId);
            if (selectedVehicleModelId) {
                modelSelect.selectpicker('val', selectedVehicleModelId);
            }
        }

        // Xử lý khi người dùng thay đổi lựa chọn Loại xe
>>>>>>> c0d0073cd07a3039456b2079010e2e03cbac3c12
        $('#vehicle-brand-select').on('changed.bs.select', function () {
            populateVehicleModels($(this).val());
        });

<<<<<<< HEAD
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
=======

        //======================================================================
        // 2. CÁC HÀM RENDER GIAO DIỆN TỪ DỮ LIỆU API
        //======================================================================

        function renderProducts(products) {
            const container = document.getElementById('product-list');
            container.innerHTML = ''; // Xóa sản phẩm cũ

            if (!products || products.length === 0) {
                container.innerHTML = `<div class="col-12"><div class="alert alert-warning text-center">Không tìm thấy sản phẩm nào phù hợp.</div></div>`;
                return;
            }

            products.forEach(product => {
                // Tạo nút "Thêm vào giỏ" với class và data-attribute cần thiết cho cart.js
                const productHtml = `
                    <div class="col">
                        <div class="card h-100 product-card shadow-sm">
                            <a href="/products/${product.id}">
                                <img src="${product.thumbnail_url}" class="card-img-top" alt="${product.name}">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title flex-grow-1">
                                    <a href="/products/${product.id}" class="text-decoration-none text-dark stretched-link">${product.name}</a>
                                </h5>
                                <p class="card-text text-danger fw-bold fs-5 mt-auto mb-2">${product.formatted_price}</p>
                                <div class="d-grid">
                                    <button class="btn btn-primary btn-sm add-to-cart-btn" data-product-id="${product.id}">
                                        <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                container.insertAdjacentHTML('beforeend', productHtml);
            });
        }

        function renderPagination(links) {
            const container = document.getElementById('pagination-links');
            container.innerHTML = ''; // Xóa phân trang cũ
            if (!links || links.length <= 3) return; // Không render nếu chỉ có prev/next và 1 trang

            const paginationList = document.createElement('ul');
            paginationList.className = 'pagination';

            links.forEach(link => {
                const pageItem = document.createElement('li');
                pageItem.className = `page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`;

                const pageLink = document.createElement('a');
                pageLink.className = 'page-link';
                pageLink.href = link.url || '#!';
                pageLink.innerHTML = link.label;
                if (link.url) {
                    pageLink.dataset.url = link.url; // Lưu URL để JS bắt sự kiện
                }

                pageItem.appendChild(pageLink);
                paginationList.appendChild(pageItem);
            });
            container.appendChild(paginationList);
        }

        //======================================================================
        // 3. HÀM FETCH DỮ LIỆU CHÍNH TỪ API
        //======================================================================

        async function fetchProducts(url) {
            window.showAppLoader();
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Network response was not ok.');

                const data = await response.json();

                renderProducts(data.data);
                renderPagination(data.links);
                document.getElementById('product-count').textContent = `Tìm thấy ${data.total} sản phẩm`;

            } catch (error) {
                console.error('Lỗi khi fetch sản phẩm:', error);
                document.getElementById('product-list').innerHTML = `<div class="col-12"><div class="alert alert-danger text-center">Không thể tải dữ liệu sản phẩm. Vui lòng thử lại.</div></div>`;
                window.showAppInfoModal('Không thể tải dữ liệu sản phẩm. Vui lòng thử lại.', 'error');
            } finally {
                window.hideAppLoader();
            }
        }


        //======================================================================
        // 4. GẮN SỰ KIỆN VÀ KHỞI CHẠY
        //======================================================================

        // Sự kiện submit form lọc
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            // Sửa lại đường dẫn API cho đúng
            const url = `/api/customer/products?${params.toString()}`;
            // Thay đổi URL trên thanh địa chỉ mà không tải lại trang
            window.history.pushState({}, '', `/products?${params.toString()}`);
            fetchProducts(url);
        });

        // Sự kiện click vào link phân trang
        document.getElementById('pagination-links').addEventListener('click', function (e) {
            const targetLink = e.target.closest('a');
            if (!targetLink) return;
            e.preventDefault();

            const url = targetLink.dataset.url;
            if (url) {
                // Lấy phần query string từ URL của API
                const queryString = new URL(url).search;
                window.history.pushState({}, '', `/products${queryString}`);
                fetchProducts(url);
            }
        });

        // Tải sản phẩm lần đầu tiên khi trang được mở
        // Sử dụng query string từ URL hiện tại để gọi API
        const initialApiUrl = `/api/customer/products${window.location.search}`;
        fetchProducts(initialApiUrl);

    };
})();

>>>>>>> c0d0073cd07a3039456b2079010e2e03cbac3c12
