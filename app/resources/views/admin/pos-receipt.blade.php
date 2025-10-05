<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body{font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif}
        .ticket{max-width:360px;margin:0 auto;border:1px dashed #ccc;border-radius:10px;padding:12px}
        .zigzag{height:8px;background-image:linear-gradient(135deg,#fff 25%,#0000 25%),linear-gradient(225deg,#fff 25%,#0000 25%);background-position:0 0,0 0;background-size:8px 8px;border-top-left-radius:10px;border-top-right-radius:10px}
        .header{text-align:center;margin-bottom:8px}
        .shop{font-weight:700}
        .muted{color:#6b7280;font-size:12px}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{padding:6px 4px;border-bottom:1px dashed #e5e7eb;font-size:12px}
        th{text-align:left;color:#6b7280}
        .price{text-align:right}
        .total{display:flex;justify-content:space-between;margin-top:8px;font-weight:600}
        .actions{margin-top:10px;text-align:center}
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
        @if(!empty($order->customer_email))
            <div class="muted" style="text-align:center;margin-bottom:8px;">Email: {{ $order->customer_email }}</div>
        @endif
        @if(($routePrefix ?? 'admin.pos') === 'client.ordering')
            <div class="muted" style="text-align:center;margin-bottom:8px;">Payment: GCash (PayMongo Checkout)</div>
        @else

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
        @if($rp !== 'client.ordering')
        <div class="total">
            <div>Downpayment</div>
            <div>₱{{ number_format(($order->downpayment ?? 0), 2) }}</div>
        </div>
        @endif
        <div class="total">
            <div>Remaining Balance</div>
            <div>₱{{ number_format(($order->remaining_balance ?? 0), 2) }}</div>
        </div>
        @php
            $latestPayment = ($order->payments ?? collect())
                ->sortByDesc(function($p){ return $p->paid_at ?? $p->created_at; })
                ->first();
        @endphp
        @if($rp !== 'client.ordering' && !empty($latestPayment?->due_date))
        <div class="muted" style="text-align:center;margin-top:4px;">Due Date: {{ $latestPayment->due_date->format('Y-m-d') }}</div>
        @endif

        @if(empty($download))
            <div class="actions">
                @php $routePrefix = $routePrefix ?? 'admin.pos'; @endphp
                <a href="{{ route($routePrefix . '.receipt.download', $order) }}" class="btn btn-primary">{{ __('Download PDF') }}</a>
            </div>
        @endif

        <div class="sep">******************************</div>
        <div class="thanks">THANK YOU!</div>
    </div>
</body>
</html>