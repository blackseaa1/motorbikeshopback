// /*
// |--------------------------------------------------------------------------
// | File JavaScript chung cho trang Admin
// |--------------------------------------------------------------------------
// |
// | File này chứa các mã JavaScript được sử dụng trên nhiều trang trong
// | khu vực quản trị.
// |
// */

// document.addEventListener('DOMContentLoaded', function () {

//     // --- 1. Chức năng bật/tắt Sidebar trên màn hình nhỏ ---
//     const sidebarToggle = document.getElementById('sidebarToggle');
//     const sidebar = document.getElementById('sidebar');

//     if (sidebarToggle && sidebar) {
//         sidebarToggle.addEventListener('click', function () {
//             sidebar.classList.toggle('active');
//         });
//     }


//     // --- 2. Khởi tạo biểu đồ doanh thu (Chart.js) ---
//     const ctxRevenue = document.getElementById('revenueChart');
//     if (ctxRevenue) {
//         new Chart(ctxRevenue, {
//             type: 'line',
//             data: {
//                 labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
//                 datasets: [{
//                     label: 'Doanh Thu (Triệu VNĐ)',
//                     data: [120, 190, 150, 220, 180, 250], // Dữ liệu ví dụ
//                     borderColor: 'rgba(24, 144, 255, 1)',
//                     backgroundColor: 'rgba(24, 144, 255, 0.1)',
//                     tension: 0.3,
//                     fill: true,
//                     pointBackgroundColor: 'rgba(24, 144, 255, 1)',
//                     pointBorderColor: '#fff',
//                     pointHoverRadius: 7,
//                     pointHoverBackgroundColor: 'rgba(24, 144, 255, 1)',
//                     pointRadius: 5,
//                 }]
//             },
//             options: {
//                 responsive: true,
//                 maintainAspectRatio: false,
//                 scales: {
//                     y: {
//                         beginAtZero: true,
//                         ticks: {
//                             callback: function (value) {
//                                 return value + ' Tr';
//                             }
//                         }
//                     }
//                 },
//                 plugins: {
//                     legend: {
//                         display: true,
//                         position: 'top',
//                     },
//                     tooltip: {
//                         callbacks: {
//                             label: function (context) {
//                                 let label = context.dataset.label || '';
//                                 if (label) {
//                                     label += ': ';
//                                 }
//                                 if (context.parsed.y !== null) {
//                                     label += context.parsed.y + ' Triệu VNĐ';
//                                 }
//                                 return label;
//                             }
//                         }
//                     }
//                 },
//                 interaction: {
//                     mode: 'index',
//                     intersect: false,
//                 },
//             }
//         });
//     }


//     // --- 3. Chức năng xem trước ảnh (Preview Image) ---
//     function previewImage(inputId, previewId) {
//         const input = document.getElementById(inputId);
//         const preview = document.getElementById(previewId);
//         if (input && preview) {
//             input.addEventListener('change', function (event) {
//                 if (event.target.files && event.target.files[0]) {
//                     const reader = new FileReader();
//                     reader.onload = function (e) {
//                         preview.src = e.target.result;
//                     }
//                     reader.readAsDataURL(event.target.files[0]);
//                 }
//             });
//         }
//     }

//     // --- KÍCH HOẠT CÁC CHỨC NĂNG ---

//     // Kích hoạt xem trước ảnh cho tất cả các modal
//     previewImage('adminAvatarCreate', 'adminAvatarPreviewCreate');
//     previewImage('adminAvatarUpdate', 'adminAvatarPreviewUpdate');
//     previewImage('customerAvatarCreate', 'customerAvatarPreviewCreate');
//     previewImage('customerAvatarUpdate', 'customerAvatarPreviewUpdate');
//     // ĐÃ DI CHUYỂN VÀO TRONG
//     previewImage('categoryLogoCreate', 'categoryLogoPreviewCreate');
//     previewImage('categoryLogoUpdate', 'categoryLogoPreviewUpdate');

//     // (Tùy chọn) Xử lý điền dữ liệu vào modal
//     // ĐÃ DI CHUYỂN VÀO TRONG
//     document.querySelectorAll('.btn-action[data-bs-target="#updateCategoryModal"]').forEach(button => {
//         button.addEventListener('click', function () {
//             const categoryId = this.dataset.categoryId;
//             const categoryName = this.dataset.categoryName;
//             const categoryDescription = this.dataset.categoryDescription;
//             const categoryLogoUrl = this.dataset.categoryLogoUrl;

//             document.getElementById('categoryIdUpdate').value = categoryId;
//             document.getElementById('categoryNameUpdate').value = categoryName;
//             document.getElementById('categoryDescriptionUpdate').value = categoryDescription;
//             document.getElementById('categoryLogoPreviewUpdate').src = categoryLogoUrl || 'https://placehold.co/100x100/EFEFEF/AAAAAA&text=Logo';
//         });
//     });

//     // ĐÃ DI CHUYỂN VÀO TRONG
//     document.querySelectorAll('.btn-action[data-bs-target="#deleteCategoryModal"]').forEach(button => {
//         button.addEventListener('click', function () {
//             const categoryId = this.dataset.categoryId;
//             const categoryName = this.dataset.categoryName;
//             const deleteForm = document.getElementById('deleteCategoryForm');
//             // Cập nhật action của form xóa, ví dụ: deleteForm.action = `/admin/categories/${categoryId}`;
//             deleteForm.querySelector('.modal-body strong').textContent = categoryName;
//         });
//     });

// }); // <-- DẤU ĐÓNG NGOẶC CỦA DOMContentLoaded. TẤT CẢ PHẢI NẰM TRÊN DÒNG NÀY.
// /**
//  * Hàm này gọi đến API để lấy số thông báo chưa đọc và cập nhật giao diện.
//  */
// async function fetchNotificationCount() {
//     // Dùng try...catch để bắt lỗi nếu có sự cố mạng hoặc server
//     try {
//         // Gọi API đã tạo ở Bước 1
//         const response = await fetch('/api/notifications/unread-count');

//         // Nếu response không thành công (ví dụ: bị redirect do chưa đăng nhập, lỗi server)
//         if (!response.ok) {
//             console.error('Lỗi khi lấy thông báo. Trạng thái:', response.status);
//             return;
//         }

//         // Chuyển đổi kết quả trả về thành đối tượng JSON
//         const data = await response.json();
//         const count = data.count;

//         // Tìm đến phần tử hiển thị số trên giao diện bằng ID đã đặt ở Bước 2
//         const badge = document.getElementById('notification-badge-count');

//         // Luôn kiểm tra xem phần tử có tồn tại không trước khi thao tác
//         if (badge) {
//             // Cập nhật con số hiển thị
//             badge.textContent = count;

//             // Xử lý ẩn/hiện badge
//             if (count > 0) {
//                 badge.classList.remove('d-none'); // Hiển thị badge nếu có thông báo
//             } else {
//                 badge.classList.add('d-none'); // Ẩn đi nếu không có thông báo
//             }
//         }
//     } catch (error) {
//         // Ghi lại lỗi ra console để dễ gỡ rối
//         console.error('Đã xảy ra lỗi không xác định khi gọi API thông báo:', error);
//     }
// }

// // Sự kiện 'DOMContentLoaded' đảm bảo code chỉ chạy sau khi toàn bộ cây HTML đã được tải xong.
// document.addEventListener('DOMContentLoaded', function () {
//     // Gọi hàm lần đầu tiên ngay khi trang tải xong
//     fetchNotificationCount();

//     // Tự động gọi lại hàm này sau mỗi 30 giây (30000 milliseconds)
//     setInterval(fetchNotificationCount, 30000);
// });