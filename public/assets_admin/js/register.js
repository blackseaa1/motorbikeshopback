/**
 * ===================================================================
 * register.js
 *
 * Xử lý logic cho trang đăng ký admin bằng AJAX.
 * ===================================================================
 */

// File: public/assets_admin/js/register.js

(function () {
    'use strict';

    // Đảm bảo hàm này được gọi sau khi DOM đã tải hoàn toàn
    function initializeRegisterPage() {
        const registerForm = document.getElementById('registerForm');
        if (!registerForm) {
            // console.log("Register JS: Form đăng ký không tìm thấy.");
            return;
        }
        // console.log("Register JS: Khởi tạo trang đăng ký.");

        const passwordInput = document.getElementById('passwordInput');
        const passwordConfirmationInput = document.getElementById('passwordConfirmationInput');
        const submitButton = registerForm.querySelector('button[type="submit"]');

        // Các element hiển thị yêu cầu mật khẩu
        const lengthReq = document.getElementById('lengthReq');
        const uppercaseReq = document.getElementById('uppercaseReq');
        const lowercaseReq = document.getElementById('lowercaseReq');
        const numberReq = document.getElementById('numberReq');
        const symbolReq = document.getElementById('symbolReq');
        const passwordRequirementsContainer = document.getElementById('passwordRequirements');
        const passwordConfirmationHelp = document.getElementById('passwordConfirmationHelp');

        let passwordFocusedOnce = false; // Biến cờ để chỉ hiển thị lỗi yêu cầu sau khi focus lần đầu

        function updateRequirementStatus(element, isValid, hasInteracted) {
            if (!element) return;
            element.classList.remove('valid', 'invalid');
            if (isValid) {
                element.classList.add('valid');
            } else if (hasInteracted && passwordInput.value.length > 0) {
                // Chỉ thêm 'invalid' nếu người dùng đã tương tác và có nhập liệu
                element.classList.add('invalid');
            }
        }

        function checkPasswordRequirements() {
            if (!passwordInput || !passwordRequirementsContainer) return false;

            const password = passwordInput.value;
            let allValid = true;
            const hasInteracted = passwordFocusedOnce || document.activeElement === passwordInput;

            // 1. Độ dài
            const isLengthValid = password.length >= 8;
            updateRequirementStatus(lengthReq, isLengthValid, hasInteracted);
            if (!isLengthValid) allValid = false;

            // 2. Chữ hoa
            const hasUppercase = /[A-Z]/.test(password);
            updateRequirementStatus(uppercaseReq, hasUppercase, hasInteracted);
            if (!hasUppercase) allValid = false;

            // 3. Chữ thường
            const hasLowercase = /[a-z]/.test(password);
            updateRequirementStatus(lowercaseReq, hasLowercase, hasInteracted);
            if (!hasLowercase) allValid = false;

            // 4. Số
            const hasNumber = /[0-9]/.test(password);
            updateRequirementStatus(numberReq, hasNumber, hasInteracted);
            if (!hasNumber) allValid = false;

            // 5. Ký tự đặc biệt
            const hasSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`£€¥₩₽₹]/.test(password);
            updateRequirementStatus(symbolReq, hasSymbol, hasInteracted);
            if (!hasSymbol) allValid = false;

            return allValid;
        }

        function checkPasswordMatch() {
            if (!passwordInput || !passwordConfirmationInput || !passwordConfirmationHelp) return false;

            const password = passwordInput.value;
            const confirmPassword = passwordConfirmationInput.value;

            passwordConfirmationHelp.classList.remove('valid', 'invalid');
            passwordConfirmationInput.classList.remove('is-valid', 'is-invalid');

            if (confirmPassword.length === 0 && password.length === 0) {
                passwordConfirmationHelp.textContent = '';
                return true; // Chưa nhập gì, coi như hợp lệ để không chặn form
            }

            if (confirmPassword.length === 0 && password.length > 0) {
                passwordConfirmationHelp.textContent = ''; // Nếu ô confirm rỗng thì không báo gì
                return false;
            }

            if (password === confirmPassword) {
                passwordConfirmationHelp.textContent = 'Mật khẩu xác nhận khớp.';
                passwordConfirmationHelp.classList.add('valid');
                passwordConfirmationInput.classList.add('is-valid');
                return true;
            } else {
                passwordConfirmationHelp.textContent = 'Mật khẩu xác nhận không khớp.';
                passwordConfirmationHelp.classList.add('invalid');
                passwordConfirmationInput.classList.add('is-invalid');
                return false;
            }
        }

        if (passwordInput) {
            passwordInput.addEventListener('focus', () => {
                passwordFocusedOnce = true;
                // Hiển thị các yêu cầu khi focus lần đầu (nếu muốn)
                if (passwordRequirementsContainer) passwordRequirementsContainer.style.display = 'block';
                checkPasswordRequirements(); // Kiểm tra ngay khi focus
            });
            passwordInput.addEventListener('input', () => {
                checkPasswordRequirements();
                checkPasswordMatch(); // Kiểm tra lại khớp khi mật khẩu chính thay đổi
            });
        }

        if (passwordConfirmationInput) {
            passwordConfirmationInput.addEventListener('input', checkPasswordMatch);
            passwordConfirmationInput.addEventListener('focus', checkPasswordMatch); // Kiểm tra ngay khi focus
        }

        // ------ PHẦN XỬ LÝ SUBMIT AJAX (Giữ nguyên hoặc điều chỉnh nếu cần) ------
        const errorModalElement = document.getElementById('registerErrorModal');
        let registerErrorModalInstance;
        if (errorModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                registerErrorModalInstance = new bootstrap.Modal(errorModalElement);
            } catch (e) {
                console.error("Register JS: Không thể khởi tạo Bootstrap Modal.", e);
            }
        }
        const registerErrorModalBody = document.getElementById('registerErrorModalBody');
        const errorAlertDiv = document.getElementById('registerErrorAlert');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const registerUrl = registerForm.dataset.action;
        const localLoadingOverlay = document.getElementById('loading-overlay');

        function showLoader() {
            if (localLoadingOverlay) localLoadingOverlay.classList.add('active');
        }
        function hideLoader() {
            if (localLoadingOverlay) localLoadingOverlay.classList.remove('active');
        }

        function displayAjaxError(messages, isHtml = false) {
            // ... (Hàm displayAjaxError của bạn)
            // Ví dụ đơn giản:
            let errorContent = '';
            if (typeof messages === 'string') {
                errorContent = messages;
            } else if (typeof messages === 'object' && messages !== null) {
                const ul = document.createElement('ul');
                ul.className = 'mb-0 ps-3 text-start';
                ul.style.listStyleType = 'disc';
                for (const key in messages) {
                    if (Array.isArray(messages[key])) {
                        messages[key].forEach(msg => {
                            const li = document.createElement('li');
                            li.textContent = msg;
                            ul.appendChild(li);
                        });
                    }
                }
                errorContent = ul.outerHTML;
                isHtml = true;
            }

            if (registerErrorModalBody && registerErrorModalInstance) {
                registerErrorModalBody.innerHTML = isHtml ? errorContent : escapeHtml(errorContent);
                registerErrorModalInstance.show();
            } else if (errorAlertDiv) {
                errorAlertDiv.innerHTML = isHtml ? errorContent : escapeHtml(errorContent);
                errorAlertDiv.classList.remove('d-none');
            } else {
                alert(isHtml ? "Có lỗi xảy ra, vui lòng xem chi tiết trong console." : errorContent);
            }
        }
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') return '';
            return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }


        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const allPasswordReqMet = checkPasswordRequirements();
            const passwordsMatched = checkPasswordMatch();

            if (!allPasswordReqMet || !passwordsMatched) {
                // Hiển thị lỗi chung nếu một trong các điều kiện client-side không đạt
                let clientSideErrorMessage = "Vui lòng kiểm tra lại thông tin đăng ký.";
                if (!allPasswordReqMet) {
                    clientSideErrorMessage = "Mật khẩu chưa đáp ứng đủ các yêu cầu. Vui lòng kiểm tra lại.";
                } else if (!passwordsMatched) {
                    clientSideErrorMessage = "Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.";
                }
                // Ưu tiên hiển thị lỗi này trước khi gửi lên server
                // Bạn có thể dùng hàm displayAjaxError hoặc một alert đơn giản
                if (errorAlertDiv) {
                    errorAlertDiv.textContent = clientSideErrorMessage;
                    errorAlertDiv.classList.remove('d-none');
                } else {
                    alert(clientSideErrorMessage);
                }
                // Cuộn lên đầu form hoặc đến phần lỗi để người dùng thấy
                // registerForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return; // Ngăn chặn submit nếu client-side validation thất bại
            }


            if (errorAlertDiv) errorAlertDiv.classList.add('d-none');
            if (registerErrorModalBody) registerErrorModalBody.innerHTML = '';
            registerForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            registerForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

            submitButton.disabled = true;
            showLoader();

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(registerUrl, {
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
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        alert(result.message || 'Đăng ký thành công! Vui lòng đăng nhập.');
                        window.location.href = '/admin/login'; // Fallback
                    }
                    return;
                } else if (response.status === 422 && result.errors) {
                    displayAjaxError(result.errors);
                    Object.keys(result.errors).forEach(key => {
                        const inputField = registerForm.querySelector(`[name="${key}"]`);
                        const errorField = inputField ? inputField.closest('.mb-3, .mb-4').querySelector('.invalid-feedback') : null;
                        if (inputField) {
                            inputField.classList.add('is-invalid');
                            if (errorField && !errorField.textContent) { // Chỉ ghi đè nếu chưa có lỗi từ JS
                                errorField.textContent = result.errors[key][0];
                                errorField.style.display = 'block'; // Đảm bảo hiển thị
                            }
                        }
                    });
                } else {
                    const errorMessage = result.message || 'Đã có lỗi xảy ra trong quá trình đăng ký.';
                    displayAjaxError(errorMessage);
                }
            } catch (error) {
                console.error('Register request failed:', error);
                displayAjaxError('Không thể kết nối đến máy chủ hoặc có lỗi không xác định. Vui lòng thử lại.');
            } finally {
                submitButton.disabled = false;
                hideLoader();
            }
        });
    } // Kết thúc initializeRegisterPage

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeRegisterPage);
    } else {
        initializeRegisterPage(); // DOM đã sẵn sàng
    }
})();