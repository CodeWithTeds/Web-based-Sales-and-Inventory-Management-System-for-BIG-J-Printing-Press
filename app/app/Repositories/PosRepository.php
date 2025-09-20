<?php

namespace App\Repositories;

use App\Models\Product;

class PosRepository
{
    public function getProducts(?string $category = null, string $search = '')
    {
        $query = Product::query()->where('active', true);
        if ($category) {
            $query->where('category', $category);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }
        return $query->orderBy('name')->get();
    }

    public function getCategories(): array
    {
        return Product::query()->where('active', true)->distinct()->pluck('category')->filter()->values()->toArray();
    }
}
