<div class="col-md-7">
    <h4 class="mb-4">{{ $product->reviews->count() }} Đánh giá cho sản phẩm này</h4>

    @forelse($product->reviews as $review)
        <div class="d-flex mb-4">
            <div class="flex-shrink-0">
                {{-- Lấy ảnh đại diện của khách hàng --}}
                <img src="{{ $review->customer->avatar_url }}" class="rounded-circle" alt="{{ $review->customer->name }}"
                    style="width: 60px; height: 60px; object-fit: cover;">
            </div>
            <div class="ms-3 w-100">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">{{ $review->customer->name }}</h5>
                    <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                </div>
                <div class="mb-1">
                    {{-- Hiển thị số sao đánh giá --}}
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $review->rating)
                            <i class="bi bi-star-fill text-warning"></i>
                        @else
                            <i class="bi bi-star text-secondary"></i>
                        @endif
                    @endfor
                </div>
                {{-- Hiển thị nội dung comment --}}
                <p>{{ $review->comment }}</p>
            </div>
        </div>
        @if (!$loop->last)
            <hr>
        @endif
    @empty
        <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
    @endforelse
</div>