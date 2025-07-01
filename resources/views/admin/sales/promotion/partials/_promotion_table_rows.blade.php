{{-- File này chỉ chứa các hàng của bảng, được sử dụng cho cả lần tải đầu và AJAX --}}
@forelse ($promotions as $promotion)
    @include('admin.sales.promotion.partials._promotion_table_row', [
        'promotion' => $promotion,
        // Dựa vào context của $loop và pagination object để tính toán đúng STT
        'loopIndex' => $loop->index,
        'startIndex' => $promotions->firstItem() ? ($promotions->firstItem() - 1) : 0,
    ])
@empty
    <tr id="no-promotions-row">
        <td colspan="10" class="text-center">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-info-circle me-2"></i>Hiện chưa có mã khuyến mãi nào.
            </div>
        </td>
    </tr>
@endforelse