<?php

namespace App\Repositories;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutRepository
{
    /**
     * Process a checkout: create order, items, validate and deduct material stock atomically.
     * @param array $orderData
     * @param array<int, array{product_id:int,name:string,qty:int,price:float,line_total:float,selections?:array}> $items
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
                    'selections' => $it['selections'] ?? null,
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

                // Deduct product size stock when selections specify sizes
                foreach ($items as $it) {
                    $productId = (int) ($it['product_id'] ?? 0);
                    $qty = (int) ($it['qty'] ?? 0);
                    if ($productId <= 0 || $qty < 1) { continue; }
                    $sizeIds = [];
                    if (isset($it['selections']) && is_array($it['selections']) && isset($it['selections']['size_ids']) && is_array($it['selections']['size_ids'])) {
                        $sizeIds = array_values(array_filter(array_map(fn($v) => (int) $v, $it['selections']['size_ids']), fn($n) => $n > 0));
                    }
                    if (empty($sizeIds)) { continue; }

                    $product = Product::find($productId);
                    foreach ($sizeIds as $sid) {
                        $pivot = DB::table('product_size')
                            ->where('product_id', $productId)
                            ->where('size_id', (int) $sid)
                            ->lockForUpdate()
                            ->first();
                        if (!$pivot) { continue; }
                        $available = (int) ($pivot->quantity ?? 0);
                        $deduct = min($available, $qty);
                        $newQty = max($available - $deduct, 0);
                        DB::table('product_size')->where('id', $pivot->id)->update(['quantity' => $newQty]);

                        if ($deduct > 0) {
                            $sizeName = Size::find((int) $sid)?->name ?? ('Size #' . (int) $sid);
                            InventoryTransaction::create([
                                'subject_type' => 'product',
                                'subject_id'   => (int) ($product?->id ?? $productId),
                                'type'         => 'out',
                                'quantity'     => (float) $deduct,
                                'unit'         => $product?->unit ?? null,
                                'name'         => $product?->name ? ($product->name . ' - ' . $sizeName) : null,
                                'notes'        => 'Size stock used in order ' . ($order->order_number ?? ''),
                                'created_by'   => Auth::id(),
                            ]);
                        }
                    }
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
                        'selections' => $it['selections'] ?? null,
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

            // Deduct product size stock for each order in the batch when sizes are provided,
            // based on persisted selections on the order items
            foreach ($orders as $order) {
                foreach ($order->items as $it) {
                    $productId = (int) ($it->product_id ?? 0);
                    $qty = (int) ($it->qty ?? 0);
                    if ($productId <= 0 || $qty < 1) { continue; }
                    $sel = is_array($it->selections) ? $it->selections : [];
                    $sizeIds = isset($sel['size_ids']) && is_array($sel['size_ids'])
                        ? array_values(array_filter(array_map(fn($v) => (int) $v, $sel['size_ids']), fn($n) => $n > 0))
                        : [];
                    if (empty($sizeIds)) { continue; }

                    $product = Product::find($productId);
                    foreach ($sizeIds as $sid) {
                        $pivot = DB::table('product_size')
                            ->where('product_id', $productId)
                            ->where('size_id', (int) $sid)
                            ->lockForUpdate()
                            ->first();
                        if (!$pivot) { continue; }
                        $available = (int) ($pivot->quantity ?? 0);
                        $deduct = min($available, $qty);
                        $newQty = max($available - $deduct, 0);
                        DB::table('product_size')->where('id', $pivot->id)->update(['quantity' => $newQty]);

                        if ($deduct > 0) {
                            $sizeName = Size::find((int) $sid)?->name ?? ('Size #' . (int) $sid);
                            InventoryTransaction::create([
                                'subject_type' => 'product',
                                'subject_id'   => (int) ($product?->id ?? $productId),
                                'type'         => 'out',
                                'quantity'     => (float) $deduct,
                                'unit'         => $product?->unit ?? null,
                                'name'         => $product?->name ? ($product->name . ' - ' . $sizeName) : null,
                                'notes'        => 'Size stock used in batch PO ' . $poNumber,
                                'created_by'   => Auth::id(),
                            ]);
                        }
                    }
                }
            }

            return $orders;
        });
    }
}
