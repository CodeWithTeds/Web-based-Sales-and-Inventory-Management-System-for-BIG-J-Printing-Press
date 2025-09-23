<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Material;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * ProductRepository constructor.
     *
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Get products by category
     *
     * @param string $category
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByCategory(string $category)
    {
        return $this->model->where('category', $category)->paginate(15)->withQueryString();
    }

    /**
     * Get all unique categories
     *
     * @return array
     */
    public function getUniqueCategories()
    {
        return $this->model->distinct()->orderBy('category')->pluck('category')->toArray();
    }

    /**
     * Get materials for a product
     *
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMaterials(int $productId)
    {
        $product = $this->find($productId);
        return $product->materials;
    }

    /**
     * Add material to a product
     *
     * @param int $productId
     * @param int $materialId
     * @param float $quantity
     * @return \App\Models\Product
     * @throws \RuntimeException If material is out of stock
     */
    public function addMaterial(int $productId, int $materialId, float $quantity)
    {
        $product = $this->find($productId);
        $material = Material::findOrFail($materialId);

        // Check if there's enough stock
        if ($material->quantity < $quantity) {
            throw new \RuntimeException("INSUFFICIENT_STOCK: {$material->name} has only {$material->quantity} {$material->unit} available, but {$quantity} {$material->unit} required.");
        }

        // Check if the material is already attached to the product
        if ($product->materials()->where('material_id', $materialId)->exists()) {
            // Update the existing pivot record
            $product->materials()->updateExistingPivot($materialId, [
                'quantity' => $quantity
            ]);
        } else {
            // Attach the new material
            $product->materials()->attach($materialId, [
                'quantity' => $quantity
            ]);
        }

        return $product->fresh(['materials']);
    }

    /**
     * Remove material from a product
     *
     * @param int $productId
     * @param int $materialId
     * @return bool
     */
    public function removeMaterial(int $productId, int $materialId)
    {
        $product = $this->find($productId);
        $product->materials()->detach($materialId);
        return true;
    }

    // Aggregates
    public function countAll(): int
    {
        return (int) $this->model->count();
    }

    public function countActive(): int
    {
        return (int) $this->model->where('active', true)->count();
    }

    public function countInactive(): int
    {
        return (int) $this->model->where('active', false)->count();
    }

    public function sumPrice(): float
    {
        return (float) $this->model->sum('price');
    }

    public function avgPrice(): float
    {
        return (float) $this->model->avg('price');
    }
}