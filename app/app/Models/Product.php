<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'category', 'image_path', 'price', 'active', 'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean'
    ];

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'product_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function calculateMaterialCost(): float
    {
        return $this->materials->sum(function ($material) {
            return $material->pivot->quantity * $material->unit_price;
        });
    }
}
