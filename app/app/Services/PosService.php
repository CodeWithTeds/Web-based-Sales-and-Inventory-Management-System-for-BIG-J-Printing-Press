<?php

namespace App\Services;

use App\Repositories\PosRepository;

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
}