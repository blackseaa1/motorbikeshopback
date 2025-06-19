(function () {
    'use strict';

    /**
     * Xử lý upload avatar
     */
    function setupAvatarUpload() {
        const form = document.getElementById('avatar-update-form');
        const fileInput = document.getElementById('avatar-input');
        const previewImg = document.getElementById('avatar-preview');
        const saveBtn = document.getElementById('avatar-save-btn');
        const sidebarImg = document.getElementById('sidebar-avatar-img');
        const headerImg = document.getElementById('header-avatar-img');

        if (!form || !fileInput || !previewImg || !saveBtn) return;

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { previewImg.src = e.target.result; };
                reader.readAsDataURL(file);
                saveBtn.style.display = 'inline-block';
            }
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(form);

            window.showAppLoader();
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            fetch(form.action, {
                method: 'POST', body: formData,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 400) {
                        if (status === 422) {
                            Object.keys(body.errors).forEach(key => {
                                const fieldInput = form.querySelector(`[name="${key}"]`);
                                const errorElement = form.querySelector(`[data-field="${key}"]`);
                                if (fieldInput) fieldInput.classList.add('is-invalid');
                                if (errorElement) errorElement.textContent = body.errors[key][0];
                            });
                        }
                        throw new Error(body.message || 'Có lỗi xảy ra.');
                    }

                    window.showAppInfoModal(body.message, 'success');
                    if (body.avatar_url) {
                        previewImg.src = body.avatar_url;
                        if (sidebarImg) sidebarImg.src = body.avatar_url;
                        if (headerImg) headerImg.src = body.avatar_url;
                    }
                    saveBtn.style.display = 'none';
                })
                .catch(error => {
                    if (error.message !== 'The given data was invalid.') {
                        window.showAppInfoModal(error.message, 'error');
                    }
                })
                .finally(() => window.hideAppLoader());
        });
    }

    /**
     * Kiểm tra độ mạnh mật khẩu real-time
     */
    function setupPasswordStrengthChecker() {
        const passwordInput = document.getElementById('password');
        const criteriaList = document.getElementById('password-strength-criteria');
        if (!passwordInput || !criteriaList) return;

        const criteriaItems = criteriaList.querySelectorAll('li');
        passwordInput.addEventListener('focus', () => { criteriaList.style.display = 'block'; });
        passwordInput.addEventListener('keyup', () => {
            criteriaItems.forEach(item => {
                item.classList.toggle('valid', new RegExp(item.dataset.regex).test(passwordInput.value));
            });
        });
    }

    /**
     * Kiểm tra mật khẩu xác nhận có khớp không
     */
    function setupPasswordConfirmationChecker() {
        const passwordInput = document.getElementById('password');
        const confirmationInput = document.getElementById('password_confirmation');
        if (!passwordInput || !confirmationInput) return;

        const errorElement = confirmationInput.parentElement.querySelector('.invalid-feedback');

        const validate = () => {
            const mismatch = confirmationInput.value.length > 0 && passwordInput.value !== confirmationInput.value;
            confirmationInput.classList.toggle('is-invalid', mismatch);
            if (errorElement) {
                errorElement.textContent = mismatch ? 'Mật khẩu xác nhận không khớp.' : '';
            }
        };

        confirmationInput.addEventListener('keyup', validate);
        passwordInput.addEventListener('keyup', validate);
    }

    /**
     * Hàm khởi tạo chính, được gọi bởi `customer_layout.js`
     */
    window.initializeAccountPage = function () {
        setupAvatarUpload();
        setupPasswordStrengthChecker();
        setupPasswordConfirmationChecker();

        if (document.getElementById('profile-update-form')) {
            window.setupAjaxForm('profile-update-form', (result, form) => {
                window.showAppInfoModal(result.message, 'success');
                const newName = form.querySelector('[name="name"]').value;
                const sidebarNameEl = document.getElementById('sidebar-user-name');
                const headerNameEl = document.getElementById('header-user-name');
                if (sidebarNameEl) sidebarNameEl.textContent = newName;
                if (headerNameEl) headerNameEl.textContent = newName;
            });
        }

        if (document.getElementById('password-update-form')) {
            window.setupAjaxForm('password-update-form', (result, form) => {
                window.showAppInfoModal(result.message, 'success');
                form.reset();

                const criteriaList = document.getElementById('password-strength-criteria');
                if (criteriaList) {
                    criteriaList.querySelectorAll('li').forEach(item => item.classList.remove('valid'));
                    criteriaList.style.display = 'none';
                }

                const collapseElement = document.getElementById('collapsePassword');
                if (collapseElement) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
                    if (bsCollapse) bsCollapse.hide();
                }
            });
        }
    };

})();