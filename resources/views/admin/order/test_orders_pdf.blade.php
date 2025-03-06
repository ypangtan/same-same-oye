<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Orders</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Test Orders</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Reference</th>
                <th>User ID</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Created At</th>
                <th>QR</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $order->reference }}</td>
                    <td>{{ $order->user->email }}</td>
                    <td>{{ number_format($order->total_price, 2) }}</td>
                    <td>PAID</td>
                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @if($order->qr_code)
                            <img src="{{ $order->qr_code }}" alt="QR Code">
                        @else
                            No QR Code
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
