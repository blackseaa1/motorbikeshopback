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
                    fetch(`/search/suggestions?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                suggestionContainer.innerHTML = ''; // Clear previous suggestions
                                data.forEach(item => {
                                    const link = document.createElement('a');
                                    link.href = item.url;
                                    link.classList.add('list-group-item', 'list-group-item-action', 'd-flex', 'justify-content-between', 'align-items-center');
                                    link.style.borderRadius = '0'; // Remove individual border-radius for list items

                                    let itemText = item.name;
                                    if (item.type === 'product' && item.price) {
                                        itemText = `Sản phẩm: ${item.name} <span class="text-danger fw-bold">${item.price}</span>`;
                                    } else if (item.type === 'category') {
                                        itemText = `Danh mục: ${item.name}`;
                                    } else if (item.type === 'brand') {
                                        itemText = `Thương hiệu: ${item.name}`;
                                    } else if (item.type === 'blog') {
                                        itemText = `Blog: ${item.name}`;
                                    }
                                    link.innerHTML = itemText;
                                    suggestionContainer.appendChild(link);
                                });
                                suggestionContainer.style.display = 'block'; // Show the suggestions
                            } else {
                                suggestionContainer.innerHTML = '<div class="list-group-item text-center text-muted">Không tìm thấy gợi ý nào.</div>';
                                suggestionContainer.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching search suggestions:', error);
                            suggestionContainer.innerHTML = '<div class="list-group-item text-center text-danger">Có lỗi xảy ra khi tải gợi ý.</div>';
                            suggestionContainer.style.display = 'block';
                        });
                }, 300); // Debounce 300ms
            });

            // Hide suggestions when input loses focus (with a small delay to allow click on suggestions)
            input.addEventListener('blur', function () {
                setTimeout(() => {
                    suggestionContainer.style.display = 'none';
                }, 100);
            });

            // Show suggestions again if input gains focus and has a query
            input.addEventListener('focus', function () {
                if (this.value.trim().length >= 2 && suggestionContainer.children.length > 0) {
                    suggestionContainer.style.display = 'block';
                }
            });
        }
    });
});
