<x-mail::message>
    # Cảm ơn bạn đã đặt hàng!

    Xin chào **{{ $order->customer?->name ?? $order->guest_name }}**,

    Đơn hàng **#{{ $order->id }}** của bạn đã được tiếp nhận và đang chờ xử lý. Chúng tôi sẽ thông báo cho bạn khi đơn
    hàng được vận chuyển.

    ---

    ### **Chi tiết đơn hàng**

    - **Mã đơn hàng:** `#{{ $order->id }}`
    - **Ngày đặt:** `{{ $order->created_at->format('d/m/Y H:i') }}`
    - **Trạng thái:** `{{ $order->status_text }}`
    - **Phương thức thanh toán:** `{{ $order->paymentMethod?->name ?? 'Chưa xác định' }}`

    <br>

    <x-mail::table>
        | Sản phẩm | Số lượng | Đơn giá | Thành tiền |
        | :--- | :------: | :-------: | :--------: |
        @foreach ($order->items as $item)
            | {{ $item->product?->name ?? 'Sản phẩm đã bị xóa' }} | {{ $item->quantity }} |
            {{ number_format($item->price) }} ₫ | **{{ number_format($item->quantity * $item->price) }} ₫** |
        @endforeach

        | &nbsp; | &nbsp; | **Tổng phụ:** | **{{ number_format($order->subtotal) }} ₫** |
        | &nbsp; | &nbsp; | **Phí vận chuyển:** | **{{ number_format($order->shipping_fee) }} ₫** |
        | &nbsp; | &nbsp; | **Giảm giá:** | **- {{ number_format($order->discount_amount) }} ₫** |
        | &nbsp; | &nbsp; | **Tổng cộng:** | **{{ number_format($order->total_price) }} ₫** |
    </x-mail::table>

    ---

    ### **Địa chỉ giao hàng**
    {{ $order->customer?->name ?? $order->guest_name }}<br>
    {{ $order->customer?->phone ?? $order->guest_phone }}<br>
    {{ $order->full_address }}

    <br>

    <x-mail::button :url="route($order->customer_id ? 'account.orders.show' : 'guest.order.show', $order->id)">
        Xem chi tiết đơn hàng
    </x-mail::button>

    Trân trọng,<br>
    Đội ngũ {{ config('app.name') }}
</x-mail::message>