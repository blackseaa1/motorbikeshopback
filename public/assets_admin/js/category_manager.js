document.addEventListener('DOMContentLoaded', function() {
    // --- JAVASCRIPT FOR MODALS ---

    // 1. Logic for Update Modal
    const updateCategoryModal = document.getElementById('updateCategoryModal');
    if (updateCategoryModal) {
        updateCategoryModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Button that triggered the modal

            // Lấy thông tin từ các thuộc tính data-* của nút bấm
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const updateUrl = button.getAttribute('data-update-url');

            // Cập nhật nội dung của modal
            const modalForm = updateCategoryModal.querySelector('#updateCategoryForm');
            const modalTitle = updateCategoryModal.querySelector('.modal-title');
            const nameInput = updateCategoryModal.querySelector('#categoryNameUpdate');
            const descriptionInput = updateCategoryModal.querySelector('#categoryDescriptionUpdate');
            
            modalForm.action = updateUrl;
            modalTitle.textContent = 'Cập nhật Danh mục: ' + name;
            nameInput.value = name;
            descriptionInput.value = description;
        });
    }

    // 2. Logic for Delete Modal
    const deleteCategoryModal = document.getElementById('deleteCategoryModal');
    if (deleteCategoryModal) {
        deleteCategoryModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const name = button.getAttribute('data-name');
            const deleteUrl = button.getAttribute('data-delete-url');

            const modalForm = deleteCategoryModal.querySelector('#deleteCategoryForm');
            const categoryNameSpan = deleteCategoryModal.querySelector('#categoryNameToDelete');

            modalForm.action = deleteUrl;
            categoryNameSpan.textContent = name;
        });
    }
});