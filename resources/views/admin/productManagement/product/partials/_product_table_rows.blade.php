{{-- File này chỉ chứa các hàng của bảng, được sử dụng cho cả lần tải đầu và AJAX --}}
@forelse ($products as $product)
    @include('admin.productManagement.product.partials._product_table_row', [
        'product' => $product,
        // Dựa vào context của $loop và pagination object để tính toán đúng STT
        'loopIndex' => $loop->index,
        'startIndex' => $products->firstItem() ? ($products->firstItem() - 1) : 0,
    ])
@empty
    <tr id="no-products-row">
        <td colspan="10" class="text-center">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>Hiện chưa có sản phẩm nào.
            </div>
        </td>
    </tr>
@endforelse