<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Admin-only: List Purchase Requests (orders with order_number starting PR-).
     */
    public function prIndex(Request $request)
    {
        // Only show pending Purchase Requests
        $query = Order::query()->with(['user', 'userAddress', 'items.product', 'payments'])
            ->where('order_number', 'like', 'PR-%')
            ->where('status', 'pending');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter removed to enforce showing only pending PRs

        $orders = $query->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'userAddress', 'items.product', 'payments']);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'userAddress', 'items.product.materials', 'payments']);
        $allMaterials = \App\Models\Material::orderBy('name')->get();

        $address = $order->userAddress;
        $addressNames = [
            'region_name' => null,
            'province_name' => null,
            'city_name' => null,
            'barangay_name' => null,
        ];

        if ($address) {
            if ($address->region_code) {
                $addressNames['region_name'] = DB::table('regions')->where('code', $address->region_code)->value('name');
            }
            if ($address->province_code) {
                $addressNames['province_name'] = DB::table('provinces')->where('code', $address->province_code)->value('name');
            }
            if ($address->city_code) {
                $addressNames['city_name'] = DB::table('cities')->where('code', $address->city_code)->value('name');
            }
            if ($address->barangay_code) {
                $addressNames['barangay_name'] = DB::table('barangays')->where('code', $address->barangay_code)->value('name');
            }
        }

        $deliveryStatuses = $this->deliveryStatuses();

        return view('admin.orders.show', [
            'order' => $order,
            'address' => $address,
            'addressNames' => $addressNames,
            'deliveryStatuses' => $deliveryStatuses,
            'materials' => $allMaterials,
        ]);
    }

    public function updateDeliveryStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_status' => ['required', 'string', 'in:' . implode(',', $this->deliveryStatuses())],
        ]);

        $order->delivery_status = $validated['delivery_status'];
        $order->save();

        return back()->with('status', 'Delivery status updated.');
    }

    private function deliveryStatuses(): array
    {
        return [
            'pending',
            'preparing',
            'out_for_delivery',
            'delivered',
            'cancelled',
        ];
    }

    /**
     * Approve a pending purchase request: set item prices, delivery date, and allocate materials.
     */
    public function approve(Request $request, Order $order)
    {
        // Only allow approving pending orders
        if ($order->status !== 'pending') {
            return back()->withErrors(['approve' => 'Only pending Purchase Requests can be approved.']);
        }

        $validated = $request->validate([
            'delivery_date' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'materials' => ['nullable', 'array'],
            'materials.*.id' => ['required', 'integer'],
            'materials.*.qty' => ['required', 'numeric', 'min:0'],
            'materials.*.required' => ['nullable', 'numeric', 'min:0'],
            'materials.*.product_id' => ['nullable', 'integer'],
        ]);

        // Load products/materials to compute default requirements
        $order->load(['items.product.materials']);

        // Build price map from validated items
        $priceByItemId = [];
        foreach ($validated['items'] as $it) {
            $priceByItemId[(int) $it['id']] = (float) $it['price'];
        }

        // Build default requirements from product-material mappings
        $requirements = [];
        foreach ($order->items as $it) {
            if ($it->product) {
                foreach ($it->product->materials as $m) {
                    $required = (float) $m->pivot->quantity * (int) $it->qty;
                    $requirements[(int) $m->id] = ($requirements[(int) $m->id] ?? 0) + $required;
                }
            }
        }

        // Allow overriding or defining required quantities from posted data
        if (!empty($validated['materials'])) {
            foreach ($validated['materials'] as $m) {
                $mid = (int) $m['id'];
                if (array_key_exists('required', $m) && $m['required'] !== null) {
                    $requirements[$mid] = max((float) $m['required'], 0);
                } elseif (!array_key_exists($mid, $requirements)) {
                    // Manual material without existing mapping; fallback to posted qty if provided
                    $requirements[$mid] = max((float) ($m['qty'] ?? 0), 0);
                }
            }
        }

        // Build material allocations (use posted values; fallback to requirements)
        $allocations = [];
        if (!empty($validated['materials'])) {
            foreach ($validated['materials'] as $m) {
                $allocations[(int) $m['id']] = (float) $m['qty'];
            }
        } else {
            $allocations = $requirements; // default to required quantities
        }

        try {
            DB::transaction(function () use ($order, $priceByItemId, $allocations, $validated, $requirements) {
                // Update item prices and line totals
                $total = 0.0;
                $items = $order->items()->get();
                foreach ($items as $item) {
                    $newPrice = $priceByItemId[$item->id] ?? null;
                    if ($newPrice === null) {
                        throw new \RuntimeException('Missing price for item #' . $item->id);
                    }
                    $item->price = $newPrice;
                    $item->line_total = (float) $newPrice * (int) $item->qty;
                    $item->save();
                    $total += (float) $item->line_total;
                }

                // Deduct materials; auto-clamp to available and track shortfalls
                $shortfalls = [];
                foreach ($allocations as $materialId => $qty) {
                    /** @var Material $material */
                    $material = Material::findOrFail($materialId);
                    $current = (float) $material->quantity;
                    $requested = (float) $qty;
                    $allocate = min($requested, $current);
                    $material->quantity = max($current - $allocate, 0);
                    $material->save();

                    $required = (float) ($requirements[$materialId] ?? $requested);
                    $shortfall = max($required - $allocate, 0);
                    if ($shortfall > 0) {
                        $shortfalls[$material->name] = $shortfall;
                    }
                }

                // Persist new material-to-product mappings for manually added materials
                // Compute ordered quantity per product in this order (for per-unit pivot quantity)
                $orderedQtyByProduct = [];
                foreach ($order->items as $it) {
                    if ($it->product_id) {
                        $orderedQtyByProduct[$it->product_id] = ($orderedQtyByProduct[$it->product_id] ?? 0) + (int) $it->qty;
                    }
                }
                if (!empty($validated['materials'])) {
                    foreach ($validated['materials'] as $mRow) {
                        $mid = (int) ($mRow['id'] ?? 0);
                        $pid = isset($mRow['product_id']) ? (int) $mRow['product_id'] : 0;
                        $postedQty = (float) ($mRow['qty'] ?? 0);
                        $postedReq = isset($mRow['required']) ? (float) $mRow['required'] : null;
                        $basis = ($postedReq !== null && $postedReq > 0) ? $postedReq : $postedQty;
                        if ($mid > 0 && $pid > 0 && $basis > 0 && isset($orderedQtyByProduct[$pid]) && $orderedQtyByProduct[$pid] > 0) {
                            $perUnit = round($basis / (float) $orderedQtyByProduct[$pid], 6);
                            $product = Product::find($pid);
                            if ($product) {
                                $exists = $product->materials()->where('materials.id', $mid)->exists();
                                if (!$exists) {
                                    $product->materials()->attach($mid, ['quantity' => $perUnit]);
                                }
                            }
                        }
                    }
                }

                // Update order fields
                $order->fill([
                    'total' => $total,
                    'delivery_date' => $validated['delivery_date'],
                    'status' => 'approved',
                    'delivery_status' => 'preparing',
                ]);
                $order->save();

                // Attach a status message about shortfalls (if any)
                if (!empty($shortfalls)) {
                    session()->flash('status', 'Approved with material shortfalls for: ' . implode(', ', array_map(function($name, $qty){ return $name . ' (' . $qty . ')'; }, array_keys($shortfalls), array_values($shortfalls))) . '. Allocations were clamped to available stock.');
                } else {
                    session()->flash('status', 'Purchase Request approved successfully.');
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['approve' => 'Failed to approve request: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.orders.show', $order);
    }
}