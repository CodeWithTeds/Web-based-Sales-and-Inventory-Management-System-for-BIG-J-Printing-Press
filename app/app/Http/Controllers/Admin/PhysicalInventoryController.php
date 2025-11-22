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

        $items = Product::query()
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
        ]);

        $product->quantity = (int) ($data['physical_count'] ?? $product->quantity);
        if (array_key_exists('remarks', $data)) {
            $product->notes = $data['remarks'];
        }
        $product->save();

        return back()->with('success', __('Inventory updated successfully.'));
    }
}