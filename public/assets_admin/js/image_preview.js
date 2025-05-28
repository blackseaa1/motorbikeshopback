// public/assets_admin/js/image_preview.js

function initializeImagePreviews() {
    const imagePreviews = [
        ['adminAvatarCreate', 'adminAvatarPreviewCreate'], ['adminAvatarUpdate', 'adminAvatarPreviewUpdate'],
        ['customerAvatarCreate', 'customerAvatarPreviewCreate'], ['customerAvatarUpdate', 'customerAvatarPreviewUpdate'],
        ['categoryLogoCreate', 'categoryLogoPreviewCreate'], ['categoryLogoUpdate', 'categoryLogoPreviewUpdate'],
    ];

    function rebindEventListener(element, eventType, handler) {
        if (!element) return;
        const newElement = element.cloneNode(true);
        element.parentNode.replaceChild(newElement, element);
        newElement.addEventListener(eventType, handler);
    }

    function setupPreviewImage(inputId, previewId) {
        const input = document.getElementById(inputId);
        if (input) {
            console.log(`Image Preview: Thiết lập xem trước cho #${inputId}`);
            rebindEventListener(input, 'change', function (event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const currentPreview = document.getElementById(previewId);
                        if (currentPreview) currentPreview.src = e.target.result;
                    }
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        }
    }

    imagePreviews.forEach(pair => setupPreviewImage(pair[0], pair[1]));
}