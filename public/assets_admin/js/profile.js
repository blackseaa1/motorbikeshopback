// public/js/admin/profile.js
document.addEventListener('DOMContentLoaded', function () {
    // --- Image preview for admin avatar ---
    const adminAvatarInput = document.getElementById('adminAvatarInput');
    const adminAvatarPreview = document.getElementById('adminAvatarPreview');
    const submitAvatarButton = document.getElementById('submitAvatarButton');
    // const avatarUpdateForm = document.getElementById('avatarUpdateForm'); // Không dùng auto-submit nữa

    if (adminAvatarInput && adminAvatarPreview && submitAvatarButton) {
        adminAvatarInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    adminAvatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
                submitAvatarButton.classList.add('visible');
            } else {
                submitAvatarButton.classList.remove('visible');
            }
        });
    }

    // --- Password requirements checker ---
    const newPasswordInput = document.getElementById('new_password');
    const requirementsList = document.getElementById('passwordRequirements');
    if (newPasswordInput && requirementsList) {
        const requirementItems = requirementsList.querySelectorAll('li');

        newPasswordInput.addEventListener('input', function() {
            const value = this.value;
            requirementItems.forEach(item => {
                const regexPattern = item.dataset.regex;
                if (!regexPattern) return; // Bỏ qua nếu không có data-regex

                try {
                    const regex = new RegExp(regexPattern);
                    if (regex.test(value)) {
                        item.classList.remove('invalid');
                        item.classList.add('valid');
                    } else {
                        item.classList.remove('valid');
                        item.classList.add('invalid');
                    }
                } catch (e) {
                    console.error("Invalid regex pattern: ", regexPattern, e);
                }
            });
        });
        // Trigger input event to check current value on page load if needed
        newPasswordInput.dispatchEvent(new Event('input'));
    }

    // --- Tab handling logic ---
    const profilePageDataElement = document.getElementById('profilePageData');
    let activeTabRestored = false;

    if (profilePageDataElement) {
        const hasPasswordErrors = profilePageDataElement.dataset.hasPasswordErrors === 'true';
        let sessionActiveTabHash = profilePageDataElement.dataset.activeTabHash;

        // 1. Priority to password errors
        if (hasPasswordErrors) {
            const pwTabLink = document.querySelector('.profile-tabs a[href="#changePassword"]');
            if (pwTabLink) {
                const tab = new bootstrap.Tab(pwTabLink);
                tab.show();
                activeTabRestored = true;
            }
        }
        // 2. Else, check session hash (set by controller after successful updates)
        else if (sessionActiveTabHash && sessionActiveTabHash !== '#') {
            const sessionTabLink = document.querySelector('.profile-tabs a[href="' + sessionActiveTabHash + '"]');
            if (sessionTabLink) {
                const tab = new bootstrap.Tab(sessionTabLink);
                tab.show();
                activeTabRestored = true;
            }
        }
    }

    // 3. Else, check URL hash (if user manually navigates or shares link with hash)
    if (!activeTabRestored && window.location.hash) {
        const urlHashTabLink = document.querySelector('.profile-tabs a[href="' + window.location.hash + '"]');
        if (urlHashTabLink) {
            const tab = new bootstrap.Tab(urlHashTabLink);
            tab.show();
            activeTabRestored = true;
        }
    }

    // 4. Else, default to the first tab if no other condition is met
    if (!activeTabRestored) {
        const firstTabLink = document.querySelector('.profile-tabs .nav-link'); // Lấy tab đầu tiên
        if (firstTabLink) {
            const tab = new bootstrap.Tab(firstTabLink);
            tab.show();
        }
    }

    // Update URL hash when tab changes
    const tabElms = document.querySelectorAll('.profile-tabs a[data-bs-toggle="tab"]');
    tabElms.forEach(function(tabElm) {
        tabElm.addEventListener('shown.bs.tab', function (event) {
            if (event.target && event.target.getAttribute('href')) {
                // Chỉ cập nhật hash nếu không phải là reload, để tránh ghi đè hash ban đầu từ URL
                if (history.pushState) {
                     history.pushState(null, null, event.target.getAttribute('href'));
                } else {
                    window.location.hash = event.target.getAttribute('href');
                }
            }
        });
    });
});