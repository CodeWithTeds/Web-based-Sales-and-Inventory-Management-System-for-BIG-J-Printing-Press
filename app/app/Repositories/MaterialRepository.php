<?php

namespace App\Repositories;

use App\Models\Material;
use Illuminate\Support\Facades\DB;

class MaterialRepository extends BaseRepository implements MaterialRepositoryInterface
{
    /**
     * MaterialRepository constructor.
     *
     * @param Material $model
     */
    public function __construct(Material $model)
    {
        parent::__construct($model);
    }

    /**
     * Add stock to a material
     *
     * @param int $id
     * @param float $quantity
     * @param string|null $notes
     * @return mixed
     */
    public function stockIn(int $id, float $quantity, ?string $notes = null)
    {
        $material = $this->find($id);
        
        DB::beginTransaction();
        try {
            // Update the quantity
            $material->quantity += $quantity;
            $material->save();
            
            // You could also log this transaction in a separate stock_transactions table
            // if you want to keep a history of all stock movements
            
            DB::commit();
            return $material;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get materials that are low in stock
     *
     * @return mixed
     */
    public function getLowStockMaterials()
    {
        return $this->model->whereRaw('quantity <= reorder_level')->get();
    }

    /**
     * Get materials by category
     *
     * @param string $category
     * @return mixed
     */
    public function getByCategory(string $category)
    {
        return $this->model->where('category', $category)->get();
    }
    
    /**
     * Get all unique categories
     *
     * @return array
     */
    public function getUniqueCategories()
    {
        return $this->model->select('category')->distinct()->orderBy('category')->pluck('category')->toArray();
    }

    public function stockOut(int $id, float $quantity)
    {
        $material = $this->find($id);
        if ($quantity <= 0) {
            return $material;
        }
        if ((float) $material->quantity < $quantity) {
            throw new \RuntimeException('INSUFFICIENT_STOCK:' . json_encode([
                [
                    'name' => $material->name,
                    'required' => $quantity,
                    'available' => (float) $material->quantity,
                    'unit' => $material->unit ?? '',
                ]
            ]));
        }
        $material->quantity = (float) $material->quantity - $quantity;
        $material->save();
        return $material;
    }
}