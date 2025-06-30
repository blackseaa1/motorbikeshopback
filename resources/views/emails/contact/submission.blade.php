<x-mail::message>
    # Có liên hệ mới từ website

    Bạn nhận được một tin nhắn mới từ form liên hệ trên website của bạn.

    Thông tin người gửi:
    - Tên: {{ $name }}
    - Email:{{ $email }}
    - Số Điện Thoại:{{ $phone }}

    Nội dung tin nhắn:
    {{ $messageContent }}
    Vui lòng liên hệ lại với khách hàng theo địa chỉ email hoặc số điện thoại (nếu có) đã cung cấp.
    Trân trọng,
    Hệ thống tự động {{ config('app.name') }}
</x-mail::message>