<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;

class MaterialPhysicalInventoryController extends Controller
{
    /**
     * Display the Physical Inventory list for materials.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $items = Material::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('notes', 'like', "%$q%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.materials-physical-inventory.index', [
            'items' => $items,
            'q' => $q,
        ]);
    }

    /**
     * Update the physical count and remarks for a material.
     */
    public function update(Request $request, Material $material)
    {
        // Treat blank input as null so validation passes, then default to 0.
        $raw = $request->input('physical_count');
        if ($raw === '' || $raw === null) {
            $request->merge(['physical_count' => null]);
        }

        $data = $request->validate([
            'physical_count' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // Store physical count separately; do not alter system quantity
        $material->physical_count = (float) ($data['physical_count'] ?? 0);
        if (array_key_exists('remarks', $data)) {
            $material->notes = $data['remarks'];
        }
        $material->save();

        return back()->with('success', __('Inventory updated successfully.'));
    }
}