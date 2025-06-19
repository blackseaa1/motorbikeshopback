{{-- @php
    $sessionMessage = null;
    $messageType = '';
    $messageTitle = 'Thông báo'; // Tiêu đề mặc định

    if (session('success')) {
        $sessionMessage = session('success');
        $messageType = 'success';
        $messageTitle = 'Thành công!';
    } elseif (session('error')) {
        $sessionMessage = session('error');
        $messageType = 'error';
        $messageTitle = 'Lỗi!';
    } elseif (session('warning')) {
        $sessionMessage = session('warning');
        $messageType = 'warning';
        $messageTitle = 'Cảnh báo!';
    } elseif (session('info')) {
        $sessionMessage = session('info');
        $messageType = 'info';
        $messageTitle = 'Thông tin';
    }

    $validationErrorsHtml = null;
    if ($errors->any() && !request()->expectsJson()) { // Chỉ xử lý nếu không phải AJAX request mong muốn JSON
        $errorHtml = '<ul class="mb-0 ps-3 text-start" style="list-style-type: disc; text-align: left !important;">';
        foreach ($errors->all() as $error) {
            $errorHtml .= '<li>' . e($error) . '</li>'; // Dùng e() để escape HTML
        }
        $errorHtml .= '</ul>';
        $validationErrorsHtml = $errorHtml;
        // Loại và tiêu đề sẽ được đặt trong script
    }
@endphp

@if ($sessionMessage || $validationErrorsHtml)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sessionMsg = @json($sessionMessage);
            const validationHtml = {!! $validationErrorsHtml ? json_encode($validationErrorsHtml) : 'null' !!};
            const msgTypeFromPhp = '{{ $messageType }}';
            const msgTitleFromPhp = @json($messageTitle);

            if (typeof window.showAppInfoModal === 'function') {
                if (validationHtml) {
                    // Ưu tiên hiển thị lỗi validation nếu có
                    window.showAppInfoModal({ html: validationHtml }, 'validation_error', 'Lỗi Dữ Liệu!');
                } else if (sessionMsg) {
                    window.showAppInfoModal(sessionMsg, msgTypeFromPhp, msgTitleFromPhp);
                }
            } else {
                // Fallback nếu hàm showAppInfoModal không tồn tại
                if (validationHtml) {
                    console.error("Lỗi validation (modal function not found):", {!! $errors->toJson() !!});
                    alert('Có lỗi dữ liệu. Vui lòng kiểm tra lại các trường đã nhập.');
                } else if (sessionMsg) {
                    alert((msgTitleFromPhp !== 'Thông báo' ? msgTitleFromPhp + ": " : "") + sessionMsg);
                }
            }
        });
    </script>
@endif --}}