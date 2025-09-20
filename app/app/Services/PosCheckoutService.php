<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\CheckoutRepository;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

class PosCheckoutService
{
    public function __construct(
        protected CheckoutRepository $checkoutRepo,
        protected CartService $cart
    ) {}

    /**
     * Validate cart, prepare order data, build material requirements & line items, and call repository.
     * Returns array with keys: success(bool), order(Order|null), error(string|null)
     */
    public function checkout(array $payload): array
    {
        $cart = $this->cart->all();
        if (empty($cart)) {
            return [ 'success' => false, 'order' => null, 'error' => 'Cart is empty.' ];
        }

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        if ($customerName === '') {
            // Default to a generic label when no name is provided
            $customerName = 'Walk-in Customer';
        }
        if ($customerName === '') {
            return [ 'success' => false, 'order' => null, 'error' => 'Customer name is required.' ];
        }

        try {
            // Build requirements and items
            $requirements = [];
            $items = [];
            $total = 0.0;

            foreach ($cart as $item) {
                if (!isset($item['id'], $item['qty'], $item['price'], $item['name'])) {
                    // Skip malformed line
                    continue;
                }
                $product = Product::with('materials')->findOrFail((int) $item['id']);
                foreach ($product->materials as $material) {
                    $required = (float) $material->pivot->quantity * (int) $item['qty'];
                    $requirements[$material->id] = ($requirements[$material->id] ?? 0) + $required;
                }
                $lineTotal = (float) $item['price'] * (int) $item['qty'];
                $items[] = [
                    'product_id' => (int) $item['id'],
                    'name' => (string) $item['name'],
                    'qty' => (int) $item['qty'],
                    'price' => (float) $item['price'],
                    'line_total' => $lineTotal,
                ];
                $total += $lineTotal;
            }

            $orderData = [
                'order_number' => 'POS-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'customer_name' => $customerName,
                'total' => $total,
                'status' => 'completed',
                'user_id' => Auth::id(),
            ];

            $order = $this->checkoutRepo->processCheckout($orderData, $items, $requirements);
            $this->cart->clear();
            return [ 'success' => true, 'order' => $order, 'error' => null ];
        } catch (\Throwable $e) {
            return [ 'success' => false, 'order' => null, 'error' => $e->getMessage() ];
        }
    }
}