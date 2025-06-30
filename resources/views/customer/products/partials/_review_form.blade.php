<div class="col-md-5 border-start">
    @auth('customer') {{-- Chỉ hiển thị phần này nếu khách hàng đã đăng nhập --}}
        @if (!$hasReviewed) {{-- Thêm điều kiện này để kiểm tra nếu khách hàng CHƯA đánh giá --}}
            <h4 class="mb-4">Gửi đánh giá của bạn</h4>
            <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Đánh giá của bạn:</label>
                    <div class="rating-stars">
                        <input type="radio" name="rating" id="rating-5" value="5" required><label for="rating-5"><i
                                class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" id="rating-4" value="4"><label for="rating-4"><i
                                class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" id="rating-3" value="3"><label for="rating-3"><i
                                class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" id="rating-2" value="2"><label for="rating-2"><i
                                class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" id="rating-1" value="1"><label for="rating-1"><i
                                class="bi bi-star-fill"></i></label>
                    </div>
                    @error('rating')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="reviewComment" class="form-label">Nhận xét của bạn *</label>
                    <textarea class="form-control @error('comment') is-invalid @enderror" id="reviewComment" name="comment"
                        rows="4" required>{{ old('comment') }}</textarea>
                    @error('comment')
                        <div class="invalid-feedback">{{$message}}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </form>
        @else
            {{-- Hiển thị thông báo nếu khách hàng đã đánh giá sản phẩm này rồi --}}
            <div class="alert alert-info">
                Bạn đã đánh giá sản phẩm này rồi.
            </div>
        @endif
    @else
        {{-- Phần này hiển thị nếu khách hàng CHƯA đăng nhập --}}
        <div class="alert alert-info">
            Vui lòng <a href="{{ route('login') }}" class="alert-link">đăng nhập</a> để gửi đánh giá.
        </div>
    @endauth
</div>

{{-- Thêm CSS và JS cho phần chọn sao đánh giá --}}
@push('styles')
    <style>
        .rating-stars {
            display: inline-block;
            direction: rtl;
            /* Đảo ngược để chọn từ phải sang trái */
        }

        .rating-stars input[type="radio"] {
            display: none;
        }

        .rating-stars label {
            color: #ddd;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .rating-stars input[type="radio"]:checked~label,
        .rating-stars label:hover,
        .rating-stars label:hover~label {
            color: #ffc107;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Có thể thêm JS ở đây nếu cần, nhưng CSS đã xử lý phần lớn
    </script>
@endpush