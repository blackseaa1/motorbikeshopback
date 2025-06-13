/**
 * ===================================================================
 * blog_management.js
 * Xử lý JavaScript cho trang quản lý Bài viết.
 * Phiên bản hoàn thiện, sửa lỗi và tham khảo từ product_management.js
 * ===================================================================
 */

// Đặt toàn bộ logic vào trong hàm để tránh xung đột
function initializeBlogsPage() {
    // Chỉ chạy khi tìm thấy element đặc trưng của trang blog
    if (!document.getElementById('adminBlogsPage')) return;

    console.log("Khởi tạo JS cho trang Quản lý Bài viết...");

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

        // Sửa lỗi modal backdrop
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.show();
    }

    async function handleShowViewModal(blogId) {
        if (!viewModalEl) return;
        window.showAppLoader();
        try {
            const response = await fetch(`/admin/content/blogs/${blogId}`);
            if (!response.ok) throw new Error('Không thể tải dữ liệu bài viết.');
            const blog = await response.json();

            viewModalEl.querySelector('#blogTitleView').textContent = blog.title || 'N/A';
            viewModalEl.querySelector('#blogContentView').innerHTML = blog.content ? blog.content.replace(/\n/g, '<br>') : 'Không có nội dung.';
            viewModalEl.querySelector('#blogAuthorView').textContent = blog.author ? `${blog.author.name}` : 'N/A';
            viewModalEl.querySelector('#blogCreatedAtView').textContent = new Date(blog.created_at).toLocaleString('vi-VN');
            viewModalEl.querySelector('#blogUpdatedAtView').textContent = new Date(blog.updated_at).toLocaleString('vi-VN');
            viewModalEl.querySelector('#blogImageView').src = blog.image_full_url;

            const statusSpan = viewModalEl.querySelector('#blogStatusView');
            statusSpan.textContent = blog.status_info.text;
            statusSpan.className = `badge ${blog.status_info.badge}`;

            const modalInstance = bootstrap.Modal.getInstance(viewModalEl) || new bootstrap.Modal(viewModalEl);
            modalInstance.show();
        } catch (error) {
            window.showAppInfoModal(error.message, 'error');
        } finally {
            window.hideAppLoader();
        }
    }

    async function handleShowUpdateModal(blogId) {
        if (!updateModalEl) return;
        window.showAppLoader();
        try {
            const response = await fetch(`/admin/content/blogs/${blogId}`);
            if (!response.ok) throw new Error('Không thể tải dữ liệu bài viết.');
            const blog = await response.json();

            const form = document.getElementById('updateBlogForm');
            form.action = `/admin/content/blogs/${blog.id}`;
            form.querySelector('#blogTitleUpdate').value = blog.title || '';
            form.querySelector('#blogContentUpdate').value = blog.content || '';
            form.querySelector('#blogStatusUpdate').value = blog.status || 'draft';
            document.getElementById('blogImagePreviewUpdate').src = blog.image_full_url || defaultImgSrc;

            const modalInstance = bootstrap.Modal.getInstance(updateModalEl) || new bootstrap.Modal(updateModalEl);
            modalInstance.show();
        } catch (error) {
            window.showAppInfoModal(error.message, 'error');
        } finally {
            window.hideAppLoader();
        }
    }


    // --- HÀM XỬ LÝ AJAX ---

    async function handleToggleStatus(button, url) {
        window.showAppLoader();
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

            window.showAppInfoModal(result.message, 'success');
            const blog = result.blog;
            const row = button.closest('tr');
            if (!row) return;

            const isPublished = blog.status === 'published';
            row.classList.toggle('row-inactive', !isPublished);
            row.querySelector('.status-cell').innerHTML = `<span class="badge ${blog.status_info.badge}">${blog.status_info.text}</span>`;
            button.className = `btn btn-sm btn-action toggle-status-blog-btn ${isPublished ? 'btn-secondary' : 'btn-success'}`;
            button.title = isPublished ? 'Chuyển thành bản nháp' : 'Xuất bản';
            button.querySelector('i').className = `bi ${isPublished ? 'bi-pause-circle-fill' : 'bi-play-circle-fill'}`;

        } catch (error) {
            window.showAppInfoModal(error.message, 'error', 'Lỗi Hệ thống');
        } finally {
            window.hideAppLoader();
        }
    }


    // --- THIẾT LẬP BAN ĐẦU ---

    const setupAjaxForms = () => {
        if (typeof window.setupAjaxForm !== 'function') {
            console.error('Hàm setupAjaxForm không tồn tại!');
            return;
        }
        // Hàm callback mặc định là reload lại trang để đảm bảo dữ liệu luôn mới
        const reloadCallback = () => setTimeout(() => window.location.reload(), 1200);

        if (createModalEl) window.setupAjaxForm('createBlogForm', 'createBlogModal', reloadCallback);
        if (updateModalEl) window.setupAjaxForm('updateBlogForm', 'updateBlogModal', reloadCallback);
        if (deleteModalEl) window.setupAjaxForm('deleteBlogForm', 'confirmDeleteBlogModal', reloadCallback);
        if (forceDeleteModalEl) window.setupAjaxForm('forceDeleteBlogForm', 'confirmForceDeleteBlogModal', reloadCallback);
        if (restoreModalEl) window.setupAjaxForm('restoreBlogForm', 'confirmRestoreBlogModal', reloadCallback);
    };

    const setupImagePreviews = () => {
        if (createModalEl) {
            const input = document.getElementById('blogImageCreate');
            const preview = document.getElementById('blogImagePreviewCreate');
            if (input) input.addEventListener('change', () => {
                const file = input.files[0];
                preview.src = file ? URL.createObjectURL(file) : defaultImgSrc;
            });
        }
        if (updateModalEl) {
            const input = document.getElementById('blogImageUpdate');
            const preview = document.getElementById('blogImagePreviewUpdate');
            if (input) input.addEventListener('change', () => {
                const file = input.files[0];
                preview.src = file ? URL.createObjectURL(file) : defaultImgSrc;
            });
        }
    };

    // Chạy các hàm thiết lập
    setupEventListeners();
    setupAjaxForms();
    setupImagePreviews();

    console.log("JS cho trang Quản lý Bài viết đã được khởi tạo thành công.");
}


