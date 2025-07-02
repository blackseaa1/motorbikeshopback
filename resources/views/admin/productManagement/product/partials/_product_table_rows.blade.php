{{-- resources/views/admin/productManagement/product/partials/_product_table_rows.blade.php --}}
@forelse ($products as $product)
    {{-- Mỗi hàng sẽ được render bởi partial _product_table_row.blade.php --}}
    @include('admin.productManagement.product.partials._product_table_row', [
        'product' => $product,
        'loopIndex' => $loop->index, // Loop index từ vòng lặp hiện tại
        'startIndex' => $startIndex, // startIndex được truyền từ Controller/Parent view
        'statusFilterSelected' => $statusFilterSelected // Truyền trạng thái lọc để biết nút nào sẽ hiển thị
    ])
@empty
    {{-- Hiển thị thông báo nếu không có sản phẩm nào --}}
    <tr id="no-products-row"><td colspan="10" class="text-center">Chưa có sản phẩm nào.</td></tr>
@endforelse