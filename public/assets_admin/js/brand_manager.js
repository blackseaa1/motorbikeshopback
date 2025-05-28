function initializeBrandsPage() {
    console.log("Khởi tạo JS cho trang Thương hiệu...");

    // Logic for Update Modal
    const updateBrandModal = document.getElementById('updateBrandModal');
    if (updateBrandModal) {
        updateBrandModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const logoUrl = button.getAttribute('data-logo-url');
            const updateUrl = button.getAttribute('data-update-url');

            const modalForm = updateBrandModal.querySelector('#updateBrandForm');
            const nameInput = updateBrandModal.querySelector('#brandNameUpdate');
            const descriptionInput = updateBrandModal.querySelector('#brandDescriptionUpdate');
            const logoPreview = updateBrandModal.querySelector('#brandLogoPreviewUpdate');

            modalForm.action = updateUrl;
            nameInput.value = name;
            descriptionInput.value = description;
            logoPreview.src = logoUrl;
        });
    }

    // Logic for Delete Modal
    const deleteBrandModal = document.getElementById('deleteBrandModal');
    if (deleteBrandModal) {
        deleteBrandModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const name = button.getAttribute('data-name');
            const deleteUrl = button.getAttribute('data-delete-url');

            const modalForm = deleteBrandModal.querySelector('#deleteBrandForm');
            const brandNameSpan = deleteBrandModal.querySelector('#brandNameToDelete');

            modalForm.action = deleteUrl;
            brandNameSpan.textContent = name;
        });
    }
}