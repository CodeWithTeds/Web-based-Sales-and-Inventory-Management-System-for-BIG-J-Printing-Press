<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    protected string $sessionKey = 'pos_cart';

    public function all(): array
    {
        return session()->get($this->sessionKey, []);
    }

    public function save(array $cart): void
    {
        session()->put($this->sessionKey, $cart);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    public function add(int $productId): void
    {
        $cart = $this->all();
        $product = Product::findOrFail($productId);
        if (!isset($cart[$productId])) {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'qty' => 0,
                'image_path' => $product->image_path ?? null,
            ];
        }
        $cart[$productId]['qty'] += 1;
        $this->save($cart);
    }

    public function increment(int $productId): void
    {
        $cart = $this->all();
        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] += 1;
            $this->save($cart);
        }
    }

    public function decrement(int $productId): void
    {
        $cart = $this->all();
        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] -= 1;
            if ($cart[$productId]['qty'] <= 0) {
                unset($cart[$productId]);
            }
            $this->save($cart);
        }
    }

    public function remove(int $productId): void
    {
        $cart = $this->all();
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->save($cart);
        }
    }

    public function total(): float
    {
        return collect($this->all())->reduce(function ($carry, $item) {
            return $carry + ($item['price'] * $item['qty']);
        }, 0.0);
    }

    public function itemCount(): int
    {
        return (int) collect($this->all())->sum('qty');
    }
}