<?php

namespace App\Repositories;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
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

                    // Log inventory transaction for material usage
                    InventoryTransaction::create([
                        'subject_type' => 'material',
                        'subject_id'   => (int) $material->id,
                        'type'         => 'out',
                        'quantity'     => (float) $requiredQty,
                        'unit'         => $material->unit ?? null,
                        'name'         => $material->name ?? null,
                        'notes'        => 'Used in POS order ' . ($order->order_number ?? ''),
                        'created_by'   => Auth::id(),
                    ]);
                }
            }

            return $order;
        });
    }

    /**
     * Process multiple checkouts atomically under one PO: validate aggregated material stock, create orders and items, then deduct materials.
     * @param string $poNumber Shared PO number for all orders in the batch
     * @param array<int, array{orderData:array, items:array<int, array{product_id:int,name:string,qty:int,price:float,line_total:float}>}> $batches
     * @param array<int,float> $materialRequirements Aggregated requirements across all batches
     * @return array<int, Order>
     */
    public function processBatchCheckout(string $poNumber, array $batches, array $materialRequirements): array
    {
        return DB::transaction(function () use ($poNumber, $batches, $materialRequirements) {
            // Lock materials and validate stock for aggregated requirements
            if (!empty($materialRequirements)) {
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

            // Create orders and items
            $orders = [];
            foreach ($batches as $batch) {
                $data = $batch['orderData'];
                $data['po_number'] = $poNumber;
                $order = Order::create($data);
                foreach ($batch['items'] as $it) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $it['product_id'],
                        'name'       => $it['name'],
                        'qty'        => $it['qty'],
                        'price'      => $it['price'],
                        'line_total' => $it['line_total'],
                    ]);
                }
                $orders[] = $order;
            }

            // Deduct aggregated materials and log usage
            foreach ($materialRequirements as $materialId => $requiredQty) {
                $material = Material::findOrFail($materialId);
                $material->quantity -= $requiredQty;
                $material->save();

                InventoryTransaction::create([
                    'subject_type' => 'material',
                    'subject_id'   => (int) $material->id,
                    'type'         => 'out',
                    'quantity'     => (float) $requiredQty,
                    'unit'         => $material->unit ?? null,
                    'name'         => $material->name ?? null,
                    'notes'        => 'Used in batch PO ' . $poNumber,
                    'created_by'   => Auth::id(),
                ]);
            }

            return $orders;
        });
    }
}