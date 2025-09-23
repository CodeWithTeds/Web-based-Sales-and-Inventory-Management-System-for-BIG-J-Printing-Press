<?php

namespace App\Repositories;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutRepository
{
    /**
     * Process a checkout: create order, items, validate and deduct material stock atomically.
     * @param array $orderData
     * @param array<int, array{product_id:int,name:string,qty:int,price:float,line_total:float}> $items
     * @param array<int,float> $materialRequirements material_id => total_required
     * @param bool $skipStock When true, skip stock validation and deduction (used for client ordering "free" orders)
     * @return Order
     */
    public function processCheckout(array $orderData, array $items, array $materialRequirements, bool $skipStock = false): Order
    {
        return DB::transaction(function () use ($orderData, $items, $materialRequirements, $skipStock) {
            // Lock materials and validate stock
            if (!$skipStock && !empty($materialRequirements)) {
                $materials = Material::whereIn('id', array_keys($materialRequirements))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $insufficient = [];
                foreach ($materialRequirements as $materialId => $requiredQty) {
                    $material = $materials[$materialId] ?? null;
                    if (!$material) {
                        throw new \RuntimeException("Material {$materialId} not found");
                    }
                    if ((float) $material->quantity < (float) $requiredQty) {
                        $insufficient[] = [
                            'name' => $material->name,
                            'required' => (float) $requiredQty,
                            'available' => (float) $material->quantity,
                            'unit' => $material->unit ?? '',
                        ];
                    }
                }
                if (!empty($insufficient)) {
                    throw new \RuntimeException('INSUFFICIENT_STOCK:' . json_encode($insufficient));
                }
            }

            // Create order
            $order = Order::create($orderData);

            // Create order items (ensure required columns match migration/model)
            foreach ($items as $it) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $it['product_id'],
                    'name'       => $it['name'],
                    'qty'        => $it['qty'],
                    'price'      => $it['price'],
                    'line_total' => $it['line_total'],
                ]);
            }

            // Deduct materials only when not skipping stock handling
            if (!$skipStock) {
                foreach ($materialRequirements as $materialId => $requiredQty) {
                    $material = Material::findOrFail($materialId);
                    $material->quantity -= $requiredQty;
                    $material->save();
                }
            }

            return $order;
        });
    }
}