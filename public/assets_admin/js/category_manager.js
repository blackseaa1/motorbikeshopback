/**
 * ===================================================================
 * category_manager.js
 * Xử lý JavaScript cho trang quản lý Danh mục.
 * - Điền dữ liệu vào modal Cập nhật, Xóa.
 * - Xử lý AJAX cho việc thay đổi trạng thái danh mục.
 * ===================================================================
 */
function initializeCategoriesPage() { //
    console.log("Khởi tạo JS cho trang Danh mục..."); //

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // --- Logic for Update Modal ---
    const updateCategoryModalElement = document.getElementById('updateCategoryModal'); //
    if (updateCategoryModalElement) { //
        // const updateCategoryModal = new bootstrap.Modal(updateCategoryModalElement); // Không cần new nếu chỉ lắng nghe event
        updateCategoryModalElement.addEventListener('show.bs.modal', function (event) { //
            const button = event.relatedTarget; //
            if (!button) return; //

            const categoryId = button.getAttribute('data-id');
            const name = button.getAttribute('data-name'); //
            const description = button.getAttribute('data-description'); //
            const status = button.getAttribute('data-status'); // Lấy trạng thái
            const updateUrl = button.getAttribute('data-update-url'); //

            const modalForm = updateCategoryModalElement.querySelector('#updateCategoryForm'); //
            const nameInput = updateCategoryModalElement.querySelector('#categoryNameUpdate'); //
            const descriptionInput = updateCategoryModalElement.querySelector('#categoryDescriptionUpdate'); //
            const statusSelect = updateCategoryModalElement.querySelector('#categoryStatusUpdate'); // Select cho status

            if (modalForm) { //
                modalForm.action = updateUrl; //
                // Thêm một thuộc tính để JS biết action đã được set, dùng cho việc mở lại modal khi lỗi
                modalForm.setAttribute('action_is_set_by_js_for_error', 'true');
            }
            if (nameInput) nameInput.value = name; //
            if (descriptionInput) descriptionInput.value = description; //
            if (statusSelect) statusSelect.value = status; // Đặt giá trị cho select status
        });
    }

    // --- Logic for Delete Modal ---
    const deleteCategoryModalElement = document.getElementById('deleteCategoryModal'); //
    if (deleteCategoryModalElement) { //
        deleteCategoryModalElement.addEventListener('show.bs.modal', function (event) { //
            const button = event.relatedTarget; //
            if (!button) return; //

            const name = button.getAttribute('data-name'); //
            const deleteUrl = button.getAttribute('data-delete-url'); //

            const modalForm = deleteCategoryModalElement.querySelector('#deleteCategoryForm'); // ID form xóa đã sửa
            const categoryNameSpan = deleteCategoryModalElement.querySelector('#categoryNameToDelete'); //

            if (modalForm) { //
                modalForm.action = deleteUrl; //
            } else {
                console.error('Không tìm thấy deleteCategoryForm trong modal!'); //
            }
            if (categoryNameSpan) categoryNameSpan.textContent = name; //
        });
    }

    // --- Logic for Toggle Status Button ---
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const categoryId = this.dataset.id;
            const url = this.dataset.url;
            const currentButton = this; // Lưu lại tham chiếu đến nút

            // Không cần confirm nếu chỉ là toggle nhanh
            // if (!confirm('Bạn có chắc chắn muốn thay đổi trạng thái của danh mục này?')) {
            //     return;
            // }

            if (typeof window.showAppLoader === 'function') window.showAppLoader();

            try {
                const response = await fetch(url, {
                    method: 'POST', // Route đang là POST
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                        // 'Content-Type': 'application/json' // Không cần nếu không gửi body
                    },
                    // body: JSON.stringify({ _method: 'PATCH' }) // Nếu bạn muốn dùng PATCH override
                });

                if (!response.ok) {
                    const errorResult = await response.json().catch(() => ({ message: 'Lỗi không xác định từ server.' }));
                    throw new Error(errorResult.message || `Lỗi HTTP: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    // Cập nhật UI trực tiếp
                    const row = currentButton.closest('tr');
                    const statusCell = document.getElementById(`category-status-${categoryId}`);
                    
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge ${result.new_status === 'active' ? 'bg-success' : 'bg-secondary'}">${result.status_text}</span>`;
                    }
                    
                    currentButton.innerHTML = `<i class="bi ${result.new_icon_class}"></i>`;
                    currentButton.title = result.new_button_title;
                    
                    if (row) {
                        if (result.new_status === 'inactive') {
                            row.classList.add('category-inactive');
                        } else {
                            row.classList.remove('category-inactive');
                        }
                    }

                    // Thông báo thành công (ví dụ: dùng toast nhẹ nhàng hơn)
                    // Bạn có thể tạo một hàm global để hiển thị toast
                    // Ví dụ: showToast('success', result.message);
                    console.log(result.message); // Tạm thời log ra console

                } else {
                    throw new Error(result.message || 'Có lỗi xảy ra khi cập nhật trạng thái.');
                }
            } catch (error) {
                console.error('Lỗi khi thay đổi trạng thái:', error);
                alert('Lỗi: ' + error.message); // Thông báo lỗi đơn giản
            } finally {
                if (typeof window.hideAppLoader === 'function') window.hideAppLoader();
            }
        });
    });
}

// Đảm bảo hàm này được gọi, ví dụ từ admin_layout.js
// Trong admin_layout.js, trong hàm runPageSpecificInitializers():
// if (typeof initializeCategoriesPage === 'function') initializeCategoriesPage();