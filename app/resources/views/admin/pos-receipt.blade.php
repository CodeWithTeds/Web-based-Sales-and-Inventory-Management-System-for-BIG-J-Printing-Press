<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <link rel="icon" href="/favicon.svg">
    <style>
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; margin: 0; padding: 24px; color: #111827; }
        .receipt { max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .muted { color: #6b7280; font-size: 14px; }
        h1 { font-size: 20px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #f3f4f6; }
        th { font-size: 12px; letter-spacing: .04em; text-transform: uppercase; color: #6b7280; }
        tfoot td { font-weight: 600; }
        .actions { margin-top: 16px; display: flex; gap: 10px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; border: 1px solid transparent; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-print { background: #111827; color: white; }
        .btn-download { background: #eef2ff; color: #3730a3; border-color: #c7d2fe; }
        @media print { .actions { display: none; } .receipt { border: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div>
                <h1>Sales Receipt</h1>
                <div class="muted">Order #: {{ $order->order_number }}</div>
                <div class="muted">Date: {{ $order->created_at->format('M d, Y h:i A') }}</div>
            </div>
            <div style="text-align:right">
                <div><strong>Customer:</strong></div>
                <div>{{ $order->customer_name }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%">Item</th>
                    <th style="width: 15%">Qty</th>
                    <th style="width: 17%">Price</th>
                    <th style="width: 18%">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>₱{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right">Total</td>
                    <td>₱{{ number_format($order->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="actions">
            <button class="btn btn-print" onclick="window.print()" type="button">Print</button>
            <a class="btn btn-download" href="{{ route('admin.pos.receipt.download', $order) }}">Download HTML</a>
            <a class="btn" href="{{ route('admin.pos') }}">Back to POS</a>
        </div>
    </div>
</body>
</html>