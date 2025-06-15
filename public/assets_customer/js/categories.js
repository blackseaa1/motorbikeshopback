/**
 * ===================================================================
 * categories.js
 *
 * Chứa tất cả các mã JavaScript dành riêng cho trang Danh mục sản phẩm.
 * Hàm initializeCategoriesPage sẽ được gọi bởi "nhạc trưởng" customer_layout.js.
 * ===================================================================
 */
window.initializeCategoriesPage = function () {
    // Dùng console.log để kiểm tra xem hàm có được gọi không khi bạn ở trang categories
    console.log("Khởi tạo mã cho trang Danh mục...");

    // --- Phần 1: Xử lý click vào thẻ danh mục ---
    const categoryCards = document.querySelectorAll('.category-card');

    categoryCards.forEach(card => {
        card.addEventListener('click', function () {
            const url = this.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });

        // Thêm hiệu ứng hover
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 15px rgba(0,0,0,0.1)';
            this.style.cursor = 'pointer';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });

    // --- Phần 2: Xử lý bộ lọc tự động ---
    const filterForm = document.getElementById('filter-form');
    const brandSelect = document.getElementById('brand-filter');
    const vehicleBrandSelect = document.getElementById('vehicle-brand-filter');

    // Hàm để submit form filter
    const submitFilterForm = () => {
        if (filterForm) {
            // Hiển thị loading overlay trước khi tải lại trang
            window.showAppLoader();
            filterForm.submit();
        }
    };

    // Tự động submit khi thay đổi lựa chọn
    if (brandSelect) {
        brandSelect.addEventListener('change', submitFilterForm);
    }
    if (vehicleBrandSelect) {
        vehicleBrandSelect.addEventListener('change', submitFilterForm);
    }
};