document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');

    if (!loginForm) {
        return;
    }

    const submitButton = loginForm.querySelector('button[type="submit"]');
    const errorAlert = document.getElementById('loginErrorAlert');

    // THAY ĐỔI Ở ĐÂY: Trỏ đến ID của lớp phủ
    const loadingOverlay = document.getElementById('loadingOverlay');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const loginUrl = loginForm.dataset.action;

    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Chuẩn bị UI
        errorAlert.classList.add('d-none');
        submitButton.disabled = true;

        // THAY ĐỔI Ở ĐÂY: Hiển thị lớp phủ
        loadingOverlay.classList.remove('d-none');

        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(loginUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // Không cần ẩn overlay ở đây vì trang sẽ được chuyển hướng ngay lập tức
                window.location.href = result.redirect_url;
            } else {
                if (result.errors && result.errors.email) {
                    errorAlert.textContent = result.errors.email[0];
                } else {
                    errorAlert.textContent = result.message || 'Đã có lỗi xảy ra.';
                }
                errorAlert.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Login request failed:', error);
            errorAlert.textContent = 'Không thể kết nối đến máy chủ. Vui lòng thử lại.';
            errorAlert.classList.remove('d-none');
        } finally {
            // Khôi phục UI
            submitButton.disabled = false;

            // THAY ĐỔI Ở ĐÂY: Ẩn lớp phủ đi nếu có lỗi
            if (loadingOverlay) {
                loadingOverlay.classList.add('d-none');
            }
        }
    });
});