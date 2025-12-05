<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class PhysicalInventoryController extends Controller
{
    /**
     * Display the Physical Inventory list.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $items = Product::query()->with('sizes')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('notes', 'like', "%$q%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.physical-inventory.index', [
            'items' => $items,
            'q' => $q,
        ]);
    }

    /**
     * Update the physical count and remarks for a product.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'physical_count' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'size_quantities' => ['nullable', 'array'],
            'size_quantities.*' => ['integer', 'min:0'],
        ]);

        // Store physical count separately; do not alter system quantity
        $product->physical_count = (int) ($data['physical_count'] ?? $product->physical_count);
        if (array_key_exists('remarks', $data)) {
            $product->notes = $data['remarks'];
        }
        $product->save();

        if (is_array($data['size_quantities'] ?? null)) {
            foreach ($data['size_quantities'] as $sid => $qty) {
                $sidInt = (int) $sid;
                $q = (int) $qty;
                if ($q < 0) { $q = 0; }
                $product->sizes()->updateExistingPivot($sidInt, ['quantity' => $q]);
            }
        }

        return back()->with('success', __('Inventory updated successfully.'));
    }
}
