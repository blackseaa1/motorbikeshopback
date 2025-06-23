/**
 * ===================================================================
 * blog_management.js
 * Xử lý JavaScript cho trang quản lý Bài viết.
 * Phiên bản hoàn thiện, sửa lỗi và tham khảo từ promotion_manager.js
 * ===================================================================
 */

// Đặt toàn bộ logic vào trong hàm để tránh xung đột
document.addEventListener('DOMContentLoaded', function () { // Đổi sang DOMContentLoaded để đảm bảo DOM đã sẵn sàng
    // Chỉ chạy khi tìm thấy element đặc trưng của trang blog
    if (!document.getElementById('adminBlogsPage')) return;

    console.log("Khởi tạo JS cho trang Quản lý Bài viết...");

    // Lấy các hàm helper toàn cục (giả định tồn tại từ admin_layout.js hoặc tương tự)
    const showAppLoader = typeof window.showAppLoader === 'function' ? window.showAppLoader : () => console.log('Show Loader');
    const hideAppLoader = typeof window.hideAppLoader === 'function' ? window.hideAppLoader : () => console.log('Hide Loader');
    const showAppInfoModal = typeof window.showAppInfoModal === 'function' ? window.showAppInfoModal : (msg, type) => alert(`${type}: ${msg}`);
    const showToast = typeof window.showToast === 'function' ? window.showToast : (msg, type) => {
        // Fallback đơn giản nếu showToast không được định nghĩa toàn cục
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Không tìm thấy .toast-container. Vui lòng thêm vào layout chính.');
            alert(`${type}: ${msg}`); // Fallback sang alert nếu không có container
            return;
        }

        const toastEl = document.createElement('div');
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
        toastContainer.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };


    // Lấy các element modal một lần
    const createModalEl = document.getElementById('createBlogModal');
    const updateModalEl = document.getElementById('updateBlogModal');
    const viewModalEl = document.getElementById('viewBlogModal');
    const deleteModalEl = document.getElementById('confirmDeleteBlogModal');
    const forceDeleteModalEl = document.getElementById('confirmForceDeleteBlogModal');
    const restoreModalEl = document.getElementById('confirmRestoreBlogModal');
    const blogTableBody = document.getElementById('blog-table-body');

    if (!blogTableBody) {
        console.warn('Không tìm thấy table body. Script dừng lại.');
        return;
    }

    const defaultImgSrc = 'https://placehold.co/400x250/EFEFEF/AAAAAA&text=Preview';

    // --- CÁC HÀM TIỆN ÍCH ---

    /**
     * Xóa các lỗi validation đang hiển thị trên form.
     * @param {HTMLElement} formElement - Form cần xóa lỗi.
     */
    function clearValidationErrors(formElement) {
        if (!formElement) return;
        formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    /**
     * Hiển thị lỗi validation từ phản hồi server dưới các trường input tương ứng.
     * @param {HTMLElement} formElement - Form đang có lỗi.
     * @param {object} errors - Đối tượng chứa các lỗi từ server (key: field_name, value: [error_message]).
     */
    function displayValidationErrors(formElement, errors) {
        clearValidationErrors(formElement); // Xóa lỗi cũ trước
        let firstErrorField = null;

        for (const fieldName in errors) {
            if (errors.hasOwnProperty(fieldName)) {
                let inputField = formElement.querySelector(`[name="${fieldName}"]`);

                if (inputField) {
                    inputField.classList.add('is-invalid');
                    const errorDiv = inputField.nextElementSibling; // Lấy div invalid-feedback ngay sau input

                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.textContent = errors[fieldName][0]; // Chỉ hiển thị lỗi đầu tiên
                    } else {
                        console.warn(`Không tìm thấy div .invalid-feedback đúng cách cho trường: ${fieldName}. Đảm bảo có <div class="invalid-feedback"></div> ngay sau input/select.`);
                    }

                    if (!firstErrorField) {
                        firstErrorField = inputField;
                    }
                } else {
                    console.warn(`Không tìm thấy trường input/select cho lỗi: ${fieldName}`);
                }
            }
        }
        // Di chuyển focus đến trường đầu tiên có lỗi
        if (firstErrorField) {
            firstErrorField.focus();
        }
    }


    // --- CÁC HÀM XỬ LÝ SỰ KIỆN ---

    const setupEventListeners = () => {
        blogTableBody.addEventListener('click', async (event) => {
            const button = event.target.closest('.btn-action');
            if (!button) return;

            event.preventDefault(); // Ngăn hành vi mặc định
            const id = button.dataset.id;
            const name = button.dataset.name;
            const url = button.dataset.url;
            const deleteUrl = button.dataset.deleteUrl;

            if (button.classList.contains('toggle-status-blog-btn')) {
                await handleToggleStatus(button, url);
            } else if (button.classList.contains('btn-view')) {
                await handleShowViewModal(id);
            } else if (button.classList.contains('btn-edit')) {
                await handleShowUpdateModal(id);
            } else if (button.classList.contains('btn-delete')) {
                handleShowConfirmModal(deleteModalEl, 'deleteBlogForm', `/admin/content/blogs/${id}`, 'blogNameToDelete', name);
            } else if (button.classList.contains('btn-restore-blog')) {
                handleShowConfirmModal(restoreModalEl, 'restoreBlogForm', `/admin/content/blogs/${id}/restore`, 'blogNameToRestore', name);
            } else if (button.classList.contains('btn-force-delete-blog')) {
                handleShowConfirmModal(forceDeleteModalEl, 'forceDeleteBlogForm', deleteUrl, 'blogNameToForceDelete', name);
            }
        });
    };

    // --- CÁC HÀM HIỂN THỊ MODAL ---

    function handleShowConfirmModal(modalEl, formId, action, textElId, name) {
        if (!modalEl) return;
        const form = document.getElementById(formId);
        const textEl = document.getElementById(textElId);
        if (form) form.action = action;
        if (textEl) textEl.textContent = name || 'mục này';

        clearValidationErrors(form); // Clear errors before showing confirm modal
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.show();
    }

    async function handleShowViewModal(blogId) {
        if (!viewModalEl) return;
        showAppLoader();
        try {
            const response = await fetch(`/admin/content/blogs/${blogId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Không thể tải dữ liệu bài viết.');
            const blog = await response.json();

            viewModalEl.querySelector('#blogTitleView').textContent = blog.title || 'N/A';
            viewModalEl.querySelector('#blogContentView').innerHTML = blog.content ? blog.content.replace(/\n/g, '<br>') : 'Không có nội dung.';
            viewModalEl.querySelector('#blogAuthorView').textContent = blog.author ? `${blog.author.name}` : 'N/A';
            viewModalEl.querySelector('#blogCreatedAtView').textContent = new Date(blog.created_at).toLocaleString('vi-VN');
            viewModalEl.querySelector('#blogUpdatedAtView').textContent = new Date(blog.updated_at).toLocaleString('vi-VN');
            viewModalEl.querySelector('#blogImageView').src = blog.image_full_url || defaultImgSrc;

            const statusSpan = viewModalEl.querySelector('#blogStatusView');
            statusSpan.textContent = blog.status_info.text;
            statusSpan.className = `badge ${blog.status_info.badge}`;

            const modalInstance = bootstrap.Modal.getInstance(viewModalEl) || new bootstrap.Modal(viewModalEl);
            modalInstance.show();
        } catch (error) {
            showAppInfoModal(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    }

    async function handleShowUpdateModal(blogId) {
        if (!updateModalEl) return;
        showAppLoader();
        try {
            const response = await fetch(`/admin/content/blogs/${blogId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Không thể tải dữ liệu bài viết.');
            const blog = await response.json();

            const form = document.getElementById('updateBlogForm');
            form.action = `/admin/content/blogs/${blog.id}`;
            form.querySelector('#blogTitleUpdate').value = blog.title || '';
            form.querySelector('#blogContentUpdate').value = blog.content || '';
            form.querySelector('#blogStatusUpdate').value = blog.status || 'draft';
            document.getElementById('blogImagePreviewUpdate').src = blog.image_full_url || defaultImgSrc;

            clearValidationErrors(form); // Clear errors before showing update modal
            const modalInstance = bootstrap.Modal.getInstance(updateModalEl) || new bootstrap.Modal(updateModalEl);
            modalInstance.show();
        } catch (error) {
            showAppInfoModal(error.message, 'error');
        } finally {
            hideAppLoader();
        }
    }


    // --- HÀM XỬ LÝ AJAX ---

    async function handleToggleStatus(button, url) {
        showAppLoader();
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Lỗi không xác định');

            showToast(result.message, 'success'); // Sử dụng showToast
            const blog = result.blog;
            const row = button.closest('tr');
            if (!row) return;

            // Cập nhật DOM thay vì reload trang
            const isPublished = blog.status === 'published';
            row.classList.toggle('row-inactive', !isPublished); // Tùy chọn làm mờ hàng
            row.querySelector('.status-cell').innerHTML = `<span class="badge ${blog.status_info.badge}">${blog.status_info.text}</span>`;
            button.title = isPublished ? 'Chuyển thành bản nháp' : 'Xuất bản';
            button.querySelector('i').className = `bi ${isPublished ? 'bi-pause-circle-fill' : 'bi-play-circle-fill'}`;
            button.classList.toggle('btn-secondary', isPublished);
            button.classList.toggle('btn-success', !isPublished);

        } catch (error) {
            showToast(error.message, 'error'); // Sử dụng showToast
        } finally {
            hideAppLoader();
        }
    }


    // --- THIẾT LẬP FORM AJAX CHUNG ---
    /**
     * Thiết lập xử lý AJAX cho một form cụ thể.
     * @param {string} formId - ID của form.
     * @param {string} modalId - ID của modal chứa form.
     * @param {function} successCallback - Hàm callback khi form gửi thành công.
     * @param {string} method - Phương thức HTTP ('POST', 'PUT', 'DELETE').
     */
    function setupAjaxForm(formId, modalId, successCallback, method = 'POST') {
        const form = document.getElementById(formId);
        const modalEl = document.getElementById(modalId);
        // Lưu ý: Đối với modal confirm/delete/restore, có thể không cần modalInstance.hide()
        const modalInstance = modalEl ? (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)) : null;

        if (!form) {
            console.error(`Không thể thiết lập AJAX form: Form ID "${formId}" không tồn tại.`);
            return;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault(); // Ngăn chặn hành vi submit mặc định
            showAppLoader(); // Hiển thị loader

            clearValidationErrors(form); // Xóa lỗi cũ trước mỗi lần submit

            const formData = new FormData(form);

            // Thêm _method cho PUT/DELETE nếu cần (được xử lý bởi Laravel)
            if (method === 'PUT' || method === 'DELETE') {
                formData.append('_method', method);
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST', // Luôn là POST với FormData, Laravel sẽ đọc _method
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json' // Báo server trả về JSON
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) { // Status code 2xx
                    showToast(result.message, 'success');
                    if (modalInstance) { // Chỉ đóng modal nếu nó tồn tại
                        modalInstance.hide();
                    }
                    successCallback(result.blog); // Gọi callback thành công, truyền dữ liệu blog mới/cập nhật
                } else if (response.status === 422) { // Validation errors
                    displayValidationErrors(form, result.errors);
                    showToast('Vui lòng kiểm tra lại thông tin nhập liệu.', 'error');
                } else { // Other errors
                    showToast(result.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.', 'error');
                    console.error('AJAX Error:', result);
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                showToast('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
            } finally {
                hideAppLoader(); // Ẩn loader
            }
        });
    }

    // --- CÁC HÀM CALLBACK KHI THÀNH CÔNG (Cập nhật DOM) ---

    // Callback cho Create Blog
    const handleCreateBlogSuccess = (newBlog) => {
        // Tạo một hàng mới trong bảng
        const newRow = document.createElement('tr');
        newRow.id = `blog-row-${newBlog.id}`;
        // Lấy nội dung mẫu của một hàng từ HTML hoặc xây dựng động
        // Để đơn giản, ở đây sẽ reload trang. Để tối ưu UX, bạn cần xây dựng hàng HTML hoàn chỉnh
        // và chèn vào bảng mà không cần reload.
        // Tuy nhiên, việc xây dựng một hàng phức tạp với tất cả các thuộc tính động
        // (như image_full_url, status_info, author.name, toggle button, edit button, delete button)
        // là khá nhiều công việc nếu không có template engine ở client-side hoặc trả về HTML từ server.
        // Để đạt được yêu cầu "Không reload lại trang", đây là phần phức tạp nhất.
        // Tạm thời, chúng ta sẽ vẫn reload trang sau 1 khoảng thời gian ngắn để đảm bảo dữ liệu mới nhất.
        // Để tối ưu thực sự, bạn sẽ cần:
        // 1. Một hàm renderRow(blogData) để tạo HTML của <tr>
        // 2. Chèn newRow vào đầu blogTableBody
        // 3. Cập nhật số thứ tự (STT) của các hàng nếu cần.
        setTimeout(() => window.location.reload(), 1200);
    };

    // Callback cho Update Blog
    const handleUpdateBlogSuccess = (updatedBlog) => {
        const row = document.getElementById(`blog-row-${updatedBlog.id}`);
        if (row) {
            // Cập nhật các ô trong hàng với dữ liệu mới
            row.querySelector('.blog-title-cell').textContent = updatedBlog.title;
            row.querySelector('.status-cell').innerHTML = `<span class="badge ${updatedBlog.status_info.badge}">${updatedBlog.status_info.text}</span>`;
            // Cập nhật icon và title của nút toggle
            const toggleButton = row.querySelector('.toggle-status-blog-btn');
            if (toggleButton) {
                const isPublished = updatedBlog.status === 'published';
                toggleButton.title = isPublished ? 'Chuyển thành bản nháp' : 'Xuất bản';
                toggleButton.querySelector('i').className = `bi ${isPublished ? 'bi-pause-circle-fill' : 'bi-play-circle-fill'}`;
                toggleButton.classList.toggle('btn-secondary', isPublished);
                toggleButton.classList.toggle('btn-success', !isPublished);
            }
            // Cập nhật data-name cho các nút delete/restore để modal confirm hiển thị đúng tên
            const deleteButton = row.querySelector('.btn-delete');
            if (deleteButton) deleteButton.dataset.name = updatedBlog.title;
            const restoreButton = row.querySelector('.btn-restore-blog');
            if (restoreButton) restoreButton.dataset.name = updatedBlog.title;
            const forceDeleteButton = row.querySelector('.btn-force-delete-blog');
            if (forceDeleteButton) forceDeleteButton.dataset.name = updatedBlog.title;

            row.classList.toggle('row-inactive', updatedBlog.status !== 'published'); // Thêm/bỏ class làm mờ nếu trạng thái không phải published
        }
        // Sau khi DOM được cập nhật, có thể reload trang để đơn giản hóa việc đồng bộ STT và pagination.
        // Để *hoàn toàn* không reload, bạn cần quản lý STT và pagination bằng JS.
        setTimeout(() => window.location.reload(), 1200);
    };

    // Callback cho Delete/Restore Blog
    const handleDeleteRestoreSuccess = () => {
        // Đối với xóa/khôi phục, cách đơn giản nhất để đảm bảo dữ liệu hiển thị đúng là reload trang.
        // Để tối ưu: xóa hàng khỏi DOM hoặc di chuyển nó giữa các bảng (nếu có soft deletes)
        setTimeout(() => window.location.reload(), 1200);
    };


    // --- THIẾT LẬP BAN ĐẦU ---

    const setupAjaxForms = () => {
        // Thiết lập AJAX cho form tạo mới
        if (createModalEl) setupAjaxForm('createBlogForm', 'createBlogModal', handleCreateBlogSuccess, 'POST');
        // Thiết lập AJAX cho form cập nhật
        if (updateModalEl) setupAjaxForm('updateBlogForm', 'updateBlogModal', handleUpdateBlogSuccess, 'PUT');
        // Thiết lập AJAX cho form xóa mềm (delete)
        if (deleteModalEl) setupAjaxForm('deleteBlogForm', 'confirmDeleteBlogModal', handleDeleteRestoreSuccess, 'DELETE');
        // Thiết lập AJAX cho form xóa vĩnh viễn (force delete)
        if (forceDeleteModalEl) setupAjaxForm('forceDeleteBlogForm', 'confirmForceDeleteBlogModal', handleDeleteRestoreSuccess, 'DELETE');
        // Thiết lập AJAX cho form khôi phục
        if (restoreModalEl) setupAjaxForm('restoreBlogForm', 'confirmRestoreBlogModal', handleDeleteRestoreSuccess, 'POST');
    };

    const setupImagePreviews = () => {
        if (createModalEl) {
            const input = document.getElementById('blogImageCreate');
            const preview = document.getElementById('blogImagePreviewCreate');
            if (input) input.addEventListener('change', () => {
                const file = input.files[0];
                preview.src = file ? URL.createObjectURL(file) : defaultImgSrc;
            });
            // Clear preview on modal hide
            createModalEl.addEventListener('hidden.bs.modal', () => {
                preview.src = defaultImgSrc;
            });
        }
        if (updateModalEl) {
            const input = document.getElementById('blogImageUpdate');
            const preview = document.getElementById('blogImagePreviewUpdate');
            if (input) input.addEventListener('change', () => {
                const file = input.files[0];
                preview.src = file ? URL.createObjectURL(file) : defaultImgSrc;
            });
            // Clear preview on modal hide
            updateModalEl.addEventListener('hidden.bs.modal', () => {
                preview.src = defaultImgSrc;
            });
        }
    };

    // Reset form và clear validation errors khi modal ẩn
    [createModalEl, updateModalEl, deleteModalEl, forceDeleteModalEl, restoreModalEl].forEach(modalEl => {
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                const form = modalEl.querySelector('form');
                if (form) {
                    form.reset();
                    clearValidationErrors(form);
                }
            });
        }
    });


    // Chạy các hàm thiết lập
    setupEventListeners();
    setupAjaxForms();
    setupImagePreviews();

    console.log("JS cho trang Quản lý Bài viết đã được khởi tạo thành công.");
});