<?php

namespace App\Repositories;

interface MaterialRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Add stock to a material
     * 
     * @param int $id
     * @param float $quantity
     * @param string|null $notes
     * @return mixed
     */
    public function stockIn(int $id, float $quantity, ?string $notes = null);
    
    /**
     * Get materials that are low in stock
     * 
     * @return mixed
     */
    public function getLowStockMaterials();
    
    /**
     * Get materials by category
     * 
     * @param string $category
     * @return mixed
     */
    public function getByCategory(string $category);
    
    /**
     * Get all unique categories
     * 
     * @return array
     */
    public function getUniqueCategories();

    /**
     * Deduct stock from a material (stock out). Should be used inside a DB transaction.
     *
     * @param int $id
     * @param float $quantity
     * @return mixed
     */
    public function stockOut(int $id, float $quantity);
}