/**
 * ===================================================================
 * force_password_change.js
 *
 * Xử lý validation phía client cho trang bắt buộc đổi mật khẩu.
 * - Cung cấp phản hồi tức thì về các yêu cầu của mật khẩu mới.
 * - Kiểm tra mật khẩu xác nhận có khớp không.
 * - Ngăn chặn việc gửi form nếu dữ liệu không hợp lệ.
 *
 * Cập nhật: 06/06/2025
 * ===================================================================
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Lấy các phần tử cần thiết từ DOM
        const passwordForm = document.querySelector('form');
        const passwordInput = document.getElementById('password');
        const confirmationInput = document.getElementById('password_confirmation');
        const requirementsContainer = document.getElementById('passwordRequirements');

        // Nếu không tìm thấy form hoặc các trường input, không làm gì cả
        if (!passwordForm || !passwordInput || !confirmationInput || !requirementsContainer) {
            return;
        }

        const requirementItems = requirementsContainer.querySelectorAll('.requirement-item');

        // Định nghĩa các yêu cầu validation bằng biểu thức chính quy (regex)
        const validations = {
            length: /.{8,}/,
            uppercase: /[A-Z]/,
            lowercase: /[a-z]/,
            number: /[0-9]/,
            symbol: /[^A-Za-z0-9]/
        };

        /**
         * Cập nhật giao diện cho một yêu cầu cụ thể.
         * @param {HTMLElement} item - Phần tử HTML của yêu cầu (thẻ <small>).
         * @param {boolean} isValid - Trạng thái hợp lệ của yêu cầu.
         */
        function updateRequirementUI(item, isValid) {
            if (isValid) {
                item.style.color = '#198754'; // Màu xanh lá (success)
                item.innerHTML = item.innerHTML.replace('•', '✔');
            } else {
                item.style.color = '#6c757d'; // Màu xám (muted)
                item.innerHTML = item.innerHTML.replace('✔', '•');
            }
        }

        /**
         * Kiểm tra toàn bộ các yêu cầu của mật khẩu.
         */
        function validatePassword() {
            const password = passwordInput.value;
            let allRequirementsMet = true;

            // Ánh xạ các yêu cầu với phần tử UI tương ứng
            const requirementMap = {
                length: requirementItems[0], // Ít nhất 8 ký tự
                case: requirementItems[1],    // Chữ hoa và chữ thường
                number: requirementItems[2],   // Ít nhất 1 chữ số
                symbol: requirementItems[3]    // Ít nhất 1 ký tự đặc biệt
            };

            // Kiểm tra từng yêu cầu và cập nhật UI
            updateRequirementUI(requirementMap.length, validations.length.test(password));
            updateRequirementUI(requirementMap.case, validations.uppercase.test(password) && validations.lowercase.test(password));
            updateRequirementUI(requirementMap.number, validations.number.test(password));
            updateRequirementUI(requirementMap.symbol, validations.symbol.test(password));

            // Kiểm tra xem tất cả các yêu cầu có được đáp ứng không
            if (!validations.length.test(password) ||
                !validations.uppercase.test(password) ||
                !validations.lowercase.test(password) ||
                !validations.number.test(password) ||
                !validations.symbol.test(password)) {
                allRequirementsMet = false;
            }

            // Cập nhật class is-invalid/is-valid cho input mật khẩu
            if (password.length > 0) {
                passwordInput.classList.toggle('is-invalid', !allRequirementsMet);
                passwordInput.classList.toggle('is-valid', allRequirementsMet);
            } else {
                passwordInput.classList.remove('is-invalid', 'is-valid');
            }

            return allRequirementsMet;
        }

        /**
         * Kiểm tra xem mật khẩu và mật khẩu xác nhận có khớp không.
         */
        function validateConfirmation() {
            const password = passwordInput.value;
            const confirmation = confirmationInput.value;
            const passwordsMatch = password === confirmation;

            if (confirmation.length > 0) {
                confirmationInput.classList.toggle('is-invalid', !passwordsMatch);
                confirmationInput.classList.toggle('is-valid', passwordsMatch);
            } else {
                confirmationInput.classList.remove('is-invalid', 'is-valid');
            }
            return passwordsMatch;
        }

        // Gán sự kiện 'input' để kiểm tra ngay khi người dùng gõ
        passwordInput.addEventListener('input', () => {
            validatePassword();
            validateConfirmation(); // Kiểm tra lại confirmation mỗi khi mật khẩu chính thay đổi
        });

        confirmationInput.addEventListener('input', validateConfirmation);

        // Gán sự kiện 'submit' để kiểm tra lần cuối trước khi gửi
        passwordForm.addEventListener('submit', function(event) {
            // Chạy lại tất cả các validation
            const isPasswordValid = validatePassword();
            const arePasswordsMatching = validateConfirmation();

            // Nếu một trong các điều kiện không thỏa mãn, chặn việc gửi form
            if (!isPasswordValid || !arePasswordsMatching) {
                event.preventDefault(); // Ngăn chặn form được gửi đi
                alert('Mật khẩu mới không hợp lệ hoặc không khớp. Vui lòng kiểm tra lại.');
            }
        });
    });
})();