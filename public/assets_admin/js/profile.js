function initializeProfilePage() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Lấy các hàm helper toàn cục (đặc biệt là showToast)
    // Hoặc định nghĩa fallback showToast nếu không có trong window
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Fallback đơn giản nếu showToast không được định nghĩa toàn cục
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`); // Fallback sang alert nếu không có container
            return;
        }

        const toastEl = document.createElement('div');
        // Sử dụng các lớp Bootstrap Toast
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl); // Thêm vào container thay vì body

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    // Hàm hiển thị lỗi validation dưới trường input
    function displayValidationErrors(formElement, errors) {
        // Clear previous errors first
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        for (const fieldName in errors) {
            if (errors.hasOwnProperty(fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    const errorDiv = inputField.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0];
                    } else {
                        console.warn(`Không tìm thấy div .invalid-feedback cho trường: ${fieldName}`);
                    }
                } else {
                    console.warn(`Không tìm thấy trường input cho lỗi: ${fieldName}`);
                    // Fallback to toast for errors without a specific input field
                    showToast(`Lỗi: ${fieldName} - ${errors[fieldName][0]}`, 'error');
                }
            }
        }
    }


    // --- Image preview for admin avatar ---
    // (admin_layout.js đã xử lý việc hiển thị ảnh xem trước chung qua initializeImagePreviews)
    // Tuy nhiên, logic hiển thị nút submit riêng cho avatar vẫn cần ở đây.
    const adminAvatarInput = document.getElementById('adminAvatarInput');
    const submitAvatarButton = document.getElementById('submitAvatarButton');

    if (adminAvatarInput && submitAvatarButton) {
        adminAvatarInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                submitAvatarButton.classList.add('visible');
            } else {
                submitAvatarButton.classList.remove('visible');
            }
        });
    }

    // --- AJAX Form Submission for Avatar Update ---
    const avatarUpdateForm = document.getElementById('avatarUpdateForm'); // Giả sử form có ID này
    if (avatarUpdateForm) {
        avatarUpdateForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!adminAvatarInput.files || adminAvatarInput.files.length === 0) {
                showToast('Vui lòng chọn một ảnh để tải lên.', 'warning');
                return;
            }

            showAppLoader();

            const formData = new FormData(this); // FormData sẽ tự lấy file từ adminAvatarInput
            const actionUrl = this.action; // Lấy URL từ attribute action của form

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    hideAppLoader();
                    showToast(result.message || 'Cập nhật avatar thành công!', 'success');
                    if (result.avatar_url) {
                        const adminAvatarPreview = document.getElementById('adminAvatarPreview');
                        if (adminAvatarPreview) adminAvatarPreview.src = result.avatar_url;
                        // Cập nhật avatar ở topnav nếu có
                        const topNavAvatar = document.querySelector('.top-nav .user-avatar');
                        if (topNavAvatar) topNavAvatar.src = result.avatar_url;
                        // Cập nhật avatar ở sidebar footer nếu có
                        const sidebarAvatar = document.querySelector('.sidebar-footer .user-info img');
                        if (sidebarAvatar) sidebarAvatar.src = result.avatar_url;
                    }
                    if (submitAvatarButton) submitAvatarButton.classList.remove('visible');
                    adminAvatarInput.value = ''; // Reset input file
                } else {
                    // Xử lý lỗi validation từ server (nếu có)
                    if (response.status === 422 && result.errors && result.errors.avatar) {
                        showToast(result.errors.avatar[0], 'error');
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra khi cập nhật avatar.', 'error');
                    }
                }
            } catch (error) {
                console.error('Lỗi cập nhật avatar:', error);
                hideAppLoader();
                showToast(error.message, 'error');
            }
        });
    }


    // --- Password requirements checker ---
    // (Giữ nguyên phần này)
    const newPasswordInput = document.getElementById('new_password');
    const requirementsList = document.getElementById('passwordRequirements');
    if (newPasswordInput && requirementsList) {
        const requirementItems = requirementsList.querySelectorAll('li');
        newPasswordInput.addEventListener('input', function () {
            const value = this.value;
            requirementItems.forEach(item => {
                const regexPattern = item.dataset.regex;
                if (!regexPattern) return;
                try {
                    const regex = new RegExp(regexPattern);
                    if (regex.test(value)) {
                        item.classList.remove('invalid');
                        item.classList.add('valid');
                    } else {
                        item.classList.remove('valid');
                        item.classList.add('invalid');
                    }
                } catch (e) { console.error("Invalid regex pattern: ", regexPattern, e); }
            });
        });
        if (document.contains(newPasswordInput)) newPasswordInput.dispatchEvent(new Event('input'));
    }

    // --- Tab handling logic ---
    // (Giữ nguyên phần này)
    const profilePageDataElement = document.getElementById('profilePageData');
    let activeTabRestored = false;
    if (profilePageDataElement) {
        const hasPasswordErrors = profilePageDataElement.dataset.hasPasswordErrors === 'true';
        let sessionActiveTabHash = profilePageDataElement.dataset.activeTabHash;
        if (hasPasswordErrors) {
            const pwTabLink = document.querySelector('.profile-tabs a[href="#changePassword"]');
            if (pwTabLink) { new bootstrap.Tab(pwTabLink).show(); activeTabRestored = true; }
        } else if (sessionActiveTabHash && sessionActiveTabHash !== '#') {
            const sessionTabLink = document.querySelector('.profile-tabs a[href="' + sessionActiveTabHash + '"]');
            if (sessionTabLink) { new bootstrap.Tab(sessionTabLink).show(); activeTabRestored = true; }
        }
    }
    if (!activeTabRestored && window.location.hash) {
        const urlHashTabLink = document.querySelector('.profile-tabs a[href="' + window.location.hash + '"]');
        if (urlHashTabLink) { new bootstrap.Tab(urlHashTabLink).show(); activeTabRestored = true; }
    }
    if (!activeTabRestored) {
        const firstTabLink = document.querySelector('.profile-tabs .nav-link');
        if (firstTabLink) { new bootstrap.Tab(firstTabLink).show(); }
    }
    const tabElms = document.querySelectorAll('.profile-tabs a[data-bs-toggle="tab"]');
    tabElms.forEach(function (tabElm) {
        tabElm.addEventListener('shown.bs.tab', function (event) {
            if (event.target && event.target.getAttribute('href')) {
                if (history.pushState) {
                    history.pushState(null, null, event.target.getAttribute('href'));
                } else {
                    window.location.hash = event.target.getAttribute('href');
                }
            }
        });
    });

    // --- AJAX Form Submission for Profile Information Update ---
    const profileInfoForm = document.getElementById('profileInfoForm'); // Giả sử ID form là 'profileInfoForm'
    if (profileInfoForm) {
        profileInfoForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();

            const formData = new FormData(this);
            const plainFormData = Object.fromEntries(formData.entries()); // Gửi JSON tiện hơn
            const actionUrl = this.action;

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST', // Hoặc PUT/PATCH, thường POST + _method
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(plainFormData),
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    hideAppLoader();
                    showToast(result.message || 'Cập nhật thông tin thành công!', 'success');
                    // Cập nhật tên hiển thị ở sidebar/topnav nếu có thay đổi
                    if (result.updated_admin_name) {
                        const adminNameDisplay = document.querySelector('.sidebar-footer .user-info span'); // Điều chỉnh selector nếu cần
                        if (adminNameDisplay) adminNameDisplay.textContent = result.updated_admin_name;
                        const topNavName = document.querySelector('.dropdown-toggle[data-bs-toggle="dropdown"]'); // Heuristic
                        if (topNavName && topNavName.childNodes.length > 0 && topNavName.childNodes[0].nodeType === Node.TEXT_NODE) {
                            topNavName.childNodes[0].textContent = result.updated_admin_name + ' ';
                        }
                    }
                } else if (response.status === 422 && result.errors) { // Validation errors
                    hideAppLoader();
                    displayValidationErrors(profileInfoForm, result.errors);
                    showToast('Vui lòng kiểm tra lại thông tin nhập.', 'error');
                }
                else {
                    showToast(result.message || 'Có lỗi xảy ra khi cập nhật thông tin.', 'error');
                }
            } catch (error) {
                console.error('Lỗi cập nhật thông tin:', error);
                hideAppLoader();
                showToast(error.message, 'error');
            }
        });
    }

    // --- AJAX Form Submission for Change Password ---
    const changePasswordForm = document.getElementById('changePasswordForm'); // Giả sử ID form là 'changePasswordForm'
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            showAppLoader();

            const formData = new FormData(this);
            const plainFormData = Object.fromEntries(formData.entries());
            const actionUrl = this.action;

            // Clear previous validation errors
            changePasswordForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            changePasswordForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(plainFormData),
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    hideAppLoader();
                    showToast(result.message || 'Thay đổi mật khẩu thành công!', 'success');
                    this.reset(); // Xóa các trường trong form
                    if (requirementsList) { // Reset password requirements UI
                        requirementsList.querySelectorAll('li').forEach(item => {
                            item.classList.remove('valid', 'invalid');
                        });
                    }
                } else if (response.status === 422 && result.errors) { // Validation errors
                    hideAppLoader();
                    displayValidationErrors(changePasswordForm, result.errors);
                    showToast('Vui lòng kiểm tra lại thông tin nhập.', 'error');
                }
                else {
                    showToast(result.message || 'Có lỗi xảy ra khi thay đổi mật khẩu.', 'error');
                }
            } catch (error) {
                console.error('Lỗi thay đổi mật khẩu:', error);
                hideAppLoader();
                showToast(error.message, 'error');
            }
        });
    }
    // console.log("Profile JS Initialized.");
}