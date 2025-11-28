<?php

namespace App\Repositories;

use App\Models\Material;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
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
     * Override paginate to sort low-stock materials first
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model
            ->orderByRaw('CASE WHEN quantity <= reorder_level THEN 0 ELSE 1 END ASC')
            ->orderBy('quantity')
            ->orderBy('name')
            ->paginate($perPage, $columns);
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
            
            // Log inventory transaction for reporting
            InventoryTransaction::create([
                'subject_type' => 'material',
                'subject_id'   => $material->id,
                'type'         => 'in',
                'quantity'     => $quantity,
                'unit'         => $material->unit ?? null,
                'name'         => $material->name ?? null,
                'notes'        => $notes,
                'created_by'   => Auth::id(),
            ]);
            
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
        DB::beginTransaction();
        try {
            $material->quantity = (float) $material->quantity - $quantity;
            $material->save();

            // Log inventory transaction for reporting
            InventoryTransaction::create([
                'subject_type' => 'material',
                'subject_id'   => $material->id,
                'type'         => 'out',
                'quantity'     => $quantity,
                'unit'         => $material->unit ?? null,
                'name'         => $material->name ?? null,
                'notes'        => null,
                'created_by'   => Auth::id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $material;
    }

    /**
     * Override update to log inventory transactions when quantity changes via edit form.
     *
     * @param \Illuminate\Database\Eloquent\Model|array $data
     * @param array|int $id
     * @return mixed
     */
    public function update($data, $id)
    {
        // Resolve record and update payload
        if ($data instanceof Material) {
            $record = $data;
            $updateData = $id;
        } else {
            $record = $this->find($id);
            $updateData = $data;
        }

        $oldQuantity = (float) ($record->quantity ?? 0);
        $newQuantity = array_key_exists('quantity', (array) $updateData)
            ? (float) $updateData['quantity']
            : $oldQuantity;

        // Perform the update
        $result = parent::update($data, $id);

        // Log an inventory transaction only when quantity actually changes
        $delta = $newQuantity - $oldQuantity;
        if ($delta !== 0) {
            InventoryTransaction::create([
                'subject_type' => 'material',
                'subject_id'   => (int) $record->id,
                'type'         => $delta > 0 ? 'in' : 'out',
                'quantity'     => abs((float) $delta),
                'unit'         => $record->unit ?? null,
                'name'         => $record->name ?? null,
                'notes'        => 'Stock adjusted from ' . $oldQuantity . ' to ' . $newQuantity,
                'created_by'   => Auth::id(),
            ]);
        }

        return $result;
    }
}