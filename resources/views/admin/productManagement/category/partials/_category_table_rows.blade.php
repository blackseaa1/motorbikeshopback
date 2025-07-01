{{-- This file iterates through categories and includes the single row partial --}}
@forelse ($categories as $category)
    @include('admin.productManagement.category.partials._category_table_row', [ // Notice the singular '_category_table_row'
        'category' => $category,
        'loopIndex' => $loop->index, // Pass the loop index from the foreach
        'startIndex' => $categories->firstItem() ? ($categories->firstItem() - 1) : 0,
    ])
@empty
    <tr id="no-categories-row">
        <td colspan="6" class="text-center">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>Hiện chưa có danh mục nào.
            </div>
        </td>
    </tr>
@endforelse