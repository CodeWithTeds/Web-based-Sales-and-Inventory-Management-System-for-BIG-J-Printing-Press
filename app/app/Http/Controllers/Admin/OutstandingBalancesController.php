<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Mail\BalanceReminderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OutstandingBalancesController extends Controller
{
    /**
     * Display a listing of outstanding balances.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'payments'])
            ->whereRaw('(total - IFNULL(downpayment, 0)) > (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE payments.order_id = orders.id AND (payments.reference IS NULL OR payments.reference NOT LIKE "POSDP-%"))')
            ->orderByDesc('created_at');

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Calculate additional data for each order with consistent formula
        foreach ($orders as $order) {
            $paidSum = (float) $order->payments()
                ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
                ->sum('amount');
            $down = (float) ($order->downpayment ?? 0);
            $total = (float) ($order->total ?? 0);

            $order->computed_paid_sum = $paidSum; // excludes downpayment Payment records
            $order->computed_total_paid = $down + $paidSum; // includes downpayment field
            $order->computed_remaining = max($total - $down - $paidSum, 0);
            $order->computed_latest_due = optional($order->payments()->orderByDesc('due_date')->first())->due_date;
        }

        return view('admin.outstanding-balances.index', compact('orders'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function createPayment(Order $order)
    {
        return view('admin.outstanding-balances.create', compact('order'));
    }

    /**
     * Store a newly created payment.
     */
    public function storePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        // Prevent overpayment: compute remaining balance excluding POS downpayment Payment records
        $paidSum = (float) $order->payments()
            ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
            ->sum('amount');
        $down = (float) ($order->downpayment ?? 0);
        $total = (float) ($order->total ?? 0);
        $remaining = max($total - $down - $paidSum, 0);
        if ((float) ($validated['amount'] ?? 0) > $remaining) {
            return back()
                ->withErrors(['amount' => 'Payment amount exceeds remaining balance (₱' . number_format($remaining, 2) . ').'])
                ->withInput();
        }

        $payment = new Payment($validated);
        $payment->order_id = $order->id;
        $payment->save();

        return redirect()->route('admin.outstanding-balances.index')->with('status', 'Payment added successfully.');
    }

    /**
     * Show the form for editing a payment.
     */
    public function editPayment(Payment $payment)
    {
        $order = $payment->order;
        return view('admin.outstanding-balances.edit', compact('payment', 'order'));
    }

    /**
     * Update the specified payment.
     */
    public function updatePayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
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
        if ((float) ($validated['amount'] ?? 0) > $remaining) {
            return back()
                ->withErrors(['amount' => 'Payment amount exceeds remaining balance (₱' . number_format($remaining, 2) . ').'])
                ->withInput();
        }

        $payment->update($validated);

        return redirect()->route('admin.outstanding-balances.index')->with('status', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment.
     */
    public function destroyPayment(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.outstanding-balances.index')->with('status', 'Payment deleted successfully.');
    }

    /**
     * Send a balance reminder email.
     */
    public function sendReminder(Request $request, Order $order)
    {
        $paidSum = (float) $order->payments()
            ->where(function ($q) { $q->whereNull('reference')->orWhere('reference', 'not like', 'POSDP-%'); })
            ->sum('amount');
        $down = (float) ($order->downpayment ?? 0);
        $total = (float) ($order->total ?? 0);
        $remaining = max($total - $down - $paidSum, 0);
        
        $latestDue = optional($order->payments()->orderByDesc('due_date')->first())->due_date;

        Mail::to($order->customer_email)->send(new BalanceReminderMail($order, $remaining, $latestDue));

        return redirect()->back()->with('status', 'Balance reminder sent successfully.');
    }
}