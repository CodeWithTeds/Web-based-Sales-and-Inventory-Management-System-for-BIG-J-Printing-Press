<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'total',
        'downpayment',
        'status',
        'delivery_status',
        'user_id',
        'user_address_id',
        'attachment_path',
    ];

    // Expose a virtual total_amount attribute for legacy references
    protected $appends = ['total_amount'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Alias expected by various Livewire components
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) ($this->total ?? 0);
    }

    public function getRemainingBalanceAttribute(): float
    {
        $down = (float) ($this->downpayment ?? 0);
        $total = (float) ($this->total ?? 0);
        return max($total - $down, 0);
    }
}