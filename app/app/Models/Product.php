<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
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
        'image_path',
        'price',
        'active',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get the materials required for this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Calculate the total cost of materials for this product.
     *
     * @return float
     */
    public function calculateMaterialCost(): float
    {
        $totalCost = 0;

        foreach ($this->materials as $material) {
            $totalCost += (float) $material->unit_price * (float) $material->pivot->quantity;
        }

        return $totalCost;
    }
}
