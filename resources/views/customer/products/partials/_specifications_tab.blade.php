@php
    $specs_string = $product->specifications;
    $specs_array = [];
    if (!empty($specs_string)) {
        // Tách chuỗi thành các dòng riêng biệt
        $lines = preg_split('/\\r\\n|\\r|\\n/', $specs_string);
        foreach ($lines as $line) {
            $trimmed_line = trim($line);
            // Bỏ qua các dòng trống
            if (empty($trimmed_line))
                continue;

            // Loại bỏ dấu chấm ở đầu dòng (nếu có) và khoảng trắng thừa
            $cleaned_line = ltrim(trim($trimmed_line), '.');
            $cleaned_line = trim($cleaned_line);

            // Tách tên và giá trị của thông số bởi dấu hai chấm (:)
            $parts = explode(':', $cleaned_line, 2);
            if (count($parts) === 2) {
                $specs_array[] = [
                    'name' => trim($parts[0]),
                    'value' => trim($parts[1])
                ];
            }
        }
    }
@endphp

@if(count($specs_array) > 0)
    <table class="table table-striped table-bordered">
        <tbody>
            @foreach($specs_array as $spec)
                <tr>
                    <th scope="row" style="width: 30%;">{{ $spec['name'] }}</th>
                    <td>{{ $spec['value'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-muted">Chưa có thông số kỹ thuật chi tiết cho sản phẩm này.</p>
@endif