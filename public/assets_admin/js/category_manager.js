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
            // Thêm data-logo-url cho nút sửa danh mục nếu cần
            // const logoUrl = button.getAttribute('data-logo-url');
            const updateUrl = button.getAttribute('data-update-url');

            const modalForm = updateCategoryModal.querySelector('#updateCategoryForm');
            const nameInput = updateCategoryModal.querySelector('#categoryNameUpdate');
            const descriptionInput = updateCategoryModal.querySelector('#categoryDescriptionUpdate');
            // const logoPreview = updateCategoryModal.querySelector('#categoryLogoPreviewUpdate');

            modalForm.action = updateUrl;
            nameInput.value = name;
            descriptionInput.value = description;
            // logoPreview.src = logoUrl;
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

            const modalForm = deleteCategoryModal.querySelector('#deleteForm');
            const categoryNameSpan = deleteCategoryModal.querySelector('#categoryNameToDelete');

            modalForm.action = deleteUrl;
            categoryNameSpan.textContent = name;
        });
    }
}