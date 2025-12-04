<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'category', 'paper_type', 'image_path', 'price', 'unit', 'quantity', 'physical_count', 'status', 'active', 'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'physical_count' => 'integer',
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

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
