<?php

namespace App\Services;

use App\Repositories\PosRepository;
use App\Models\Order;

class PosService
{
    public function __construct(
        protected PosRepository $pos,
        protected CartService $cart
    ) {}

    public function getIndexData(?string $category, string $search): array
    {
        return [
            'products' => $this->pos->getProducts($category, $search),
            'categories' => $this->pos->getCategories(),
            'cart' => $this->cart->all(),
            'total' => $this->cart->total(),
            'itemCount' => $this->cart->itemCount(),
            'category' => $category,
            'search' => $search,
            // Add outstanding orders list for admin POS
            'outstandingOrders' => $this->getOutstandingOrders(),
        ];
    }

    public function getCartPartialData(): array
    {
        return [
            'cart' => $this->cart->all(),
            'total' => $this->cart->total(),
            'itemCount' => $this->cart->itemCount(),
            'success' => null,
        ];
    }

    public function formatInsufficientStockError(array $details): string
    {
        $errorLines = array_map(function ($d) {
            $unit = isset($d['unit']) && $d['unit'] !== '' ? ' ' . $d['unit'] : '';
            return sprintf(
                '%s needs %.2f%s but only %.2f%s available',
                $d['name'],
                (float) $d['required'],
                $unit,
                (float) $d['available'],
                $unit
            );
        }, $details);
        return 'Insufficient material stock: ' . implode('; ', $errorLines) . '.';
    }

    // Fetch orders with remaining balance > 0 and their latest due date
    public function getOutstandingOrders(): array
    {
        // Load payments to compute latest due date per order
        $orders = Order::with('payments')
            ->orderByDesc('created_at')
            ->get();

        $outstanding = [];
        foreach ($orders as $order) {
            $remaining = (float) ($order->remaining_balance ?? 0);
            if ($remaining > 0) {
                $latestDue = $order->payments
                    ->filter(fn($p) => !is_null($p->due_date))
                    ->sortByDesc('due_date')
                    ->first();
                $outstanding[] = [
                    'order' => $order,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'remaining_balance' => $remaining,
                    'due_date' => $latestDue?->due_date,
                ];
            }
        }

        return $outstanding;
    }
}