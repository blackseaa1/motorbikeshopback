<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel với Bootstrap</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

</head>
<body class="antialiased">
    
    <div class="container mt-5">
        <h1>Xin chào, Bootstrap đã được cài đặt!</h1>
        <p>Đây là một ví dụ sử dụng các lớp của Bootstrap.</p>

        <div class="alert alert-success" role="alert">
            Một thông báo thành công đơn giản—hãy thử xem!
        </div>

        <button type="button" class="btn btn-primary">Nút Primary</button>
        <button type="button" class="btn btn-secondary">Nút Secondary</button>

        <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Dropdown Menu
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li><a class="dropdown-item" href="#">Something else here</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Separated link</a></li>
            </ul>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>