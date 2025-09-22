<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        /* Thermal receipt look */
        body{background:#f3f4f6;color:#111827;font-family: DejaVu Sans, Arial, sans-serif;}
        .ticket{width:320px;margin:24px auto;background:#fff;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,.08);padding:14px 16px;}
        .zigzag{height:10px;background:repeating-linear-gradient(90deg,#fff 0 8px,transparent 8px 16px),
                         linear-gradient(#e5e7eb,#e5e7eb);background-size:16px 8px,100% 2px;background-repeat:repeat,no-repeat;background-position:top;}
        .header{text-align:center;margin-top:6px}
        .shop{font-weight:700;letter-spacing:1px}
        .muted{color:#6b7280;font-size:12px}
        .section-title{text-align:center;font-weight:700;margin:10px 0;font-size:13px;letter-spacing:1px}
        .sep{margin:8px 0;text-align:center;color:#9ca3af;font-size:12px;letter-spacing:2px}
        table{width:100%;border-collapse:collapse}
        th,td{font-size:12px;padding:6px 0;border-bottom:1px dashed #e5e7eb}
        th{text-align:left;color:#6b7280}
        td.price, th.price{text-align:right}
        .total{display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding:8px 0;border-top:2px dotted #111827;font-weight:700}
        .actions{margin-top:12px;text-align:center}
        .btn{display:inline-block;background:#111827;color:#fff;text-decoration:none;border-radius:6px;padding:8px 10px;font-size:12px}
        .thanks{text-align:center;margin-top:10px;font-size:12px;color:#6b7280}
        /* PDF hint: avoid anchors when generating PDF */
        @page{margin:12px}
    </style>
</head>
<body>
    <div class="ticket">
        <div class="zigzag"></div>
        <div class="header">
            <div class="shop">{{ config('app.name', 'SHOP NAME') }}</div>
            <div class="muted">Address: —</div>
            <div class="muted">Telp. —</div>
        </div>

        <div class="sep">******************************</div>
        <div class="section-title">
            @php $rp = $routePrefix ?? 'admin.pos'; @endphp
            {{ $rp === 'client.ordering' ? 'ONLINE RECEIPT' : 'CASH RECEIPT' }}
        </div>
        <div class="sep">******************************</div>

        <div class="muted" style="text-align:center;margin-bottom:8px;">#{{ $order->order_number }} • {{ $order->created_at->format('Y-m-d H:i') }}</div>
        @if(!empty($order->customer_name))
            <div class="muted" style="text-align:center;margin-bottom:8px;">Customer: {{ $order->customer_name }}</div>
        @endif
        @if(($routePrefix ?? 'admin.pos') === 'client.ordering')
            <div class="muted" style="text-align:center;margin-bottom:8px;">Payment: GCash (PayMongo Checkout)</div>
        @else
            <div class="muted" style="text-align:center;margin-bottom:8px;">Payment: Cash</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="price">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->name }} × {{ $item->qty }}</td>
                        <td class="price">₱{{ number_format($item->line_total,2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sep">******************************</div>
        <div class="total">
            <div>Total</div>
            <div>₱{{ number_format($order->total, 2) }}</div>
        </div>

        @empty($download)
            <div class="actions">
                @php
                    $routePrefix = $routePrefix ?? 'admin.pos';
                @endphp
                <a href="{{ route($routePrefix . '.receipt.download', $order) }}" class="btn btn-primary">{{ __('Download PDF') }}</a>
            </div>
        @endempty

        <div class="sep">******************************</div>
        <div class="thanks">THANK YOU!</div>
    </div>
</body>
</html>