<?php

namespace App\Repositories;

use App\Models\Product;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get products by category
     *
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCategory(string $category);

    /**
     * Get all unique categories
     *
     * @return array
     */
    public function getUniqueCategories();

    /**
     * Get materials for a product
     *
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMaterials(int $productId);

    /**
     * Add material to a product
     *
     * @param int $productId
     * @param int $materialId
     * @param float $quantity
     * @return \App\Models\Product
     */
    public function addMaterial(int $productId, int $materialId, float $quantity);

    /**
     * Remove material from a product
     *
     * @param int $productId
     * @param int $materialId
     * @return bool
     */
    public function removeMaterial(int $productId, int $materialId);

    /**
     * Count all products
     */
    public function countAll(): int;

    /**
     * Count active products
     */
    public function countActive(): int;

    /**
     * Count inactive products
     */
    public function countInactive(): int;

    /**
     * Sum of all product prices
     */
    public function sumPrice(): float;

    /**
     * Average product price
     */
    public function avgPrice(): float;
}