<?php

namespace App\Services;

use App\Repositories\ProductRepositoryInterface;
use App\Repositories\MaterialRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $products,
        protected MaterialRepositoryInterface $materials,
    ) {}

    public function getIndexData(): array
    {
        return [
            'items' => $this->products->paginate(),
            'categories' => $this->products->getUniqueCategories(),
            'metrics' => [
                'total_products' => $this->products->countAll(),
                'active_products' => $this->products->countActive(),
                'inactive_products' => $this->products->countInactive(),
                'total_price' => $this->products->sumPrice(),
                'avg_price' => $this->products->avgPrice(),
            ],
        ];
    }

    public function getCreateData(): array
    {
        return [
            'materials' => $this->materials->all(),
        ];
    }

    public function create(array $data, ?UploadedFile $image = null, ?array $materialIds = null, ?array $quantities = null)
    {
        if ($image && $image->isValid()) {
            $path = $image->store('products', 'public');
            $data['image_path'] = $path;
        }

        $item = $this->products->create($data);

        // Attach materials if provided
        if (is_array($materialIds) && !empty($materialIds)) {
            $q = is_array($quantities) ? $quantities : array_fill_keys($materialIds, 1);
            foreach ($materialIds as $materialId) {
                $quantity = isset($q[$materialId]) ? (float) $q[$materialId] : 1.0;
                if ($quantity <= 0) {
                    $quantity = 1.0;
                }
                $this->products->addMaterial($item->id, (int) $materialId, $quantity);
            }
        }

        return $item->load('materials');
    }

    public function getEditData(string|int $id): array
    {
        $item = $this->products->find($id);
        $item->load('materials');
        return [
            'item' => $item,
            'materials' => $this->materials->all(),
        ];
    }

    public function update(string|int $id, array $data, ?UploadedFile $image = null)
    {
        $item = $this->products->find($id);

        if ($image && $image->isValid()) {
            if ($item && $item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $path = $image->store('products', 'public');
            $data['image_path'] = $path;
        }

        if ($item instanceof \Illuminate\Database\Eloquent\Model) {
            $this->products->update($item, $data);
        } else {
            $this->products->update($id, $data);
        }

        return $this->products->find($id);
    }

    public function destroy(string|int $id): void
    {
        $item = $this->products->find($id);
        if ($item && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        if ($item instanceof \Illuminate\Database\Eloquent\Model) {
            $this->products->delete($item);
        } else {
            $this->products->delete($id);
        }
    }

    public function byCategoryData(?string $category): array
    {
        $categories = $this->products->getUniqueCategories();
        if ($category) {
            $items = $this->products->getByCategory($category);
        } else {
            $items = $this->products->paginate();
        }
        return compact('items', 'categories');
    }

    public function materialsFormData(string $id): array
    {
        $item = $this->products->find($id);
        $item->load('materials');
        $allMaterials = $this->materials->all();
        return [
            'item' => $item,
            'allMaterials' => $allMaterials,
        ];
    }

    public function addMaterials(string $id, array $materialIds, array $quantities)
    {
        $product = null;
        foreach ($quantities as $materialId => $quantity) {
            if (!in_array($materialId, $materialIds)) {
                continue;
            }
            $product = $this->products->addMaterial($id, $materialId, $quantity);
        }
        return $product;
    }

    public function removeMaterial(string $id, string $materialId): void
    {
        $this->products->removeMaterial($id, $materialId);
    }
}