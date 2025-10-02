<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Reminder</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #111827; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
        .btn { display: inline-block; padding: 10px 16px; background: #4f46e5; color: #ffffff; border-radius: 8px; text-decoration: none; }
        .muted { color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 style="margin-top:0">Payment Reminder</h2>
            <p>Dear {{ $order->customer_name ?? 'Valued Customer' }},</p>
            <p>
                We hope this message finds you well. This is a friendly reminder regarding the outstanding balance for your order <strong>#{{ $order->id }}</strong> placed on {{ $order->created_at->format('F j, Y') }}.
            </p>
            <p>
                Remaining Balance: <strong>₱{{ number_format($remaining, 2) }}</strong>
            </p>
            <p>
                @if($dueDate)
                    Due Date: <strong>{{ $dueDate->format('F j, Y') }}</strong>
                @else
                    Due Date: <strong>Not specified</strong>
                @endif
            </p>
            <p class="muted">
                Please arrange payment by the due date to avoid any interruption to services or additional fees. If you have already settled this balance, kindly disregard this notice.
            </p>
            <p>
                If you have any questions or require assistance, please reply to this email and we will be happy to help.
            </p>
            <p>Thank you for your business.</p>
            <p>Best regards,<br>
            The Admin POS Team</p>
        </div>
    </div>
</body>
</html>