<x-mail::message>
    # Cảm ơn bạn đã đặt hàng!

    Xin chào {{ $order->guest_name ?? $order->customer?->name }},

    Đơn hàng #{{ $order->id }} của bạn đã được tiếp nhận và đang chờ xử lý. Chúng tôi sẽ thông báo cho bạn khi đơn
    hàng được vận chuyển.
    ---
    ### Chi tiết đơn hàng
    - Mã đơn hàng: #{{ $order->id }}
    - Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}
    - Trạng thái: {{ $order->status_text }}
    - Phương thức thanh toán: {{ $order->paymentMethod?->name ?? 'Chưa xác định' }}
    ### Tóm tắt sản phẩm
    @foreach ($order->items as $item)
        -{{ $item->product?->name ?? 'Sản phẩm đã bị xóa' }} (SL: {{ $item->quantity }}) -  {{ number_format($item->quantity * $item->price) }} ₫
    @endforeach
    ---
    ### Tổng thanh toán
    - Tạm tính: {{ number_format($order->subtotal) }} ₫
    - Phí vận chuyển: {{ number_format($order->shipping_fee) }} ₫
    @if($order->discount_amount > 0)
        - Giảm giá: -{{ number_format($order->discount_amount) }} ₫
    @endif
    - Tổng cộng: {{ number_format($order->total_price) }} ₫
    ---
    ### Địa chỉ giao hàng
    {{ $order->guest_name ?? $order->customer?->name }}
    {{ $order->guest_phone ?? $order->customer?->phone }}
    {{ $order->full_address }}

    Trân trọng,
    Đội ngũ {{ config('app.name') }}
</x-mail::message>