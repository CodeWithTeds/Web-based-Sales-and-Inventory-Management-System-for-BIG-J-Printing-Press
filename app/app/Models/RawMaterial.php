<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RawMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'unit',
        'quantity',
        'reorder_level',
        'unit_price',
        'supplier',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'float',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Check if raw material is low in stock
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }
    
    /**
     * Calculate the total value of this raw material in inventory
     *
     * @return float
     */
    public function getTotalValue(): float
    {
        return $this->quantity * $this->unit_price;
    }
}