@if($cartItems->isEmpty())
    <div class="alert alert-info">
        Giỏ hàng của bạn đang trống. <a href="{{ route('products.index') }}">Tiếp tục mua sắm</a>.
    </div>
@else
    @foreach($cartItems as $item)
        <div class="card mb-3 cart-item" data-product-id="{{ $item->product_id }}">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap">
                    <div class="form-check me-3">
                        <input class="form-check-input item-checkbox" type="checkbox" data-product-id="{{ $item->product_id }}">
                    </div>
                    <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}"
                        style="width: 100px; height: 100px; object-fit: cover;" class="me-3 mb-2 mb-md-0">

                    <div class="ms-md-3 me-md-auto flex-grow-1" style="min-width: 0;">
                        <h5 class="mb-1">
                            {{-- SỬA ĐỔI CHÍNH Ở ĐÂY --}}
                            <a href="{{ route('products.show', $item->product_id) }}"
                                class="text-dark text-decoration-none d-block" title="{{ $item->product->name }}">
                                {{ Str::limit($item->product->name, 20) }}
                            </a>
                        </h5>
                        <p class="mb-1 text-muted">{{ number_format($item->product->price) }} ₫</p>
                    </div>

                    <div class="d-flex align-items-center mt-2 mt-md-0 flex-shrink-0">
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <button class="btn btn-outline-secondary quantity-decrease" type="button"
                                data-product-id="{{ $item->product_id }}">-</button>
                            <input type="number" class="form-control text-center cart-quantity-input"
                                value="{{ $item->quantity }}" min="1" aria-label="Số lượng"
                                data-product-id="{{ $item->product_id }}">
                            <button class="btn btn-outline-secondary quantity-increase" type="button"
                                data-product-id="{{ $item->product_id }}">+</button>
                        </div>
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