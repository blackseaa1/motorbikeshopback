<x-mail::message>
    # Xác nhận Đơn hàng của bạn

    Xin chào {{ $order->customer_name ?? $order->guest_name }},

    Cảm ơn bạn đã đặt hàng tại cửa hàng của chúng tôi! Đơn hàng của bạn **#{{ $order->id }}** đã được tiếp nhận và đang
    chờ xử lý.

    **Thông tin đơn hàng:**
    - Mã đơn hàng: #{{ $order->id }}
    - Tổng tiền: {{ number_format($order->total_price) }} ₫
    - Phương thức thanh toán: {{ $order->paymentMethod->name ?? 'N/A' }}
    - Trạng thái: {{ $order->status_text }}
    - Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}

    **Sản phẩm trong đơn hàng:**
    <x-mail::table>
        | Sản phẩm | Số lượng | Giá | Thành tiền |
        | :------- | :------: | :-------: | :--------: |
        @foreach ($order->items as $item)
            | {{ $item->product->name ?? 'Sản phẩm không tồn tại' }} | {{ $item->quantity }} |
            {{ number_format($item->price) }} ₫ | {{ number_format($item->quantity * $item->price) }} ₫ |
        @endforeach
    </x-mail::table>

    **Địa chỉ giao hàng:**
    {{ $order->customer_name ?? $order->guest_name }}
    {{ $order->guest_phone ?? ($order->customer->phone ?? 'N/A') }}
    {{ $order->full_address }}

    @if ($order->status === \App\Models\Order::STATUS_PENDING)
        Nếu bạn đã chọn phương thức thanh toán trực tuyến, vui lòng hoàn tất thanh toán để đơn hàng được xử lý.
    @endif

    <x-mail::button :url="route('guest.order.show', $order->id)">
        Xem chi tiết đơn hàng
    </x-mail::button>

    Trân trọng,
    Đội ngũ {{ config('app.name') }}
</x-mail::message>