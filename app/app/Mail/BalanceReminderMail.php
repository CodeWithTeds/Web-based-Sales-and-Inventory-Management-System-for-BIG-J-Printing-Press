<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class BalanceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public float $remaining;
    public ?Carbon $dueDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, float $remaining, ?\DateTimeInterface $dueDate = null)
    {
        $this->order = $order;
        $this->remaining = $remaining;
        $this->dueDate = $dueDate ? Carbon::parse($dueDate) : null;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $subject = sprintf('Payment Reminder for Order #%d', $this->order->id);

        return $this->subject($subject)
            ->view('emails.balance-reminder')
            ->with([
                'order' => $this->order,
                'remaining' => $this->remaining,
                'dueDate' => $this->dueDate,
            ]);
    }
}