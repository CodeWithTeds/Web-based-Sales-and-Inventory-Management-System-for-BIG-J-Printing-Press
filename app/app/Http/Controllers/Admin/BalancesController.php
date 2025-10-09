<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BalanceReminderMail;

class BalancesController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with('payments');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $orders->getCollection()->transform(function ($order) {
            $paidSum = (float) $order->payments()
                ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
                ->sum('amount');
            $down = (float) ($order->downpayment ?? 0);
            $totalPaid = $down + $paidSum;
            $remaining = max((float) ($order->total ?? 0) - $totalPaid, 0);
            $latestDue = optional($order->payments()->orderByDesc('due_date')->first())->due_date;

            // attach computed values for view
            $order->computed_paid_sum = $paidSum;
            $order->computed_total_paid = $totalPaid;
            $order->computed_remaining = $remaining;
            $order->computed_latest_due = $latestDue;
            return $order;
        });

        return view('admin.balances.index', compact('orders'));
    }

    public function createPayment(Order $order)
    {
        return view('admin.balances.create', compact('order'));
    }

    public function storePayment(Order $order, Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'provider' => ['nullable', 'string', 'max:100'],
            'method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent overpayment: compute remaining balance excluding POS downpayment Payment records
        $paidSum = (float) $order->payments()
            ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
            ->sum('amount');
        $down = (float) ($order->downpayment ?? 0);
        $total = (float) ($order->total ?? 0);
        $remaining = max($total - $down - $paidSum, 0);
        if ((float) ($data['amount'] ?? 0) > $remaining) {
            return back()
                ->withErrors(['amount' => 'Payment amount exceeds remaining balance (₱' . number_format($remaining, 2) . ').'])
                ->withInput();
        }

        $data['currency'] = $data['currency'] ?? 'PHP';

        $payment = new Payment($data);
        $payment->order()->associate($order);
        $payment->save();

        return redirect()->route('admin.balances.index')->with('status', 'Payment added successfully.');
    }

    public function editPayment(Payment $payment)
    {
        $order = $payment->order;
        return view('admin.balances.edit', compact('payment', 'order'));
    }

    public function updatePayment(Payment $payment, Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'provider' => ['nullable', 'string', 'max:100'],
            'method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent overpayment during update: recompute remaining excluding this payment and POS downpayment Payment records
        $order = $payment->order;
        $paidSum = (float) $order->payments()
            ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
            ->where('id', '!=', $payment->id)
            ->sum('amount');
        $down = (float) ($order->downpayment ?? 0);
        $total = (float) ($order->total ?? 0);
        $remaining = max($total - $down - $paidSum, 0);
        if ((float) ($data['amount'] ?? 0) > $remaining) {
            return back()
                ->withErrors(['amount' => 'Payment amount exceeds remaining balance (₱' . number_format($remaining, 2) . ').'])
                ->withInput();
        }

        $payment->update($data);

        return redirect()->route('admin.balances.index')->with('status', 'Payment updated successfully.');
    }

    public function destroyPayment(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('admin.balances.index')->with('status', 'Payment deleted successfully.');
    }

    public function sendReminder(Order $order)
    {
        $paidSum = (float) $order->payments()
            ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
            ->sum('amount');
        $down = (float) ($order->downpayment ?? 0);
        $remaining = max((float) ($order->total ?? 0) - ($down + $paidSum), 0);
        $latestDue = optional($order->payments()->orderByDesc('due_date')->first())->due_date;

        if (!$order->customer_email) {
            return redirect()->back()->withErrors(['email' => 'Customer email not available.']);
        }

        Mail::to($order->customer_email)->send(new BalanceReminderMail($order, $remaining, $latestDue));

        return redirect()->back()->with('status', 'Balance reminder sent successfully.');
    }
}