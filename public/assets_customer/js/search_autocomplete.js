document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = [
        document.getElementById('global-search-input-desktop'),
        document.getElementById('global-search-input-mobile')
    ];
    const suggestionContainers = [
        document.getElementById('search-suggestions-desktop'),
        document.getElementById('search-suggestions-mobile')
    ];

    let debounceTimer;

    searchInputs.forEach((input, index) => {
        if (input) {
            const suggestionContainer = suggestionContainers[index];

            input.addEventListener('keyup', function () {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                if (query.length < 2) { // Yêu cầu ít nhất 2 ký tự để bắt đầu tìm kiếm gợi ý
                    suggestionContainer.innerHTML = '';
                    suggestionContainer.style.display = 'none';
                    return;
                }

                debounceTimer = setTimeout(() => {
                    // Thay đổi URL API để gọi phương thức autocompleteSearch mới
                    fetch(`/search/autocomplete?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            // Kiểm tra xem có bất kỳ gợi ý nào không
                            const hasSuggestions = Object.values(data).some(arr => arr.length > 0);

                            if (hasSuggestions) {
                                suggestionContainer.innerHTML = ''; // Xóa các gợi ý trước đó

                                // Hàm trợ giúp để tạo phần tử gợi ý
                                const createSuggestionItem = (item, typeLabel, hasImage) => {
                                    const link = document.createElement('a');
                                    link.href = item.url;
                                    link.classList.add('list-group-item', 'list-group-item-action', 'd-flex', 'align-items-center');
                                    link.style.borderRadius = '0';

                                    let itemContent = '';
                                    if (hasImage && item.image) {
                                        itemContent += `<img src="${item.image}" alt="${item.name}" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px; border-radius: 4px;">`;
                                    }
                                    itemContent += `<div><strong>${typeLabel}:</strong> ${item.name}`;
                                    if (item.type === 'product' && item.price) {
                                        itemContent += ` <span class="text-danger fw-bold">${item.price}</span>`;
                                    }
                                    itemContent += `</div>`;
                                    link.innerHTML = itemContent;
                                    suggestionContainer.appendChild(link);
                                };

                                // Hiển thị gợi ý Sản phẩm
                                if (data.products && data.products.length > 0) {
                                    const productHeader = document.createElement('div');
                                    productHeader.classList.add('list-group-item', 'fw-bold', 'text-primary');
                                    productHeader.textContent = 'Sản phẩm';
                                    productHeader.style.backgroundColor = '#f8f9fa';
                                    suggestionContainer.appendChild(productHeader);
                                    data.products.forEach(product => createSuggestionItem(product, 'Sản phẩm', true));
                                }

                                // Hiển thị gợi ý Danh mục
                                if (data.categories && data.categories.length > 0) {
                                    const categoryHeader = document.createElement('div');
                                    categoryHeader.classList.add('list-group-item', 'fw-bold', 'text-primary');
                                    categoryHeader.textContent = 'Danh mục';
                                    categoryHeader.style.backgroundColor = '#f8f9fa';
                                    suggestionContainer.appendChild(categoryHeader);
                                    data.categories.forEach(category => createSuggestionItem(category, 'Danh mục', false));
                                }

                                // Hiển thị gợi ý Thương hiệu
                                if (data.brands && data.brands.length > 0) {
                                    const brandHeader = document.createElement('div');
                                    brandHeader.classList.add('list-group-item', 'fw-bold', 'text-primary');
                                    brandHeader.textContent = 'Thương hiệu';
                                    brandHeader.style.backgroundColor = '#f8f9fa';
                                    suggestionContainer.appendChild(brandHeader);
                                    data.brands.forEach(brand => createSuggestionItem(brand, 'Thương hiệu', true));
                                }

                                // Hiển thị gợi ý Bài Blog
                                if (data.blogPosts && data.blogPosts.length > 0) {
                                    const blogHeader = document.createElement('div');
                                    blogHeader.classList.add('list-group-item', 'fw-bold', 'text-primary');
                                    blogHeader.textContent = 'Bài Blog';
                                    blogHeader.style.backgroundColor = '#f8f9fa';
                                    suggestionContainer.appendChild(blogHeader);
                                    data.blogPosts.forEach(blog => createSuggestionItem(blog, 'Blog', true));
                                }

                                suggestionContainer.style.display = 'block'; // Hiển thị các gợi ý
                            } else {
                                suggestionContainer.innerHTML = '<div class="list-group-item text-center text-muted">Không tìm thấy gợi ý nào.</div>';
                                suggestionContainer.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi khi tìm nạp gợi ý tìm kiếm:', error);
                            suggestionContainer.innerHTML = '<div class="list-group-item text-center text-danger">Có lỗi xảy ra khi tải gợi ý.</div>';
                            suggestionContainer.style.display = 'block';
                        });
                }, 300); // Debounce 300ms
            });

            // Ẩn gợi ý khi input mất focus (với một độ trễ nhỏ để cho phép nhấp vào gợi ý)
            input.addEventListener('blur', function () {
                setTimeout(() => {
                    suggestionContainer.style.display = 'none';
                }, 100);
            });

            // Hiển thị lại gợi ý nếu input có focus và có query
            input.addEventListener('focus', function () {
                if (this.value.trim().length >= 2 && suggestionContainer.children.length > 0) {
                    suggestionContainer.style.display = 'block';
                }
            });
        }
    });
});