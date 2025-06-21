@if($cartItems->isEmpty())
    <div class="alert alert-info">Giỏ hàng của bạn đang trống. <a href="{{ route('products.index') }}">Tiếp tục mua sắm</a>.
    </div>
@else
    @foreach($cartItems as $item)
        <div class="card mb-3 cart-item" data-product-id="{{ $item->product_id }}">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap">
                    <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}"
                        style="width: 100px; height: 100px; object-fit: cover;" class="me-3 mb-2 mb-md-0">
                    <div class="ms-md-3 me-md-auto flex-grow-1">
                        <h5 class="mb-1"><a href="{{ route('products.show', $item->product_id) }}"
                                class="text-dark text-decoration-none">{{ $item->product->name }}</a></h5>
                        <p class="mb-1 text-muted">{{ number_format($item->product->price) }} ₫</p>
                    </div>
                    <div class="d-flex align-items-center mt-2 mt-md-0">
                        <input type="number" class="form-control form-control-sm cart-quantity-input"
                            value="{{ $item->quantity }}" min="1" style="width: 70px;" aria-label="Số lượng">
                        <button class="btn btn-sm btn-outline-danger ms-3 remove-from-cart-btn"
                            data-product-id="{{ $item->product_id }}" aria-label="Xóa">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif