function initializeCategoriesPage() {
    console.log("Khởi tạo JS cho trang Danh mục...");

    // Logic for Update Modal
    const updateCategoryModal = document.getElementById('updateCategoryModal');
    if (updateCategoryModal) {
        updateCategoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const updateUrl = button.getAttribute('data-update-url');

            const modalForm = updateCategoryModal.querySelector('#updateCategoryForm');
            const nameInput = updateCategoryModal.querySelector('#categoryNameUpdate');
            const descriptionInput = updateCategoryModal.querySelector('#categoryDescriptionUpdate');

            if (modalForm) modalForm.action = updateUrl; // Thêm kiểm tra null cho an toàn
            if (nameInput) nameInput.value = name;
            if (descriptionInput) descriptionInput.value = description;
        });
    }

    // Logic for Delete Modal
    const deleteCategoryModal = document.getElementById('deleteCategoryModal');
    if (deleteCategoryModal) {
        deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const name = button.getAttribute('data-name');
            const deleteUrl = button.getAttribute('data-delete-url');

            // SỬA Ở ĐÂY:
            const modalForm = deleteCategoryModal.querySelector('#deleteCategoryForm'); // Sửa ID selector
            const categoryNameSpan = deleteCategoryModal.querySelector('#categoryNameToDelete');

            if (modalForm) { // Thêm kiểm tra null để tránh lỗi nếu selector vẫn sai
                modalForm.action = deleteUrl;
            } else {
                console.error('Không tìm thấy deleteCategoryForm trong modal!');
            }
            if (categoryNameSpan) categoryNameSpan.textContent = name;
        });
    }
}

// Gọi hàm khởi tạo nếu trang đã tải xong (ví dụ)
// Hoặc đảm bảo file này được nạp sau khi DOM sẵn sàng
// document.addEventListener('DOMContentLoaded', initializeCategoriesPage);
// Nếu bạn dùng admin_layout.js để gọi, thì không cần dòng trên.