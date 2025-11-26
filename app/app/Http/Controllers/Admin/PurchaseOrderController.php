<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\CheckoutRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function __construct(protected CheckoutRepository $checkoutRepo) {}

    /**
     * Show the Walk-in Purchase Order creation page (admin).
     */
    public function create()
    {
        return view('admin.purchase-orders.create');
    }

    /**
     * Store a Walk-in Purchase Order created by admin.
     */
    public function store(Request $request)
    {
        // Support single-order and multi-order payloads
        $validated = $request->validate([
            'ordersPayload' => ['nullable'], // JSON array of sub-orders
            'itemsPayload' => ['nullable'], // single order fallback
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $poNumber = 'PO-' . now()->format('YmdHis') . '-' . random_int(100, 999);

        // If multi-order provided, parse and process as batch
        $ordersPayload = $validated['ordersPayload'] ?? null;
        if (is_string($ordersPayload)) {
            $ordersPayload = json_decode($ordersPayload, true);
        }

        if (is_array($ordersPayload) && !empty($ordersPayload)) {
            // Build batches and aggregate requirements
            $batches = [];
            $requirements = [];
            foreach ($ordersPayload as $idx => $sub) {
                $custName = (string) ($sub['customer_name'] ?? ($validated['customer_name'] ?? 'Walk-in Customer'));
                $custEmail = (string) ($sub['customer_email'] ?? ($validated['customer_email'] ?? ''));
                $itemsRaw = $sub['items'] ?? [];
                if (!is_array($itemsRaw) || empty($itemsRaw)) {
                    return back()->withErrors(['ordersPayload' => 'Order #' . ($idx + 1) . ' has no items.'])->withInput();
                }
                $lineItems = [];
                $total = 0.0;
                foreach ($itemsRaw as $row) {
                    $productId = (int) ($row['id'] ?? 0);
                    $qty = (int) ($row['qty'] ?? 0);
                    if ($productId <= 0 || $qty < 1) {
                        return back()->withErrors(['ordersPayload' => 'Invalid item in Order #' . ($idx + 1) . '.'])->withInput();
                    }
                    $product = Product::with(['materials'])->find($productId);
                    if (!$product) {
                        return back()->withErrors(['ordersPayload' => 'Product not found in Order #' . ($idx + 1) . '.'])->withInput();
                    }
                    $price = (float) ($product->price ?? 0);
                    $lineTotal = (float) $price * (int) $qty;
                    $lineItems[] = [
                        'product_id' => $product->id,
                        'name' => (string) $product->name,
                        'qty' => $qty,
                        'price' => $price,
                        'line_total' => $lineTotal,
                    ];
                    $total += $lineTotal;
                    foreach ($product->materials as $m) {
                        $required = (float) $m->pivot->quantity * (int) $qty;
                        $requirements[(int) $m->id] = ($requirements[(int) $m->id] ?? 0) + $required;
                    }
                }
                $batches[] = [
                    'orderData' => [
                        'order_number' => $poNumber . '-' . ($idx + 1),
                        'customer_name' => $custName,
                        'customer_email' => $custEmail ?: null,
                        'total' => $total,
                        'downpayment' => 0,
                        'status' => 'approved',
                        'delivery_status' => 'preparing',
                        'user_id' => Auth::id(),
                        'user_address_id' => null,
                        'attachment_path' => null,
                    ],
                    'items' => $lineItems,
                ];
            }

            try {
                $orders = $this->checkoutRepo->processBatchCheckout($poNumber, $batches, $requirements);
                return redirect()->route('admin.orders.show', $orders[0])
                    ->with('status', 'PO ' . $poNumber . ' created with ' . count($orders) . ' orders.');
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (str_starts_with($msg, 'INSUFFICIENT_STOCK:')) {
                    $details = json_decode(substr($msg, strlen('INSUFFICIENT_STOCK:')), true);
                    $human = is_array($details)
                        ? 'Insufficient stock for: ' . implode(', ', array_map(fn($d) => ($d['name'] ?? 'Material') . ' (required: ' . ($d['required'] ?? '?') . ' ' . ($d['unit'] ?? '') . ', available: ' . ($d['available'] ?? '?') . ' ' . ($d['unit'] ?? '') . ')', $details))
                        : 'Insufficient stock for one or more materials.';
                    return back()->withErrors(['materials' => $human])->withInput();
                }
                return back()->withErrors(['checkout' => 'Failed to create batch PO: ' . $msg])->withInput();
            }
        }

        // Fallback: single order payload (existing behavior)
        $rawItems = $validated['itemsPayload'] ?? '';
        if (is_string($rawItems)) {
            $decoded = json_decode($rawItems, true);
            $rawItems = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($rawItems) || empty($rawItems)) {
            return back()->withErrors(['itemsPayload' => 'Please add at least one product and quantity.'])->withInput();
        }

        // Build single order
        $lineItems = [];
        $total = 0.0;
        $requirements = [];
        foreach ($rawItems as $row) {
            $type = (string) ($row['type'] ?? '');
            $productId = (int) ($row['id'] ?? 0);
            $qty = (int) ($row['qty'] ?? 0);
            if ($type !== 'products' || $productId <= 0 || $qty < 1) {
                return back()->withErrors(['itemsPayload' => 'Invalid item selection or quantity.'])->withInput();
            }
            $product = Product::with(['materials'])->find($productId);
            if (!$product) {
                return back()->withErrors(['itemsPayload' => 'Selected product not found.'])->withInput();
            }
            $price = (float) ($product->price ?? 0);
            $lineTotal = (float) $price * (int) $qty;
            $lineItems[] = [
                'product_id' => $product->id,
                'name' => (string) $product->name,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $lineTotal,
            ];
            $total += $lineTotal;
            foreach ($product->materials as $m) {
                $required = (float) $m->pivot->quantity * (int) $qty;
                $requirements[(int) $m->id] = ($requirements[(int) $m->id] ?? 0) + $required;
            }
        }

        $user = Auth::user();
        $orderData = [
            'order_number' => $poNumber,
            'customer_name' => $validated['customer_name'] ?? ($user?->name ? ('Walk-in by ' . $user->name) : 'Walk-in Customer'),
            'customer_email' => $validated['customer_email'] ?? null,
            'total' => $total,
            'downpayment' => 0,
            'status' => 'approved',
            'delivery_status' => 'preparing',
            'user_id' => Auth::id(),
            'user_address_id' => null,
            'attachment_path' => null,
        ];

        try {
            $order = $this->checkoutRepo->processCheckout($orderData, $lineItems, $requirements, false);
            return redirect()->route('admin.orders.show', $order)
                ->with('status', 'Walk-in PO #' . $order->order_number . ' created successfully.');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_starts_with($msg, 'INSUFFICIENT_STOCK:')) {
                $details = json_decode(substr($msg, strlen('INSUFFICIENT_STOCK:')), true);
                $human = is_array($details)
                    ? 'Insufficient stock for: ' . implode(', ', array_map(fn($d) => ($d['name'] ?? 'Material') . ' (required: ' . ($d['required'] ?? '?') . ' ' . ($d['unit'] ?? '') . ', available: ' . ($d['available'] ?? '?') . ' ' . ($d['unit'] ?? '') . ')', $details))
                    : 'Insufficient stock for one or more materials.';
                return back()->withErrors(['materials' => $human])->withInput();
            }
            return back()->withErrors(['checkout' => 'Failed to create Purchase Order: ' . $msg])->withInput();
        }
    }
}