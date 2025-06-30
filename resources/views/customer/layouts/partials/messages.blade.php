@php
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
        $errorHtml = '<ul class="mb-0 ps-3 text-start" style="text-align: left !important;">';
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

            const appInfoModalElement = document.getElementById('appInfoModal');
            const appInfoModalBody = document.getElementById('appInfoModalBody');
            const appInfoModalTitle = document.getElementById('appInfoModalLabel');

            if (appInfoModalElement && appInfoModalBody && appInfoModalTitle && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
                const appInfoModal = new bootstrap.Modal(appInfoModalElement);

                if (validationHtml) {
                    // Hiển thị lỗi validation dưới dạng HTML
                    appInfoModalTitle.textContent = 'Lỗi Dữ Liệu!';
                    appInfoModalBody.innerHTML = validationHtml; // Use innerHTML to render HTML content
                    appInfoModalElement.querySelector('.modal-header').classList.remove('bg-success', 'bg-warning', 'bg-info');
                    appInfoModalElement.querySelector('.modal-header').classList.add('bg-danger', 'text-white');
                    appInfoModal.show();
                } else if (sessionMsg) {
                    // If window.showAppInfoModal is defined, use it for session messages
                    if (typeof window.showAppInfoModal === 'function') {
                         window.showAppInfoModal(sessionMsg, msgTypeFromPhp, msgTitleFromPhp);
                    } else {
                        // Fallback if window.showAppInfoModal is not defined
                        appInfoModalTitle.textContent = msgTitleFromPhp;
                        appInfoModalBody.textContent = sessionMsg;
                        appInfoModalElement.querySelector('.modal-header').classList.remove('bg-danger', 'text-white');
                        appInfoModalElement.querySelector('.modal-header').classList.add(`bg-${msgTypeFromPhp}`, 'text-dark'); // Adjust class based on type
                        appInfoModal.show();
                    }
                }
            } else {
                // Fallback if Bootstrap Modal API is not available or elements are missing
                if (validationHtml) {
                    console.error("Lỗi validation (modal function not found or Bootstrap not loaded):", {!! $errors->toJson() !!});
                    alert('Có lỗi dữ liệu. Vui lòng kiểm tra lại các trường đã nhập:\n' + validationHtml.replace(/<[^>]*>?/gm, '')); // Remove HTML tags for alert
                } else if (sessionMsg) {
                    alert((msgTitleFromPhp !== 'Thông báo' ? msgTitleFromPhp + ": " : "") + sessionMsg);
                }
            }
        });
    </script>
@endif