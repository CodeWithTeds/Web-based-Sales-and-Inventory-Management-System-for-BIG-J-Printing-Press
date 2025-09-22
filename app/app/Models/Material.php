<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'category', 'unit', 'quantity', 'reorder_level', 'unit_price', 'supplier', 'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2'
    ];

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getTotalValue(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }
}