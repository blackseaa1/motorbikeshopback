// Script đơn giản để xử lý form (ví dụ)
        // Trong một ứng dụng thực tế, bạn sẽ gửi dữ liệu này đến server
        const loginForm = document.getElementById('loginForm');
        const loginErrorAlert = document.getElementById('loginErrorAlert');

        loginForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Ngăn chặn việc gửi form mặc định

            const email = document.getElementById('emailInput').value;
            const password = document.getElementById('passwordInput').value;

            // Logic xác thực mẫu (thay thế bằng logic thực tế của bạn)
            if (email === "test@example.com" && password === "password") {
                // Đăng nhập thành công
                loginErrorAlert.classList.add('d-none'); // Ẩn thông báo lỗi nếu có
                alert('Đăng nhập thành công!'); // Thay bằng chuyển hướng hoặc hành động khác
                // window.location.href = "/dashboard"; // Ví dụ chuyển hướng
            } else {
                // Đăng nhập thất bại
                loginErrorAlert.textContent = "Email hoặc mật khẩu không chính xác. Vui lòng thử lại.";
                loginErrorAlert.classList.remove('d-none'); // Hiển thị thông báo lỗi
            }
        });